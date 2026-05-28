<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/workout.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/dashboard.php');

  $action = (string)($_POST['action'] ?? 'log');
  $db     = getDatabaseConnection();

  try {
    if ($action === 'delete') {
      Workout::delete($db, (int)($_POST['workout_id'] ?? 0));
      $session->addMessage('success', 'Workout removed.');
    } else {
      $type     = trim((string)($_POST['workout_type'] ?? ''));
      $duration = (int)($_POST['duration'] ?? 0);
      $calories = (int)($_POST['calories'] ?? 0);
      if ($type === '')          throw new RuntimeException('Workout type is required.');
      if ($duration < 1 || $duration > 600) throw new RuntimeException('Duration must be between 1 and 600 minutes.');
      Workout::log($db, $session->getId(), $type, $duration, $calories > 0 ? $calories : null, 'Completed');
      $session->addMessage('success', 'Workout logged.');
    }
  } catch (RuntimeException $e) {
    $session->addMessage('error', $e->getMessage());
  } catch (Throwable $e) {
    error_log('action_workout: ' . $e->getMessage());
    $session->addMessage('error', 'Something went wrong. Please try again.');
  }

  header('Location: ../pages/dashboard.php');
  exit;
?>
