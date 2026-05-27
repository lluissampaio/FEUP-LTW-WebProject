<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');
  require_once(__DIR__ . '/../templates/admin_users.tpl.php');

  $session = new Session();
  $session->requireLogin('login.php');
  if (!isAdminUser($session)) {
    $session->addMessage('error', 'Admin access required.');
    header('Location: index.php');
    exit;
  }

  $db        = getDatabaseConnection();
  $me        = User::getUser($db, $session->getId());
  $filter    = $_GET['role'] ?? '';
  $validRole = in_array($filter, ['member','trainer','admin'], true) ? $filter : null;
  $users     = User::getAll($db, $validRole);
  $csrf      = $session->csrfToken();
  $meId      = $session->getId();

  drawHeader($session, '', 'Admin · Users — Iron Haven');
?>
  <section class="dashboard">
    <?php drawDashboardSidebar($me, 'admin_users', $session->csrfToken()); ?>
    <main class="dashboard-main">
      <?php drawDashboardPageHeader('Admin', 'Users', 'Promote, deactivate or remove accounts.'); ?>

      <?php drawAdminUsers($users, $csrf, $meId, $validRole); ?>
    </main>
  </section>
<?php
  drawFooter();
?>
