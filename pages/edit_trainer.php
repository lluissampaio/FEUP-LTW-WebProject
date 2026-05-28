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
  require_once(__DIR__ . '/../templates/edit_trainer.tpl.php');

  $db = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());

  if (!$user || $user->role !== 'trainer') {
    $session->addMessage('error', 'Unauthorized access.');
    header('Location: dashboard.php');
    exit();
  }

  $trainer = Trainer::getTrainer($db, $session->getId());

  drawHeader($session, '', 'Edit Profile — Iron Haven', 'DASHBOARD', 'dashboard.php');
?>

  <section class="dashboard">
    <?php drawDashboardSidebar($user, 'edit_trainer', $session->csrfToken()); ?>

    <main class="dashboard-main">
      <?php drawDashboardPageHeader('Trainer Management', 'Edit Profile', 'Update your public trainer profile information.'); ?>
      <?php drawEditTrainerForm($trainer, $session->csrfToken()); ?>
    </main>
  </section>

<?php drawFooter(); ?>
