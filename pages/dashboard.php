<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();
  $session->requireLogin('login.php');

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');
  require_once(__DIR__ . '/../database/nutrition.class.php');

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
  $hasPlan       = true;

  if ($isAdmin) {
    require_once(__DIR__ . '/../database/admin_analytics.class.php');
    $adminCounts = AdminAnalytics::getDashboardCounts($db);
  } elseif ($isTrainer) {
    require_once(__DIR__ . '/../database/trainer_analytics.class.php');
    $trainerStats = TrainerAnalytics::getOverallStats($db, $user->id);
  } else {
    require_once(__DIR__ . '/../database/workout.class.php');
    require_once(__DIR__ . '/../database/membership.class.php');
    $workouts = Workout::getForUser($db, $session->getId(), 1000);
    $nutritionPlan = NutritionPlan::getPlanByUserId($db, $session->getId());
    $hasPlan = Membership::userHasActivePlan($db, $session->getId());

    $totalWorkouts = count($workouts);
    $weeklyDays    = [];
    $currentYear   = date('o');//diz o ano
    $currentWeek   = date('W');//de 1 a 52

    foreach ($workouts as $w) {
      if ($w->status === 'Completed') {
        $totalCalories += (int)($w->calories ?? 0);
        $totalDuration += (int)($w->durationMinutes ?? 0);

        $wTime = strtotime($w->date);
        if (date('o', $wTime) === $currentYear && date('W', $wTime) === $currentWeek) {
          $weeklyDays[date('Y-m-d', $wTime)] = true;//para garantir que nao conta o mesmo dia 2 vezes
        }
      }
    }
    $activeHours = round($totalDuration / 60, 1);
    $weeklyFrequency = count($weeklyDays);//quantidade de dias na semana que o usuario treinou
  }

  $name      = $user ? $user->name() : ($session->getName() ?? 'Member');
  $firstName = explode(' ', $name)[0];

  drawHeader($session, '', 'Dashboard — Iron Haven', includeMain: false);
?>

  <div class="dashboard">
    <?php drawDashboardSidebar($user, 'dashboard', $session->csrfToken()); ?>

    <main id="main-content" class="dashboard__main">
      <?php if ($isAdmin) { ?>
        <?php drawDashboardPageHeader('Admin dashboard', "Welcome back, $firstName.", 'Manage gym operations, classes, and members.'); ?>
        <?php drawAdminDashboard($adminCounts); ?>
      <?php } elseif ($isTrainer) { ?>
        <?php drawDashboardPageHeader('Trainer dashboard', "Welcome back, $firstName.", 'Manage your classes, schedule, and view analytics.'); ?>
        <?php drawTrainerDashboard($trainerStats); ?>
      <?php } else { ?>
        <?php drawDashboardPageHeader('Member dashboard', "Welcome back, $firstName.", 'Today is a great day to train.'); ?>
        <?php drawMemberDashboard($totalWorkouts, $activeHours, $totalCalories, $weeklyFrequency, array_slice($workouts, 0, 3), $session->csrfToken(), $nutritionPlan, $hasPlan); ?>
      <?php } ?>
    </main>
  </div>

<?php drawFooter(false); ?>
