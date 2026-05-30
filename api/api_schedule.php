<?php
  declare(strict_types = 1);

  // Public, read-only API: class schedule. No auth required (read of public data).
  header('Content-Type: application/json');

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/schedule.class.php');
  require_once(__DIR__ . '/../database/enrollment.class.php');
  require_once(__DIR__ . '/../database/waitlist.class.php');

  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'GET required.']);
    exit;
  }

  // Optional filters mirror the public Classes page (type, trainer, location, day, time bucket).
  $type       = isset($_GET['type'])     && $_GET['type']     !== '' ? (string)$_GET['type'] : null;
  $day        = isset($_GET['day'])      && $_GET['day']      !== '' ? (string)$_GET['day']  : null;
  $trainerId  = isset($_GET['trainer'])  && $_GET['trainer']  !== '' ? (int)$_GET['trainer']  : null;
  $locationId = isset($_GET['location']) && $_GET['location'] !== '' ? (int)$_GET['location'] : null;
  $time       = isset($_GET['time'])     && $_GET['time']     !== '' ? (string)$_GET['time'] : '';

  try {
    $db        = getDatabaseConnection();
    $schedules = Schedule::search($db, $type, $trainerId, $locationId, $day);
    $schedules = Schedule::filterByTimeBucket($schedules, $time);
    $schedules = Schedule::sortByNextOccurrence($schedules);

    // Annotate each session with the current viewer's state, mirroring pages/classes.php.
    $session    = new Session();
    $isLoggedIn = $session->isLoggedIn();
    $myEnrolled = [];
    $myWaiting  = [];
    if ($isLoggedIn) {
      foreach (Enrollment::getForUser($db, $session->getId(), 'enrolled') as $e) $myEnrolled[$e->scheduleId] = true;
      foreach (Waitlist::getForUser($db, $session->getId())                as $w) $myWaiting[$w->scheduleId]  = true;
    }

    $out = array_map(function (Schedule $s) use ($myEnrolled, $myWaiting) {
      $state = isset($myEnrolled[$s->id]) ? 'enrolled' : (isset($myWaiting[$s->id]) ? 'waiting' : null);
      return [
        'schedule_id'    => $s->id,
        'class_id'       => $s->classId,
        'class'          => $s->className,
        'type'           => $s->classType,
        'trainer'        => $s->trainerName,
        'trainer_id'     => $s->trainerId,
        'location'       => $s->locationName,
        'day'            => $s->dayOfWeek,
        'start_time'     => $s->startTime,
        'duration_min'   => $s->duration,
        'capacity'       => $s->capacity,
        'enrolled'       => $s->enrolledCount,
        'full'           => $s->isFull(),
        'my_state'       => $state,
      ];
    }, $schedules);

    echo json_encode(['ok' => true, 'logged_in' => $isLoggedIn, 'count' => count($out), 'schedule' => $out]);
  } catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Could not load the schedule.']);
  }
?>
