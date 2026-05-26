<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();
  $session->requireLogin('login.php');

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');

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

  $name      = $session->getName() ?? 'Member';
  $firstName = explode(' ', $name)[0];

  drawHeader($session, '', 'Dashboard — Iron Haven', 'SIGN OUT', '#');
?>

  <section class="dashboard">

    <aside class="dashboard-sidebar" aria-label="Member sidebar">
      <header class="dashboard-profile">
        <div class="dashboard-profile-photo" aria-hidden="true"></div>
        <p class="dashboard-profile-name"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></p>
        <p class="dashboard-profile-since">Member since 2026</p>
      </header>

      <nav class="dashboard-nav" aria-label="Dashboard navigation">
        <a href="dashboard.php" class="dashboard-nav-link is-active">Dashboard</a>
        <?php if ($isAdmin) { ?>
          <a href="admin_classes.php"   class="dashboard-nav-link">Manage classes</a>
          <a href="admin_equipment.php" class="dashboard-nav-link">Manage equipment</a>
          <a href="classes.php"         class="dashboard-nav-link">Public schedule</a>
          <a href="notifications.php"   class="dashboard-nav-link">Notifications</a>
        <?php } else { ?>
          <a href="#workouts"     class="dashboard-nav-link">Workout history</a>
          <a href="#nutrition"    class="dashboard-nav-link">Nutrition plan</a>
          <a href="#account"      class="dashboard-nav-link">Manage account</a>
        <?php } ?>
        <form action="../actions/action_logout.php" method="post" class="dashboard-logout">
          <button type="submit" class="dashboard-nav-link dashboard-nav-link--signout">Sign out</button>
        </form>
      </nav>
    </aside>

    <main class="dashboard-main">

      <header class="dashboard-header">
        <p class="dashboard-eyebrow"><?= $isAdmin ? 'Admin console' : 'Member dashboard' ?></p>
        <h1 class="dashboard-heading">Welcome back, <?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>.</h1>
        <p class="dashboard-sub"><?= $isAdmin ? 'Manage the catalog, sessions and gym equipment.' : 'Today is a great day to train.' ?></p>
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
<<<<<<< Updated upstream
=======
              <h2 class="card-title">Users</h2>
              <p class="card-body">Promote, deactivate or remove member, trainer and admin accounts.</p>
              <a class="btn-primary" href="admin_users.php">Manage users</a>
            </article>
            <article class="card">
              <h2 class="card-title">Analytics</h2>
              <p class="card-body">Gym-wide metrics: popular classes, equipment usage, member retention and revenue.</p>
              <a class="btn-primary" href="admin_analytics.php">View analytics</a>
            </article>
            <article class="card">
              <h2 class="card-title">Disputes & feedback</h2>
              <p class="card-body">Triage member reports on equipment, classes and billing, and respond.</p>
              <a class="btn-primary" href="admin_disputes.php">Manage disputes</a>
            </article>
            <article class="card">
>>>>>>> Stashed changes
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
