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

  $planName = trim($_POST['plan'] ?? '');
  $db       = getDatabaseConnection();

  // A signed-in member who chose a real plan is applying for membership; record it.
  $planId = null;
  if ($planName !== '' && strtolower($planName) !== 'not sure yet') {
    $stmt = $db->prepare('SELECT PlanId FROM Plan WHERE Name = ?');
    $stmt->execute([$planName]);
    $row = $stmt->fetch();
    $planId = $row ? (int)$row['PlanId'] : null;
  }

  if ($session->isLoggedIn() && isMemberUser($session) && $planId !== null) {
    try {
      MembershipApplication::create($db, $session->getId(), $planId, $message);
      $session->addMessage('success', 'Your application for the ' . $planName . ' plan has been submitted. An admin will review it shortly.');
    } catch (PDOException $e) {
      $session->addMessage('error', 'You already have a pending application for this plan.');
    } catch (Throwable $e) {
      error_log('action_enquiry: ' . $e->getMessage());
      $session->addMessage('error', 'Something went wrong. Please try again.');
    }
  } else {
    // General enquiry (not signed in, or no specific plan) — log the lead.
    error_log(sprintf('enquiry from %s %s <%s> [plan=%s]: %s', $first, $last, $email, $planName, $message));
    $session->addMessage('success', 'Thank you for your enquiry. Our team will be in touch within twenty-four hours.');
  }

  header('Location: ../pages/contact.php');
  exit();
?>
