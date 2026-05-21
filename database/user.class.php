<?php
  declare(strict_types = 1);

  class User {
    public int     $id;
    public string  $firstName;
    public string  $lastName;
    public string  $username;
    public string  $email;
    public ?string $photo;
    public string  $role;
    public bool    $active;
    public string  $joinDate;

    public function __construct(int $id, string $firstName, string $lastName, string $username, string $email, ?string $photo, string $role, bool $active, string $joinDate) {
      $this->id        = $id;
      $this->firstName = $firstName;
      $this->lastName  = $lastName;
      $this->username  = $username;
      $this->email     = $email;
      $this->photo     = $photo;
      $this->role      = $role;
      $this->active    = $active;
      $this->joinDate  = $joinDate;
    }

    public function name() : string {
      return $this->firstName . ' ' . $this->lastName;
    }

    public function save(PDO $db) : void {
      $stmt = $db->prepare('
        UPDATE User SET FirstName = ?, LastName = ?, Username = ?, Email = ?, Photo = ?, Role = ?, Active = ?
        WHERE UserId = ?
      ');
      $stmt->execute(array(
        $this->firstName, $this->lastName, $this->username, $this->email,
        $this->photo, $this->role, $this->active ? 1 : 0, $this->id
      ));
    }

    public function updatePassword(PDO $db, string $plainPassword) : void {
      $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
      $stmt = $db->prepare('UPDATE User SET Password = ? WHERE UserId = ?');
      $stmt->execute(array($hash, $this->id));
    }

    private static function fromRow(array $r) : User {
      return new User(
        (int)   $r['UserId'],
        (string)$r['FirstName'],
        (string)$r['LastName'],
        (string)$r['Username'],
        (string)$r['Email'],
                $r['Photo'],
        (string)$r['Role'],
        (bool)  $r['Active'],
        (string)$r['JoinDate']
      );
    }

    public static function getUser(PDO $db, int $id) : ?User {
      $stmt = $db->prepare('
        SELECT UserId, FirstName, LastName, Username, Email, Photo, Role, Active, JoinDate
        FROM User WHERE UserId = ?
      ');
      $stmt->execute(array($id));
      $row = $stmt->fetch();
      return $row ? self::fromRow($row) : null;
    }

    public static function getUserByEmail(PDO $db, string $email) : ?User {
      $stmt = $db->prepare('
        SELECT UserId, FirstName, LastName, Username, Email, Photo, Role, Active, JoinDate
        FROM User WHERE lower(Email) = ?
      ');
      $stmt->execute(array(strtolower($email)));
      $row = $stmt->fetch();
      return $row ? self::fromRow($row) : null;
    }

    public static function authenticate(PDO $db, string $emailOrUsername, string $password) : ?User {
      $stmt = $db->prepare('
        SELECT UserId, FirstName, LastName, Username, Email, Password, Photo, Role, Active, JoinDate
        FROM User WHERE lower(Email) = ? OR lower(Username) = ?
      ');
      $key = strtolower($emailOrUsername);
      $stmt->execute(array($key, $key));
      $row = $stmt->fetch();
      if (!$row) return null;
      if (!$row['Active']) return null;
      if (!password_verify($password, $row['Password'])) return null;
      return self::fromRow($row);
    }

    public static function register(PDO $db, string $firstName, string $lastName, string $username, string $email, string $plainPassword, string $role = 'member') : User {
      $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
      $stmt = $db->prepare('
        INSERT INTO User (FirstName, LastName, Username, Email, Password, Role)
        VALUES (?, ?, ?, ?, ?, ?)
      ');
      $stmt->execute(array($firstName, $lastName, $username, $email, $hash, $role));
      return self::getUser($db, (int)$db->lastInsertId());
    }

    public static function getAll(PDO $db, ?string $role = null) : array {
      if ($role !== null) {
        $stmt = $db->prepare('
          SELECT UserId, FirstName, LastName, Username, Email, Photo, Role, Active, JoinDate
          FROM User WHERE Role = ? ORDER BY FirstName, LastName
        ');
        $stmt->execute(array($role));
      } else {
        $stmt = $db->prepare('
          SELECT UserId, FirstName, LastName, Username, Email, Photo, Role, Active, JoinDate
          FROM User ORDER BY FirstName, LastName
        ');
        $stmt->execute();
      }
      $users = array();
      while ($row = $stmt->fetch()) $users[] = self::fromRow($row);
      return $users;
    }

    public static function deactivate(PDO $db, int $id) : void {
      $stmt = $db->prepare('UPDATE User SET Active = 0 WHERE UserId = ?');
      $stmt->execute(array($id));
    }

    public static function delete(PDO $db, int $id) : void {
      $stmt = $db->prepare('DELETE FROM User WHERE UserId = ?');
      $stmt->execute(array($id));
    }
  }
?>
