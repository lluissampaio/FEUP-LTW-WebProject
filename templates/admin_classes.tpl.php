<?php
  declare(strict_types = 1);

  function drawAdminClasses(array $classes, array $trainers, array $locations, string $csrf, array $days, ?GymClass $editing, array $editingSchedules, array $sessionCounts) {
?>
  <section class="admin-section">
    <div class="section__inner">
      <h2 class="section__heading"><?= $editing ? 'Edit class' : 'New class' ?></h2>
      <form class="admin-form" method="post" action="../actions/action_admin_class.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
        <?php if ($editing) { ?><input type="hidden" name="class_id" value="<?= (int)$editing->id ?>"><?php } ?>

        <div class="form-field">
          <label for="c-name">Name <span class="required-mark" aria-hidden="true">*</span></label>
          <input type="text" id="c-name" name="name" required maxlength="120" value="<?= htmlspecialchars($editing->name ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="form-field">
          <label for="c-type">Type <span class="required-mark" aria-hidden="true">*</span></label>
          <input type="text" id="c-type" name="type" required maxlength="60" value="<?= htmlspecialchars($editing->type ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="form-field">
          <label for="c-desc">Description</label>
          <textarea id="c-desc" name="description" rows="3" maxlength="1000"><?= htmlspecialchars($editing->description ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <div class="form-field">
          <label for="c-trainer">Trainer <span class="required-mark" aria-hidden="true">*</span></label>
          <select id="c-trainer" name="trainer_id" required>
            <?php foreach ($trainers as $tr) { ?>
              <option value="<?= (int)$tr->id ?>" <?= ($editing && $editing->trainerId === $tr->id) ? 'selected' : '' ?>><?= htmlspecialchars($tr->name(), ENT_QUOTES, 'UTF-8') ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="form-field">
          <label for="c-location">Location <span class="required-mark" aria-hidden="true">*</span></label>
          <select id="c-location" name="location_id" required>
            <?php foreach ($locations as $l) { ?>
              <option value="<?= (int)$l->id ?>" <?= ($editing && $editing->locationId === $l->id) ? 'selected' : '' ?>><?= htmlspecialchars($l->name, ENT_QUOTES, 'UTF-8') ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="form-field">
          <label for="c-capacity">Capacity <span class="required-mark" aria-hidden="true">*</span></label>
          <input type="number" id="c-capacity" name="capacity" min="1" max="100" value="<?= (int)($editing->capacity ?? 10) ?>" required>
        </div>

        <?php if (!$editing) { ?>
          <fieldset class="form-field form-subsection">
            <legend>Optional first session</legend>
            <p class="schedule-card__meta">A class only shows up on the schedule page once it has at least one session. Add one here, or add more after creating.</p>
            <div class="form-grid--3">
              <div class="form-field">
                <label for="c-day">Day</label>
                <select id="c-day" name="schedule_day">
                  <option value="">— None —</option>
                  <?php foreach ($days as $d) { ?>
                    <option value="<?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="form-field">
                <label for="c-start">Start time</label>
                <input type="time" id="c-start" name="schedule_start" value="">
              </div>
              <div class="form-field">
                <label for="c-duration">Duration (min)</label>
                <input type="number" id="c-duration" name="schedule_duration" min="15" max="240" step="5" value="60">
              </div>
            </div>
          </fieldset>
        <?php } ?>

        <div class="form-actions">
          <button type="submit" class="btn--primary"><?= $editing ? 'Update class' : 'Create class' ?></button>
          <?php if ($editing) { ?><a href="admin_classes.php" class="btn--outline">Cancel</a><?php } ?>
        </div>
      </form>
    </div>
  </section>

  <?php if ($editing) { ?>
    <section class="admin-section">
      <div class="section__inner">
        <h2 class="section__heading">Sessions for &ldquo;<?= htmlspecialchars($editing->name, ENT_QUOTES, 'UTF-8') ?>&rdquo; (<?= count($editingSchedules) ?>)</h2>

        <form class="admin-form" method="post" action="../actions/action_admin_schedule.php">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="action"     value="create">
          <input type="hidden" name="class_id"   value="<?= (int)$editing->id ?>">

          <div class="form-field">
            <label for="s-day">Day <span class="required-mark" aria-hidden="true">*</span></label>
            <select id="s-day" name="day_of_week" required>
              <?php foreach ($days as $d) { ?>
                <option value="<?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="form-field">
            <label for="s-start">Start time <span class="required-mark" aria-hidden="true">*</span></label>
            <input type="time" id="s-start" name="start_time" required>
          </div>
          <div class="form-field">
            <label for="s-duration">Duration (min) <span class="required-mark" aria-hidden="true">*</span></label>
            <input type="number" id="s-duration" name="duration" min="15" max="240" step="5" value="60" required>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn--primary">Add session</button>
          </div>
        </form>

        <?php if (count($editingSchedules) === 0) { ?>
          <p class="empty-state">No sessions yet. Add one above to make this class visible on the schedule page.</p>
        <?php } else { ?>
          <div class="table-scroll">
          <table class="data-table">
            <thead><tr><th>Day</th><th>Time</th><th>Duration</th><th>Enrolled</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($editingSchedules as $s) { ?>
                <tr>
                  <td><?= htmlspecialchars($s->dayOfWeek, ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($s->startTime, ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= (int)$s->duration ?> min</td>
                  <td><?= (int)($s->enrolledCount ?? 0) ?> / <?= (int)($s->capacity ?? 0) ?></td>
                  <td class="row-actions">
                    <form method="post" action="../actions/action_admin_schedule.php" class="inline-form">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="action"      value="delete">
                      <input type="hidden" name="class_id"    value="<?= (int)$editing->id ?>">
                      <input type="hidden" name="schedule_id" value="<?= (int)$s->id ?>">
                      <button type="submit" class="btn--outline" data-confirm="Delete this session and its enrolments?">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
          </div>
        <?php } ?>
      </div>
    </section>
  <?php } ?>

  <section class="admin-section">
    <div class="section__inner">
      <h2 class="section__heading">All classes (<?= count($classes) ?>)</h2>
      <div class="table-scroll">
      <table class="data-table">
        <thead><tr><th>Name</th><th>Type</th><th>Trainer</th><th>Location</th><th>Capacity</th><th>Sessions</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($classes as $c) {
            $sessions = $sessionCounts[$c->id] ?? 0;
          ?>
            <tr>
              <td><a href="class.php?id=<?= (int)$c->id ?>"><?= htmlspecialchars($c->name, ENT_QUOTES, 'UTF-8') ?></a></td>
              <td><?= htmlspecialchars($c->type, ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($c->trainerName ?? '', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($c->locationName ?? '', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= (int)$c->capacity ?></td>
              <td>
                <?php if ($sessions === 0) { ?>
                  <span class="status-tag status-tag--full">none</span>
                <?php } else { ?>
                  <span class="status-tag status-tag--open"><?= $sessions ?></span>
                <?php } ?>
              </td>
              <td class="row-actions">
                <a href="admin_classes.php?edit=<?= (int)$c->id ?>" class="btn--outline">Edit</a>
                <form method="post" action="../actions/action_admin_class.php" class="inline-form">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="class_id" value="<?= (int)$c->id ?>">
                  <button type="submit" class="btn--outline" data-confirm="Delete this class? All schedules and enrolments will be removed.">Delete</button>
                </form>
              </td>
            </tr>
          <?php } ?>
          <?php if (empty($classes)) { ?>
            <tr><td colspan="7" class="empty-state">No classes yet.</td></tr>
          <?php } ?>
        </tbody>
      </table>
      </div>
    </div>
  </section>
<?php
  }
?>
