<?php
  declare(strict_types = 1);

  /**
   * Equipment reservations.
   *
   * A member reserves a single piece of equipment for a concrete date + time
   * slot. The (EquipmentId, UserId, ReservationDate, StartTime) tuple is unique
   * while the reservation is 'reserved', so a member cannot double-book the same
   * slot. Overall per-slot capacity (number of active reservations for an
   * equipment+date+slot vs Equipment.Quantity) is NOT a database constraint —
   * it is enforced here in application code, inside the create() transaction.
   */
  class EquipmentReservation {
    public int     $id;
    public int     $equipmentId;
    public int     $userId;
    public string  $reservationDate;
    public string  $startTime;
    public int     $duration;
    public string  $status;
    public string  $createdAt;

    // Joined display fields (populated by the listing queries).
    public ?string $equipmentName = null;
    public ?string $equipmentType = null;
    public ?string $locationName  = null;

    public function __construct(int $id, int $equipmentId, int $userId, string $reservationDate, string $startTime, int $duration, string $status, string $createdAt) {
      $this->id              = $id;
      $this->equipmentId     = $equipmentId;
      $this->userId          = $userId;
      $this->reservationDate = $reservationDate;
      $this->startTime       = $startTime;
      $this->duration        = $duration;
      $this->status          = $status;
      $this->createdAt       = $createdAt;
    }

    private static function fromRow(array $r) : EquipmentReservation {
      $res = new EquipmentReservation(
        (int)$r['ReservationId'],
        (int)$r['EquipmentId'],
        (int)$r['UserId'],
        (string)$r['ReservationDate'],
        (string)$r['StartTime'],
        (int)$r['Duration'],
        (string)$r['Status'],
        (string)$r['CreatedAt']
      );
      $res->equipmentName = isset($r['EquipmentName']) ? (string)$r['EquipmentName'] : null;
      $res->equipmentType = isset($r['EquipmentType']) ? (string)$r['EquipmentType'] : null;
      $res->locationName  = isset($r['LocationName'])  ? (string)$r['LocationName']  : null;
      return $res;
    }

    /**
     * How many active ('reserved') reservations already exist for a given
     * equipment item on a specific date + start time. Used to enforce capacity.
     */
    public static function countActiveForSlot(PDO $db, int $equipmentId, string $date, string $startTime) : int {
      $stmt = $db->prepare("
        SELECT COUNT(*) FROM EquipmentReservation
        WHERE EquipmentId = ? AND ReservationDate = ? AND StartTime = ? AND Status = 'reserved'
      ");
      $stmt->execute(array($equipmentId, $date, $startTime));
      return (int)$stmt->fetchColumn();
    }

    /**
     * Reserve a slot. Validates the date is not in the past, the equipment
     * exists, the member has no existing active reservation for the same slot,
     * and that the slot still has capacity (active reservations < Quantity).
     * Throws RuntimeException with a member-facing message on any failure.
     */
    public static function create(PDO $db, int $userId, int $equipmentId, string $date, string $startTime, int $duration) : EquipmentReservation {
      // Validate date format and that it is today or in the future.
      $today = date('Y-m-d');
      if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        throw new RuntimeException('Please choose a valid reservation date.');
      }
      if ($date < $today) {
        throw new RuntimeException('You cannot reserve equipment for a date in the past.');
      }
      if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $startTime)) {
        throw new RuntimeException('Please choose a valid time slot.');
      }
      if ($duration < 15 || $duration > 240) {
        throw new RuntimeException('Duration must be between 15 and 240 minutes.');
      }

      $db->beginTransaction();
      try {
        $stmt = $db->prepare('SELECT EquipmentId, Quantity FROM Equipment WHERE EquipmentId = ?');
        $stmt->execute(array($equipmentId));
        $equip = $stmt->fetch();
        if (!$equip) {
          throw new RuntimeException('That piece of equipment no longer exists.');
        }

        // Already holds an active reservation for this exact slot?
        $stmt = $db->prepare("
          SELECT 1 FROM EquipmentReservation
          WHERE EquipmentId = ? AND UserId = ? AND ReservationDate = ? AND StartTime = ? AND Status = 'reserved'
        ");
        $stmt->execute(array($equipmentId, $userId, $date, $startTime));
        if ($stmt->fetch()) {
          throw new RuntimeException('You already have a reservation for that equipment, date and time.');
        }

        // Capacity check: active reservations for this slot must stay below the
        // equipment's total quantity.
        $active   = self::countActiveForSlot($db, $equipmentId, $date, $startTime);
        $quantity = (int)$equip['Quantity'];
        if ($active >= $quantity) {
          throw new RuntimeException('That time slot is fully booked. Please choose another time.');
        }

        $stmt = $db->prepare("
          INSERT INTO EquipmentReservation (EquipmentId, UserId, ReservationDate, StartTime, Duration, Status)
          VALUES (?, ?, ?, ?, ?, 'reserved')
        ");
        $stmt->execute(array($equipmentId, $userId, $date, $startTime, $duration));
        $id = (int)$db->lastInsertId();
        $db->commit();
        return self::getReservation($db, $id);
      } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
      }
    }

    public static function getReservation(PDO $db, int $id) : ?EquipmentReservation {
      $stmt = $db->prepare('
        SELECT ReservationId, EquipmentId, UserId, ReservationDate, StartTime, Duration, Status, CreatedAt
        FROM EquipmentReservation WHERE ReservationId = ?
      ');
      $stmt->execute(array($id));
      $row = $stmt->fetch();
      return $row ? self::fromRow($row) : null;
    }

    /**
     * Cancel a reservation. Owner-checked: only the member who created it (and
     * while it is still 'reserved') can cancel. Returns true if a row changed.
     */
    public static function cancel(PDO $db, int $reservationId, int $userId) : bool {
      $stmt = $db->prepare("
        UPDATE EquipmentReservation SET Status = 'cancelled'
        WHERE ReservationId = ? AND UserId = ? AND Status = 'reserved'
      ");
      $stmt->execute(array($reservationId, $userId));
      return $stmt->rowCount() > 0;
    }

    /**
     * Active reservations for a member, with equipment + location detail,
     * ordered chronologically.
     */
    public static function getForUser(PDO $db, int $userId) : array {
      $stmt = $db->prepare("
        SELECT r.ReservationId, r.EquipmentId, r.UserId, r.ReservationDate, r.StartTime,
               r.Duration, r.Status, r.CreatedAt,
               e.Name AS EquipmentName, e.Type AS EquipmentType, l.Name AS LocationName
        FROM EquipmentReservation r
        JOIN Equipment e ON e.EquipmentId = r.EquipmentId
        LEFT JOIN Location l ON l.LocationId = e.LocationId
        WHERE r.UserId = ? AND r.Status = 'reserved'
        ORDER BY r.ReservationDate, r.StartTime
      ");
      $stmt->execute(array($userId));
      $items = array();
      while ($row = $stmt->fetch()) $items[] = self::fromRow($row);
      return $items;
    }
  }
?>
