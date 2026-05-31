<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/class.class.php');
  require_once(__DIR__ . '/../database/schedule.class.php');
  require_once(__DIR__ . '/../database/trainer.class.php');
  require_once(__DIR__ . '/../database/location.class.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');
  require_once(__DIR__ . '/../templates/admin_classes.tpl.php');

  $session = new Session();
  $session->requireLogin('login.php');
  if (!isAdminUser($session)) {
    $session->addMessage('error', 'Admin access required.');
    header('Location: index.php');
    exit;
  }

  $db   = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());

  $editing = null;
  if (isset($_GET['edit'])) $editing = GymClass::getClass($db, (int)$_GET['edit']);

  $classes   = GymClass::getClasses($db);
  $trainers  = Trainer::getTrainers($db);
  $locations = Location::getLocations($db);
  $csrf      = $session->csrfToken();
  $days      = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

  $editingSchedules = [];
  if ($editing) $editingSchedules = Schedule::getByClass($db, $editing->id);

  // Quick lookup: how many sessions each class has (so admins can spot
  // classes that won't show on the public schedule page yet).
  $sessionCounts = Schedule::getSessionCountsPerClass($db);

  drawHeader($session, '', 'Admin · Classes — Iron Haven', includeMain: false);
?>
  <div class="dashboard">
    <?php drawDashboardSidebar($user, 'admin', $session->csrfToken()); ?>
    <main id="main-content" class="dashboard-main">
      <?php drawDashboardPageHeader('Admin', 'Class catalog', 'Create, edit or remove classes and assign trainers.'); ?>
      <?php drawAdminClasses($classes, $trainers, $locations, $csrf, $days, $editing, $editingSchedules, $sessionCounts); ?>
    </main>
  </div>
<?php
  drawFooter(false);
?>
