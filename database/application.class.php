<?php
  declare(strict_types = 1);

  /**
   * Membership applications: a logged-in member applies for a specific plan (with an
   * optional note), an admin accepts or rejects it, and the member may then subscribe
   * only to a plan they were approved for.
   */
  class MembershipApplication {
    public int     $id;
    public int     $userId;
    public int     $planId;
    public ?string $message;
    public string  $status;       // 'pending' | 'accepted' | 'rejected'
    public string  $createdAt;
    public ?string $decidedAt;
    public ?int    $decidedBy;

    // Joined display fields (populated by getAll()).
    public ?string $memberName  = null;
    public ?string $memberEmail = null;
    public ?string $planName    = null;

    public function __construct(int $id, int $userId, int $planId, ?string $message, string $status, string $createdAt, ?string $decidedAt, ?int $decidedBy) {
      $this->id        = $id;
      $this->userId    = $userId;
      $this->planId    = $planId;
      $this->message   = $message;
      $this->status    = $status;
      $this->createdAt = $createdAt;
      $this->decidedAt = $decidedAt;
      $this->decidedBy = $decidedBy;
    }

    private static function fromRow(array $r) : MembershipApplication {
      $a = new MembershipApplication(
        (int)$r['ApplicationId'], (int)$r['UserId'], (int)$r['PlanId'],
        $r['Message'], (string)$r['Status'], (string)$r['CreatedAt'],
        $r['DecidedAt'], isset($r['DecidedBy']) ? (int)$r['DecidedBy'] : null
      );
      $a->memberName  = $r['MemberName']  ?? null;
      $a->memberEmail = $r['MemberEmail'] ?? null;
      $a->planName    = $r['PlanName']    ?? null;
      return $a;
    }

    /** A member applies for a plan (a partial unique index blocks duplicate pending apps). */
    public static function create(PDO $db, int $userId, int $planId, ?string $message = null) : void {
      $stmt = $db->prepare('INSERT INTO MembershipApplication (UserId, PlanId, Message) VALUES (?, ?, ?)');
      $stmt->execute(array($userId, $planId, $message));
    }

    /** All applications (optionally filtered by status) with member + plan names joined. */
    public static function getAll(PDO $db, ?string $status = null) : array {
      $sql = "
        SELECT a.ApplicationId, a.UserId, a.PlanId, a.Message, a.Status, a.CreatedAt, a.DecidedAt, a.DecidedBy,
               u.FirstName || ' ' || u.LastName AS MemberName, u.Email AS MemberEmail,
               p.Name AS PlanName
        FROM MembershipApplication a
        JOIN User u ON u.UserId = a.UserId
        JOIN Plan p ON p.PlanId = a.PlanId
      ";
      $params = array();
      if ($status !== null) { $sql .= ' WHERE a.Status = ?'; $params[] = $status; }
      $sql .= " ORDER BY CASE a.Status WHEN 'pending' THEN 0 ELSE 1 END, a.CreatedAt DESC";
      $stmt = $db->prepare($sql);
      $stmt->execute($params);
      $out = array();
      while ($row = $stmt->fetch()) $out[] = self::fromRow($row);
      return $out;
    }

    /** Accept or reject an application. */
    public static function decide(PDO $db, int $id, string $status, int $adminId) : void {
      if (!in_array($status, array('accepted', 'rejected'), true)) {
        throw new RuntimeException('Invalid application decision.');
      }
      $stmt = $db->prepare('
        UPDATE MembershipApplication
        SET Status = ?, DecidedAt = CURRENT_TIMESTAMP, DecidedBy = ?
        WHERE ApplicationId = ?
      ');
      $stmt->execute(array($status, $adminId, $id));
    }

    /** Latest application status for a (user, plan), or null if none. */
    public static function statusFor(PDO $db, int $userId, int $planId) : ?string {
      $stmt = $db->prepare('
        SELECT Status FROM MembershipApplication
        WHERE UserId = ? AND PlanId = ?
        ORDER BY CreatedAt DESC LIMIT 1
      ');
      $stmt->execute(array($userId, $planId));
      $row = $stmt->fetch();
      return $row ? (string)$row['Status'] : null;
    }

    /** True if the member has an accepted application for this plan (gate for subscribing). */
    public static function isApprovedFor(PDO $db, int $userId, int $planId) : bool {
      $stmt = $db->prepare("SELECT 1 FROM MembershipApplication WHERE UserId = ? AND PlanId = ? AND Status = 'accepted' LIMIT 1");
      $stmt->execute(array($userId, $planId));
      return (bool)$stmt->fetch();
    }

    /** Count of applications still awaiting a decision (for the sidebar badge). */
    public static function countPending(PDO $db) : int {
      return (int)$db->query("SELECT COUNT(*) FROM MembershipApplication WHERE Status = 'pending'")->fetchColumn();
    }
  }
?>
