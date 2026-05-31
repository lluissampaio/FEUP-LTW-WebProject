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
     * Returns true if the user has ATTENDED any session of this class: i.e. the user
     * is/was enrolled (or already marked attended) in some schedule of the class whose
     * most recent weekly occurrence (DayOfWeek + StartTime + Duration) is in the past.
     *
     * Attendance is derived rather than stored: a recurring weekly slot counts as
     * attended once that week's occurrence (the latest one on/before now) has fully
     * elapsed. Cancelled enrollments never count.
     */
    public static function hasAttended(PDO $db, int $userId, int $classId, ?DateTimeInterface $now = null) : bool {
      $stmt = $db->prepare("
        SELECT s.DayOfWeek AS DayOfWeek, s.StartTime AS StartTime, s.Duration AS Duration
        FROM Enrollment e JOIN Schedule s ON s.ScheduleId = e.ScheduleId
        WHERE e.UserId = ? AND s.ClassId = ? AND e.Status IN ('enrolled','attended')
      ");
      $stmt->execute(array($userId, $classId));

      $now = $now ?? new DateTimeImmutable('now');
      while ($row = $stmt->fetch()) {
        $end = self::lastOccurrenceEnd((string)$row['DayOfWeek'], (string)$row['StartTime'], (int)$row['Duration'], $now);
        if ($end !== null && $end <= $now) return true;
      }
      return false;
    }

    /**
     * Given a weekly recurring slot, return the end DateTime of its most recent
     * occurrence on or before $now (start time + duration). Returns null if the day
     * name is not recognised.
     */
    private static function lastOccurrenceEnd(string $dayOfWeek, string $startTime, int $duration, DateTimeInterface $now) : ?DateTimeImmutable {
      $dayMap = array(
        'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4,
        'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7,
      );
      if (!isset($dayMap[$dayOfWeek])) return null;

      $h = (int)substr($startTime, 0, 2);
      $m = (int)substr($startTime, 3, 2);

      // Walk back from today to the most recent matching weekday whose start <= now.
      $todayN = (int)$now->format('N');
      $diff   = ($todayN - $dayMap[$dayOfWeek] + 7) % 7; // days back to that weekday (0..6)

      $candidate = (new DateTimeImmutable($now->format('Y-m-d') . ' 00:00:00'))
        ->modify('-' . $diff . ' day')
        ->setTime($h, $m);

      // If the most recent same-weekday slot has not started yet, step back a week.
      if ($candidate > $now) {
        $candidate = $candidate->modify('-7 day');
      }

      return $candidate->modify('+' . $duration . ' minute');
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
