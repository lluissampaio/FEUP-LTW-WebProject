<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/application.class.php');

  $session = new Session();
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/contact.php');

  $first   = trim($_POST['first']   ?? '');
  $last    = trim($_POST['last']    ?? '');
  $email   = trim($_POST['email']   ?? '');
  $message = trim($_POST['message'] ?? '');

  if ($first === '' || $last === '' || $email === '' || $message === '') {
    $session->addMessage('error', 'Please fill in your name, email and message.');
    header('Location: ../pages/contact.php');
    exit();
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $session->addMessage('error', 'Please enter a valid email address.');
    header('Location: ../pages/contact.php');
    exit();
  }

  // Record the enquiry as a membership application for an admin to triage.
  try {
    $db = getDatabaseConnection();
    MembershipApplication::create($db, $first, $last, $email, $message);
  } catch (Throwable $e) {
    error_log('action_enquiry: ' . $e->getMessage() . sprintf(' | lead from %s %s <%s>', $first, $last, $email));
  }

  $session->addMessage('success', 'Thank you for your application. Our team will review it and be in touch within twenty-four hours.');
  header('Location: ../pages/contact.php');
  exit();
?>
