<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();
  $session->requireLogin('login.php');

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');
  require_once(__DIR__ . '/../templates/profile.tpl.php');

  $db   = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());
  $csrf = $session->csrfToken();

  // Resolve the avatar: uploaded photos live in uploads/, seeded ones in images/.
  $photoUrl = null;
  if ($user && $user->photo) {
    if (is_file(__DIR__ . '/../uploads/' . $user->photo))      $photoUrl = '../uploads/' . $user->photo;
    elseif (is_file(__DIR__ . '/../images/' . $user->photo))   $photoUrl = '../images/' . $user->photo;
  }

  drawHeader($session, '', 'Profile — Iron Haven', includeMain: false);
?>

  <div class="dashboard">
    <?php drawDashboardSidebar($user, 'account', $session->csrfToken()); ?>
    <main id="main-content" class="dashboard__main">
      <?php drawDashboardPageHeader('Manage account', 'Your profile', 'Update your name, sign-in details and profile photo.'); ?>
      <?php drawProfileForm($user, $photoUrl, $csrf); ?>
    </main>
  </div>

<?php drawFooter(false); ?>
