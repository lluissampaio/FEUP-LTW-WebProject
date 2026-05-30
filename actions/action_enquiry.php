<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');

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

  // No enquiry table in the data model yet; log the lead so it is not lost.
  error_log(sprintf('enquiry from %s %s <%s>: %s', $first, $last, $email, $message));

  $session->addMessage('success', 'Thank you for your enquiry. Our team will be in touch within twenty-four hours.');
  header('Location: ../pages/contact.php');
  exit();
?>
