<?php declare(strict_types = 1); ?>

<?php function drawClassFilters(array $ctx) { ?>
  <section class="filter-section" aria-labelledby="filter-heading">
    <div class="section-inner">
      <h2 class="visually-hidden" id="filter-heading">Filter classes</h2>
      <form class="filter-form" id="class-filter-form" method="get" action="classes.php">
        <div class="filter-field">
          <label for="f-type">Type</label>
          <select id="f-type" name="type">
            <option value="">All types</option>
            <?php foreach ($ctx['types'] as $t) { ?>
              <option value="<?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?>" <?= $ctx['type'] === $t ? 'selected' : '' ?>><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="filter-field">
          <label for="f-trainer">Trainer</label>
          <select id="f-trainer" name="trainer">
            <option value="">All trainers</option>
            <?php foreach ($ctx['trainers'] as $tr) { ?>
              <option value="<?= (int)$tr->id ?>" <?= $ctx['trainer'] === $tr->id ? 'selected' : '' ?>><?= htmlspecialchars($tr->name(), ENT_QUOTES, 'UTF-8') ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="filter-field">
          <label for="f-location">Location</label>
          <select id="f-location" name="location">
            <option value="">All locations</option>
            <?php foreach ($ctx['locations'] as $l) { ?>
              <option value="<?= (int)$l->id ?>" <?= $ctx['location'] === $l->id ? 'selected' : '' ?>><?= htmlspecialchars($l->city, ENT_QUOTES, 'UTF-8') ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="filter-field">
          <label for="f-day">Day</label>
          <select id="f-day" name="day">
            <option value="">Any day</option>
            <?php foreach ($ctx['days'] as $d) { ?>
              <option value="<?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?>" <?= $ctx['day'] === $d ? 'selected' : '' ?>><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="filter-field">
          <label for="f-time">Time</label>
          <select id="f-time" name="time">
            <option value="">Any time</option>
            <option value="morning"   <?= $ctx['time'] === 'morning'   ? 'selected' : '' ?>>Morning (before 12:00)</option>
            <option value="afternoon" <?= $ctx['time'] === 'afternoon' ? 'selected' : '' ?>>Afternoon (12:00–17:00)</option>
            <option value="evening"   <?= $ctx['time'] === 'evening'   ? 'selected' : '' ?>>Evening (after 17:00)</option>
          </select>
        </div>
        <div class="filter-actions">
          <button type="submit" class="btn-primary">Filter</button>
          <a href="classes.php" class="btn-outline">Reset</a>
        </div>
      </form>
    </div>
  </section>
<?php } ?>

<?php function drawScheduleList(array $schedules, array $myEnrolled, array $myWaiting, bool $isLoggedIn, string $csrf) { ?>
  <section class="schedule-list-section" aria-labelledby="schedule-list-heading">
    <div class="section-inner">
      <p class="section-label">01 &mdash; This Week</p>
      <h2 class="section-heading" id="schedule-list-heading">Schedule (<span id="schedule-count"><?= count($schedules) ?></span>)</h2>

      <p class="empty-state" id="schedule-empty"<?= count($schedules) === 0 ? '' : ' hidden' ?>>No classes match your filters.</p>

      <ul class="schedule-cards" data-csrf="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>" data-logged-in="<?= $isLoggedIn ? '1' : '0' ?>">
        <?php foreach ($schedules as $s) {
          $enrolled = isset($myEnrolled[$s->id]);
          $waiting  = isset($myWaiting[$s->id]);
          $full     = $s->isFull();
          $spots    = max(0, ($s->capacity ?? 0) - ($s->enrolledCount ?? 0));
        ?>
          <li class="schedule-card" data-schedule-id="<?= (int)$s->id ?>">
            <header class="schedule-card-head">
              <p class="schedule-card-day"><?= htmlspecialchars($s->dayOfWeek, ENT_QUOTES, 'UTF-8') ?> &middot; <?= htmlspecialchars($s->startTime, ENT_QUOTES, 'UTF-8') ?></p>
              <p class="schedule-card-meta"><?= (int)$s->duration ?> min &middot; <?= htmlspecialchars($s->locationName ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            </header>
            <h3 class="schedule-card-title">
              <a href="class.php?id=<?= (int)$s->classId ?>"><?= htmlspecialchars($s->className ?? '', ENT_QUOTES, 'UTF-8') ?></a>
            </h3>
            <p class="schedule-card-type"><?= htmlspecialchars($s->classType ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            <p class="schedule-card-coach">with <a href="trainer.php?id=<?= (int)$s->trainerId ?>"><?= htmlspecialchars($s->trainerName ?? '', ENT_QUOTES, 'UTF-8') ?></a></p>
            <p class="schedule-card-capacity">
              <?php if ($full) { ?>
                <span class="status-tag status-tag--full">Full</span>
              <?php } else { ?>
                <span class="status-tag status-tag--open"><?= $spots ?> spot<?= $spots === 1 ? '' : 's' ?> left</span>
              <?php } ?>
            </p>

            <div class="schedule-card-actions">
              <?php if (!$isLoggedIn) { ?>
                <a href="login.php" class="btn-outline">Sign in to book</a>
              <?php } elseif ($enrolled) { ?>
                <form method="post" action="../actions/action_cancel.php" class="js-cancel-form">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="schedule_id" value="<?= (int)$s->id ?>">
                  <span class="enrolled-tag">Enrolled</span>
                  <button type="submit" class="btn-outline">Cancel</button>
                </form>
              <?php } elseif ($waiting) { ?>
                <form method="post" action="../actions/action_waitlist_leave.php">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="schedule_id" value="<?= (int)$s->id ?>">
                  <span class="enrolled-tag">On waitlist</span>
                  <button type="submit" class="btn-outline">Leave waitlist</button>
                </form>
              <?php } elseif ($full) { ?>
                <form method="post" action="../actions/action_waitlist_join.php">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="schedule_id" value="<?= (int)$s->id ?>">
                  <button type="submit" class="btn-outline">Join waitlist</button>
                </form>
              <?php } else { ?>
                <form method="post" action="../actions/action_enroll.php" class="js-enroll-form">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="schedule_id" value="<?= (int)$s->id ?>">
                  <button type="submit" class="btn-primary">Enrol</button>
                </form>
              <?php } ?>
            </div>
          </li>
        <?php } ?>
      </ul>
    </div>
  </section>

  <script src="../javascript/schedule.js" defer></script>
<?php } ?>
