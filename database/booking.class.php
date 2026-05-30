<?php
  declare(strict_types = 1);

  /**
   * Personal-training bookings.
   *
   * Trainers publish recurring weekly availability (TrainerAvailability).
   * A member books a single upcoming occurrence of a slot — identified by the
   * availability row plus a concrete date. The (AvailabilityId, BookingDate)
   * pair is unique, so a slot on a given day can only be taken once.
   */
  class Booking {
    public int     $id;
    public int     $userId;
    public int     $trainerId;
    public int     $availabilityId;
    public string  $bookingDate;
    public string  $status;
    public ?string $notes;
    public string  $createdAt;

    // Joined display fields (populated by the listing queries).
    public ?string $trainerName = null;
    public ?string $memberName  = null;
    public ?string $dayOfWeek   = null;
    public ?string $startTime   = null;
    public ?int    $duration    = null;

    public function __construct(int $id, int $userId, int $trainerId, int $availabilityId, string $bookingDate, string $status, ?string $notes, string $createdAt) {
      $this->id             = $id;
      $this->userId         = $userId;
      $this->trainerId      = $trainerId;
      $this->availabilityId = $availabilityId;
      $this->bookingDate    = $bookingDate;
      $this->status         = $status;
      $this->notes          = $notes;
      $this->createdAt      = $createdAt;
    }

    /** Map a weekday name to PHP's date('N') number (Monday=1 .. Sunday=7). */
    private static function dayNumber(string $day) : int {
      $map = ['Monday'=>1,'Tuesday'=>2,'Wednesday'=>3,'Thursday'=>4,'Friday'=>5,'Saturday'=>6,'Sunday'=>7];
      return $map[$day] ?? 0;
    }

    /** The next date (YYYY-MM-DD) on/after today that falls on $day. */
    public static function nextDateFor(string $day) : string {
      $target = self::dayNumber($day);
      if ($target === 0) return date('Y-m-d');
      $today = (int)date('N');
      $delta = ($target - $today + 7) % 7;   // 0..6 ; today counts as this week
      return date('Y-m-d', strtotime("+{$delta} day"));
    }

    /**
     * Availability slots for a trainer, each annotated with its next occurrence
     * date and whether that occurrence is already booked.
     */
    public static function getAvailabilityForTrainer(PDO $db, int $trainerId) : array {
      $stmt = $db->prepare('
        SELECT AvailabilityId, TrainerId, DayOfWeek, StartTime, Duration
        FROM TrainerAvailability WHERE TrainerId = ?
        ORDER BY CASE DayOfWeek
          WHEN \'Monday\' THEN 1 WHEN \'Tuesday\' THEN 2 WHEN \'Wednesday\' THEN 3
          WHEN \'Thursday\' THEN 4 WHEN \'Friday\' THEN 5 WHEN \'Saturday\' THEN 6
          ELSE 7 END, StartTime
      ');
      $stmt->execute(array($trainerId));
      $slots = array();
      while ($row = $stmt->fetch()) {
        $nextDate = self::nextDateFor((string)$row['DayOfWeek']);
        $row['NextDate']  = $nextDate;
        $row['IsBooked']  = self::slotTaken($db, (int)$row['AvailabilityId'], $nextDate);
        $slots[] = $row;
      }
      return $slots;
    }

    public static function slotTaken(PDO $db, int $availabilityId, string $date) : bool {
      $stmt = $db->prepare("SELECT 1 FROM Booking WHERE AvailabilityId = ? AND BookingDate = ? AND Status = 'booked'");
      $stmt->execute(array($availabilityId, $date));
      return (bool)$stmt->fetch();
    }

    /** Count how many sessions a user has booked in a specific year-month (YYYY-MM). */
    public static function countSessionsInMonth(PDO $db, int $userId, string $yearMonth) : int {
      $stmt = $db->prepare("
        SELECT COUNT(*) FROM Booking
        WHERE UserId = ? AND Status = 'booked' AND BookingDate LIKE ?
      ");
      $stmt->execute(array($userId, $yearMonth . '-%'));
      return (int)$stmt->fetchColumn();
    }

    /** Book an availability slot for a concrete date. Throws on conflict. */
    public static function book(PDO $db, int $userId, int $availabilityId, ?string $notes = null) : Booking {
      $db->beginTransaction();
      try {
        $stmt = $db->prepare('SELECT AvailabilityId, TrainerId, DayOfWeek FROM TrainerAvailability WHERE AvailabilityId = ?');
        $stmt->execute(array($availabilityId));
        $slot = $stmt->fetch();
        if (!$slot) throw new RuntimeException('That availability slot no longer exists.');

        $date = self::nextDateFor((string)$slot['DayOfWeek']);
        if (self::slotTaken($db, $availabilityId, $date)) {
          throw new RuntimeException('That slot has just been booked by someone else.');
        }

        $stmt = $db->prepare("
          INSERT INTO Booking (UserId, TrainerId, AvailabilityId, BookingDate, Status, Notes)
          VALUES (?, ?, ?, ?, 'booked', ?)
        ");
        $stmt->execute(array($userId, (int)$slot['TrainerId'], $availabilityId, $date, $notes));
        $id = (int)$db->lastInsertId();
        $db->commit();
        return self::getBooking($db, $id);
      } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
      }
    }

    public static function getBooking(PDO $db, int $id) : ?Booking {
      $stmt = $db->prepare('SELECT BookingId, UserId, TrainerId, AvailabilityId, BookingDate, Status, Notes, CreatedAt FROM Booking WHERE BookingId = ?');
      $stmt->execute(array($id));
      $row = $stmt->fetch();
      if (!$row) return null;
      return new Booking(
        (int)$row['BookingId'], (int)$row['UserId'], (int)$row['TrainerId'],
        (int)$row['AvailabilityId'], (string)$row['BookingDate'], (string)$row['Status'],
        $row['Notes'], (string)$row['CreatedAt']
      );
    }

    /** Cancel a booking; only the member who owns it may cancel. */
    public static function cancel(PDO $db, int $bookingId, int $userId) : bool {
      $stmt = $db->prepare("UPDATE Booking SET Status = 'cancelled' WHERE BookingId = ? AND UserId = ? AND Status = 'booked'");
      $stmt->execute(array($bookingId, $userId));
      return $stmt->rowCount() > 0;
    }

    /** Upcoming bookings for a member, with trainer name + slot details. */
    public static function getForUser(PDO $db, int $userId) : array {
      $stmt = $db->prepare("
        SELECT b.BookingId, b.UserId, b.TrainerId, b.AvailabilityId, b.BookingDate, b.Status, b.Notes, b.CreatedAt,
               a.DayOfWeek, a.StartTime, a.Duration,
               u.FirstName || ' ' || u.LastName AS TrainerName
        FROM Booking b
        JOIN TrainerAvailability a ON a.AvailabilityId = b.AvailabilityId
        JOIN User u ON u.UserId = b.TrainerId
        WHERE b.UserId = ? AND b.Status = 'booked'
        ORDER BY b.BookingDate, a.StartTime
      ");
      $stmt->execute(array($userId));
      return self::hydrate($stmt);
    }

    /** Booking roster for a trainer (who has booked them). */
    public static function getForTrainer(PDO $db, int $trainerId) : array {
      $stmt = $db->prepare("
        SELECT b.BookingId, b.UserId, b.TrainerId, b.AvailabilityId, b.BookingDate, b.Status, b.Notes, b.CreatedAt,
               a.DayOfWeek, a.StartTime, a.Duration,
               u.FirstName || ' ' || u.LastName AS MemberName
        FROM Booking b
        JOIN TrainerAvailability a ON a.AvailabilityId = b.AvailabilityId
        JOIN User u ON u.UserId = b.UserId
        WHERE b.TrainerId = ? AND b.Status = 'booked'
        ORDER BY b.BookingDate, a.StartTime
      ");
      $stmt->execute(array($trainerId));
      return self::hydrate($stmt);
    }

    private static function hydrate(PDOStatement $stmt) : array {
      $out = array();
      while ($row = $stmt->fetch()) {
        $b = new Booking(
          (int)$row['BookingId'], (int)$row['UserId'], (int)$row['TrainerId'],
          (int)$row['AvailabilityId'], (string)$row['BookingDate'], (string)$row['Status'],
          $row['Notes'], (string)$row['CreatedAt']
        );
        $b->dayOfWeek   = $row['DayOfWeek']  ?? null;
        $b->startTime   = $row['StartTime']  ?? null;
        $b->duration    = isset($row['Duration']) ? (int)$row['Duration'] : null;
        $b->trainerName = $row['TrainerName'] ?? null;
        $b->memberName  = $row['MemberName']  ?? null;
        $out[] = $b;
      }
      return $out;
    }
  }
?>
