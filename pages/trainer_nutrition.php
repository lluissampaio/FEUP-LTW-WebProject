<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();
  $session->requireLogin('login.php');

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../database/trainer.class.php');
  require_once(__DIR__ . '/../database/nutrition.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');
  require_once(__DIR__ . '/../templates/nutrition.tpl.php');

  $db   = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());

  if (!$user || $user->role !== 'trainer') {
    header('Location: dashboard.php');
    exit;
  }

  $memberId = (int)($_GET['member_id'] ?? 0);
  if ($memberId <= 0) {
    header('Location: trainer_clients.php');
    exit;
  }

  // Verify that the target user is actually a client of this trainer
  $clients = Trainer::getClients($db, $user->id);
  $isClient = false;
  foreach ($clients as $c) {
    if ((int)$c['UserId'] === $memberId) {
      $isClient = true;
      break;
    }
  }

  if (!$isClient) {
    $session->addMessage('error', 'Unauthorized access to member.');
    header('Location: trainer_clients.php');
    exit;
  }

  $targetUser = User::getUser($db, $memberId);
  $plan       = NutritionPlan::getPlanByUserId($db, $memberId);
  $meals      = $plan ? NutritionMeal::getForPlan($db, $plan->planId) : array();

  drawHeader($session, '', 'Member Nutrition — Iron Haven', includeMain: false);
?>

  <div class="dashboard">
    <?php drawDashboardSidebar($user, 'trainer_clients', $session->csrfToken()); ?>
    <main id="main-content" class="dashboard-main">
      <?php drawDashboardPageHeader('Nutrition', $targetUser->firstName . '\'s Nutrition Plan', 'Manage dietary goals and macros for your client.'); ?>
      <?php drawNutritionPlan($plan, $session, $targetUser, $meals); ?>
    </main>
  </div>

<?php drawFooter(false); ?>
