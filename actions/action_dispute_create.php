<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/dispute.class.php');
  require_once(__DIR__ . '/../database/membership.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/disputes.php');

  $category = trim($_POST['category'] ?? '');
  $subject  = trim($_POST['subject'] ?? '');
  $message  = trim($_POST['message'] ?? '');

  if ($subject === '' || $message === '') {
    $session->addMessage('error', 'Please provide a subject and a description.');
    header('Location: ../pages/disputes.php');
    exit();
  }

  $db = getDatabaseConnection();

  // Members must have chosen a membership before opening a support request.
  if (isMemberUser($session) && !Membership::userHasActivePlan($db, $session->getId())) {
    $session->addMessage('error', 'Choose a membership plan before creating a support request.');
    header('Location: ../pages/membership.php');
    exit();
  }

  try {
    Dispute::create($db, $session->getId(), $category, $subject, $message);
    $session->addMessage('success', 'Your report has been submitted. Our team will respond shortly.');
  } catch (RuntimeException $e) {
    $session->addMessage('error', $e->getMessage());
  } catch (Throwable $e) {
    error_log('action_dispute_create: ' . $e->getMessage());
    $session->addMessage('error', 'Something went wrong. Please try again.');
  }

  header('Location: ../pages/disputes.php');
  exit();
?>
