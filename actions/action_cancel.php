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

  if (!isMemberUser($session)) {
    $session->addMessage('error', 'Only members can perform this action.');
    header('Location: ../pages/classes.php');
    exit;
  }

  $scheduleId = isset($_POST['schedule_id']) ? (int)$_POST['schedule_id'] : 0;
  $db = getDatabaseConnection();
  $schedule = Schedule::getSchedule($db, $scheduleId);

  if (Enrollment::cancel($db, $session->getId(), $scheduleId)) {
    $session->addMessage('success', 'Enrollment cancelled.');
    Notification::send(
      $db, $session->getId(), 'cancellation',
      'Booking cancelled',
      'You have cancelled your spot for ' . ($schedule->className ?? 'class') . ' on ' . ($schedule->dayOfWeek ?? '') . '.',
      'classes.php'
    );

    $promotedUser = Enrollment::promoteFromWaitlist($db, $scheduleId);
    if ($promotedUser !== null && $schedule) {
      Notification::send(
        $db, $promotedUser, 'promoted',
        'You are off the waitlist',
        'A spot opened in ' . $schedule->className . ' (' . $schedule->dayOfWeek . ', ' . $schedule->startTime . ') and you have been enrolled.',
        'class.php?id=' . $schedule->classId
      );
    }
  } else {
    $session->addMessage('error', 'You were not enrolled.');
  }

  header('Location: ../pages/classes.php');
  exit;
?>
