<?php
  declare(strict_types = 1);

  class Plan {
    public int     $id;
    public string  $name;
    public int     $tier;
    public int     $price;
    public string  $period;
    public ?string $description;
    public bool    $featured;
    public int     $ptSessionsLimit;

    public function __construct(int $id, string $name, int $tier, int $price, string $period, ?string $description, bool $featured, int $ptSessionsLimit) {
      $this->id              = $id;
      $this->name        = $name;
      $this->tier        = $tier;
      $this->price       = $price;
      $this->period      = $period;
      $this->description     = $description;
      $this->featured        = $featured;
      $this->ptSessionsLimit = $ptSessionsLimit;
    }

    public function priceLabel() : string {
      return '$' . number_format($this->price);
    }

    private static function fromRow(array $r) : Plan {
      return new Plan(
        (int)   $r['PlanId'],
        (string)$r['Name'],
        (int)   $r['Tier'],
        (int)   $r['Price'],
        (string)$r['Period'],
                $r['Description'],
        (bool)  $r['Featured'],
        (int)   $r['PTSessionsLimit']
      );
    }

    public static function getPlan(PDO $db, int $id) : ?Plan {
      $stmt = $db->prepare('SELECT PlanId, Name, Tier, Price, Period, Description, Featured, PTSessionsLimit FROM Plan WHERE PlanId = ?');
      $stmt->execute(array($id));
      $row = $stmt->fetch();
      return $row ? self::fromRow($row) : null;
    }

    public static function getPlans(PDO $db) : array {
      $stmt = $db->query('SELECT PlanId, Name, Tier, Price, Period, Description, Featured, PTSessionsLimit FROM Plan ORDER BY Tier');
      $plans = array();
      while ($row = $stmt->fetch()) $plans[] = self::fromRow($row);
      return $plans;
    }
  }
?>
