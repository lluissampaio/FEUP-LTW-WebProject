<?php
  declare(strict_types = 1);

  class Notification {
    public int     $id;
    public int     $userId;
    public string  $type;
    public string  $title;
    public string  $message;
    public ?string $link;
    public bool    $read;
    public string  $createdAt;

    public function __construct(int $id, int $userId, string $type, string $title, string $message, ?string $link, bool $read, string $createdAt) {
      $this->id        = $id;
      $this->userId    = $userId;
      $this->type      = $type;
      $this->title     = $title;
      $this->message   = $message;
      $this->link      = $link;
      $this->read      = $read;
      $this->createdAt = $createdAt;
    }

    private static function fromRow(array $r) : Notification {
      return new Notification(
        (int)$r['NotificationId'],
        (int)$r['UserId'],
        (string)$r['Type'],
        (string)$r['Title'],
        (string)$r['Message'],
                $r['Link'],
        (bool)$r['ReadFlag'],
        (string)$r['CreatedAt']
      );
    }

    public static function send(PDO $db, int $userId, string $type, string $title, string $message, ?string $link = null) : int {
      $stmt = $db->prepare('INSERT INTO Notification (UserId, Type, Title, Message, Link) VALUES (?, ?, ?, ?, ?)');
      $stmt->execute(array($userId, $type, $title, $message, $link));
      return (int)$db->lastInsertId();
    }

    public static function getForUser(PDO $db, int $userId, int $limit = 20) : array {
      $stmt = $db->prepare('
        SELECT NotificationId, UserId, Type, Title, Message, Link, ReadFlag, CreatedAt
        FROM Notification WHERE UserId = ? ORDER BY CreatedAt DESC LIMIT ?
      ');
      $stmt->bindValue(1, $userId, PDO::PARAM_INT);
      $stmt->bindValue(2, $limit,  PDO::PARAM_INT);
      $stmt->execute();
      $items = array();
      while ($row = $stmt->fetch()) $items[] = self::fromRow($row);
      return $items;
    }

    public static function unreadCount(PDO $db, int $userId) : int {
      $stmt = $db->prepare('SELECT COUNT(*) AS n FROM Notification WHERE UserId = ? AND ReadFlag = 0');
      $stmt->execute(array($userId));
      return (int)$stmt->fetch()['n'];
    }

    public static function markAllRead(PDO $db, int $userId) : void {
      $stmt = $db->prepare('UPDATE Notification SET ReadFlag = 1 WHERE UserId = ? AND ReadFlag = 0');
      $stmt->execute(array($userId));
    }

    public static function markRead(PDO $db, int $userId, int $id) : void {
      $stmt = $db->prepare('UPDATE Notification SET ReadFlag = 1 WHERE NotificationId = ? AND UserId = ?');
      $stmt->execute(array($id, $userId));
    }
  }
?>
