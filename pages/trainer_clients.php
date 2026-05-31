<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();
  $session->requireLogin('login.php');

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../database/trainer.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');

  require_once(__DIR__ . '/../templates/trainer_clients.tpl.php');

  $db   = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());

  if (!$user || $user->role !== 'trainer') {
    header('Location: dashboard.php');
    exit;
  }

  $clients = Trainer::getClients($db, $user->id);

  drawHeader($session, '', 'Member Nutrition — Iron Haven', includeMain: false);
?>

  <div class="dashboard">
    <?php drawDashboardSidebar($user, 'trainer_clients', $session->csrfToken()); ?>
    <main id="main-content" class="dashboard-main">
      <?php drawDashboardPageHeader('Trainer Management', 'Member Nutrition', 'Manage nutrition plans for your class members and personal training clients.'); ?>
      <?php drawTrainerClients($clients); ?>
    </main>
  </div>

<?php drawFooter(false); ?>
