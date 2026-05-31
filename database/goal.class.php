<?php
  declare(strict_types = 1);

  // Model over the FitnessGoal table. All mutations are owner-checked to prevent
  // IDOR: update/delete only ever touch rows that belong to the supplied userId.
  class Goal {
    public int     $id;
    public int     $userId;
    public string  $title;
    public string  $metric;
    public float   $targetValue;
    public float   $currentValue;
    public ?string $unit;
    public ?string $deadline;
    public string  $status;
    public ?string $createdAt;

    // Allowed metric values, mirroring the schema's documented set.
    public const METRICS = array('weight', 'workouts_per_week', 'calories', 'duration', 'custom');

    public function __construct(
      int $id, int $userId, string $title, string $metric, float $targetValue,
      float $currentValue, ?string $unit, ?string $deadline, string $status, ?string $createdAt
    ) {
      $this->id           = $id;
      $this->userId       = $userId;
      $this->title        = $title;
      $this->metric       = $metric;
      $this->targetValue  = $targetValue;
      $this->currentValue = $currentValue;
      $this->unit         = $unit;
      $this->deadline     = $deadline;
      $this->status       = $status;
      $this->createdAt    = $createdAt;
    }

    private static function fromRow(array $r) : Goal {
      return new Goal(
        (int)$r['GoalId'],
        (int)$r['UserId'],
        (string)$r['Title'],
        (string)$r['Metric'],
        (float)$r['TargetValue'],
        (float)$r['CurrentValue'],
        isset($r['Unit'])     ? (string)$r['Unit']     : null,
        isset($r['Deadline']) ? (string)$r['Deadline'] : null,
        (string)$r['Status'],
        isset($r['CreatedAt']) ? (string)$r['CreatedAt'] : null
      );
    }

    // Fraction of the target reached, clamped to 0..1. Returns 1.0 when the goal
    // has been achieved and 0.0 when the target is non-positive.
    public function progress() : float {
      if ($this->targetValue <= 0) return 0.0;
      $ratio = $this->currentValue / $this->targetValue;
      if ($ratio < 0)   return 0.0;
      if ($ratio > 1)   return 1.0;
      return $ratio;
    }

    public function progressPercent() : int {
      return (int)round($this->progress() * 100);
    }

    private static function normaliseMetric(string $metric) : string {
      return in_array($metric, self::METRICS, true) ? $metric : 'custom';
    }

    // Derive the stored status from the values: a goal whose current value has
    // reached its target is auto-marked 'achieved'. An 'abandoned' goal is left
    // untouched. Otherwise it is 'active'.
    private static function deriveStatus(float $current, float $target, string $requested) : string {
      if ($requested === 'abandoned') return 'abandoned';
      if ($target > 0 && $current >= $target) return 'achieved';
      return 'active';
    }

    public static function getForUser(PDO $db, int $userId) : array {
      $stmt = $db->prepare('
        SELECT GoalId, UserId, Title, Metric, TargetValue, CurrentValue, Unit, Deadline, Status, CreatedAt
        FROM FitnessGoal
        WHERE UserId = ?
        ORDER BY (Status = \'active\') DESC, CreatedAt DESC, GoalId DESC
      ');
      $stmt->execute(array($userId));
      $items = array();
      while ($row = $stmt->fetch()) $items[] = self::fromRow($row);
      return $items;
    }

    // Fetch a single goal that belongs to the given user. Returns null when the
    // goal does not exist or is owned by someone else (no IDOR leak).
    public static function getOwned(PDO $db, int $goalId, int $userId) : ?Goal {
      $stmt = $db->prepare('
        SELECT GoalId, UserId, Title, Metric, TargetValue, CurrentValue, Unit, Deadline, Status, CreatedAt
        FROM FitnessGoal
        WHERE GoalId = ? AND UserId = ?
      ');
      $stmt->execute(array($goalId, $userId));
      $row = $stmt->fetch();
      return $row ? self::fromRow($row) : null;
    }

    public static function create(
      PDO $db, int $userId, string $title, string $metric, float $targetValue,
      float $currentValue, ?string $unit, ?string $deadline, string $requestedStatus = 'active'
    ) : int {
      $metric = self::normaliseMetric($metric);
      $status = self::deriveStatus($currentValue, $targetValue, $requestedStatus);
      $stmt = $db->prepare('
        INSERT INTO FitnessGoal (UserId, Title, Metric, TargetValue, CurrentValue, Unit, Deadline, Status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
      ');
      $stmt->execute(array(
        $userId, $title, $metric, $targetValue, $currentValue,
        ($unit === null || $unit === '') ? null : $unit,
        ($deadline === null || $deadline === '') ? null : $deadline,
        $status,
      ));
      return (int)$db->lastInsertId();
    }

    // Owner-checked update: the WHERE clause pins both GoalId and UserId, so a
    // member can never modify another member's goal. Returns true when a row
    // was actually updated.
    public static function update(
      PDO $db, int $goalId, int $userId, string $title, string $metric, float $targetValue,
      float $currentValue, ?string $unit, ?string $deadline, string $requestedStatus = 'active'
    ) : bool {
      $metric = self::normaliseMetric($metric);
      $status = self::deriveStatus($currentValue, $targetValue, $requestedStatus);
      $stmt = $db->prepare('
        UPDATE FitnessGoal
        SET Title = ?, Metric = ?, TargetValue = ?, CurrentValue = ?, Unit = ?, Deadline = ?, Status = ?
        WHERE GoalId = ? AND UserId = ?
      ');
      $stmt->execute(array(
        $title, $metric, $targetValue, $currentValue,
        ($unit === null || $unit === '') ? null : $unit,
        ($deadline === null || $deadline === '') ? null : $deadline,
        $status, $goalId, $userId,
      ));
      return $stmt->rowCount() > 0;
    }

    // Owner-checked delete. Returns true when a row was removed.
    public static function delete(PDO $db, int $goalId, int $userId) : bool {
      $stmt = $db->prepare('DELETE FROM FitnessGoal WHERE GoalId = ? AND UserId = ?');
      $stmt->execute(array($goalId, $userId));
      return $stmt->rowCount() > 0;
    }
  }
?>
