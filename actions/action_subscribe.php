<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/membership.class.php');
  require_once(__DIR__ . '/../database/notification.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/membership.php');

  $planId = isset($_POST['plan_id']) ? (int)$_POST['plan_id'] : 0;

  $db = getDatabaseConnection();
  try {
    $current = Membership::getForUser($db, $session->getId());
    $verb    = ($current && $current->isActive())
      ? ($planId > $current->planId ? 'upgraded to' : ($planId < $current->planId ? 'changed to' : 'renewed'))
      : 'subscribed to';

    Membership::subscribe($db, $session->getId(), $planId);
    $plan = Plan::getPlan($db, $planId);

    $session->addMessage('success', 'You have ' . $verb . ' the ' . ($plan ? $plan->name : 'selected') . ' plan.');
    Notification::send(
      $db, $session->getId(), 'system', 'Membership updated',
      'Your membership is now: ' . ($plan ? $plan->name : 'updated') . '.',
      'membership.php'
    );
  } catch (Throwable $e) {
    $session->addMessage('error', $e->getMessage());
  }

  header('Location: ../pages/membership.php');
  exit();
?>
