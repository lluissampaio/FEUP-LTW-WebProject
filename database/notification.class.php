<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/mailer.php');

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
      $id = (int)$db->lastInsertId();

      // Best-effort email mirror of the in-app notification. This must never
      // block or break the originating flow (enroll, booking, waitlist, ...),
      // so every step is guarded and failures are swallowed after logging.
      self::emailNotification($db, $userId, $id, $title, $message, $link);

      return $id;
    }

    /**
     * Look up the recipient's email and dispatch the notification through the
     * email channel. On successful hand-off, flag Notification.Emailed = 1.
     * Any failure (no email, no MTA, DB hiccup) is logged and ignored so the
     * caller's request is never disrupted.
     */
    private static function emailNotification(PDO $db, int $userId, int $id, string $title, string $message, ?string $link) : void {
      try {
        $stmt = $db->prepare('SELECT Email FROM User WHERE UserId = ?');
        $stmt->execute(array($userId));
        $row   = $stmt->fetch();
        $email = ($row && isset($row['Email'])) ? (string)$row['Email'] : '';
        if ($email === '') {
          return;
        }

        $body = $message;
        if ($link !== null && $link !== '') {
          $body .= "\n\n" . $link;
        }
        $body .= "\n\n-- Iron Haven";

        if (sendEmail($email, $title, $body)) {
          $upd = $db->prepare('UPDATE Notification SET Emailed = 1 WHERE NotificationId = ?');
          $upd->execute(array($id));
        }
      } catch (\Throwable $e) {
        error_log('Notification::emailNotification failed for user ' . $userId . ': ' . $e->getMessage());
      }
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
