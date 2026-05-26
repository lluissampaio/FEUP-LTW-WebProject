<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();
  $session->requireLogin('login.php');

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');

  $db   = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());
  $csrf = $session->csrfToken();

  // Resolve the avatar: uploaded photos live in uploads/, seeded ones in images/.
  $photoUrl = null;
  if ($user && $user->photo) {
    if (is_file(__DIR__ . '/../uploads/' . $user->photo))      $photoUrl = '../uploads/' . $user->photo;
    elseif (is_file(__DIR__ . '/../images/' . $user->photo))   $photoUrl = '../images/' . $user->photo;
  }

  drawHeader($session, '', 'Profile — Iron Haven', 'SIGN OUT', '#');
?>

  <section class="dashboard">
    <?php drawDashboardSidebar($user, 'account'); ?>

    <main class="dashboard-main">
      <header class="dashboard-header">
        <p class="dashboard-eyebrow">Manage account</p>
        <h1 class="dashboard-heading">Your profile</h1>
        <p class="dashboard-sub">Update your name, sign-in details and profile photo.</p>
      </header>

      <section class="profile-section">
        <form class="profile-form" method="post" action="../actions/action_edit_profile.php" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

          <div class="profile-photo-row">
            <div class="profile-photo-preview">
              <?php if ($photoUrl) { ?>
                <img src="<?= htmlspecialchars($photoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Current profile photo">
              <?php } else { ?>
                <span class="profile-photo-initials" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr($user->firstName, 0, 1) . substr($user->lastName, 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
              <?php } ?>
            </div>
            <div class="form-field">
              <label for="profile-photo">Profile photo</label>
              <input type="file" id="profile-photo" name="photo" accept="image/jpeg,image/png,image/webp">
              <p class="form-hint">JPG, PNG or WebP &middot; max 3&nbsp;MB</p>
            </div>
          </div>

          <div class="form-grid">
            <div class="form-field">
              <label for="profile-first">First name</label>
              <input type="text" id="profile-first" name="first_name" required value="<?= htmlspecialchars($user->firstName, ENT_QUOTES, 'UTF-8') ?>" autocomplete="given-name">
            </div>
            <div class="form-field">
              <label for="profile-last">Last name</label>
              <input type="text" id="profile-last" name="last_name" required value="<?= htmlspecialchars($user->lastName, ENT_QUOTES, 'UTF-8') ?>" autocomplete="family-name">
            </div>
          </div>

          <div class="form-field">
            <label for="profile-username">Username</label>
            <input type="text" id="profile-username" name="username" required value="<?= htmlspecialchars($user->username, ENT_QUOTES, 'UTF-8') ?>" autocomplete="username">
          </div>

          <div class="form-field">
            <label for="profile-email">Email</label>
            <input type="email" id="profile-email" name="email" required value="<?= htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8') ?>" autocomplete="email">
          </div>

          <fieldset class="profile-fieldset">
            <legend>Change password</legend>
            <p class="form-hint">Leave blank to keep your current password.</p>
            <div class="form-grid">
              <div class="form-field">
                <label for="profile-new-password">New password</label>
                <input type="password" id="profile-new-password" name="new_password" minlength="8" autocomplete="new-password">
              </div>
              <div class="form-field">
                <label for="profile-confirm-password">Confirm new password</label>
                <input type="password" id="profile-confirm-password" name="confirm_password" minlength="8" autocomplete="new-password">
              </div>
            </div>
          </fieldset>

          <button type="submit" class="btn-primary">Save changes</button>
        </form>
      </section>
    </main>
  </section>

<?php drawFooter(); ?>
