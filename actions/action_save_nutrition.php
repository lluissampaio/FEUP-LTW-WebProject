<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/nutrition.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/nutrition.php');

  $db = getDatabaseConnection();

  try {
    $dietType      = trim((string)($_POST['diet_type'] ?? ''));
    $dailyCalories = (int)($_POST['daily_calories'] ?? 0);
    $protein       = (int)($_POST['protein'] ?? 0);
    $carbs         = (int)($_POST['carbs'] ?? 0);
    $fats          = (int)($_POST['fats'] ?? 0);
    $guidelines    = trim((string)($_POST['guidelines'] ?? ''));

    if ($dietType === '') throw new RuntimeException('Diet type is required.');
    if ($dailyCalories < 0) throw new RuntimeException('Calories cannot be negative.');

    NutritionPlan::savePlan($db, $session->getId(), [
      'DietType'      => $dietType,
      'DailyCalories' => $dailyCalories,
      'Protein'       => $protein,
      'Carbs'         => $carbs,
      'Fats'          => $fats,
      'Guidelines'    => $guidelines
    ]);

    $session->addMessage('success', 'Nutrition plan updated successfully.');
  } catch (Throwable $e) {
    $session->addMessage('error', $e->getMessage());
  }

  header('Location: ../pages/nutrition.php');
  exit;
?>
