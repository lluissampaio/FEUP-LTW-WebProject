<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/review.class.php');
  require_once(__DIR__ . '/../database/enrollment.class.php');
  require_once(__DIR__ . '/../database/notification.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');

  $classId = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
  $rating  = isset($_POST['rating'])   ? (int)$_POST['rating']   : 0;
  $comment = isset($_POST['comment'])  ? trim((string)$_POST['comment']) : '';

  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/class.php?id=' . $classId);

  $db = getDatabaseConnection();

  if (!isMemberUser($session)) {
    $session->addMessage('error', 'Only members can review classes.');
    header('Location: ../pages/class.php?id=' . $classId);
    exit;
  }

  // A member may review only classes they have actually ATTENDED (a past occurrence
  // of a schedule they are/were enrolled in). Being merely enrolled is not enough.
  if (!Enrollment::hasAttended($db, $session->getId(), $classId)) {
    $session->addMessage('error', 'You can only review classes you have attended.');
    header('Location: ../pages/class.php?id=' . $classId);
    exit;
  }

  try {
    Review::upsert($db, $session->getId(), $classId, $rating, $comment !== '' ? $comment : null);
    $session->addMessage('success', 'Thank you for your review.');
    Notification::send($db, $session->getId(), 'review',
      'Review submitted',
      'Your review was submitted. Thank you for the feedback.',
      'class.php?id=' . $classId);
  } catch (RuntimeException $e) {
    $session->addMessage('error', $e->getMessage());
  } catch (Throwable $e) {
    error_log('action_review: ' . $e->getMessage());
    $session->addMessage('error', 'Something went wrong. Please try again.');
  }

  header('Location: ../pages/class.php?id=' . $classId . '#reviews');
  exit;
?>
