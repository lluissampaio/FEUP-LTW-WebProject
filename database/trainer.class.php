<?php
  declare(strict_types = 1);

  class Trainer {
    public int     $id;            // = UserId
    public string  $firstName;
    public string  $lastName;
    public ?string $photo;
    public ?string $bio;
    public ?string $specialty;
    public ?string $certifications;

    public function __construct(int $id, string $firstName, string $lastName, ?string $photo, ?string $bio, ?string $specialty, ?string $certifications) {
      $this->id             = $id;
      $this->firstName      = $firstName;
      $this->lastName       = $lastName;
      $this->photo          = $photo;
      $this->bio            = $bio;
      $this->specialty      = $specialty;
      $this->certifications = $certifications;
    }

    public function name() : string {
      return $this->firstName . ' ' . $this->lastName;
    }

    public function save(PDO $db) : void {
      $stmt = $db->prepare('
        UPDATE Trainer SET Bio = ?, Specialty = ?, Certifications = ?
        WHERE TrainerId = ?
      ');
      $stmt->execute(array($this->bio, $this->specialty, $this->certifications, $this->id));
    }

    private static function fromRow(array $r) : Trainer {
      return new Trainer(
        (int)   $r['TrainerId'],
        (string)$r['FirstName'],
        (string)$r['LastName'],
                $r['Photo'],
                $r['Bio'],
                $r['Specialty'],
                $r['Certifications']
      );
    }

    public static function getTrainer(PDO $db, int $id) : ?Trainer {
      $stmt = $db->prepare('
        SELECT t.TrainerId, u.FirstName, u.LastName, u.Photo, t.Bio, t.Specialty, t.Certifications
        FROM Trainer t JOIN User u ON u.UserId = t.TrainerId
        WHERE t.TrainerId = ?
      ');
      $stmt->execute(array($id));
      $row = $stmt->fetch();
      return $row ? self::fromRow($row) : null;
    }

    public static function getTrainers(PDO $db) : array {
      $stmt = $db->prepare('
        SELECT t.TrainerId, u.FirstName, u.LastName, u.Photo, t.Bio, t.Specialty, t.Certifications
        FROM Trainer t JOIN User u ON u.UserId = t.TrainerId
        WHERE u.Active = 1
        ORDER BY u.FirstName, u.LastName
      ');
      $stmt->execute();
      $trainers = array();
      while ($row = $stmt->fetch()) $trainers[] = self::fromRow($row);
      return $trainers;
    }

    public static function getBySpecialty(PDO $db, string $specialty) : array {
      $stmt = $db->prepare('
        SELECT t.TrainerId, u.FirstName, u.LastName, u.Photo, t.Bio, t.Specialty, t.Certifications
        FROM Trainer t JOIN User u ON u.UserId = t.TrainerId
        WHERE u.Active = 1 AND t.Specialty = ?
        ORDER BY u.FirstName, u.LastName
      ');
      $stmt->execute(array($specialty));
      $trainers = array();
      while ($row = $stmt->fetch()) $trainers[] = self::fromRow($row);
      return $trainers;
    }

    public static function create(PDO $db, int $userId, ?string $bio, ?string $specialty, ?string $certifications) : void {
      $stmt = $db->prepare('
        INSERT INTO Trainer (TrainerId, Bio, Specialty, Certifications) VALUES (?, ?, ?, ?)
      ');
      $stmt->execute(array($userId, $bio, $specialty, $certifications));
    }
  }
?>
