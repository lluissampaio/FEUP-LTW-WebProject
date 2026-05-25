<?php
  declare(strict_types = 1);

  class Location {
    public int     $id;
    public string  $name;
    public string  $city;
    public string  $country;
    public ?string $address;
    public ?string $photo;

    public function __construct(int $id, string $name, string $city, string $country, ?string $address, ?string $photo) {
      $this->id      = $id;
      $this->name    = $name;
      $this->city    = $city;
      $this->country = $country;
      $this->address = $address;
      $this->photo   = $photo;
    }

    private static function fromRow(array $r) : Location {
      return new Location(
        (int)   $r['LocationId'],
        (string)$r['Name'],
        (string)$r['City'],
        (string)$r['Country'],
                $r['Address'],
                $r['Photo']
      );
    }

    public static function getLocation(PDO $db, int $id) : ?Location {
      $stmt = $db->prepare('SELECT LocationId, Name, City, Country, Address, Photo FROM Location WHERE LocationId = ?');
      $stmt->execute(array($id));
      $row = $stmt->fetch();
      return $row ? self::fromRow($row) : null;
    }

    public static function getLocations(PDO $db) : array {
      $stmt = $db->prepare('SELECT LocationId, Name, City, Country, Address, Photo FROM Location ORDER BY Name');
      $stmt->execute();
      $items = array();
      while ($row = $stmt->fetch()) $items[] = self::fromRow($row);
      return $items;
    }
  }
?>
