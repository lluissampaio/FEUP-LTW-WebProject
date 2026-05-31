<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/waitlist.class.php');
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

  try {
    Waitlist::join($db, $session->getId(), $scheduleId);
    $pos = Waitlist::position($db, $session->getId(), $scheduleId);
    $session->addMessage('success', 'You are on the waitlist (position ' . $pos . ').');
    Notification::send(
      $db, $session->getId(), 'waitlist',
      'Waitlist position ' . $pos,
      'You joined the waitlist for ' . ($schedule->className ?? 'this class') . '. You will be notified if a spot opens.',
      'classes.php'
    );
  } catch (RuntimeException $e) {
    $session->addMessage('error', $e->getMessage());
  } catch (Throwable $e) {
    error_log('action_waitlist_join: ' . $e->getMessage());
    $session->addMessage('error', 'Something went wrong. Please try again.');
  }

  header('Location: ../pages/classes.php');
  exit;
?>
