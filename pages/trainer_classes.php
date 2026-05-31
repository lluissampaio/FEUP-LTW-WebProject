<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();
  $session->requireLogin('login.php');

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../database/schedule.class.php');
  require_once(__DIR__ . '/../database/class.class.php');
  require_once(__DIR__ . '/../database/enrollment.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');
  require_once(__DIR__ . '/../templates/trainer_classes.tpl.php');

  $db = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());

  if (!$user || $user->role !== 'trainer') {
    $session->addMessage('error', 'Unauthorized access.');
    header('Location: dashboard.php');
    exit();
  }

  $schedules = Schedule::search($db, null, $user->id, null, null);
  $classes = GymClass::getByTrainer($db, $user->id);
  $rosters = [];
  foreach ($schedules as $s) {
    $rosters[$s->id] = Enrollment::getRosterForSchedule($db, $s->id);
  }

  $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

  drawHeader($session, '', 'My Classes & Roster — Iron Haven', includeMain: false);
?>

  <div class="dashboard">
    <?php drawDashboardSidebar($user, 'trainer_classes', $session->csrfToken()); ?>
    <main id="main-content" class="dashboard-main">
      <?php drawDashboardPageHeader('Trainer Management', 'My Classes & Roster', 'View your schedule and enrolled members.'); ?>
      <?php drawTrainerClasses($classes, $schedules, $rosters, $days, $session->csrfToken()); ?>
    </main>
  </div>

<?php drawFooter(false); ?>
