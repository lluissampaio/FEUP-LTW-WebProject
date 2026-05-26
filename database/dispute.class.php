<?php
  declare(strict_types = 1);

  class Dispute {
    public int     $id;
    public int     $userId;
    public string  $category;
    public string  $subject;
    public string  $message;
    public string  $status;
    public ?string $response;
    public ?int    $respondedBy;
    public string  $createdAt;
    public string  $updatedAt;

    // Joined display field.
    public ?string $memberName = null;

    public function __construct(int $id, int $userId, string $category, string $subject, string $message, string $status, ?string $response, ?int $respondedBy, string $createdAt, string $updatedAt) {
      $this->id          = $id;
      $this->userId      = $userId;
      $this->category    = $category;
      $this->subject     = $subject;
      $this->message     = $message;
      $this->status      = $status;
      $this->response    = $response;
      $this->respondedBy = $respondedBy;
      $this->createdAt   = $createdAt;
      $this->updatedAt   = $updatedAt;
    }

    public const CATEGORIES = ['equipment', 'class', 'billing', 'staff', 'other'];
    public const STATUSES   = ['open', 'in_review', 'resolved'];

    private static function fromRow(array $r) : Dispute {
      $d = new Dispute(
        (int)   $r['DisputeId'],
        (int)   $r['UserId'],
        (string)$r['Category'],
        (string)$r['Subject'],
        (string)$r['Message'],
        (string)$r['Status'],
                $r['Response'],
        isset($r['RespondedBy']) ? (($r['RespondedBy'] !== null) ? (int)$r['RespondedBy'] : null) : null,
        (string)$r['CreatedAt'],
        (string)$r['UpdatedAt']
      );
      $d->memberName = $r['MemberName'] ?? null;
      return $d;
    }

    public static function create(PDO $db, int $userId, string $category, string $subject, string $message) : Dispute {
      if (!in_array($category, self::CATEGORIES, true)) {
        throw new RuntimeException('Invalid category.');
      }
      $stmt = $db->prepare("
        INSERT INTO Dispute (UserId, Category, Subject, Message, Status)
        VALUES (?, ?, ?, ?, 'open')
      ");
      $stmt->execute(array($userId, $category, $subject, $message));
      return self::getDispute($db, (int)$db->lastInsertId());
    }

    public static function getDispute(PDO $db, int $id) : ?Dispute {
      $stmt = $db->prepare("
        SELECT d.DisputeId, d.UserId, d.Category, d.Subject, d.Message, d.Status, d.Response, d.RespondedBy, d.CreatedAt, d.UpdatedAt,
               u.FirstName || ' ' || u.LastName AS MemberName
        FROM Dispute d JOIN User u ON u.UserId = d.UserId
        WHERE d.DisputeId = ?
      ");
      $stmt->execute(array($id));
      $row = $stmt->fetch();
      return $row ? self::fromRow($row) : null;
    }

    /** All disputes raised by a single member. */
    public static function getForUser(PDO $db, int $userId) : array {
      $stmt = $db->prepare("
        SELECT d.DisputeId, d.UserId, d.Category, d.Subject, d.Message, d.Status, d.Response, d.RespondedBy, d.CreatedAt, d.UpdatedAt,
               u.FirstName || ' ' || u.LastName AS MemberName
        FROM Dispute d JOIN User u ON u.UserId = d.UserId
        WHERE d.UserId = ?
        ORDER BY d.CreatedAt DESC
      ");
      $stmt->execute(array($userId));
      return self::collect($stmt);
    }

    /** All disputes for admin triage, newest open first. */
    public static function getAll(PDO $db, ?string $status = null) : array {
      $sql = "
        SELECT d.DisputeId, d.UserId, d.Category, d.Subject, d.Message, d.Status, d.Response, d.RespondedBy, d.CreatedAt, d.UpdatedAt,
               u.FirstName || ' ' || u.LastName AS MemberName
        FROM Dispute d JOIN User u ON u.UserId = d.UserId
      ";
      $params = array();
      if ($status !== null) {
        $sql .= ' WHERE d.Status = ?';
        $params[] = $status;
      }
      $sql .= "
        ORDER BY CASE d.Status WHEN 'open' THEN 0 WHEN 'in_review' THEN 1 ELSE 2 END, d.CreatedAt DESC
      ";
      $stmt = $db->prepare($sql);
      $stmt->execute($params);
      return self::collect($stmt);
    }

    /** Admin responds to / updates the status of a dispute. */
    public static function respond(PDO $db, int $disputeId, int $adminId, string $status, ?string $response) : void {
      if (!in_array($status, self::STATUSES, true)) {
        throw new RuntimeException('Invalid status.');
      }
      $stmt = $db->prepare('
        UPDATE Dispute
        SET Status = ?, Response = ?, RespondedBy = ?, UpdatedAt = CURRENT_TIMESTAMP
        WHERE DisputeId = ?
      ');
      $stmt->execute(array($status, $response, $adminId, $disputeId));
    }

    public static function countByStatus(PDO $db) : array {
      $stmt = $db->query('SELECT Status, COUNT(*) AS n FROM Dispute GROUP BY Status');
      $counts = ['open' => 0, 'in_review' => 0, 'resolved' => 0];
      while ($row = $stmt->fetch()) $counts[(string)$row['Status']] = (int)$row['n'];
      return $counts;
    }

    private static function collect(PDOStatement $stmt) : array {
      $out = array();
      while ($row = $stmt->fetch()) $out[] = self::fromRow($row);
      return $out;
    }
  }
?>
