<?php
  declare(strict_types = 1);

  header('Content-Type: application/json');

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/enrollment.class.php');
  require_once(__DIR__ . '/../database/waitlist.class.php');
  require_once(__DIR__ . '/../database/schedule.class.php');
  require_once(__DIR__ . '/../database/notification.class.php');

  function jsend(int $code, array $payload) : void {
    http_response_code($code);
    echo json_encode($payload);
    exit;
  }

  $session = new Session();
  if (!$session->isLoggedIn()) jsend(401, ['ok' => false, 'error' => 'Not signed in.']);

  if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsend(405, ['ok' => false, 'error' => 'POST required.']);

  $body = json_decode(file_get_contents('php://input') ?: '', true) ?: [];
  $token      = $body['csrf_token']  ?? ($_POST['csrf_token']  ?? '');
  $action     = $body['action']      ?? ($_POST['action']      ?? '');
  $scheduleId = (int)($body['schedule_id'] ?? ($_POST['schedule_id'] ?? 0));

  if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$token)) {
    jsend(403, ['ok' => false, 'error' => 'Invalid CSRF token.']);
  }

  $db = getDatabaseConnection();
  $schedule = Schedule::getSchedule($db, $scheduleId);
  if (!$schedule) jsend(404, ['ok' => false, 'error' => 'Schedule not found.']);

  try {
    if ($action === 'enrol') {
      Enrollment::enrol($db, $session->getId(), $scheduleId);
      Notification::send($db, $session->getId(), 'enrollment', 'Booking confirmed',
        'You are enrolled in ' . $schedule->className . ' on ' . $schedule->dayOfWeek . ' at ' . $schedule->startTime . '.',
        'class.php?id=' . $schedule->classId);
      $state = 'enrolled';
    } elseif ($action === 'cancel') {
      if (!Enrollment::cancel($db, $session->getId(), $scheduleId)) {
        jsend(400, ['ok' => false, 'error' => 'You were not enrolled.']);
      }
      Notification::send($db, $session->getId(), 'cancellation', 'Booking cancelled',
        'You cancelled your spot for ' . $schedule->className . '.',
        'classes.php');
      $promoted = Enrollment::promoteFromWaitlist($db, $scheduleId);
      if ($promoted !== null) {
        Notification::send($db, $promoted, 'promoted', 'You are off the waitlist',
          'A spot opened in ' . $schedule->className . ' (' . $schedule->dayOfWeek . ', ' . $schedule->startTime . ') and you have been enrolled.',
          'class.php?id=' . $schedule->classId);
      }
      $state = 'available';
    } elseif ($action === 'waitlist_join') {
      Waitlist::join($db, $session->getId(), $scheduleId);
      $state = 'waiting';
    } elseif ($action === 'waitlist_leave') {
      Waitlist::leave($db, $session->getId(), $scheduleId);
      $state = 'available';
    } else {
      jsend(400, ['ok' => false, 'error' => 'Unknown action.']);
    }

    $fresh = Schedule::getSchedule($db, $scheduleId);
    jsend(200, [
      'ok'             => true,
      'state'          => $state,
      'enrolled_count' => $fresh ? $fresh->enrolledCount : null,
      'capacity'       => $fresh ? $fresh->capacity      : null,
      'full'           => $fresh ? $fresh->isFull()      : null,
    ]);
  } catch (Throwable $e) {
    jsend(400, ['ok' => false, 'error' => $e->getMessage()]);
  }
?>
