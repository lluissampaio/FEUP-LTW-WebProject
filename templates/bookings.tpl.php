<?php declare(strict_types = 1); ?>

<?php require_once(__DIR__ . '/../utils/format.php'); ?>

<?php
  /** Trainer's one-on-one sessions roster. */
  function drawTrainerRoster(array $roster) {
?>
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
              <td><?= htmlspecialchars(formatDate($b->bookingDate), ENT_QUOTES, 'UTF-8') ?></td>
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
<?php } ?>

<?php
  /** Member's upcoming one-on-one sessions, with cancel controls. */
  function drawMyBookings(array $bookings, string $csrf) {
?>
  <section class="booking-block">
    <h2 class="dashboard-section-heading">Your upcoming sessions</h2>
    <?php if (empty($bookings)) { ?>
      <p class="activity-empty">No personal training sessions booked yet. Choose a slot below.</p>
    <?php } else { ?>
      <div class="activity-table-wrap">
        <table class="activity-table">
          <thead>
            <tr>
              <th scope="col">Date</th>
              <th scope="col">Trainer</th>
              <th scope="col">Time</th>
              <th scope="col">Duration</th>
              <th scope="col"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($bookings as $b) { ?>
              <tr>
                <td><?= htmlspecialchars(formatDate($b->bookingDate), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($b->trainerName ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string)$b->startTime, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int)$b->duration ?> min</td>
                <td>
                  <form method="post" action="../actions/action_cancel_booking.php" class="inline-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="booking_id" value="<?= (int)$b->id ?>">
                    <button type="submit" class="btn-outline btn-sm" data-confirm="Cancel this session? This cannot be undone.">Cancel</button>
                  </form>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    <?php } ?>
  </section>
<?php } ?>

<?php
  /**
   * Browse trainer availability and book a slot.
   * $trainers : array of Trainer ; $slotsByTrainer : [trainerId => availability rows]
   */
  function drawTrainerAvailability(array $trainers, array $slotsByTrainer, ?Membership $membership, string $csrf) {
    $limit = $membership && $membership->plan ? $membership->plan->ptSessionsLimit : 0;
?>
  <section class="booking-block">
    <h2 class="dashboard-section-heading">Book a trainer</h2>
    <div class="booking-trainer-grid">
      <?php foreach ($trainers as $t) {
        $slots = $slotsByTrainer[$t->id] ?? [];
        if (empty($slots)) continue;
      ?>
        <article class="booking-trainer-card">
          <header class="booking-trainer-head">
            <h3 class="booking-trainer-name"><?= htmlspecialchars($t->name(), ENT_QUOTES, 'UTF-8') ?></h3>
            <?php if ($t->specialty) { ?>
              <p class="booking-trainer-specialty"><?= htmlspecialchars($t->specialty, ENT_QUOTES, 'UTF-8') ?></p>
            <?php } ?>
          </header>
          <ul class="booking-slot-list">
            <?php foreach ($slots as $s) { ?>
              <li class="booking-slot">
                <span class="booking-slot-when">
                  <?= htmlspecialchars($s['DayOfWeek'] . ' ' . $s['StartTime'], ENT_QUOTES, 'UTF-8') ?>
                  <span class="booking-slot-next">next: <?= htmlspecialchars(formatDate($s['NextDate']), ENT_QUOTES, 'UTF-8') ?></span>
                </span>
                <?php if (!empty($s['IsBooked'])) { ?>
                  <span class="booking-slot-taken">Booked</span>
                <?php } else if ($limit === 0) { ?>
                  <span class="booking-slot-taken" style="color:var(--text-muted, #888); border-color:var(--border-color, #444);">Upgrade Plan</span>
                <?php } else if ($limit > 0 && isset($s['MonthUsage']) && $s['MonthUsage'] >= $limit) { ?>
                  <span class="booking-slot-taken" style="color:var(--text-muted, #888); border-color:var(--border-color, #444);">Limit Reached</span>
                <?php } else { ?>
                  <form method="post" action="../actions/action_book_trainer.php" class="inline-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="availability_id" value="<?= (int)$s['AvailabilityId'] ?>">
                    <button type="submit" class="btn-primary btn-sm">Book</button>
                  </form>
                <?php } ?>
              </li>
            <?php } ?>
          </ul>
        </article>
      <?php } ?>
    </div>
  </section>
<?php } ?>
