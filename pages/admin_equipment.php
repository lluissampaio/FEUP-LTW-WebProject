<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/equipment.class.php');
  require_once(__DIR__ . '/../database/location.class.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');
  require_once(__DIR__ . '/../templates/admin_equipment.tpl.php');

  $session = new Session();
  $session->requireLogin('login.php');
  if (!isAdminUser($session)) {
    $session->addMessage('error', 'Admin access required.');
    header('Location: index.php');
    exit;
  }

  $db   = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());

  $editing = null;
  if (isset($_GET['edit'])) $editing = Equipment::getEquipment($db, (int)$_GET['edit']);

  $items     = Equipment::getAll($db);
  $locations = Location::getLocations($db);
  $csrf      = $session->csrfToken();

  drawHeader($session, '', 'Admin · Equipment — Iron Haven', includeMain: false);
?>
  <div class="dashboard">
    <?php drawDashboardSidebar($user, 'admin_equipment', $session->csrfToken()); ?>
    <main id="main-content" class="dashboard-main">
      <?php drawDashboardPageHeader('Admin', 'Equipment inventory', 'Add, update availability or remove equipment across all locations.'); ?>
      <?php drawAdminEquipment($items, $locations, $csrf, $editing); ?>
    </main>
  </div>
<?php
  drawFooter(false);
?>
