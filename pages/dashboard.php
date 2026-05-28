<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();
  $session->requireLogin('login.php');

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');

  $db        = getDatabaseConnection();
  $user      = User::getUser($db, $session->getId());
  $isAdmin   = ($user && $user->role === 'admin');
  $isTrainer = ($user && $user->role === 'trainer');
  $isMember  = ($user && $user->role === 'member');

  $workouts      = [];
  $totalWorkouts = 0;
  $totalCalories = 0;
  $totalDuration = 0;
  $activeHours   = 0;
  $adminCounts   = [];
  $trainerStats  = [];

  if ($isAdmin) {
    foreach (['User','Class','Schedule','Enrollment','Equipment','Waitlist','Review'] as $t) {
      $adminCounts[$t] = (int)$db->query("SELECT COUNT(*) FROM $t")->fetchColumn();
    }
  } elseif ($isTrainer) {
    require_once(__DIR__ . '/../database/analytics.class.php');
    $trainerStats = TrainerAnalytics::getOverallStats($db, $user->id);
  } else {
    $stmt = $db->prepare('SELECT WorkoutId, WorkoutType, DurationMinutes, Calories, Date, Status FROM Workout WHERE UserId = ? ORDER BY Date DESC');
    $stmt->execute([$session->getId()]);
    $workouts = $stmt->fetchAll();

    $totalWorkouts = count($workouts);
    foreach ($workouts as $w) {
      if ($w['Status'] === 'Completed') {
        $totalCalories += (int)($w['Calories'] ?? 0);
        $totalDuration += (int)($w['DurationMinutes'] ?? 0);
      }
    }
    $activeHours = round($totalDuration / 60, 1);
  }

  $name      = $user ? $user->name() : ($session->getName() ?? 'Member');
  $firstName = explode(' ', $name)[0];

  drawHeader($session, '', 'Dashboard — Iron Haven', 'SIGN OUT', '#');
?>

  <section class="dashboard">
    <?php drawDashboardSidebar($user, 'dashboard', $session->csrfToken()); ?>

    <main class="dashboard-main">

      <?php if ($isAdmin) { ?>
        <?php drawDashboardPageHeader('Admin dashboard', "Welcome back, $firstName.", 'Manage gym operations, classes, and members.'); ?>
        <?php drawAdminDashboard($adminCounts); ?>
      <?php } elseif ($isTrainer) { ?>
        <?php drawDashboardPageHeader('Trainer dashboard', "Welcome back, $firstName.", 'Manage your classes, schedule, and view analytics.'); ?>
        <?php drawTrainerDashboard($trainerStats); ?>
      <?php } else { ?>
        <?php drawDashboardPageHeader('Member dashboard', "Welcome back, $firstName.", 'Today is a great day to train.'); ?>
        <?php drawMemberDashboard($totalWorkouts, $activeHours, $totalCalories, $workouts, $session->csrfToken()); ?>
      <?php } ?>
    </main>
  </section>

<?php drawFooter(); ?>
