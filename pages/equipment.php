<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/equipment.class.php');
  require_once(__DIR__ . '/../database/location.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/equipment.tpl.php');

  $session    = new Session();
  $db         = getDatabaseConnection();
  $locationId = isset($_GET['location']) && $_GET['location'] !== '' ? (int)$_GET['location'] : null;
  $type       = isset($_GET['type']) ? trim($_GET['type']) : '';

  $items     = Equipment::getAll($db, $locationId, $type ?: null);
  $locations = Location::getLocations($db);
  $types     = Equipment::getTypes($db);

  // Logged-in members get a "Reserve" deep link on each item; the public,
  // read-only view is unchanged.
  $canReserve = $session->isLoggedIn() && isMemberUser($session);

  drawHeader($session, 'equipment', 'Equipment — Iron Haven');
  drawPageHero(
    'Equipment',
    'What\'s on the floor.<br>Right now.',
    'Live availability of equipment across our four locations. Updated by floor staff.'
  );
  drawEquipmentFilters($locations, $types, $locationId, $type);
  drawEquipmentList($items, $canReserve);
  drawFooter();
?>
