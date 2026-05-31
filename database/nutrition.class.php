<?php
  declare(strict_types = 1);

  class NutritionPlan {
    public int    $planId;
    public int    $userId;
    public string $dietType;
    public int    $dailyCalories;
    public int    $protein;
    public int    $carbs;
    public int    $fats;
    public string $guidelines;

    public function __construct(int $planId, int $userId, string $dietType, int $dailyCalories, int $protein, int $carbs, int $fats, string $guidelines) {
      $this->planId        = $planId;
      $this->userId        = $userId;
      $this->dietType      = $dietType;
      $this->dailyCalories = $dailyCalories;
      $this->protein       = $protein;
      $this->carbs         = $carbs;
      $this->fats          = $fats;
      $this->guidelines    = $guidelines;
    }

    private static function fromRow(array $r) : NutritionPlan {
      return new NutritionPlan(
        (int)   $r['PlanId'],
        (int)   $r['UserId'],
        (string)$r['DietType'],
        (int)   $r['DailyCalories'],
        (int)   $r['Protein'],
        (int)   $r['Carbs'],
        (int)   $r['Fats'],
        (string)$r['Guidelines']
      );
    }

    public static function getPlanByUserId(PDO $db, int $userId) : ?NutritionPlan {
      $stmt = $db->prepare('SELECT PlanId, UserId, DietType, DailyCalories, Protein, Carbs, Fats, Guidelines FROM NutritionPlan WHERE UserId = ?');
      $stmt->execute(array($userId));
      $row = $stmt->fetch();
      return $row ? self::fromRow($row) : null;
    }

    public static function savePlan(PDO $db, int $userId, array $data) : void {
      $existing = self::getPlanByUserId($db, $userId);
      if ($existing) {
        $stmt = $db->prepare('UPDATE NutritionPlan SET DietType = ?, DailyCalories = ?, Protein = ?, Carbs = ?, Fats = ?, Guidelines = ? WHERE UserId = ?');
        $stmt->execute(array($data['DietType'], $data['DailyCalories'], $data['Protein'], $data['Carbs'], $data['Fats'], $data['Guidelines'], $userId));
      } else {
        $stmt = $db->prepare('INSERT INTO NutritionPlan (UserId, DietType, DailyCalories, Protein, Carbs, Fats, Guidelines) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute(array($userId, $data['DietType'], $data['DailyCalories'], $data['Protein'], $data['Carbs'], $data['Fats'], $data['Guidelines']));
      }
    }
  }

  /*
   * A single meal within a nutrition plan (PlanId-scoped breakdown of the
   * daily macro target). CRUD is always scoped by PlanId so a meal can never
   * be edited/deleted against the wrong plan.
   */
  class NutritionMeal {
    public int    $mealId;
    public int    $planId;
    public string $name;
    public int    $calories;
    public int    $protein;
    public int    $carbs;
    public int    $fats;
    public int    $mealOrder;

    public function __construct(int $mealId, int $planId, string $name, int $calories, int $protein, int $carbs, int $fats, int $mealOrder) {
      $this->mealId    = $mealId;
      $this->planId    = $planId;
      $this->name      = $name;
      $this->calories  = $calories;
      $this->protein   = $protein;
      $this->carbs     = $carbs;
      $this->fats      = $fats;
      $this->mealOrder = $mealOrder;
    }

    private static function fromRow(array $r) : NutritionMeal {
      return new NutritionMeal(
        (int)   $r['MealId'],
        (int)   $r['PlanId'],
        (string)$r['Name'],
        (int)   $r['Calories'],
        (int)   $r['Protein'],
        (int)   $r['Carbs'],
        (int)   $r['Fats'],
        (int)   $r['MealOrder']
      );
    }

    /* All meals for a plan, ordered for display. */
    public static function getForPlan(PDO $db, int $planId) : array {
      $stmt = $db->prepare('SELECT MealId, PlanId, Name, Calories, Protein, Carbs, Fats, MealOrder FROM NutritionMeal WHERE PlanId = ? ORDER BY MealOrder, MealId');
      $stmt->execute(array($planId));
      $meals = array();
      while ($row = $stmt->fetch()) $meals[] = self::fromRow($row);
      return $meals;
    }

    /* A single meal, but only if it belongs to the given plan (authorisation guard). */
    public static function getForPlanById(PDO $db, int $planId, int $mealId) : ?NutritionMeal {
      $stmt = $db->prepare('SELECT MealId, PlanId, Name, Calories, Protein, Carbs, Fats, MealOrder FROM NutritionMeal WHERE PlanId = ? AND MealId = ?');
      $stmt->execute(array($planId, $mealId));
      $row = $stmt->fetch();
      return $row ? self::fromRow($row) : null;
    }

    public static function add(PDO $db, int $planId, array $data) : int {
      // Append after the current last meal unless an explicit order is supplied.
      $order = isset($data['MealOrder']) ? (int)$data['MealOrder'] : self::nextOrder($db, $planId);
      $stmt  = $db->prepare('INSERT INTO NutritionMeal (PlanId, Name, Calories, Protein, Carbs, Fats, MealOrder) VALUES (?, ?, ?, ?, ?, ?, ?)');
      $stmt->execute(array(
        $planId,
        $data['Name'],
        (int)$data['Calories'],
        (int)$data['Protein'],
        (int)$data['Carbs'],
        (int)$data['Fats'],
        $order
      ));
      return (int)$db->lastInsertId();
    }

    public static function update(PDO $db, int $planId, int $mealId, array $data) : void {
      // PlanId is part of the WHERE clause so a meal cannot be reassigned across plans.
      $stmt = $db->prepare('UPDATE NutritionMeal SET Name = ?, Calories = ?, Protein = ?, Carbs = ?, Fats = ? WHERE MealId = ? AND PlanId = ?');
      $stmt->execute(array(
        $data['Name'],
        (int)$data['Calories'],
        (int)$data['Protein'],
        (int)$data['Carbs'],
        (int)$data['Fats'],
        $mealId,
        $planId
      ));
    }

    public static function delete(PDO $db, int $planId, int $mealId) : void {
      $stmt = $db->prepare('DELETE FROM NutritionMeal WHERE MealId = ? AND PlanId = ?');
      $stmt->execute(array($mealId, $planId));
    }

    private static function nextOrder(PDO $db, int $planId) : int {
      $stmt = $db->prepare('SELECT COALESCE(MAX(MealOrder), 0) + 1 AS NextOrder FROM NutritionMeal WHERE PlanId = ?');
      $stmt->execute(array($planId));
      $row = $stmt->fetch();
      return $row ? (int)$row['NextOrder'] : 1;
    }
  }

  /*
   * A daily consumption log entry. Always scoped by UserId — a member only
   * ever reads/writes their own logs.
   */
  class NutritionLog {
    public int     $logId;
    public int     $userId;
    public string  $logDate;
    public int     $calories;
    public int     $protein;
    public int     $carbs;
    public int     $fats;
    public ?string $note;
    public ?string $createdAt;

    public function __construct(int $logId, int $userId, string $logDate, int $calories, int $protein, int $carbs, int $fats, ?string $note, ?string $createdAt) {
      $this->logId     = $logId;
      $this->userId    = $userId;
      $this->logDate   = $logDate;
      $this->calories  = $calories;
      $this->protein   = $protein;
      $this->carbs     = $carbs;
      $this->fats      = $fats;
      $this->note      = $note;
      $this->createdAt = $createdAt;
    }

    private static function fromRow(array $r) : NutritionLog {
      return new NutritionLog(
        (int)   $r['LogId'],
        (int)   $r['UserId'],
        (string)$r['LogDate'],
        (int)   $r['Calories'],
        (int)   $r['Protein'],
        (int)   $r['Carbs'],
        (int)   $r['Fats'],
                $r['Note'],
                $r['CreatedAt'] ?? null
      );
    }

    /* Most recent logs for a member (newest first). */
    public static function getForUser(PDO $db, int $userId, int $limit = 14) : array {
      $stmt = $db->prepare('SELECT LogId, UserId, LogDate, Calories, Protein, Carbs, Fats, Note, CreatedAt FROM NutritionLog WHERE UserId = ? ORDER BY LogDate DESC, LogId DESC LIMIT ?');
      $stmt->bindValue(1, $userId, PDO::PARAM_INT);
      $stmt->bindValue(2, $limit, PDO::PARAM_INT);
      $stmt->execute();
      $logs = array();
      while ($row = $stmt->fetch()) $logs[] = self::fromRow($row);
      return $logs;
    }

    public static function getForUserByDate(PDO $db, int $userId, string $logDate) : ?NutritionLog {
      $stmt = $db->prepare('SELECT LogId, UserId, LogDate, Calories, Protein, Carbs, Fats, Note, CreatedAt FROM NutritionLog WHERE UserId = ? AND LogDate = ? ORDER BY LogId DESC LIMIT 1');
      $stmt->execute(array($userId, $logDate));
      $row = $stmt->fetch();
      return $row ? self::fromRow($row) : null;
    }

    /*
     * Record (or replace) a member's consumption for a given date. If a log
     * already exists for that user+date it is updated, otherwise inserted.
     * UserId is always taken from the caller — never from request data.
     */
    public static function logForUser(PDO $db, int $userId, string $logDate, array $data) : void {
      $existing = self::getForUserByDate($db, $userId, $logDate);
      if ($existing) {
        $stmt = $db->prepare('UPDATE NutritionLog SET Calories = ?, Protein = ?, Carbs = ?, Fats = ?, Note = ? WHERE LogId = ? AND UserId = ?');
        $stmt->execute(array(
          (int)$data['Calories'],
          (int)$data['Protein'],
          (int)$data['Carbs'],
          (int)$data['Fats'],
          $data['Note'] !== null && $data['Note'] !== '' ? $data['Note'] : null,
          $existing->logId,
          $userId
        ));
      } else {
        $stmt = $db->prepare('INSERT INTO NutritionLog (UserId, LogDate, Calories, Protein, Carbs, Fats, Note) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute(array(
          $userId,
          $logDate,
          (int)$data['Calories'],
          (int)$data['Protein'],
          (int)$data['Carbs'],
          (int)$data['Fats'],
          $data['Note'] !== null && $data['Note'] !== '' ? $data['Note'] : null
        ));
      }
    }

    public static function deleteForUser(PDO $db, int $userId, int $logId) : void {
      $stmt = $db->prepare('DELETE FROM NutritionLog WHERE LogId = ? AND UserId = ?');
      $stmt->execute(array($logId, $userId));
    }
  }
?>
