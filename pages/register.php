<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();
  $session->redirectIfLoggedIn();

  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/auth.tpl.php');

  $csrf = $session->csrfToken();

  drawHeader($session, '', 'Register — Iron Haven', 'SIGN IN', 'login.php');
?>

  <?php drawRegisterForm($csrf); ?>

<?php drawFooter(); ?>
