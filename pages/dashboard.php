<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();
  $session->requireLogin('login.php');

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');

  $db      = getDatabaseConnection();
  $isAdmin = isAdminUser($session);

  $workouts      = [];
  $totalWorkouts = 0;
  $totalCalories = 0;
  $totalDuration = 0;
  $activeHours   = 0;
  $adminCounts   = [];

  if ($isAdmin) {
    foreach (['User','Class','Schedule','Enrollment','Equipment','Waitlist','Review'] as $t) {
      $adminCounts[$t] = (int)$db->query("SELECT COUNT(*) FROM $t")->fetchColumn();
    }
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

  $user = User::getUser($db, $session->getId());
  $name      = $user ? $user->name() : ($session->getName() ?? 'Member');
  $firstName = explode(' ', $name)[0];

  drawHeader($session, '', 'Dashboard — Iron Haven', 'SIGN OUT', '#');
?>

  <section class="dashboard">
    <?php drawDashboardSidebar($user, 'dashboard'); ?>

    <main class="dashboard-main">

      <header class="dashboard-header">
        <p class="dashboard-eyebrow">Member dashboard</p>
        <h1 class="dashboard-heading">Welcome back, <?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>.</h1>
        <p class="dashboard-sub">Today is a great day to train.</p>
      </header>
      
      <?php if ($isAdmin) { ?>
        <section class="dashboard-stats" aria-label="Catalog overview">
          <article class="stat-card">
            <p class="stat-card-label">Classes</p>
            <p class="stat-card-value"><?= (int)$adminCounts['Class'] ?></p>
            <p class="stat-card-note"><?= (int)$adminCounts['Schedule'] ?> sessions scheduled</p>
          </article>
          <article class="stat-card">
            <p class="stat-card-label">Equipment</p>
            <p class="stat-card-value"><?= (int)$adminCounts['Equipment'] ?></p>
            <p class="stat-card-note">Items tracked</p>
          </article>
          <article class="stat-card">
            <p class="stat-card-label">Members</p>
            <p class="stat-card-value"><?= (int)$adminCounts['User'] ?></p>
            <p class="stat-card-note"><?= (int)$adminCounts['Enrollment'] ?> active enrolments</p>
          </article>
          <article class="stat-card">
            <p class="stat-card-label">Engagement</p>
            <p class="stat-card-value"><?= (int)$adminCounts['Review'] ?></p>
            <p class="stat-card-note"><?= (int)$adminCounts['Waitlist'] ?> on waitlists</p>
          </article>
        </section>

        <section class="admin-section">
          <div class="section-inner admin-cards">
            <article class="card">
              <h2 class="card-title">Classes</h2>
              <p class="card-body">Create, edit or remove classes. Add sessions so they appear on the public schedule.</p>
              <a class="btn-primary" href="admin_classes.php">Manage classes</a>
            </article>
            <article class="card">
              <h2 class="card-title">Equipment</h2>
              <p class="card-body">Track inventory, availability and operational status of gym equipment.</p>
              <a class="btn-primary" href="admin_equipment.php">Manage equipment</a>
            </article>
            <article class="card">
              <h2 class="card-title">Activity</h2>
              <p class="card-body"><?= (int)$adminCounts['Enrollment'] ?> enrolments &middot; <?= (int)$adminCounts['Waitlist'] ?> waitlist entries &middot; <?= (int)$adminCounts['Review'] ?> reviews</p>
              <a class="btn-outline" href="classes.php">View public schedule</a>
            </article>
          </div>
        </section>
      
      <?php } else { ?>
      
      <section class="dashboard-stats" aria-label="Your statistics">
        <article class="stat-card">
          <p class="stat-card-label">Total workouts</p>
          <p class="stat-card-value"><?= $totalWorkouts ?></p>
          <p class="stat-card-note">Total activity recorded</p>
        </article>

        <article class="stat-card">
          <p class="stat-card-label">Active time</p>
          <p class="stat-card-value"><?= $activeHours ?>h</p>
          <p class="stat-card-note">Cumulative hours logged</p>
        </article>

        <article class="stat-card">
          <p class="stat-card-label">Estimated calories</p>
          <p class="stat-card-value"><?= number_format($totalCalories) ?></p>
          <p class="stat-card-note">Calories burned</p>
        </article>

        <article class="stat-card">
          <p class="stat-card-label">Weekly frequency</p>
          <p class="stat-card-value">0 / 7</p>
          <p class="stat-card-note">Days this week</p>
        </article>
      </section>

      <section class="dashboard-activity" aria-labelledby="activity-heading">
        <h2 class="dashboard-section-heading" id="activity-heading">Recent activity</h2>

        <div class="activity-table-wrap">
          <table class="activity-table">
            <thead>
              <tr>
                <th scope="col">Date</th>
                <th scope="col">Workout type</th>
                <th scope="col">Duration</th>
                <th scope="col">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($workouts as $w) { ?>
                <tr>
                  <td><?= htmlspecialchars(date('d/m/Y', strtotime($w['Date'])), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($w['WorkoutType'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= (int)$w['DurationMinutes'] ?> min</td>
                  <td><span class="status-tag status-tag--<?= htmlspecialchars(strtolower($w['Status']), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($w['Status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                </tr>
              <?php } ?>
              <?php if (empty($workouts)) { ?>
                <tr>
                  <td colspan="4" class="activity-empty">No activity recorded yet. Start training.</td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </section>
     <?php } ?>
    </main>
  </section>

<?php drawFooter(); ?>
