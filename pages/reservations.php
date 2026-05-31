<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();
  $session->requireLogin('login.php');

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../database/equipment.class.php');
  require_once(__DIR__ . '/../database/reservation.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');
  require_once(__DIR__ . '/../templates/reservations.tpl.php');

  $db   = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());

  if (!$user || $user->role !== 'member') {
    $session->addMessage('error', 'Only members can reserve equipment.');
    header('Location: dashboard.php');
    exit;
  }

  $reservations = EquipmentReservation::getForUser($db, $session->getId());
  $equipment    = Equipment::getAll($db);
  $preselectId  = isset($_GET['equipment_id']) && $_GET['equipment_id'] !== '' ? (int)$_GET['equipment_id'] : null;
  $today        = date('Y-m-d');

  drawHeader($session, '', 'Equipment Reservations — Iron Haven', includeMain: false);
?>

  <div class="dashboard">
    <?php drawDashboardSidebar($user, 'reservations', $session->csrfToken()); ?>

    <main id="main-content" class="dashboard__main">
      <?php drawDashboardPageHeader('Equipment reservations', 'Reserve your spot', 'Book a piece of equipment for a date and time slot.'); ?>
      <?php drawReserveForm($equipment, $preselectId, $today, $session->csrfToken()); ?>
      <?php drawMyReservations($reservations, $session->csrfToken()); ?>
    </main>
  </div>

<?php drawFooter(false); ?>
