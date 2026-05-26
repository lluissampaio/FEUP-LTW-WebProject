<?php
  declare(strict_types = 1);

  // Public, read-only API: trainer profiles.
  header('Content-Type: application/json');
  header('Access-Control-Allow-Origin: *');

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/trainer.class.php');

  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'GET required.']);
    exit;
  }

  try {
    $db = getDatabaseConnection();

    // Single trainer by id, or the full roster.
    if (isset($_GET['id']) && $_GET['id'] !== '') {
      $t = Trainer::getTrainer($db, (int)$_GET['id']);
      if (!$t) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Trainer not found.']);
        exit;
      }
      $trainers = [$t];
    } else {
      $trainers = Trainer::getTrainers($db);
    }

    $out = array_map(function (Trainer $t) {
      return [
        'id'             => $t->id,
        'name'           => $t->name(),
        'specialty'      => $t->specialty,
        'bio'            => $t->bio,
        'certifications' => $t->certifications,
        'photo'          => $t->photo,
      ];
    }, $trainers);

    echo json_encode(['ok' => true, 'count' => count($out), 'trainers' => $out]);
  } catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Could not load trainers.']);
  }
?>
