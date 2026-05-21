<?php
  declare(strict_types = 1);

  class Equipment {
    public int     $id;
    public string  $name;
    public string  $type;
    public int     $locationId;
    public ?string $locationName;
    public int     $quantity;
    public int     $available;
    public string  $status;

    public function __construct(int $id, string $name, string $type, int $locationId, ?string $locationName, int $quantity, int $available, string $status) {
      $this->id           = $id;
      $this->name         = $name;
      $this->type         = $type;
      $this->locationId   = $locationId;
      $this->locationName = $locationName;
      $this->quantity     = $quantity;
      $this->available    = $available;
      $this->status       = $status;
    }

    public function save(PDO $db) : void {
      $stmt = $db->prepare('
        UPDATE Equipment SET Name = ?, Type = ?, LocationId = ?, Quantity = ?, Available = ?, Status = ?
        WHERE EquipmentId = ?
      ');
      $stmt->execute(array(
        $this->name, $this->type, $this->locationId,
        $this->quantity, $this->available, $this->status, $this->id
      ));
    }

    private static function fromRow(array $r) : Equipment {
      return new Equipment(
        (int)$r['EquipmentId'],
        (string)$r['Name'],
        (string)$r['Type'],
        (int)$r['LocationId'],
        isset($r['LocationName']) ? (string)$r['LocationName'] : null,
        (int)$r['Quantity'],
        (int)$r['Available'],
        (string)$r['Status']
      );
    }

    private const SELECT_FULL = "
      SELECT e.EquipmentId, e.Name, e.Type, e.LocationId, l.Name AS LocationName,
             e.Quantity, e.Available, e.Status
      FROM Equipment e JOIN Location l ON l.LocationId = e.LocationId
    ";

    public static function getEquipment(PDO $db, int $id) : ?Equipment {
      $stmt = $db->prepare(self::SELECT_FULL . ' WHERE e.EquipmentId = ?');
      $stmt->execute(array($id));
      $row = $stmt->fetch();
      return $row ? self::fromRow($row) : null;
    }

    public static function getAll(PDO $db, ?int $locationId = null, ?string $type = null) : array {
      $sql = self::SELECT_FULL . ' WHERE 1=1';
      $params = array();
      if ($locationId !== null) { $sql .= ' AND e.LocationId = ?'; $params[] = $locationId; }
      if ($type !== null && $type !== '') { $sql .= ' AND e.Type = ?'; $params[] = $type; }
      $sql .= ' ORDER BY e.Type, e.Name';
      $stmt = $db->prepare($sql);
      $stmt->execute($params);
      $items = array();
      while ($row = $stmt->fetch()) $items[] = self::fromRow($row);
      return $items;
    }

    public static function getTypes(PDO $db) : array {
      $stmt = $db->prepare('SELECT DISTINCT Type FROM Equipment ORDER BY Type');
      $stmt->execute();
      $types = array();
      while ($row = $stmt->fetch()) $types[] = $row['Type'];
      return $types;
    }

    public static function create(PDO $db, string $name, string $type, int $locationId, int $quantity, int $available, string $status = 'operational') : int {
      $stmt = $db->prepare('
        INSERT INTO Equipment (Name, Type, LocationId, Quantity, Available, Status) VALUES (?, ?, ?, ?, ?, ?)
      ');
      $stmt->execute(array($name, $type, $locationId, $quantity, $available, $status));
      return (int)$db->lastInsertId();
    }

    public static function delete(PDO $db, int $id) : void {
      $stmt = $db->prepare('DELETE FROM Equipment WHERE EquipmentId = ?');
      $stmt->execute(array($id));
    }
  }
?>
