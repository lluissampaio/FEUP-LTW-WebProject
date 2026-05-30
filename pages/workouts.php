<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();
  $session->requireLogin('login.php');

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../database/workout.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');
  require_once(__DIR__ . '/../templates/workouts.tpl.php');

  $db   = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());

  if (!$user || $user->role !== 'member') {
    $session->addMessage('error', 'Only members can access workout history.');
    header('Location: dashboard.php');
    exit;
  }

  $workouts = Workout::getForUser($db, $session->getId(), 1000);

  drawHeader($session, '', 'Workout History — Iron Haven', includeMain: false);
?>

  <div class="dashboard">
    <?php drawDashboardSidebar($user, 'workouts', $session->csrfToken()); ?>

    <main class="dashboard-main">
      <?php drawDashboardPageHeader('Workout history', 'Your training log', 'A complete record of your activity at Iron Haven.'); ?>
      <?php drawWorkoutsHistory($workouts, $session->csrfToken()); ?>
    </main>
  </div>

<?php drawFooter(false); ?>
