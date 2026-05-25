<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../utils/session.php');

  $session = new Session();
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/login.php');

  $email    = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($email === '' || $password === '') {
    $session->addMessage('error', 'Please fill in all fields.');
    header('Location: ../pages/login.php');
    exit();
  }

  $db = getDatabaseConnection();
  $stmt = $db->prepare('SELECT UserId, FirstName, LastName, Password, Active FROM User WHERE lower(Email) = ?');
  $stmt->execute([strtolower($email)]);
  $user = $stmt->fetch();

  if ($user && (int)$user['Active'] === 1 && password_verify($password, $user['Password'])) {
    $session->setId((int)$user['UserId']);
    $session->setName($user['FirstName'] . ' ' . $user['LastName']);
    $session->addMessage('success', 'Welcome back!');
    header('Location: ../pages/dashboard.php');
    exit();
  }

  $session->addMessage('error', 'Invalid email or password.');
  header('Location: ../pages/login.php');
  exit();
?>
