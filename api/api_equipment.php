<?php
  declare(strict_types = 1);

  // Public, read-only API: equipment availability in the training area.
  header('Content-Type: application/json');

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/equipment.class.php');

  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'GET required.']);
    exit;
  }

  $locationId = isset($_GET['location']) && $_GET['location'] !== '' ? (int)$_GET['location'] : null;
  $type       = isset($_GET['type'])     && $_GET['type']     !== '' ? (string)$_GET['type'] : null;

  try {
    $db   = getDatabaseConnection();
    $list = Equipment::getAll($db, $locationId, $type);

    $out = array_map(function (Equipment $e) {
      return [
        'id'        => $e->id,
        'name'      => $e->name,
        'type'      => $e->type,
        'location'  => $e->locationName,
        'quantity'  => $e->quantity,
        'available' => $e->available,
        'status'    => $e->status,
      ];
    }, $list);

    echo json_encode(['ok' => true, 'count' => count($out), 'equipment' => $out]);
  } catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Could not load equipment.']);
  }
?>
