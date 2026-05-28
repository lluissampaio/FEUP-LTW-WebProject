<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/nutrition.class.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../database/trainer.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/nutrition.php');

  $db = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());

  try {
    $targetUserId  = (int)($_POST['target_user_id'] ?? $session->getId());
    
    if ($user && $user->role === 'trainer' && $targetUserId !== $session->getId()) {
      $clients = Trainer::getClients($db, $user->id);
      $isClient = false;
      foreach ($clients as $c) {
        if ((int)$c['UserId'] === $targetUserId) {
          $isClient = true;
          break;
        }
      }
      if (!$isClient) throw new RuntimeException('Unauthorized to edit this user\'s nutrition plan.');
    } elseif (!$user || $user->role !== 'trainer') {
      $targetUserId = $session->getId();
    }
    $dietType      = trim((string)($_POST['diet_type'] ?? ''));
    $dailyCalories = (int)($_POST['daily_calories'] ?? 0);
    $protein       = (int)($_POST['protein'] ?? 0);
    $carbs         = (int)($_POST['carbs'] ?? 0);
    $fats          = (int)($_POST['fats'] ?? 0);
    $guidelines    = trim((string)($_POST['guidelines'] ?? ''));

    if ($dietType === '') throw new RuntimeException('Diet type is required.');
    if ($dailyCalories < 0) throw new RuntimeException('Calories cannot be negative.');

    NutritionPlan::savePlan($db, $targetUserId, [
      'DietType'      => $dietType,
      'DailyCalories' => $dailyCalories,
      'Protein'       => $protein,
      'Carbs'         => $carbs,
      'Fats'          => $fats,
      'Guidelines'    => $guidelines
    ]);

    if ($user && $user->role === 'trainer'){
      $session->addMessage('success', 'Nutrition plan updated successfully.');
    }
    else{
      $session->addMessage('success', 'Nutrition plan updated successfully. Check the dashboard for the latest updates.');
    }
  } catch (RuntimeException $e) {
    $session->addMessage('error', $e->getMessage());
  } catch (Throwable $e) {
    error_log('action_save_nutrition: ' . $e->getMessage());
    $session->addMessage('error', 'Something went wrong. Please try again.');
  }

  if (isset($user) && $user->role === 'trainer' && isset($targetUserId) && $targetUserId !== $session->getId()) {
    header('Location: ../pages/trainer_nutrition.php?member_id=' . $targetUserId);
  } else {
    header('Location: ../pages/nutrition.php');
  }
  exit;
?>
