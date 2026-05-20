<?php
$page_title       = 'Iron Haven — Register';
$page_description = 'Create your Iron Haven membership account.';
$nav_cta_label    = 'Login';
$nav_cta_href     = 'login.php';
include __DIR__ . '/partials/header.php';
?>

  <main>
    <section class="auth" aria-labelledby="register-heading">
      <div class="auth__card">

        <h1 class="auth__title" id="register-heading">Join us</h1>
        <p class="auth__subtitle">Create your account</p>

        <form class="form" id="register-form" method="post">

          <div class="form__field">
            <label for="register-name" class="form__label">Full Name</label>
            <input type="text" id="register-name" name="name" class="form__input" placeholder="João Paulo" required
              autocomplete="name">
          </div>

          <div class="form__field">
            <label for="register-email" class="form__label">Email</label>
            <input type="email" id="register-email" name="email" class="form__input" placeholder="your@email.com"
              required autocomplete="email">
          </div>

          <div class="form__field">
            <label for="register-password" class="form__label">Password</label>
            <input type="password" id="register-password" name="password" class="form__input"
              placeholder="Your password" required autocomplete="new-password" minlength="8">
          </div>

          <div class="form__field">
            <label for="register-confirm" class="form__label">Confirm Password</label>
            <input type="password" id="register-confirm" name="confirm_password" class="form__input"
              placeholder="Repeat your password" required autocomplete="new-password" minlength="8">
          </div>

          <button type="submit" class="btn btn--primary btn--block">Create Account</button>

        </form>

        <div class="auth__divider" role="separator" aria-hidden="true">
          <span>or</span>
        </div>

        <p class="auth__alt">
          Already have an account? <a href="login.php" class="auth__alt-link">Sign in</a>
        </p>

      </div>
    </section>
  </main>

<?php include __DIR__ . '/partials/footer.php'; ?>
