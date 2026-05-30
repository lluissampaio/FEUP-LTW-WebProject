<?php
  declare(strict_types = 1);

  function drawProfileForm(User $user, ?string $photoUrl, string $csrf) {
?>
  <section class="profile-section">
    <form class="profile-form" method="post" action="../actions/action_edit_profile.php" enctype="multipart/form-data" data-validate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

      <div class="profile-photo-row">
        <div class="profile-photo-preview">
          <?php if ($photoUrl) { ?>
            <img src="<?= htmlspecialchars($photoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Current profile photo">
          <?php } else { ?>
            <span class="profile-photo-initials" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr($user->firstName, 0, 1) . substr($user->lastName, 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
          <?php } ?>
        </div>
        <label class="form-field">
          Profile photo
          <input type="file" id="profile-photo" name="photo" accept="image/jpeg,image/png,image/webp">
          <p class="form-hint">JPG, PNG or WebP &middot; max 3&nbsp;MB</p>
        </label>
      </div>

      <div class="form-grid">
        <label class="form-field">
          First name
          <input type="text" id="profile-first" name="first_name" required value="<?= htmlspecialchars($user->firstName, ENT_QUOTES, 'UTF-8') ?>" autocomplete="given-name">
        </label>
        <label class="form-field">
          Last name
          <input type="text" id="profile-last" name="last_name" required value="<?= htmlspecialchars($user->lastName, ENT_QUOTES, 'UTF-8') ?>" autocomplete="family-name">
        </label>
      </div>

      <label class="form-field">
        Username
        <input type="text" id="profile-username" name="username" required value="<?= htmlspecialchars($user->username, ENT_QUOTES, 'UTF-8') ?>" autocomplete="username" maxlength="20" pattern="[a-zA-Z0-9_]{3,20}" title="3 to 20 letters, numbers, or underscores">
      </label>

      <label class="form-field">
        Email
        <input type="email" id="profile-email" name="email" required value="<?= htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8') ?>" autocomplete="email">
      </label>

      <fieldset class="profile-fieldset">
        <legend>Change password</legend>
        <p class="form-hint">Leave blank to keep your current password.</p>
        <div class="form-grid">
          <label class="form-field">
            New password
            <input type="password" id="profile-new-password" name="new_password" minlength="8" autocomplete="new-password" data-strength>
          </label>
          <label class="form-field">
            Confirm new password
            <input type="password" id="profile-confirm-password" name="confirm_password" minlength="8" autocomplete="new-password">
          </label>
        </div>
      </fieldset>

      <button type="submit" class="btn-primary">Save changes</button>
    </form>
  </section>

  <script src="../javascript/form-validate.js" defer></script>
<?php
  }
?>
