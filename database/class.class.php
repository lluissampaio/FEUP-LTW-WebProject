<?php
  declare(strict_types = 1);

  // PHP-side class is named GymClass because `class` is a reserved word in PHP.
  // The SQL table remains `Class`.
  class GymClass {
    public int     $id;
    public string  $name;
    public string  $type;
    public ?string $description;
    public int     $trainerId;
    public ?string $trainerName;
    public int     $locationId;
    public ?string $locationName;
    public int     $capacity;
    public bool    $featured;

    public function __construct(int $id, string $name, string $type, ?string $description, int $trainerId, ?string $trainerName, int $locationId, ?string $locationName, int $capacity, bool $featured = false) {
      $this->id           = $id;
      $this->name         = $name;
      $this->type         = $type;
      $this->description  = $description;
      $this->trainerId    = $trainerId;
      $this->trainerName  = $trainerName;
      $this->locationId   = $locationId;
      $this->locationName = $locationName;
      $this->capacity     = $capacity;
      $this->featured     = $featured;
    }

    public function save(PDO $db) : void {
      $stmt = $db->prepare('
        UPDATE Class SET Name = ?, Type = ?, Description = ?, TrainerId = ?, LocationId = ?, Capacity = ?
        WHERE ClassId = ?
      ');
      $stmt->execute(array(
        $this->name, $this->type, $this->description,
        $this->trainerId, $this->locationId, $this->capacity, $this->id
      ));
    }

    private static function fromRow(array $r) : GymClass {
      return new GymClass(
        (int)   $r['ClassId'],
        (string)$r['Name'],
        (string)$r['Type'],
                $r['Description'],
        (int)   $r['TrainerId'],
        isset($r['TrainerName']) ? (string)$r['TrainerName'] : null,
        (int)   $r['LocationId'],
        isset($r['LocationName']) ? (string)$r['LocationName'] : null,
        (int)   $r['Capacity'],
        isset($r['Featured']) ? (bool)$r['Featured'] : false
      );
    }

    private const SELECT_FULL = "
      SELECT c.ClassId, c.Name, c.Type, c.Description, c.TrainerId, c.LocationId, c.Capacity, c.Featured,
             (u.FirstName || ' ' || u.LastName) AS TrainerName,
             l.Name AS LocationName
      FROM Class c
      JOIN Trainer  t ON t.TrainerId = c.TrainerId
      JOIN User     u ON u.UserId    = t.TrainerId
      JOIN Location l ON l.LocationId = c.LocationId
    ";

    public static function getClass(PDO $db, int $id) : ?GymClass {
      $stmt = $db->prepare(self::SELECT_FULL . ' WHERE c.ClassId = ?');
      $stmt->execute(array($id));
      $row = $stmt->fetch();
      return $row ? self::fromRow($row) : null;
    }

    public static function getClasses(PDO $db) : array {
      $stmt = $db->prepare(self::SELECT_FULL . ' ORDER BY c.Type, c.Name');
      $stmt->execute();
      $items = array();
      while ($row = $stmt->fetch()) $items[] = self::fromRow($row);
      return $items;
    }

    public static function search(PDO $db, ?string $type = null, ?int $trainerId = null, ?int $locationId = null) : array {
      $sql = self::SELECT_FULL . ' WHERE 1=1';
      $params = array();
      if ($type !== null && $type !== '') {
        $sql .= ' AND c.Type = ?';
        $params[] = $type;
      }
      if ($trainerId !== null) {
        $sql .= ' AND c.TrainerId = ?';
        $params[] = $trainerId;
      }
      if ($locationId !== null) {
        $sql .= ' AND c.LocationId = ?';
        $params[] = $locationId;
      }
      $sql .= ' ORDER BY c.Type, c.Name';
      $stmt = $db->prepare($sql);
      $stmt->execute($params);
      $items = array();
      while ($row = $stmt->fetch()) $items[] = self::fromRow($row);
      return $items;
    }

    public static function getByTrainer(PDO $db, int $trainerId) : array {
      $stmt = $db->prepare(self::SELECT_FULL . ' WHERE c.TrainerId = ? ORDER BY c.Name');
      $stmt->execute(array($trainerId));
      $items = array();
      while ($row = $stmt->fetch()) $items[] = self::fromRow($row);
      return $items;
    }

    public static function getTypes(PDO $db) : array {
      $stmt = $db->prepare('SELECT DISTINCT Type FROM Class ORDER BY Type');
      $stmt->execute();
      $types = array();
      while ($row = $stmt->fetch()) $types[] = $row['Type'];
      return $types;
    }

    public static function create(PDO $db, string $name, string $type, ?string $description, int $trainerId, int $locationId, int $capacity) : int {
      $stmt = $db->prepare('
        INSERT INTO Class (Name, Type, Description, TrainerId, LocationId, Capacity)
        VALUES (?, ?, ?, ?, ?, ?)
      ');
      $stmt->execute(array($name, $type, $description, $trainerId, $locationId, $capacity));
      return (int)$db->lastInsertId();
    }

    public static function delete(PDO $db, int $id) : void {
      $stmt = $db->prepare('DELETE FROM Class WHERE ClassId = ?');
      $stmt->execute(array($id));
    }

    // Promotional features: classes flagged Featured = 1 surface on the homepage.
    public static function getFeatured(PDO $db) : array {
      $stmt = $db->prepare(self::SELECT_FULL . ' WHERE c.Featured = 1 ORDER BY c.Name');
      $stmt->execute();
      $items = array();
      while ($row = $stmt->fetch()) $items[] = self::fromRow($row);
      return $items;
    }

    public static function setFeatured(PDO $db, int $id, bool $featured) : void {
      $stmt = $db->prepare('UPDATE Class SET Featured = ? WHERE ClassId = ?');
      $stmt->execute(array($featured ? 1 : 0, $id));
    }
  }
?>
