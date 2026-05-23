<?php
  declare(strict_types = 1);

  class Waitlist {
    public int    $id;
    public int    $userId;
    public int    $scheduleId;
    public string $status;
    public string $joinedAt;

    public function __construct(int $id, int $userId, int $scheduleId, string $status, string $joinedAt) {
      $this->id         = $id;
      $this->userId     = $userId;
      $this->scheduleId = $scheduleId;
      $this->status     = $status;
      $this->joinedAt   = $joinedAt;
    }

    private static function fromRow(array $r) : Waitlist {
      return new Waitlist(
        (int)$r['WaitlistId'],
        (int)$r['UserId'],
        (int)$r['ScheduleId'],
        (string)$r['Status'],
        (string)$r['JoinedAt']
      );
    }

    public static function find(PDO $db, int $userId, int $scheduleId) : ?Waitlist {
      $stmt = $db->prepare('SELECT WaitlistId, UserId, ScheduleId, Status, JoinedAt FROM Waitlist WHERE UserId = ? AND ScheduleId = ?');
      $stmt->execute(array($userId, $scheduleId));
      $row = $stmt->fetch();
      return $row ? self::fromRow($row) : null;
    }

    public static function join(PDO $db, int $userId, int $scheduleId) : Waitlist {
      $existing = self::find($db, $userId, $scheduleId);
      if ($existing && $existing->status === 'waiting') {
        throw new RuntimeException('Already on the waitlist.');
      }
      if ($existing) {
        $stmt = $db->prepare("UPDATE Waitlist SET Status = 'waiting', JoinedAt = CURRENT_TIMESTAMP WHERE WaitlistId = ?");
        $stmt->execute(array($existing->id));
        return self::find($db, $userId, $scheduleId);
      }
      $stmt = $db->prepare("INSERT INTO Waitlist (UserId, ScheduleId, Status) VALUES (?, ?, 'waiting')");
      $stmt->execute(array($userId, $scheduleId));
      return self::find($db, $userId, $scheduleId);
    }

    public static function leave(PDO $db, int $userId, int $scheduleId) : bool {
      $stmt = $db->prepare("UPDATE Waitlist SET Status = 'cancelled' WHERE UserId = ? AND ScheduleId = ? AND Status = 'waiting'");
      $stmt->execute(array($userId, $scheduleId));
      return $stmt->rowCount() > 0;
    }

    public static function nextForSchedule(PDO $db, int $scheduleId) : ?Waitlist {
      $stmt = $db->prepare("
        SELECT WaitlistId, UserId, ScheduleId, Status, JoinedAt
        FROM Waitlist WHERE ScheduleId = ? AND Status = 'waiting'
        ORDER BY JoinedAt ASC LIMIT 1
      ");
      $stmt->execute(array($scheduleId));
      $row = $stmt->fetch();
      return $row ? self::fromRow($row) : null;
    }

    public static function position(PDO $db, int $userId, int $scheduleId) : ?int {
      $stmt = $db->prepare("
        SELECT COUNT(*) AS n FROM Waitlist
        WHERE ScheduleId = ? AND Status = 'waiting' AND JoinedAt <= (
          SELECT JoinedAt FROM Waitlist WHERE UserId = ? AND ScheduleId = ? AND Status = 'waiting'
        )
      ");
      $stmt->execute(array($scheduleId, $userId, $scheduleId));
      $row = $stmt->fetch();
      return $row && $row['n'] > 0 ? (int)$row['n'] : null;
    }

    public static function markPromoted(PDO $db, int $waitlistId) : void {
      $stmt = $db->prepare("UPDATE Waitlist SET Status = 'promoted' WHERE WaitlistId = ?");
      $stmt->execute(array($waitlistId));
    }

    public static function getForUser(PDO $db, int $userId) : array {
      $stmt = $db->prepare("
        SELECT WaitlistId, UserId, ScheduleId, Status, JoinedAt
        FROM Waitlist WHERE UserId = ? AND Status = 'waiting'
        ORDER BY JoinedAt DESC
      ");
      $stmt->execute(array($userId));
      $items = array();
      while ($row = $stmt->fetch()) $items[] = self::fromRow($row);
      return $items;
    }
  }
?>
