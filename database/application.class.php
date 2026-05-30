<?php
  declare(strict_types = 1);

  /**
   * Membership applications: people apply to join (via the contact form), an admin
   * accepts or rejects each one, and only emails of *accepted* applications may
   * register an account.
   */
  class MembershipApplication {
    public int     $id;
    public string  $firstName;
    public string  $lastName;
    public string  $email;
    public ?string $message;
    public string  $status;       // 'pending' | 'accepted' | 'rejected'
    public string  $createdAt;
    public ?string $decidedAt;
    public ?int    $decidedBy;

    public function __construct(int $id, string $firstName, string $lastName, string $email, ?string $message, string $status, string $createdAt, ?string $decidedAt, ?int $decidedBy) {
      $this->id        = $id;
      $this->firstName = $firstName;
      $this->lastName  = $lastName;
      $this->email     = $email;
      $this->message   = $message;
      $this->status    = $status;
      $this->createdAt = $createdAt;
      $this->decidedAt = $decidedAt;
      $this->decidedBy = $decidedBy;
    }

    public function name() : string {
      return $this->firstName . ' ' . $this->lastName;
    }

    private static function fromRow(array $r) : MembershipApplication {
      return new MembershipApplication(
        (int)   $r['ApplicationId'],
        (string)$r['FirstName'],
        (string)$r['LastName'],
        (string)$r['Email'],
                $r['Message'],
        (string)$r['Status'],
        (string)$r['CreatedAt'],
                $r['DecidedAt'],
        isset($r['DecidedBy']) ? (int)$r['DecidedBy'] : null
      );
    }

    /** Record a new application (status defaults to 'pending'). */
    public static function create(PDO $db, string $firstName, string $lastName, string $email, ?string $message) : void {
      $stmt = $db->prepare('
        INSERT INTO MembershipApplication (FirstName, LastName, Email, Message)
        VALUES (?, ?, ?, ?)
      ');
      $stmt->execute(array($firstName, $lastName, $email, $message));
    }

    /** All applications (optionally filtered by status), pending first then newest. */
    public static function getAll(PDO $db, ?string $status = null) : array {
      $sql = '
        SELECT ApplicationId, FirstName, LastName, Email, Message, Status, CreatedAt, DecidedAt, DecidedBy
        FROM MembershipApplication
      ';
      $params = array();
      if ($status !== null) {
        $sql .= ' WHERE Status = ?';
        $params[] = $status;
      }
      $sql .= " ORDER BY CASE Status WHEN 'pending' THEN 0 ELSE 1 END, CreatedAt DESC";
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

    /** True if this email has an accepted application (gate for registration). */
    public static function hasAccepted(PDO $db, string $email) : bool {
      $stmt = $db->prepare("SELECT 1 FROM MembershipApplication WHERE lower(Email) = ? AND Status = 'accepted' LIMIT 1");
      $stmt->execute(array(strtolower($email)));
      return (bool)$stmt->fetch();
    }

    /** Count of applications still awaiting a decision (for the sidebar badge). */
    public static function countPending(PDO $db) : int {
      return (int)$db->query("SELECT COUNT(*) FROM MembershipApplication WHERE Status = 'pending'")->fetchColumn();
    }
  }
?>
