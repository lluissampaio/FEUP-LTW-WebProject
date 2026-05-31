<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/class.class.php');
  require_once(__DIR__ . '/../database/trainer.class.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');
  require_once(__DIR__ . '/../templates/admin_promotions.tpl.php');

  $session = new Session();
  $session->requireLogin('login.php');
  if (!isAdminUser($session)) {
    $session->addMessage('error', 'Admin access required.');
    header('Location: index.php');
    exit;
  }

  $db   = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());

  $classes  = GymClass::getClasses($db);
  $trainers = Trainer::getTrainers($db);
  $csrf     = $session->csrfToken();

  drawHeader($session, '', 'Admin · Promotions — Iron Haven', includeMain: false);
?>
  <div class="dashboard">
    <?php drawDashboardSidebar($user, 'admin_promotions', $session->csrfToken()); ?>
    <main id="main-content" class="dashboard-main">
      <?php drawDashboardPageHeader('Admin', 'Promotional features', 'Highlight classes and trainers to feature them on the public homepage.'); ?>
      <?php drawAdminPromotions($classes, $trainers, $csrf); ?>
    </main>
  </div>
<?php
  drawFooter(false);
?>
