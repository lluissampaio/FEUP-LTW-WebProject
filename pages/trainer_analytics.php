<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();
  $session->requireLogin('login.php');

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../database/trainer_analytics.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');
  require_once(__DIR__ . '/../templates/trainer_analytics.tpl.php');

  $db = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());

  if (!$user || $user->role !== 'trainer') {
    $session->addMessage('error', 'Unauthorized access.');
    header('Location: dashboard.php');
    exit();
  }

  $overallStats = TrainerAnalytics::getOverallStats($db, $user->id);
  $classStats = TrainerAnalytics::getClassStats($db, $user->id);

  drawHeader($session, '', 'Trainer Analytics — Iron Haven', includeMain: false);
?>

  <div class="dashboard">
    <?php drawDashboardSidebar($user, 'trainer_analytics', $session->csrfToken()); ?>
    <main id="main-content" class="dashboard__main">
      <?php drawDashboardPageHeader('Trainer Management', 'Analytics Dashboard', 'Track attendance, ratings, and engagement for your classes.'); ?>
      <?php drawTrainerAnalytics($overallStats, $classStats); ?>
    </main>
  </div>

<?php drawFooter(false); ?>
