<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();
  $session->redirectIfLoggedIn();

  require_once(__DIR__ . '/../templates/common.tpl.php');

  $csrf = $session->csrfToken();

  drawHeader($session, '', 'Sign In — Iron Haven', 'JOIN', 'register.php');
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

<?php drawFooter(); ?>
