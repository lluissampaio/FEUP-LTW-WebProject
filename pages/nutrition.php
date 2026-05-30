<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();
  $session->requireLogin('login.php');

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../database/nutrition.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');
  require_once(__DIR__ . '/../templates/nutrition.tpl.php');

  $db   = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());

  if (!$user || $user->role !== 'member') {
    header('Location: dashboard.php');
    exit;
  }

  $plan = NutritionPlan::getPlanByUserId($db, $user->id);

  drawHeader($session, '', 'Nutrition Plan — Iron Haven', includeMain: false);
?>

  <div class="dashboard">
    <?php drawDashboardSidebar($user, 'nutrition', $session->csrfToken()); ?>
    <main class="dashboard-main">
      <?php drawDashboardPageHeader('Nutrition', 'Your Nutrition Plan', 'Manage your daily macros and dietary goals.'); ?>
      <?php drawNutritionPlan($plan, $session); ?>
    </main>
  </div>

<?php drawFooter(false); ?>
