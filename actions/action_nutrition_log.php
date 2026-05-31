<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/nutrition.class.php');
  require_once(__DIR__ . '/../database/user.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/nutrition.php');

  $db   = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());

  // Member-only.
  if (!$user || $user->role !== 'member') {
    $session->addMessage('error', 'Only members can log nutrition.');
    header('Location: ../pages/dashboard.php');
    exit;
  }

  try {
    $action = (string)($_POST['action'] ?? 'log');

    if ($action === 'delete') {
      $logId = (int)($_POST['log_id'] ?? 0);
      // UserId is taken from the session, never from the request.
      NutritionLog::deleteForUser($db, $user->id, $logId);
      $session->addMessage('success', 'Log entry removed.');
    } else {
      $calories = (int)($_POST['calories'] ?? 0);
      $protein  = (int)($_POST['protein'] ?? 0);
      $carbs    = (int)($_POST['carbs'] ?? 0);
      $fats     = (int)($_POST['fats'] ?? 0);
      $note     = trim((string)($_POST['note'] ?? ''));

      if ($calories < 0 || $protein < 0 || $carbs < 0 || $fats < 0) {
        throw new RuntimeException('Macros cannot be negative.');
      }
      if ($calories > 20000) throw new RuntimeException('Calories value is out of range.');

      // Always log against today for the current member.
      $logDate = date('Y-m-d');

      NutritionLog::logForUser($db, $user->id, $logDate, array(
        'Calories' => $calories,
        'Protein'  => $protein,
        'Carbs'    => $carbs,
        'Fats'     => $fats,
        'Note'     => $note
      ));

      $session->addMessage('success', 'Today\'s nutrition logged.');
    }
  } catch (RuntimeException $e) {
    $session->addMessage('error', $e->getMessage());
  } catch (Throwable $e) {
    error_log('action_nutrition_log: ' . $e->getMessage());
    $session->addMessage('error', 'Something went wrong. Please try again.');
  }

  header('Location: ../pages/nutrition.php');
  exit;
?>
