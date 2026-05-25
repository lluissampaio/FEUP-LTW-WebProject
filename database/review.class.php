<?php
  declare(strict_types = 1);

  class Review {
    public int     $id;
    public int     $userId;
    public ?string $userName;
    public int     $classId;
    public ?string $className;
    public int     $rating;
    public ?string $comment;
    public string  $createdAt;

    public function __construct(int $id, int $userId, ?string $userName, int $classId, ?string $className, int $rating, ?string $comment, string $createdAt) {
      $this->id        = $id;
      $this->userId    = $userId;
      $this->userName  = $userName;
      $this->classId   = $classId;
      $this->className = $className;
      $this->rating    = $rating;
      $this->comment   = $comment;
      $this->createdAt = $createdAt;
    }

    private static function fromRow(array $r) : Review {
      return new Review(
        (int)$r['ReviewId'],
        (int)$r['UserId'],
        isset($r['UserName'])  ? (string)$r['UserName']  : null,
        (int)$r['ClassId'],
        isset($r['ClassName']) ? (string)$r['ClassName'] : null,
        (int)$r['Rating'],
                $r['Comment'],
        (string)$r['CreatedAt']
      );
    }

    private const SELECT_FULL = "
      SELECT r.ReviewId, r.UserId, r.ClassId, r.Rating, r.Comment, r.CreatedAt,
             (u.FirstName || ' ' || u.LastName) AS UserName,
             c.Name AS ClassName
      FROM Review r
      JOIN User  u ON u.UserId  = r.UserId
      JOIN Class c ON c.ClassId = r.ClassId
    ";

    public static function getReview(PDO $db, int $id) : ?Review {
      $stmt = $db->prepare(self::SELECT_FULL . ' WHERE r.ReviewId = ?');
      $stmt->execute(array($id));
      $row = $stmt->fetch();
      return $row ? self::fromRow($row) : null;
    }

    public static function getForClass(PDO $db, int $classId) : array {
      $stmt = $db->prepare(self::SELECT_FULL . ' WHERE r.ClassId = ? ORDER BY r.CreatedAt DESC');
      $stmt->execute(array($classId));
      $items = array();
      while ($row = $stmt->fetch()) $items[] = self::fromRow($row);
      return $items;
    }

    public static function getForUser(PDO $db, int $userId) : array {
      $stmt = $db->prepare(self::SELECT_FULL . ' WHERE r.UserId = ? ORDER BY r.CreatedAt DESC');
      $stmt->execute(array($userId));
      $items = array();
      while ($row = $stmt->fetch()) $items[] = self::fromRow($row);
      return $items;
    }

    public static function averageForClass(PDO $db, int $classId) : ?float {
      $stmt = $db->prepare('SELECT AVG(Rating) AS Avg FROM Review WHERE ClassId = ?');
      $stmt->execute(array($classId));
      $row = $stmt->fetch();
      return $row && $row['Avg'] !== null ? (float)$row['Avg'] : null;
    }

    /** Create or update (upsert) a review for the given user/class. */
    public static function upsert(PDO $db, int $userId, int $classId, int $rating, ?string $comment) : void {
      if ($rating < 1 || $rating > 5) throw new RangeException('Rating must be 1..5.');
      $stmt = $db->prepare('SELECT ReviewId FROM Review WHERE UserId = ? AND ClassId = ?');
      $stmt->execute(array($userId, $classId));
      $existing = $stmt->fetch();
      if ($existing) {
        $stmt = $db->prepare('UPDATE Review SET Rating = ?, Comment = ?, CreatedAt = CURRENT_TIMESTAMP WHERE ReviewId = ?');
        $stmt->execute(array($rating, $comment, (int)$existing['ReviewId']));
      } else {
        $stmt = $db->prepare('INSERT INTO Review (UserId, ClassId, Rating, Comment) VALUES (?, ?, ?, ?)');
        $stmt->execute(array($userId, $classId, $rating, $comment));
      }
    }

    public static function delete(PDO $db, int $id) : void {
      $stmt = $db->prepare('DELETE FROM Review WHERE ReviewId = ?');
      $stmt->execute(array($id));
    }
  }
?>
