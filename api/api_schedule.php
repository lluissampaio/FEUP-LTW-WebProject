<?php
  declare(strict_types = 1);

  // Public, read-only API: class schedule. No auth required (read of public data).
  header('Content-Type: application/json');
  header('Access-Control-Allow-Origin: *');

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/schedule.class.php');

  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'GET required.']);
    exit;
  }

  // Optional filters mirror the public Classes page.
  $type       = isset($_GET['type'])     && $_GET['type']     !== '' ? (string)$_GET['type'] : null;
  $day        = isset($_GET['day'])      && $_GET['day']      !== '' ? (string)$_GET['day']  : null;
  $trainerId  = isset($_GET['trainer'])  && $_GET['trainer']  !== '' ? (int)$_GET['trainer']  : null;
  $locationId = isset($_GET['location']) && $_GET['location'] !== '' ? (int)$_GET['location'] : null;

  try {
    $db        = getDatabaseConnection();
    $schedules = Schedule::search($db, $type, $trainerId, $locationId, $day);

    $out = array_map(function (Schedule $s) {
      return [
        'schedule_id'    => $s->id,
        'class_id'       => $s->classId,
        'class'          => $s->className,
        'type'           => $s->classType,
        'trainer'        => $s->trainerName,
        'location'       => $s->locationName,
        'day'            => $s->dayOfWeek,
        'start_time'     => $s->startTime,
        'duration_min'   => $s->duration,
        'capacity'       => $s->capacity,
        'enrolled'       => $s->enrolledCount,
        'full'           => $s->isFull(),
      ];
    }, $schedules);

    echo json_encode(['ok' => true, 'count' => count($out), 'schedule' => $out]);
  } catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Could not load the schedule.']);
  }
?>
