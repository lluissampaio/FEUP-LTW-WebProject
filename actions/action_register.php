<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/application.class.php');
  require_once(__DIR__ . '/../utils/session.php');

  $session = new Session();
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/register.php');

  $name             = trim($_POST['name'] ?? '');
  $username         = strtolower(trim($_POST['username'] ?? ''));
  $email            = trim($_POST['email'] ?? '');
  $password         = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  if ($name === '' || $username === '' || $email === '' || $password === '') {
    $session->addMessage('error', 'Please fill in all fields.');
    header('Location: ../pages/register.php');
    exit();
  }

  if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    $session->addMessage('error', 'Username must be 3-20 characters long and contain only letters, numbers, and underscores.');
    header('Location: ../pages/register.php');
    exit();
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $session->addMessage('error', 'Please enter a valid email address.');
    header('Location: ../pages/register.php');
    exit();
  }

  if ($password !== $confirm_password) {
    $session->addMessage('error', 'Passwords do not match.');
    header('Location: ../pages/register.php');
    exit();
  }

  if (strlen($password) < 8) {
    $session->addMessage('error', 'Password must be at least 8 characters.');
    header('Location: ../pages/register.php');
    exit();
  }

  $parts     = preg_split('/\s+/', $name, 2);
  $firstName = $parts[0];
  $lastName  = $parts[1] ?? '';

  $hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);

  $db = getDatabaseConnection();

  // Membership is by approval: only emails with an accepted application may register.
  if (!MembershipApplication::hasAccepted($db, $email)) {
    $session->addMessage('error', 'There is no approved membership application for this email. Please apply via the contact page and wait for approval.');
    header('Location: ../pages/register.php');
    exit();
  }

  $stmt = $db->prepare('INSERT INTO User (FirstName, LastName, Username, Email, Password, Role) VALUES (?, ?, ?, ?, ?, ?)');
  try {
    $stmt->execute([$firstName, $lastName, $username, $email, $hash, 'member']);
    $session->addMessage('success', 'Account created. Please sign in.');
    header('Location: ../pages/login.php');
    exit();
  } catch (PDOException $e) {
    $session->addMessage('error', 'An account with that email already exists.');
    header('Location: ../pages/register.php');
    exit();
  }
?>
