<?php
  declare(strict_types = 1);

  class Enrollment {
    public int    $id;
    public int    $userId;
    public int    $scheduleId;
    public string $status;
    public string $enrolledAt;

    public function __construct(int $id, int $userId, int $scheduleId, string $status, string $enrolledAt) {
      $this->id         = $id;
      $this->userId     = $userId;
      $this->scheduleId = $scheduleId;
      $this->status     = $status;
      $this->enrolledAt = $enrolledAt;
    }

    private static function fromRow(array $r) : Enrollment {
      return new Enrollment(
        (int)$r['EnrollmentId'],
        (int)$r['UserId'],
        (int)$r['ScheduleId'],
        (string)$r['Status'],
        (string)$r['EnrolledAt']
      );
    }

    public static function getEnrollment(PDO $db, int $id) : ?Enrollment {
      $stmt = $db->prepare('SELECT EnrollmentId, UserId, ScheduleId, Status, EnrolledAt FROM Enrollment WHERE EnrollmentId = ?');
      $stmt->execute(array($id));
      $row = $stmt->fetch();
      return $row ? self::fromRow($row) : null;
    }

    public static function find(PDO $db, int $userId, int $scheduleId) : ?Enrollment {
      $stmt = $db->prepare('SELECT EnrollmentId, UserId, ScheduleId, Status, EnrolledAt FROM Enrollment WHERE UserId = ? AND ScheduleId = ?');
      $stmt->execute(array($userId, $scheduleId));
      $row = $stmt->fetch();
      return $row ? self::fromRow($row) : null;
    }

    public static function countEnrolled(PDO $db, int $scheduleId) : int {
      $stmt = $db->prepare("SELECT COUNT(*) AS n FROM Enrollment WHERE ScheduleId = ? AND Status = 'enrolled'");
      $stmt->execute(array($scheduleId));
      return (int)$stmt->fetch()['n'];
    }

    /** Returns true if the user has ever enrolled in or attended any session of this class. */
    public static function hasEnrolledInClass(PDO $db, int $userId, int $classId) : bool {
      $stmt = $db->prepare("
        SELECT 1 FROM Enrollment e JOIN Schedule s ON s.ScheduleId = e.ScheduleId
        WHERE e.UserId = ? AND s.ClassId = ? AND e.Status IN ('enrolled','attended')
      ");
      $stmt->execute(array($userId, $classId));
      return (bool)$stmt->fetch();
    }

    /**
     * Enrol a user into a schedule, respecting capacity. Throws on full / duplicate.
     */
    public static function enrol(PDO $db, int $userId, int $scheduleId) : Enrollment {
      $db->beginTransaction();
      try {
        $stmt = $db->prepare('
          SELECT c.Capacity,
                 (SELECT COUNT(*) FROM Enrollment e WHERE e.ScheduleId = s.ScheduleId AND e.Status = ?) AS Enrolled
          FROM Schedule s JOIN Class c ON c.ClassId = s.ClassId
          WHERE s.ScheduleId = ?
        ');
        $stmt->execute(array('enrolled', $scheduleId));
        $row = $stmt->fetch();
        if (!$row) throw new RuntimeException('Schedule not found.');
        if ((int)$row['Enrolled'] >= (int)$row['Capacity']) throw new RuntimeException('Class is full.');

        $existing = self::find($db, $userId, $scheduleId);
        if ($existing !== null && $existing->status === 'enrolled') {
          throw new RuntimeException('Already enrolled.');
        }

        if ($existing !== null) {
          $stmt = $db->prepare("UPDATE Enrollment SET Status = 'enrolled', EnrolledAt = CURRENT_TIMESTAMP WHERE EnrollmentId = ?");
          $stmt->execute(array($existing->id));
          $id = $existing->id;
        } else {
          $stmt = $db->prepare("INSERT INTO Enrollment (UserId, ScheduleId, Status) VALUES (?, ?, 'enrolled')");
          $stmt->execute(array($userId, $scheduleId));
          $id = (int)$db->lastInsertId();
        }

        $db->commit();
        return self::getEnrollment($db, $id);
      } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
      }
    }

    public static function cancel(PDO $db, int $userId, int $scheduleId) : bool {
      $stmt = $db->prepare("UPDATE Enrollment SET Status = 'cancelled' WHERE UserId = ? AND ScheduleId = ? AND Status = 'enrolled'");
      $stmt->execute(array($userId, $scheduleId));
      return $stmt->rowCount() > 0;
    }

    /**
     * Try to auto-promote the first waitlisted user into a freed slot.
     * Returns the promoted UserId or null if the waitlist is empty / class is full.
     */
    public static function promoteFromWaitlist(PDO $db, int $scheduleId) : ?int {
      require_once(__DIR__ . '/waitlist.class.php');

      $stmt = $db->prepare("
        SELECT c.Capacity,
               (SELECT COUNT(*) FROM Enrollment e WHERE e.ScheduleId = s.ScheduleId AND e.Status = 'enrolled') AS Enrolled
        FROM Schedule s JOIN Class c ON c.ClassId = s.ClassId
        WHERE s.ScheduleId = ?
      ");
      $stmt->execute(array($scheduleId));
      $row = $stmt->fetch();
      if (!$row) return null;
      if ((int)$row['Enrolled'] >= (int)$row['Capacity']) return null;

      $next = Waitlist::nextForSchedule($db, $scheduleId);
      if ($next === null) return null;

      $db->beginTransaction();
      try {
        $existing = self::find($db, $next->userId, $scheduleId);
        if ($existing) {
          $stmt = $db->prepare("UPDATE Enrollment SET Status = 'enrolled', EnrolledAt = CURRENT_TIMESTAMP WHERE EnrollmentId = ?");
          $stmt->execute(array($existing->id));
        } else {
          $stmt = $db->prepare("INSERT INTO Enrollment (UserId, ScheduleId, Status) VALUES (?, ?, 'enrolled')");
          $stmt->execute(array($next->userId, $scheduleId));
        }
        Waitlist::markPromoted($db, $next->id);
        $db->commit();
        return $next->userId;
      } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
      }
    }

    public static function getForUser(PDO $db, int $userId, ?string $status = 'enrolled') : array {
      $sql = '
        SELECT EnrollmentId, UserId, ScheduleId, Status, EnrolledAt
        FROM Enrollment WHERE UserId = ?
      ';
      $params = array($userId);
      if ($status !== null) {
        $sql .= ' AND Status = ?';
        $params[] = $status;
      }
      $sql .= ' ORDER BY EnrolledAt DESC';
      $stmt = $db->prepare($sql);
      $stmt->execute($params);
      $items = array();
      while ($row = $stmt->fetch()) $items[] = self::fromRow($row);
      return $items;
    }

    public static function getRosterForSchedule(PDO $db, int $scheduleId) : array {
      $stmt = $db->prepare("
        SELECT u.UserId, u.FirstName, u.LastName, u.Email, e.EnrolledAt
        FROM Enrollment e JOIN User u ON u.UserId = e.UserId
        WHERE e.ScheduleId = ? AND e.Status = 'enrolled'
        ORDER BY e.EnrolledAt
      ");
      $stmt->execute(array($scheduleId));
      $rows = array();
      while ($row = $stmt->fetch()) $rows[] = $row;
      return $rows;
    }
  }
?>
