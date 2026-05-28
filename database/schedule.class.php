<?php
  declare(strict_types = 1);

  class Schedule {
    public int     $id;
    public int     $classId;
    public ?string $className;
    public ?string $classType;
    public ?int    $trainerId;
    public ?string $trainerName;
    public ?int    $locationId;
    public ?string $locationName;
    public ?int    $capacity;
    public string  $dayOfWeek;
    public string  $startTime;
    public int     $duration;
    public ?int    $enrolledCount;

    public function __construct(int $id, int $classId, ?string $className, ?string $classType, ?int $trainerId, ?string $trainerName, ?int $locationId, ?string $locationName, ?int $capacity, string $dayOfWeek, string $startTime, int $duration, ?int $enrolledCount = null) {
      $this->id            = $id;
      $this->classId       = $classId;
      $this->className     = $className;
      $this->classType     = $classType;
      $this->trainerId     = $trainerId;
      $this->trainerName   = $trainerName;
      $this->locationId    = $locationId;
      $this->locationName  = $locationName;
      $this->capacity      = $capacity;
      $this->dayOfWeek     = $dayOfWeek;
      $this->startTime     = $startTime;
      $this->duration      = $duration;
      $this->enrolledCount = $enrolledCount;
    }

    public function isFull() : bool {
      return $this->capacity !== null && $this->enrolledCount !== null && $this->enrolledCount >= $this->capacity;
    }

    private static function fromRow(array $r) : Schedule {
      return new Schedule(
        (int)$r['ScheduleId'],
        (int)$r['ClassId'],
        isset($r['ClassName'])    ? (string)$r['ClassName']    : null,
        isset($r['ClassType'])    ? (string)$r['ClassType']    : null,
        isset($r['TrainerId'])    ? (int)   $r['TrainerId']    : null,
        isset($r['TrainerName'])  ? (string)$r['TrainerName']  : null,
        isset($r['LocationId'])   ? (int)   $r['LocationId']   : null,
        isset($r['LocationName']) ? (string)$r['LocationName'] : null,
        isset($r['Capacity'])     ? (int)   $r['Capacity']     : null,
        (string)$r['DayOfWeek'],
        (string)$r['StartTime'],
        (int)$r['Duration'],
        isset($r['EnrolledCount']) ? (int)$r['EnrolledCount'] : null
      );
    }

    private const SELECT_FULL = "
      SELECT s.ScheduleId, s.ClassId, s.DayOfWeek, s.StartTime, s.Duration,
             c.Name AS ClassName, c.Type AS ClassType, c.Capacity,
             c.TrainerId, (u.FirstName || ' ' || u.LastName) AS TrainerName,
             c.LocationId, l.Name AS LocationName,
             (SELECT COUNT(*) FROM Enrollment e WHERE e.ScheduleId = s.ScheduleId AND e.Status = 'enrolled') AS EnrolledCount
      FROM Schedule s
      JOIN Class    c ON c.ClassId    = s.ClassId
      JOIN Trainer  t ON t.TrainerId  = c.TrainerId
      JOIN User     u ON u.UserId     = t.TrainerId
      JOIN Location l ON l.LocationId = c.LocationId
    ";

    private const DAY_ORDER = "
      CASE s.DayOfWeek
        WHEN 'Monday' THEN 1 WHEN 'Tuesday' THEN 2 WHEN 'Wednesday' THEN 3
        WHEN 'Thursday' THEN 4 WHEN 'Friday' THEN 5 WHEN 'Saturday' THEN 6
        WHEN 'Sunday' THEN 7 END
    ";

    public static function getSchedule(PDO $db, int $id) : ?Schedule {
      $stmt = $db->prepare(self::SELECT_FULL . ' WHERE s.ScheduleId = ?');
      $stmt->execute(array($id));
      $row = $stmt->fetch();
      return $row ? self::fromRow($row) : null;
    }

    public static function getAll(PDO $db) : array {
      $stmt = $db->prepare(self::SELECT_FULL . ' ORDER BY ' . self::DAY_ORDER . ', s.StartTime');
      $stmt->execute();
      $items = array();
      while ($row = $stmt->fetch()) $items[] = self::fromRow($row);
      return $items;
    }

    public static function search(PDO $db, ?string $type = null, ?int $trainerId = null, ?int $locationId = null, ?string $day = null) : array {
      $sql = self::SELECT_FULL . ' WHERE 1=1';
      $params = array();
      if ($type !== null && $type !== '')      { $sql .= ' AND c.Type = ?';       $params[] = $type; }
      if ($trainerId !== null)                 { $sql .= ' AND c.TrainerId = ?';  $params[] = $trainerId; }
      if ($locationId !== null)                { $sql .= ' AND c.LocationId = ?'; $params[] = $locationId; }
      if ($day !== null && $day !== '')        { $sql .= ' AND s.DayOfWeek = ?';  $params[] = $day; }
      $sql .= ' ORDER BY ' . self::DAY_ORDER . ', s.StartTime';
      $stmt = $db->prepare($sql);
      $stmt->execute($params);
      $items = array();
      while ($row = $stmt->fetch()) $items[] = self::fromRow($row);
      return $items;
    }

    public static function getByClass(PDO $db, int $classId) : array {
      $stmt = $db->prepare(self::SELECT_FULL . ' WHERE s.ClassId = ? ORDER BY ' . self::DAY_ORDER . ', s.StartTime');
      $stmt->execute(array($classId));
      $items = array();
      while ($row = $stmt->fetch()) $items[] = self::fromRow($row);
      return $items;
    }

    public static function getSessionCountsPerClass(PDO $db) : array {
      $counts = [];
      $stmt = $db->query('SELECT ClassId, COUNT(*) AS n FROM Schedule GROUP BY ClassId');
      while ($row = $stmt->fetch()) $counts[(int)$row['ClassId']] = (int)$row['n'];
      return $counts;
    }

    public static function create(PDO $db, int $classId, string $dayOfWeek, string $startTime, int $duration) : int {
      $stmt = $db->prepare('INSERT INTO Schedule (ClassId, DayOfWeek, StartTime, Duration) VALUES (?, ?, ?, ?)');
      $stmt->execute(array($classId, $dayOfWeek, $startTime, $duration));
      return (int)$db->lastInsertId();
    }

    public static function delete(PDO $db, int $id) : void {
      $stmt = $db->prepare('DELETE FROM Schedule WHERE ScheduleId = ?');
      $stmt->execute(array($id));
    }

    /**
     * Keep only sessions whose start time falls in the given bucket.
     * $time is one of 'morning' (<12:00), 'afternoon' (12:00–17:00), 'evening' (>=17:00).
     * Any other value returns the list unchanged.
     */
    public static function filterByTimeBucket(array $schedules, string $time) : array {
      if ($time === '') return $schedules;
      return array_values(array_filter($schedules, function (Schedule $s) use ($time) {
        $h = (int)substr($s->startTime, 0, 2);
        if ($time === 'morning')   return $h < 12;
        if ($time === 'afternoon') return $h >= 12 && $h < 17;
        if ($time === 'evening')   return $h >= 17;
        return true;
      }));
    }

    /**
     * Sort sessions by their next weekly occurrence, so slots already past for
     * the current week sink to the bottom (they reappear next week). Sorts in place.
     */
    public static function sortByNextOccurrence(array $schedules) : array {
      $dayMap = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7];
      $now    = new DateTime('now');
      $todayN = (int)$now->format('N');
      $nowH   = (int)$now->format('G');
      $nowM   = (int)$now->format('i');
      usort($schedules, function (Schedule $a, Schedule $b) use ($dayMap, $todayN, $nowH, $nowM) {
        $score = function (Schedule $s) use ($dayMap, $todayN, $nowH, $nowM) {
          $sd  = $dayMap[$s->dayOfWeek] ?? $todayN;
          $h   = (int)substr($s->startTime, 0, 2);
          $m   = (int)substr($s->startTime, 3, 2);
          $off = $sd - $todayN;
          if ($off < 0 || ($off === 0 && ($h < $nowH || ($h === $nowH && $m <= $nowM)))) $off += 7;
          return $off * 1440 + $h * 60 + $m;
        };
        return $score($a) <=> $score($b);
      });
      return $schedules;
    }
  }
?>
