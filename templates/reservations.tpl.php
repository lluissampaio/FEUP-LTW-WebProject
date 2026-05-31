<?php declare(strict_types = 1); ?>

<?php require_once(__DIR__ . '/../utils/format.php'); ?>

<?php
  /**
   * Form to reserve a piece of equipment for a date + time slot.
   * $equipment   : array of Equipment ; $preselectId : equipment to pre-select (deep link)
   * $today       : 'YYYY-MM-DD' used as the date input's min and default.
   */
  function drawReserveForm(array $equipment, ?int $preselectId, string $today, string $csrf) {
    // Fixed half-hourly time slots from 06:00 to 21:00.
    $slots = array();
    for ($h = 6; $h <= 21; $h++) {
      $slots[] = sprintf('%02d:00', $h);
      if ($h < 21) $slots[] = sprintf('%02d:30', $h);
    }
?>
    <section class="dashboard__activity">
      <h2 class="dashboard__section-heading">New reservation</h2>
      <?php if (empty($equipment)) { ?>
        <p class="empty-state">No equipment is available to reserve yet.</p>
      <?php } else { ?>
      <form class="admin-form" id="reservation-form" method="post" action="../actions/action_reserve_equipment.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <label class="form-field">
          Equipment <span class="required-mark" aria-hidden="true">*</span>
          <select id="r-equipment" name="equipment_id" required>
            <option value="">Choose equipment</option>
            <?php foreach ($equipment as $e) {
              $label = $e->name . ' — ' . $e->type . ($e->locationName ? ' (' . $e->locationName . ')' : '');
            ?>
              <option value="<?= (int)$e->id ?>" data-quantity="<?= (int)$e->quantity ?>" <?= $preselectId === $e->id ? 'selected' : '' ?>>
                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
              </option>
            <?php } ?>
          </select>
        </label>
        <label class="form-field">
          Date <span class="required-mark" aria-hidden="true">*</span>
          <input type="date" id="r-date" name="reservation_date" required
                 min="<?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?>"
                 value="<?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label class="form-field">
          Time slot <span class="required-mark" aria-hidden="true">*</span>
          <select id="r-time" name="start_time" required>
            <?php foreach ($slots as $slot) { ?>
              <option value="<?= htmlspecialchars($slot, ENT_QUOTES, 'UTF-8') ?>" <?= $slot === '18:00' ? 'selected' : '' ?>>
                <?= htmlspecialchars($slot, ENT_QUOTES, 'UTF-8') ?>
              </option>
            <?php } ?>
          </select>
        </label>
        <label class="form-field">
          Duration (min) <span class="required-mark" aria-hidden="true">*</span>
          <select id="r-duration" name="duration" required>
            <option value="30">30</option>
            <option value="45">45</option>
            <option value="60" selected>60</option>
            <option value="90">90</option>
            <option value="120">120</option>
          </select>
        </label>
        <div class="form-actions">
          <p class="reservation-summary" id="reservation-summary" aria-live="polite"></p>
          <button type="submit" class="btn--primary" id="reservation-submit">Reserve equipment</button>
        </div>
      </form>
      <?php } ?>
    </section>

    <script src="../javascript/reservations.js" defer></script>
<?php } ?>

<?php
  /** A member's upcoming equipment reservations, with cancel controls. */
  function drawMyReservations(array $reservations, string $csrf) {
?>
    <section class="dashboard__activity" aria-labelledby="reservations-heading">
      <h2 class="dashboard__section-heading" id="reservations-heading">Your reservations</h2>
      <?php if (empty($reservations)) { ?>
        <p class="empty-state">No equipment reservations yet. Reserve a slot above.</p>
      <?php } else { ?>
        <div class="activity-table__wrap">
          <table class="activity-table">
            <thead>
              <tr>
                <th scope="col">Equipment</th>
                <th scope="col">Location</th>
                <th scope="col">Date</th>
                <th scope="col">Time</th>
                <th scope="col">Duration</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($reservations as $r) { ?>
                <tr>
                  <td><?= htmlspecialchars($r->equipmentName ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($r->locationName ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars(formatDate($r->reservationDate), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($r->startTime, ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= (int)$r->duration ?> min</td>
                  <td>
                    <form method="post" action="../actions/action_cancel_reservation.php" class="inline-form">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="reservation_id" value="<?= (int)$r->id ?>">
                      <button type="submit" class="btn--outline btn--sm" data-confirm="Cancel this reservation? This cannot be undone.">Cancel</button>
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
