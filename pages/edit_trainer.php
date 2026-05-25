<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();
  $session->requireLogin('login.php');

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../database/trainer.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');

  $db = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());

  if (!$user || $user->role !== 'trainer') {
    $session->addMessage('error', 'Unauthorized access.');
    header('Location: dashboard.php');
    exit();
  }

  $trainer = Trainer::getTrainer($db, $session->getId());

  drawHeader($session, '', 'Edit Profile — Iron Haven', 'DASHBOARD', 'dashboard.php');
?>

  <section class="dashboard">
    <?php drawDashboardSidebar($user, 'edit_trainer'); ?>

    <main class="dashboard-main">
      <header class="dashboard-header">
        <p class="dashboard-eyebrow">Trainer Management</p>
        <h1 class="dashboard-heading">Edit Profile</h1>
        <p class="dashboard-sub">Update your public trainer profile information.</p>
      </header>

      <section class="dashboard-activity">
        <form action="../actions/action_edit_trainer.php" method="post" class="auth-form" style="max-width: 600px;">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

          <div class="form-group">
            <label class="form-label" for="specialty">Specialty</label>
            <input class="form-input" type="text" id="specialty" name="specialty" 
                   value="<?= htmlspecialchars($trainer->specialty ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                   placeholder="e.g. Strength, Mobility, Pilates">
          </div>

          <div class="form-group">
            <label class="form-label" for="certifications">Certifications</label>
            <input class="form-input" type="text" id="certifications" name="certifications" 
                   value="<?= htmlspecialchars($trainer->certifications ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                   placeholder="e.g. NASM-CPT, CSCS">
          </div>

          <div class="form-group">
            <label class="form-label" for="bio">Bio</label>
            <textarea class="form-input" id="bio" name="bio" rows="5" 
                      placeholder="Tell us about yourself..."><?= htmlspecialchars($trainer->bio ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>

          <button type="submit" class="btn auth-btn">Save Changes</button>
        </form>
      </section>
    </main>
  </section>

<?php drawFooter(); ?>
