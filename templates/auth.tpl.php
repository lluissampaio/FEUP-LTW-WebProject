<?php
  declare(strict_types = 1);

  function drawLoginForm(string $csrf) {
?>
  <section class="auth-section">
    <div class="auth-card">
      <p class="auth-eyebrow">Member access</p>
      <h1 class="auth-heading">Welcome back.</h1>
      <p class="auth-sub">Enter your credentials to access the Haven.</p>

      <form class="auth-form" method="post" action="../actions/action_login.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-field">
          <label for="login-id">Email or username</label>
          <input type="text" id="login-id" name="identifier" required autocomplete="username">
        </div>

        <div class="form-field">
          <label for="login-password">Password</label>
          <input type="password" id="login-password" name="password" required autocomplete="current-password">
        </div>

        <button type="submit" class="btn-primary auth-submit">Sign in</button>
      </form>

      <p class="auth-alt">New here? <a href="register.php">Apply for membership</a></p>
    </div>
  </section>
<?php
  }

  function drawRegisterForm(string $csrf) {
?>
  <section class="auth-section">
    <div class="auth-card">
      <p class="auth-eyebrow">New membership</p>
      <h1 class="auth-heading">Join the Haven.</h1>
      <p class="auth-sub">Create your account to get started. You can apply for a membership once you're signed in.</p>

      <form class="auth-form" method="post" action="../actions/action_register.php" data-validate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-field">
          <label for="register-name">Full name</label>
          <input type="text" id="register-name" name="name" required autocomplete="name">
        </div>

        <div class="form-field">
          <label for="register-username">Username</label>
          <input type="text" id="register-username" name="username" required autocomplete="username" pattern="[a-zA-Z0-9_]{3,20}" title="3 to 20 letters, numbers, or underscores">
        </div>

        <div class="form-field">
          <label for="register-email">Email</label>
          <input type="email" id="register-email" name="email" required autocomplete="email">
        </div>

        <div class="form-field">
          <label for="register-password">Password</label>
          <input type="password" id="register-password" name="password" required minlength="8" autocomplete="new-password" data-strength>
        </div>

        <div class="form-field">
          <label for="register-confirm">Confirm password</label>
          <input type="password" id="register-confirm" name="confirm_password" required minlength="8" autocomplete="new-password">
        </div>

        <button type="submit" class="btn-primary auth-submit">Create account</button>
      </form>

      <p class="auth-alt">Already a member? <a href="login.php">Sign in</a></p>
    </div>
  </section>

  <script src="../javascript/form-validate.js" defer></script>
<?php
  }
?>
