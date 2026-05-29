<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/plan.class.php');

  class Membership {
    public int     $id;
    public int     $userId;
    public int     $planId;
    public string  $status;
    public string  $startedAt;
    public ?string $renewsAt;
    public ?Plan   $plan;   // joined plan, when loaded

    public function __construct(int $id, int $userId, int $planId, string $status, string $startedAt, ?string $renewsAt, ?Plan $plan = null) {
      $this->id        = $id;
      $this->userId    = $userId;
      $this->planId    = $planId;
      $this->status    = $status;
      $this->startedAt = $startedAt;
      $this->renewsAt  = $renewsAt;
      $this->plan      = $plan;
    }

    public function isActive() : bool {
      return $this->status === 'active';
    }

    private static function fromRow(array $r) : Membership {
      $plan = null;
      if (isset($r['Name'])) {
        $plan = new Plan(
          (int)$r['PlanId'], (string)$r['Name'], (int)$r['Tier'], (int)$r['Price'],
          (string)$r['Period'], $r['Description'], (bool)$r['Featured'], (int)$r['PTSessionsLimit']
        );
      }
      return new Membership(
        (int)   $r['MembershipId'],
        (int)   $r['UserId'],
        (int)   $r['PlanId'],
        (string)$r['Status'],
        (string)$r['StartedAt'],
                $r['RenewsAt'],
        $plan
      );
    }

    /** Current subscription for a member, with its plan joined, or null. */
    public static function getForUser(PDO $db, int $userId) : ?Membership {
      $stmt = $db->prepare('
        SELECT m.MembershipId, m.UserId, m.PlanId, m.Status, m.StartedAt, m.RenewsAt,
               p.Name, p.Tier, p.Price, p.Period, p.Description, p.Featured, p.PTSessionsLimit
        FROM Membership m JOIN Plan p ON p.PlanId = m.PlanId
        WHERE m.UserId = ?
      ');
      $stmt->execute(array($userId));
      $row = $stmt->fetch();
      return $row ? self::fromRow($row) : null;
    }

    /**
     * Subscribe a member to a plan, or upgrade/downgrade/reactivate an existing
     * subscription. One Membership row per user (enforced by UNIQUE).
     */
    public static function subscribe(PDO $db, int $userId, int $planId) : void {
      if (Plan::getPlan($db, $planId) === null) {
        throw new RuntimeException('Unknown membership plan.');
      }
      $existing = self::getForUser($db, $userId);
      if ($existing === null) {
        $stmt = $db->prepare("
          INSERT INTO Membership (UserId, PlanId, Status, RenewsAt)
          VALUES (?, ?, 'active', datetime('now', '+1 month'))
        ");
        $stmt->execute(array($userId, $planId));
      } else {
        $stmt = $db->prepare("
          UPDATE Membership
          SET PlanId = ?, Status = 'active', StartedAt = CURRENT_TIMESTAMP, RenewsAt = datetime('now', '+1 month')
          WHERE UserId = ?
        ");
        $stmt->execute(array($planId, $userId));
      }
    }

    public static function cancel(PDO $db, int $userId) : bool {
      $stmt = $db->prepare("UPDATE Membership SET Status = 'cancelled' WHERE UserId = ? AND Status = 'active'");
      $stmt->execute(array($userId));
      return $stmt->rowCount() > 0;
    }
  }
?>
