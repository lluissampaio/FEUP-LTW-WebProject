<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');

  $session = new Session();
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/index.php');
  $session->logout();
  header('Location: ../pages/index.php');
  exit();
?>
