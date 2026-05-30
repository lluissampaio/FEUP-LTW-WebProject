<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../database/admin_analytics.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');

  require_once(__DIR__ . '/../templates/admin_analytics.tpl.php');

  $session = new Session();
  $session->requireLogin('login.php');
  if (!isAdminUser($session)) {
    $session->addMessage('error', 'Admin access required.');
    header('Location: index.php');
    exit();
  }

  $db   = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());

  $overview  = AdminAnalytics::getOverview($db);
  $popular   = AdminAnalytics::getPopularClasses($db);
  $equipment = AdminAnalytics::getEquipmentUsage($db);
  $retention = AdminAnalytics::getRetention($db);
  $planDist  = AdminAnalytics::getPlanDistribution($db);

  drawHeader($session, 'admin', 'Admin · Analytics — Iron Haven', includeMain: false);
?>

  <div class="dashboard">
    <?php drawDashboardSidebar($user, 'analytics', $session->csrfToken()); ?>
    <main class="dashboard-main">
      <?php drawDashboardPageHeader('Admin analytics', 'Gym-wide metrics', 'Engagement, equipment usage, retention and revenue at a glance.'); ?>
      <?php drawAdminAnalytics($overview, $popular, $equipment, $retention, $planDist); ?>
    </main>
  </div>

<?php drawFooter(false); ?>
