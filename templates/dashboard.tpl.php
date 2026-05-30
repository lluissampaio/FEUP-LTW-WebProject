<?php declare(strict_types = 1); ?>

<?php function drawDashboardSidebar(?User $user, string $activePage, string $csrf) { 

  $name = $user ? $user->name() : 'Member';
  $roleDisplay = $user ? $user->getRoleDisplay() : 'Member';
  $photoSrc = $user ? $user->getPhotoUrl() : '';
?>
 
 <aside class="dashboard-sidebar" aria-label="Member sidebar">
    <header class="dashboard-profile">
      <?php if ($photoSrc !== '') { ?>
        <img class="dashboard-profile-photo" src="<?= htmlspecialchars($photoSrc, ENT_QUOTES, 'UTF-8') ?>" alt="" aria-hidden="true">
      <?php } else { ?>
        <div class="dashboard-profile-photo dashboard-profile-photo-placeholder" aria-hidden="true">
          <?= htmlspecialchars(strtoupper(substr($name, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php } ?>
      <p class="dashboard-profile-name"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></p>
      <p class="dashboard-profile-since"><?= htmlspecialchars($roleDisplay, ENT_QUOTES, 'UTF-8') ?></p>
    </header>

    <nav class="dashboard-nav" aria-label="Dashboard navigation">
      <a href="dashboard.php" class="dashboard-nav-link <?= $activePage === 'dashboard' ? 'is-active' : '' ?>">Dashboard</a>
      
      <?php if (!$user || $user->role === 'member') { ?>
        <a href="workouts.php"  class="dashboard-nav-link <?= $activePage === 'workouts' ? 'is-active' : '' ?>">Workout history</a>
        <a href="nutrition.php" class="dashboard-nav-link <?= $activePage === 'nutrition' ? 'is-active' : '' ?>">Nutrition plan</a>
      <?php } ?>

      <a href="profile.php" class="dashboard-nav-link <?= $activePage === 'account' ? 'is-active' : '' ?>">Manage account</a>

      <?php if (!$user || $user->role === 'member') { ?>
        <a href="bookings.php"   class="dashboard-nav-link <?= $activePage === 'bookings' ? 'is-active' : '' ?>">Personal training</a>
        <a href="membership.php" class="dashboard-nav-link <?= $activePage === 'membership' ? 'is-active' : '' ?>">My membership</a>
        <a href="disputes.php"   class="dashboard-nav-link <?= $activePage === 'disputes' ? 'is-active' : '' ?>">Support &amp; disputes</a>
      <?php } ?>
      
      <?php if ($user && $user->role === 'trainer') { ?>
        <a href="trainer_analytics.php" class="dashboard-nav-link <?= $activePage === 'trainer_analytics' ? 'is-active' : '' ?>">Analytics</a>
        <a href="trainer_classes.php" class="dashboard-nav-link <?= $activePage === 'trainer_classes' ? 'is-active' : '' ?>">My Classes & Roster</a>
        <a href="trainer_clients.php" class="dashboard-nav-link <?= $activePage === 'trainer_clients' ? 'is-active' : '' ?>">Member Nutrition</a>
        <a href="bookings.php" class="dashboard-nav-link <?= $activePage === 'bookings' ? 'is-active' : '' ?>">Personal training</a>
        <a href="edit_trainer.php" class="dashboard-nav-link <?= $activePage === 'edit_trainer' ? 'is-active' : '' ?>">Edit public profile</a>
      <?php } ?>

      <?php if ($user && $user->role === 'admin') { ?>
        <a href="admin_classes.php" class="dashboard-nav-link <?= $activePage === 'admin' ? 'is-active' : '' ?>">Manage classes</a>
        <a href="admin_equipment.php" class="dashboard-nav-link <?= $activePage === 'admin_equipment' ? 'is-active' : '' ?>">Manage equipment</a>
        <a href="admin_users.php" class="dashboard-nav-link <?= $activePage === 'admin_users' ? 'is-active' : '' ?>">Manage users</a>
        <a href="admin_analytics.php" class="dashboard-nav-link <?= $activePage === 'analytics' ? 'is-active' : '' ?>">Analytics</a>
        <a href="admin_disputes.php" class="dashboard-nav-link <?= $activePage === 'disputes' ? 'is-active' : '' ?>">Disputes</a>
      <?php } ?>
        
      <form action="../actions/action_logout.php" method="post" class="dashboard-logout">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="dashboard-nav-link dashboard-nav-link-signout">Sign out</button>
      </form>
    </nav>
  </aside>
<?php } ?>

<?php function drawDashboardPageHeader(string $eyebrow, string $heading, string $sub) { ?>
    <header class="dashboard-header">
      <p class="dashboard-eyebrow"><?= htmlspecialchars($eyebrow, ENT_QUOTES, 'UTF-8') ?></p>
      <h1 class="dashboard-heading"><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h1>
      <p class="dashboard-sub"><?= htmlspecialchars($sub, ENT_QUOTES, 'UTF-8') ?></p>
    </header>
<?php } ?>

<?php
  function drawAdminDashboard(array $adminCounts) {
?>
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
          <h2 class="card-title">Activity</h2>
          <p class="card-body"><?= (int)$adminCounts['Enrollment'] ?> enrolments &middot; <?= (int)$adminCounts['Waitlist'] ?> waitlist entries &middot; <?= (int)$adminCounts['Review'] ?> reviews</p>
          <a class="btn-outline" href="classes.php">View public schedule</a>
        </article>
      </div>
    </section>
<?php
  }
?>

<?php
  function drawTrainerDashboard(array $trainerStats) {
?>
    <section class="dashboard-stats" aria-label="Trainer overview">
      <article class="stat-card">
        <p class="stat-card-label">Total Enrollments</p>
        <p class="stat-card-value"><?= (int)$trainerStats['TotalEnrollments'] ?></p>
        <p class="stat-card-note">Across <?= (int)$trainerStats['TotalSessions'] ?> sessions</p>
      </article>
      <article class="stat-card">
        <p class="stat-card-label">Attendance Rate</p>
        <p class="stat-card-value"><?= (int)$trainerStats['AttendanceRate'] ?><span class="stat-unit">%</span></p>
        <p class="stat-card-note">Attended / Enrolled</p>
      </article>
      <article class="stat-card">
        <p class="stat-card-label">Average Rating</p>
        <p class="stat-card-value"><?= htmlspecialchars((string)($trainerStats['AverageRating'] ?? '—'), ENT_QUOTES, 'UTF-8') ?><span class="stat-unit">/5</span></p>
        <p class="stat-card-note">From <?= (int)$trainerStats['TotalReviews'] ?> reviews</p>
      </article>
    </section>

    <section class="admin-section">
      <div class="section-inner admin-cards">
        <article class="card">
          <h2 class="card-title">Analytics</h2>
          <p class="card-body">View detailed attendance, ratings, and engagement data for your classes.</p>
          <a class="btn-primary" href="trainer_analytics.php">View analytics</a>
        </article>
        <article class="card">
          <h2 class="card-title">My Classes & Roster</h2>
          <p class="card-body">Track and manage your assigned class schedule and enrolled members.</p>
          <a class="btn-primary" href="trainer_classes.php">Manage schedule</a>
        </article>
        <article class="card">
          <h2 class="card-title">Public Profile</h2>
          <p class="card-body">Manage your public profile, including bio, specializations, and certifications.</p>
          <a class="btn-outline" href="edit_trainer.php">Edit profile</a>
        </article>
      </div>
    </section>
<?php
  }
?>

<?php
  function drawMemberDashboard(int $totalWorkouts, float $activeHours, float $totalCalories, int $weeklyFrequency, array $workouts, string $csrf, ?NutritionPlan $nutritionPlan = null) {
?>
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
        <p class="stat-card-value"><?= $weeklyFrequency ?> / 7</p>
        <p class="stat-card-note">Days this week</p>
      </article>
    </section>

    <?php if ($nutritionPlan) { ?>
      <section class="dashboard-activity">
        <h2 class="dashboard-section-heading">My Nutrition Plan - <?= htmlspecialchars($nutritionPlan->dietType, ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="macro-grid">
          <article class="stat-card">
            <p class="stat-card-label">Calories</p>
            <p class="stat-card-value"><?= number_format($nutritionPlan->dailyCalories) ?></p>
          </article>
          <article class="stat-card">
            <p class="stat-card-label">Protein</p>
            <p class="stat-card-value"><?= $nutritionPlan->protein ?>g</p>
          </article>
          <article class="stat-card">
            <p class="stat-card-label">Carbs</p>
            <p class="stat-card-value"><?= $nutritionPlan->carbs ?>g</p>
          </article>
          <article class="stat-card">
            <p class="stat-card-label">Fats</p>
            <p class="stat-card-value"><?= $nutritionPlan->fats ?>g</p>
          </article>
        </div>
        
        <?php if (!empty(trim($nutritionPlan->guidelines))) { ?>
          <div class="dashboard-notes">
            <h3 class="dashboard-notes-heading">Guidelines & Notes</h3>
            <p class="dashboard-notes-body"><?= htmlspecialchars(trim($nutritionPlan->guidelines), ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        <?php } ?>
      </section>
    <?php } ?>

    <section class="dashboard-activity">
      <h2 class="dashboard-section-heading">Log a workout</h2>
      <form class="admin-form" method="post" action="../actions/action_workout.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action"     value="log">
        <div class="form-field">
          <label for="w-type">Type</label>
          <input type="text" id="w-type" name="workout_type" required maxlength="60" placeholder="e.g. Strength, Cardio, Yoga">
        </div>
        <div class="form-field">
          <label for="w-duration">Duration (min)</label>
          <input type="number" id="w-duration" name="duration" min="1" max="600" step="1" value="45" required>
        </div>
        <div class="form-field">
          <label for="w-calories">Calories (optional)</label>
          <input type="number" id="w-calories" name="calories" min="0" max="5000" step="10" value="0">
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-primary">Log workout</button>
        </div>
      </form>
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
                <td><?= htmlspecialchars(date('d/m/Y', strtotime($w->date)), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($w->workoutType, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int)$w->durationMinutes ?> min</td>
                <td><span class="status-tag status-tag-<?= htmlspecialchars(strtolower($w->status), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($w->status, ENT_QUOTES, 'UTF-8') ?></span></td>
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
      <?php if ($totalWorkouts > count($workouts)) { ?>
        <div style="margin-top: var(--spacing-md, 1.5rem); text-align: center;">
          <a href="workouts.php" class="btn-outline btn-sm">View all history</a>
        </div>
      <?php } ?>
    </section>
<?php
  }
?>
