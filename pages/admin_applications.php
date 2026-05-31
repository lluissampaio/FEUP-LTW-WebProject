<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../database/application.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');
  require_once(__DIR__ . '/../templates/admin_applications.tpl.php');

  $session = new Session();
  $session->requireLogin('login.php');
  if (!isAdminUser($session)) {
    $session->addMessage('error', 'Admin access required.');
    header('Location: index.php');
    exit;
  }

  $db          = getDatabaseConnection();
  $me          = User::getUser($db, $session->getId());
  $filter      = $_GET['status'] ?? '';
  $validStatus = in_array($filter, ['pending', 'accepted', 'rejected'], true) ? $filter : null;
  $apps        = MembershipApplication::getAll($db, $validStatus);
  $csrf        = $session->csrfToken();

  drawHeader($session, '', 'Admin · Applications — Iron Haven', includeMain: false);
?>
  <div class="dashboard">
    <?php drawDashboardSidebar($me, 'admin_applications', $session->csrfToken()); ?>
    <main id="main-content" class="dashboard-main">
      <?php drawDashboardPageHeader('Admin', 'Membership applications', 'Review applications and accept or reject them. Accepted emails can register an account.'); ?>
      <?php drawAdminApplications($apps, $csrf, $validStatus); ?>
    </main>
  </div>
<?php
  drawFooter(false);
?>
