<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/review.class.php');
  require_once(__DIR__ . '/../database/notification.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');

  $classId = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
  $rating  = isset($_POST['rating'])   ? (int)$_POST['rating']   : 0;
  $comment = isset($_POST['comment'])  ? trim((string)$_POST['comment']) : '';

  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/class.php?id=' . $classId);

  $db = getDatabaseConnection();

  $stmt = $db->prepare("
    SELECT COUNT(*) AS n FROM Enrollment e JOIN Schedule s ON s.ScheduleId = e.ScheduleId
    WHERE e.UserId = ? AND s.ClassId = ? AND e.Status IN ('enrolled','attended')
  ");
  $stmt->execute([$session->getId(), $classId]);
  if ((int)$stmt->fetch()['n'] === 0) {
    $session->addMessage('error', 'You can only review classes you have attended or are enrolled in.');
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
