<?php
  declare(strict_types = 1);

  function drawDashboardSidebar(?User $user, string $activePage = 'dashboard') {
    $name = $user ? $user->name() : 'Member';
    if ($user && $user->role === 'trainer')      $roleDisplay = 'Trainer';
    elseif ($user && $user->role === 'admin')    $roleDisplay = 'Administrator';
    elseif ($user && !empty($user->joinDate))    $roleDisplay = 'Member since ' . date('Y', strtotime($user->joinDate));
    else                                         $roleDisplay = 'Member';

    $photoSrc = '';
    if ($user && !empty($user->photo)) {
      $photoFile = __DIR__ . '/../uploads/' . $user->photo;
      if (is_file($photoFile)) $photoSrc = '../uploads/' . $user->photo;
    }
    $csrf = $_SESSION['csrf_token'] ?? '';
?>
    <aside class="dashboard-sidebar" aria-label="Member sidebar">
      <header class="dashboard-profile">
        <?php if ($photoSrc !== '') { ?>
          <img class="dashboard-profile-photo" src="<?= htmlspecialchars($photoSrc, ENT_QUOTES, 'UTF-8') ?>" alt="" aria-hidden="true">
        <?php } else { ?>
          <div class="dashboard-profile-photo dashboard-profile-photo--placeholder" aria-hidden="true">
            <?= htmlspecialchars(strtoupper(substr($name, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
          </div>
        <?php } ?>
        <p class="dashboard-profile-name"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></p>
        <p class="dashboard-profile-since"><?= htmlspecialchars($roleDisplay, ENT_QUOTES, 'UTF-8') ?></p>
      </header>

      <nav class="dashboard-nav" aria-label="Dashboard navigation">
        <a href="dashboard.php" class="dashboard-nav-link <?= $activePage === 'dashboard' ? 'is-active' : '' ?>">Dashboard</a>
        
        <?php if (!$user || $user->role === 'member') { ?>
          <a href="#workouts"  class="dashboard-nav-link <?= $activePage === 'workouts' ? 'is-active' : '' ?>">Workout history</a>
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
          <button type="submit" class="dashboard-nav-link dashboard-nav-link--signout">Sign out</button>
        </form>
      </nav>
    </aside>
<?php
  }
?>
