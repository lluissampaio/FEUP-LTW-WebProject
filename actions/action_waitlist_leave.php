<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/waitlist.class.php');

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
  if (Waitlist::leave($db, $session->getId(), $scheduleId)) {
    $session->addMessage('success', 'You left the waitlist.');
  } else {
    $session->addMessage('error', 'You were not on the waitlist.');
  }

  header('Location: ../pages/classes.php');
  exit;
?>
