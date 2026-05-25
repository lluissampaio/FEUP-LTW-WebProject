<?php
  declare(strict_types = 1);

  class Workout {
    public int     $id;
    public int     $userId;
    public string  $workoutType;
    public ?int    $durationMinutes;
    public ?int    $calories;
    public string  $date;
    public string  $status;

    public function __construct(int $id, int $userId, string $workoutType, ?int $durationMinutes, ?int $calories, string $date, string $status) {
      $this->id              = $id;
      $this->userId          = $userId;
      $this->workoutType     = $workoutType;
      $this->durationMinutes = $durationMinutes;
      $this->calories        = $calories;
      $this->date            = $date;
      $this->status          = $status;
    }

    private static function fromRow(array $r) : Workout {
      return new Workout(
        (int)$r['WorkoutId'],
        (int)$r['UserId'],
        (string)$r['WorkoutType'],
        isset($r['DurationMinutes']) ? (int)$r['DurationMinutes'] : null,
        isset($r['Calories'])        ? (int)$r['Calories']        : null,
        (string)$r['Date'],
        (string)$r['Status']
      );
    }

    public static function getForUser(PDO $db, int $userId, int $limit = 50) : array {
      $stmt = $db->prepare('
        SELECT WorkoutId, UserId, WorkoutType, DurationMinutes, Calories, Date, Status
        FROM Workout WHERE UserId = ? ORDER BY Date DESC LIMIT ?
      ');
      $stmt->bindValue(1, $userId, PDO::PARAM_INT);
      $stmt->bindValue(2, $limit,  PDO::PARAM_INT);
      $stmt->execute();
      $items = array();
      while ($row = $stmt->fetch()) $items[] = self::fromRow($row);
      return $items;
    }

    public static function summaryForUser(PDO $db, int $userId) : array {
      $stmt = $db->prepare('
        SELECT COUNT(*) AS Total,
               COALESCE(SUM(DurationMinutes), 0) AS TotalMinutes,
               COALESCE(SUM(Calories), 0)        AS TotalCalories
        FROM Workout WHERE UserId = ? AND Status = ?
      ');
      $stmt->execute(array($userId, 'Completed'));
      $row = $stmt->fetch();
      return array(
        'total'         => (int)($row['Total']         ?? 0),
        'totalMinutes'  => (int)($row['TotalMinutes']  ?? 0),
        'totalCalories' => (int)($row['TotalCalories'] ?? 0),
      );
    }

    public static function log(PDO $db, int $userId, string $workoutType, ?int $durationMinutes, ?int $calories, string $status = 'Completed') : int {
      $stmt = $db->prepare('
        INSERT INTO Workout (UserId, WorkoutType, DurationMinutes, Calories, Status)
        VALUES (?, ?, ?, ?, ?)
      ');
      $stmt->execute(array($userId, $workoutType, $durationMinutes, $calories, $status));
      return (int)$db->lastInsertId();
    }

    public static function delete(PDO $db, int $id) : void {
      $stmt = $db->prepare('DELETE FROM Workout WHERE WorkoutId = ?');
      $stmt->execute(array($id));
    }
  }
?>
