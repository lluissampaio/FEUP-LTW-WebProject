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
?>
