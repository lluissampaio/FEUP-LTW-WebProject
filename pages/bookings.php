<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();
  $session->requireLogin('login.php');

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../database/trainer.class.php');
  require_once(__DIR__ . '/../database/booking.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');
  require_once(__DIR__ . '/../templates/bookings.tpl.php');

  $db   = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());
  $csrf = $session->csrfToken();

  $isTrainer = ($user && $user->role === 'trainer');

  if ($isTrainer) {
    $roster = Booking::getForTrainer($db, $user->id);
  } else {
    $myBookings     = Booking::getForUser($db, $session->getId());
    $trainers       = Trainer::getTrainers($db);
    $slotsByTrainer = [];
    foreach ($trainers as $t) {
      $slotsByTrainer[$t->id] = Booking::getAvailabilityForTrainer($db, $t->id);
    }
  }

  drawHeader($session, '', 'Personal Training — Iron Haven', includeMain: false);
?>

  <div class="dashboard">
    <?php drawDashboardSidebar($user, 'bookings', $session->csrfToken()); ?>

    <main class="dashboard-main">
      <?php if ($isTrainer) { ?>
        <?php drawDashboardPageHeader('Personal training', 'Your one-on-one roster', 'Members who have booked private sessions with you.'); ?>
        <?php drawTrainerRoster($roster); ?>
      <?php } else { ?>
        <?php drawDashboardPageHeader('Personal training', 'One-on-one sessions', 'Book private sessions with our coaches around their weekly availability.'); ?>
        <?php drawMyBookings($myBookings, $csrf); ?>
        <?php drawTrainerAvailability($trainers, $slotsByTrainer, $csrf); ?>
      <?php } ?>
    </main>
  </div>

<?php drawFooter(false); ?>
