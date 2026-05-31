<?php declare(strict_types = 1); require_once(__DIR__ . '/../utils/format.php'); ?>

<?php function drawDashboardSidebar(?User $user, string $activePage, string $csrf) { 

  $name = $user ? $user->name() : 'Member';
  $roleDisplay = $user ? $user->getRoleDisplay() : 'Member';
  $photoSrc = $user ? $user->getPhotoUrl() : '';

  $role = $user ? $user->role : 'member';
  $sidebarLabel = match ($role) {
    'trainer' => 'Trainer sidebar',
    'admin'   => 'Admin sidebar',
    default   => 'Member sidebar',
  };
?>

 <aside class="dashboard__sidebar" aria-label="<?= htmlspecialchars($sidebarLabel, ENT_QUOTES, 'UTF-8') ?>">
    <header class="dashboard__profile">
      <?php if ($photoSrc !== '') { ?>
        <img class="dashboard__profile-photo" src="<?= htmlspecialchars($photoSrc, ENT_QUOTES, 'UTF-8') ?>" alt="" aria-hidden="true">
      <?php } else { ?>
        <div class="dashboard__profile-photo dashboard__profile-photo--placeholder" aria-hidden="true">
          <?= htmlspecialchars(strtoupper(substr($name, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php } ?>
      <p class="dashboard__profile-name"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></p>
      <p class="dashboard__profile-since"><?= htmlspecialchars($roleDisplay, ENT_QUOTES, 'UTF-8') ?></p>
    </header>

    <nav class="dashboard__nav" aria-label="Dashboard navigation">
      <?php
        // Renders a sidebar link, marking it active (is-active + aria-current) when its slug matches.
        $navLink = function (string $href, string $slug, string $label) use ($activePage) {
          $isActive = $activePage === $slug;
          $classes = 'dashboard__nav-link' . ($isActive ? ' is-active' : '');
          echo '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"'
             . ' class="' . htmlspecialchars($classes, ENT_QUOTES, 'UTF-8') . '"'
             . ($isActive ? ' aria-current="page"' : '')
             . '>' . $label . '</a>';
        };
      ?>
      <?php $navLink('dashboard.php', 'dashboard', 'Dashboard'); ?>

      <?php if (!$user || $user->role === 'member') { ?>
        <?php $navLink('workouts.php', 'workouts', 'Workout history'); ?>
        <?php $navLink('nutrition.php', 'nutrition', 'Nutrition plan'); ?>
      <?php } ?>

      <?php $navLink('profile.php', 'account', 'Manage account'); ?>

      <?php if (!$user || $user->role === 'member') { ?>
        <?php $navLink('bookings.php', 'bookings', 'Personal training'); ?>
        <?php $navLink('reservations.php', 'reservations', 'Equipment Reservations'); ?>
        <?php $navLink('membership.php', 'membership', 'My membership'); ?>
        <?php $navLink('disputes.php', 'disputes', 'Support &amp; disputes'); ?>
      <?php } ?>

      <?php if ($user && $user->role === 'trainer') { ?>
        <?php $navLink('trainer_analytics.php', 'trainer_analytics', 'Analytics'); ?>
        <?php $navLink('trainer_classes.php', 'trainer_classes', 'My Classes &amp; Roster'); ?>
        <?php $navLink('trainer_clients.php', 'trainer_clients', 'Member Nutrition'); ?>
        <?php $navLink('bookings.php', 'bookings', 'Personal training'); ?>
        <?php $navLink('edit_trainer.php', 'edit_trainer', 'Edit public profile'); ?>
      <?php } ?>

      <?php if ($user && $user->role === 'admin') { ?>
        <?php $navLink('admin_classes.php', 'admin', 'Manage classes'); ?>
        <?php $navLink('admin_equipment.php', 'admin_equipment', 'Manage equipment'); ?>
        <?php $navLink('admin_users.php', 'admin_users', 'Manage users'); ?>
        <?php $navLink('admin_applications.php', 'admin_applications', 'Membership applications'); ?>
        <?php $navLink('admin_promotions.php', 'admin_promotions', 'Promotions'); ?>
        <?php $navLink('admin_analytics.php', 'analytics', 'Analytics'); ?>
        <?php $navLink('admin_disputes.php', 'disputes', 'Disputes'); ?>
      <?php } ?>

      <form action="../actions/action_logout.php" method="post" class="dashboard__logout">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="dashboard__nav-link dashboard__nav-link--signout">Sign out</button>
      </form>
    </nav>
  </aside>
<?php } ?>

<?php function drawDashboardPageHeader(string $eyebrow, string $heading, string $sub) { ?>
    <header class="dashboard__header">
      <p class="dashboard__eyebrow"><?= htmlspecialchars($eyebrow, ENT_QUOTES, 'UTF-8') ?></p>
      <h1 class="dashboard__heading"><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h1>
      <p class="dashboard__sub"><?= htmlspecialchars($sub, ENT_QUOTES, 'UTF-8') ?></p>
    </header>
<?php } ?>

<?php
  function drawAdminDashboard(array $adminCounts) {
?>
    <section class="dashboard__stats" aria-label="Catalog overview">
      <article class="stat-card">
        <p class="stat-card__label">Classes</p>
        <p class="stat-card__value"><?= (int)$adminCounts['Class'] ?></p>
        <p class="stat-card__note"><?= (int)$adminCounts['Schedule'] ?> sessions scheduled</p>
      </article>
      <article class="stat-card">
        <p class="stat-card__label">Equipment</p>
        <p class="stat-card__value"><?= (int)$adminCounts['Equipment'] ?></p>
        <p class="stat-card__note">Items tracked</p>
      </article>
      <article class="stat-card">
        <p class="stat-card__label">Members</p>
        <p class="stat-card__value"><?= (int)$adminCounts['User'] ?></p>
        <p class="stat-card__note"><?= (int)$adminCounts['Enrollment'] ?> active enrolments</p>
      </article>
      <article class="stat-card">
        <p class="stat-card__label">Engagement</p>
        <p class="stat-card__value"><?= (int)$adminCounts['Review'] ?></p>
        <p class="stat-card__note"><?= (int)$adminCounts['Waitlist'] ?> on waitlists</p>
      </article>
    </section>

    <section class="admin-section">
      <div class="section__inner admin-cards">
        <article class="card">
          <h2 class="card__title">Classes</h2>
          <p class="card__body">Create, edit or remove classes. Add sessions so they appear on the public schedule.</p>
          <a class="btn--primary" href="admin_classes.php">Manage classes</a>
        </article>
        <article class="card">
          <h2 class="card__title">Equipment</h2>
          <p class="card__body">Track inventory, availability and operational status of gym equipment.</p>
          <a class="btn--primary" href="admin_equipment.php">Manage equipment</a>
        </article>
        <article class="card">
          <h2 class="card__title">Users</h2>
          <p class="card__body">Promote, deactivate or remove member, trainer and admin accounts.</p>
          <a class="btn--primary" href="admin_users.php">Manage users</a>
        </article>
        <article class="card">
          <h2 class="card__title">Analytics</h2>
          <p class="card__body">Gym-wide metrics: popular classes, equipment usage, member retention and revenue.</p>
          <a class="btn--primary" href="admin_analytics.php">View analytics</a>
        </article>
        <article class="card">
          <h2 class="card__title">Disputes & feedback</h2>
          <p class="card__body">Triage member reports on equipment, classes and billing, and respond.</p>
          <a class="btn--primary" href="admin_disputes.php">Manage disputes</a>
        </article>
        <article class="card">
          <h2 class="card__title">Activity</h2>
          <p class="card__body"><?= (int)$adminCounts['Enrollment'] ?> enrolments &middot; <?= (int)$adminCounts['Waitlist'] ?> waitlist entries &middot; <?= (int)$adminCounts['Review'] ?> reviews</p>
          <a class="btn--outline" href="classes.php">View public schedule</a>
        </article>
      </div>
    </section>
<?php
  }
?>

<?php
  function drawTrainerDashboard(array $trainerStats) {
?>
    <section class="dashboard__stats" aria-label="Trainer overview">
      <article class="stat-card">
        <p class="stat-card__label">Total Enrollments</p>
        <p class="stat-card__value"><?= (int)$trainerStats['TotalEnrollments'] ?></p>
        <p class="stat-card__note">Across <?= (int)$trainerStats['TotalSessions'] ?> sessions</p>
      </article>
      <article class="stat-card">
        <p class="stat-card__label">Attendance Rate</p>
        <p class="stat-card__value"><?= (int)$trainerStats['AttendanceRate'] ?><span class="stat-unit">%</span></p>
        <p class="stat-card__note">Attended / Enrolled</p>
      </article>
      <article class="stat-card">
        <p class="stat-card__label">Average Rating</p>
        <p class="stat-card__value"><?= htmlspecialchars((string)($trainerStats['AverageRating'] ?? '—'), ENT_QUOTES, 'UTF-8') ?><span class="stat-unit">/5</span></p>
        <p class="stat-card__note">From <?= (int)$trainerStats['TotalReviews'] ?> reviews</p>
      </article>
    </section>

    <section class="admin-section">
      <div class="section__inner admin-cards">
        <article class="card">
          <h2 class="card__title">Analytics</h2>
          <p class="card__body">View detailed attendance, ratings, and engagement data for your classes.</p>
          <a class="btn--primary" href="trainer_analytics.php">View analytics</a>
        </article>
        <article class="card">
          <h2 class="card__title">My Classes & Roster</h2>
          <p class="card__body">Track and manage your assigned class schedule and enrolled members.</p>
          <a class="btn--primary" href="trainer_classes.php">Manage schedule</a>
        </article>
        <article class="card">
          <h2 class="card__title">Public Profile</h2>
          <p class="card__body">Manage your public profile, including bio, specializations, and certifications.</p>
          <a class="btn--outline" href="edit_trainer.php">Edit profile</a>
        </article>
      </div>
    </section>
<?php
  }
?>

<?php
  function drawMemberDashboard(int $totalWorkouts, float $activeHours, float $totalCalories, int $weeklyFrequency, array $workouts, string $csrf, ?NutritionPlan $nutritionPlan = null, bool $hasPlan = true) {
?>
    <?php if (!$hasPlan) { ?>
      <section class="dashboard__activity">
        <h2 class="dashboard__section-heading">Choose your membership</h2>
        <p class="activity-empty">Select a membership plan to unlock class enrolment, workout logging and support requests.</p>
        <p><a href="membership.php" class="btn--primary">Choose a plan</a></p>
      </section>
    <?php } ?>

    <section class="dashboard__stats" aria-label="Your statistics">
      <article class="stat-card">
        <p class="stat-card__label">Total workouts</p>
        <p class="stat-card__value"><?= $totalWorkouts ?></p>
        <p class="stat-card__note">Total activity recorded</p>
      </article>
      <article class="stat-card">
        <p class="stat-card__label">Active time</p>
        <p class="stat-card__value"><?= $activeHours ?>h</p>
        <p class="stat-card__note">Cumulative hours logged</p>
      </article>
      <article class="stat-card">
        <p class="stat-card__label">Estimated calories</p>
        <p class="stat-card__value"><?= number_format($totalCalories) ?></p>
        <p class="stat-card__note">Calories burned</p>
      </article>
      <article class="stat-card">
        <p class="stat-card__label">Weekly frequency</p>
        <p class="stat-card__value"><?= $weeklyFrequency ?> / 7</p>
        <p class="stat-card__note">Days this week</p>
      </article>
    </section>

    <?php if ($nutritionPlan) { ?>
      <section class="dashboard__activity">
        <h2 class="dashboard__section-heading">My Nutrition Plan - <?= htmlspecialchars($nutritionPlan->dietType, ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="macro-grid">
          <article class="stat-card">
            <p class="stat-card__label">Calories</p>
            <p class="stat-card__value"><?= number_format($nutritionPlan->dailyCalories) ?></p>
          </article>
          <article class="stat-card">
            <p class="stat-card__label">Protein</p>
            <p class="stat-card__value"><?= $nutritionPlan->protein ?>g</p>
          </article>
          <article class="stat-card">
            <p class="stat-card__label">Carbs</p>
            <p class="stat-card__value"><?= $nutritionPlan->carbs ?>g</p>
          </article>
          <article class="stat-card">
            <p class="stat-card__label">Fats</p>
            <p class="stat-card__value"><?= $nutritionPlan->fats ?>g</p>
          </article>
        </div>
        
        <?php if (!empty(trim($nutritionPlan->guidelines))) { ?>
          <div class="dashboard__notes">
            <h3 class="dashboard__notes-heading">Guidelines & Notes</h3>
            <p class="dashboard__notes-body"><?= htmlspecialchars(trim($nutritionPlan->guidelines), ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        <?php } ?>
      </section>
    <?php } ?>

    <section class="dashboard__activity">
      <h2 class="dashboard__section-heading">Log a workout</h2>
      <?php if (!$hasPlan) { ?>
        <p class="activity-empty">Choose a membership plan to start logging workouts.</p>
      <?php } else { ?>
      <form class="admin-form" method="post" action="../actions/action_workout.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action"     value="log">
        <label class="form-field">
          Type
          <input type="text" id="w-type" name="workout_type" required maxlength="60" placeholder="e.g. Strength, Cardio, Yoga">
        </label>
        <label class="form-field">
          Duration (min)
          <input type="number" id="w-duration" name="duration" min="1" max="600" step="1" value="45" required>
        </label>
        <label class="form-field">
          Calories (optional)
          <input type="number" id="w-calories" name="calories" min="0" max="5000" step="10" value="0">
        </label>
        <div class="form-actions">
          <button type="submit" class="btn--primary">Log workout</button>
        </div>
      </form>
      <?php } ?>
    </section>

    <section class="dashboard__activity" aria-labelledby="activity-heading">
      <h2 class="dashboard__section-heading" id="activity-heading">Recent activity</h2>

      <div class="activity-table__wrap">
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
                <td><?= htmlspecialchars(formatDate($w->date), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($w->workoutType, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int)$w->durationMinutes ?> min</td>
                <td><span class="status-tag status-tag--<?= htmlspecialchars(strtolower($w->status), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($w->status, ENT_QUOTES, 'UTF-8') ?></span></td>
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
          <a href="workouts.php" class="btn--outline btn--sm">View all history</a>
        </div>
      <?php } ?>
    </section>
<?php
  }
?>
