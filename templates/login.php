<?php
$page_title       = 'Iron Haven — Sign In';
$page_description = 'Sign in to your Iron Haven member account.';
include __DIR__ . '/partials/header.php';
?>

  <main>
    <section class="auth" aria-labelledby="login-heading">
      <div class="auth__card">

        <h1 class="auth__title" id="login-heading">Welcome Back</h1>
        <p class="auth__subtitle">Sign in to continue your journey</p>

        <form class="form" id="login-form" method="post" novalidate>

          <div class="form__field">
            <label for="login-email" class="form__label">Email</label>
            <input type="email" id="login-email" name="email" class="form__input" placeholder="your@email.com" required
              autocomplete="email">
          </div>

          <div class="form__field">
            <label for="login-password" class="form__label">Password</label>
            <input type="password" id="login-password" name="password" class="form__input" placeholder="Your password"
              required autocomplete="current-password">
          </div>

          <div class="auth__options">
            <label class="auth__remember">
              <input type="checkbox" id="login-remember" name="remember">
              <span>Remember me</span>
            </label>
            <a href="#" class="auth__forgot">Forgot password?</a>
          </div>

          <button type="submit" class="btn btn--primary btn--block">Sign In</button>

        </form>

        <div class="auth__divider" role="separator" aria-hidden="true">
          <span>or</span>
        </div>

        <p class="auth__alt">
          Don't have an account? <a href="register.php" class="auth__alt-link">Create one</a>
        </p>

      </div>
    </section>
  </main>

<?php include __DIR__ . '/partials/footer.php'; ?>
