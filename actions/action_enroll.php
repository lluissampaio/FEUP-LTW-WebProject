<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/enrollment.class.php');
  require_once(__DIR__ . '/../database/schedule.class.php');
  require_once(__DIR__ . '/../database/notification.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/classes.php');

  $scheduleId = isset($_POST['schedule_id']) ? (int)$_POST['schedule_id'] : 0;
  $db = getDatabaseConnection();
  $schedule = Schedule::getSchedule($db, $scheduleId);
  if (!$schedule) {
    $session->addMessage('error', 'Class not found.');
    header('Location: ../pages/classes.php');
    exit;
  }

  try {
    Enrollment::enrol($db, $session->getId(), $scheduleId);
    $session->addMessage('success', 'You are enrolled in ' . $schedule->className . '.');
    Notification::send(
      $db, $session->getId(), 'enrollment',
      'Booking confirmed',
      'You are enrolled in ' . $schedule->className . ' on ' . $schedule->dayOfWeek . ' at ' . $schedule->startTime . '.',
      'class.php?id=' . $schedule->classId
    );
  } catch (Throwable $e) {
    $session->addMessage('error', $e->getMessage());
  }

  header('Location: ../pages/classes.php');
  exit;
?>
