<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();
  $session->redirectIfLoggedIn();

  require_once(__DIR__ . '/../templates/common.tpl.php');

  $csrf = $session->csrfToken();

  drawHeader($session, '', 'Register — Iron Haven', 'SIGN IN', 'login.php');
?>

  <section class="auth-section">
    <div class="auth-card">
      <p class="auth-eyebrow">New membership</p>
      <h1 class="auth-heading">Join the Haven.</h1>
      <p class="auth-sub">Create your account to begin.</p>

      <form class="auth-form" method="post" action="../actions/action_register.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-field">
          <label for="register-name">Full name</label>
          <input type="text" id="register-name" name="name" required autocomplete="name">
        </div>

        <div class="form-field">
          <label for="register-email">Email</label>
          <input type="email" id="register-email" name="email" required autocomplete="email">
        </div>

        <div class="form-field">
          <label for="register-password">Password</label>
          <input type="password" id="register-password" name="password" required minlength="8" autocomplete="new-password">
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

<?php drawFooter(); ?>
