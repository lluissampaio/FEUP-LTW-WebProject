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

  drawHeader($session, '', 'Personal Training — Iron Haven', 'SIGN OUT', '#');
?>

  <section class="dashboard">
    <?php drawDashboardSidebar($user, 'bookings'); ?>

    <main class="dashboard-main">
      <?php if ($isTrainer) { ?>
        <header class="dashboard-header">
          <p class="dashboard-eyebrow">Personal training</p>
          <h1 class="dashboard-heading">Your one-on-one roster</h1>
          <p class="dashboard-sub">Members who have booked private sessions with you.</p>
        </header>

        <section class="booking-block">
          <div class="activity-table-wrap">
            <table class="activity-table">
              <thead>
                <tr>
                  <th scope="col">Date</th>
                  <th scope="col">Member</th>
                  <th scope="col">Time</th>
                  <th scope="col">Duration</th>
                  <th scope="col">Notes</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($roster as $b) { ?>
                  <tr>
                    <td><?= htmlspecialchars(date('D j M', strtotime($b->bookingDate)), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($b->memberName ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string)$b->startTime, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= (int)$b->duration ?> min</td>
                    <td><?= htmlspecialchars($b->notes ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                  </tr>
                <?php } ?>
                <?php if (empty($roster)) { ?>
                  <tr><td colspan="5" class="activity-empty">No personal training sessions booked yet.</td></tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </section>
      <?php } else { ?>
        <header class="dashboard-header">
          <p class="dashboard-eyebrow">Personal training</p>
          <h1 class="dashboard-heading">One-on-one sessions</h1>
          <p class="dashboard-sub">Book private sessions with our coaches around their weekly availability.</p>
        </header>

        <?php drawMyBookings($myBookings, $csrf); ?>
        <?php drawTrainerAvailability($trainers, $slotsByTrainer, $csrf); ?>
      <?php } ?>
    </main>
  </section>

<?php drawFooter(); ?>
