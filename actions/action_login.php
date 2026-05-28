<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../utils/session.php');

  $session = new Session();
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/login.php');

  $identifier = trim($_POST['identifier'] ?? $_POST['email'] ?? $_POST['username'] ?? '');
  $password   = $_POST['password'] ?? '';

  if ($identifier === '' || $password === '') {
    $session->addMessage('error', 'Please fill in all fields.');
    header('Location: ../pages/login.php');
    exit();
  }

  $db   = getDatabaseConnection();
  $user = User::authenticate($db, $identifier, $password);

  if ($user) {
    session_regenerate_id(true);
    $session->setId($user->id);
    $session->setName($user->name());
    $session->setRole($user->role);
    $session->setPhoto($user->photo);
    $session->setJoinDate($user->joinDate);
    $session->addMessage('success', 'Welcome back!');
    header('Location: ../pages/dashboard.php');
    exit();
  }

  $session->addMessage('error', 'Invalid credentials.');
  header('Location: ../pages/login.php');
  exit();
?>
