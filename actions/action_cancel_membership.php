<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/membership.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/membership.php');

  $db = getDatabaseConnection();
  if (Membership::cancel($db, $session->getId())) {
    $session->addMessage('success', 'Your membership has been cancelled. You can resubscribe at any time.');
  } else {
    $session->addMessage('error', 'You do not have an active membership to cancel.');
  }

  header('Location: ../pages/membership.php');
  exit();
?>
