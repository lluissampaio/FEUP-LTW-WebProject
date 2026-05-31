<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/nutrition.class.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../database/trainer.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');

  $memberId = (int)($_POST['member_id'] ?? 0);
  $redirect = '../pages/trainer_nutrition.php?member_id=' . $memberId;

  $session->verifyCsrf($_POST['csrf_token'] ?? '', $redirect);

  $db   = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());

  // Trainer-only.
  if (!$user || $user->role !== 'trainer') {
    $session->addMessage('error', 'Only trainers can edit a meal plan.');
    header('Location: ../pages/dashboard.php');
    exit;
  }

  try {
    if ($memberId <= 0) throw new RuntimeException('Invalid member.');

    // The target member must actually be one of this trainer's clients
    // (mirrors the check in pages/trainer_nutrition.php).
    $clients  = Trainer::getClients($db, $user->id);
    $isClient = false;
    foreach ($clients as $c) {
      if ((int)$c['UserId'] === $memberId) { $isClient = true; break; }
    }
    if (!$isClient) throw new RuntimeException('Unauthorized access to member.');

    // The member must already have a plan to attach meals to.
    $plan = NutritionPlan::getPlanByUserId($db, $memberId);
    if (!$plan) throw new RuntimeException('Create a nutrition plan before adding meals.');

    $action = (string)($_POST['action'] ?? 'add');

    if ($action === 'delete') {
      $mealId = (int)($_POST['meal_id'] ?? 0);
      $meal   = NutritionMeal::getForPlanById($db, $plan->planId, $mealId);
      if (!$meal) throw new RuntimeException('Meal not found.');
      NutritionMeal::delete($db, $plan->planId, $mealId);
      $session->addMessage('success', 'Meal removed.');
    } else {
      $name     = trim((string)($_POST['name'] ?? ''));
      $calories = (int)($_POST['calories'] ?? 0);
      $protein  = (int)($_POST['protein'] ?? 0);
      $carbs    = (int)($_POST['carbs'] ?? 0);
      $fats     = (int)($_POST['fats'] ?? 0);

      if ($name === '') throw new RuntimeException('Meal name is required.');
      if ($calories < 0 || $protein < 0 || $carbs < 0 || $fats < 0) {
        throw new RuntimeException('Macros cannot be negative.');
      }

      $payload = array(
        'Name'     => $name,
        'Calories' => $calories,
        'Protein'  => $protein,
        'Carbs'    => $carbs,
        'Fats'     => $fats
      );

      if ($action === 'edit') {
        $mealId = (int)($_POST['meal_id'] ?? 0);
        $meal   = NutritionMeal::getForPlanById($db, $plan->planId, $mealId);
        if (!$meal) throw new RuntimeException('Meal not found.');
        NutritionMeal::update($db, $plan->planId, $mealId, $payload);
        $session->addMessage('success', 'Meal updated.');
      } else {
        NutritionMeal::add($db, $plan->planId, $payload);
        $session->addMessage('success', 'Meal added.');
      }
    }
  } catch (RuntimeException $e) {
    $session->addMessage('error', $e->getMessage());
  } catch (Throwable $e) {
    error_log('action_nutrition_meal: ' . $e->getMessage());
    $session->addMessage('error', 'Something went wrong. Please try again.');
  }

  header('Location: ' . $redirect);
  exit;
?>
