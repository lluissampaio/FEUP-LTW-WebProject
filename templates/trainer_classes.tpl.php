<?php
  declare(strict_types = 1);

  function drawTrainerClasses(array $classes, array $schedules, array $rosters, array $days, string $csrf) {
?>
  <section class="dashboard-activity dashboard-activity-gap">
    <h2 class="dashboard-section-heading">Add a session</h2>
    <?php if (empty($classes)) { ?>
      <p class="activity-empty">You do not have any assigned classes to add sessions to.</p>
    <?php } else { ?>
      <form class="dashboard-form schedule-add-form" method="post" action="../actions/action_trainer_schedule.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="create">

        <div class="schedule-add-grid">
          <label class="form-field">
            Class
            <select id="class_id" name="class_id" required>
              <?php foreach ($classes as $c) { ?>
                <option value="<?= (int)$c->id ?>"><?= htmlspecialchars($c->name, ENT_QUOTES, 'UTF-8') ?></option>
              <?php } ?>
            </select>
          </label>
          <label class="form-field">
            Day
            <select id="schedule_day" name="schedule_day" required>
              <?php foreach ($days as $d) { ?>
                <option value="<?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></option>
              <?php } ?>
            </select>
          </label>
          <label class="form-field">
            Start
            <input type="time" id="schedule_start" name="schedule_start" required>
          </label>
          <label class="form-field">
            Mins
            <input type="number" id="schedule_duration" name="schedule_duration" min="15" max="240" step="5" value="60" required>
          </label>
        </div>
        <button type="submit" class="btn-primary">Add session</button>
      </form>
    <?php } ?>
  </section>

  <section class="dashboard-activity">
    <?php if (empty($schedules)) { ?>
      <p class="activity-empty">You are not assigned to any classes yet.</p>
    <?php } else { ?>
      <?php foreach ($schedules as $s) {
        $roster = $rosters[$s->id] ?? [];
      ?>
        <article class="roster-block">
          <div class="roster-head">
            <h2 class="dashboard-section-heading roster-head-title">
              <?= htmlspecialchars($s->className ?? '', ENT_QUOTES, 'UTF-8') ?> &mdash; <?= htmlspecialchars($s->classType ?? '', ENT_QUOTES, 'UTF-8') ?>
            </h2>
            <form method="post" action="../actions/action_trainer_schedule.php" class="inline-form">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="schedule_id" value="<?= (int)$s->id ?>">
              <button type="submit" class="btn-outline btn-sm" data-confirm="Cancel this session?">Delete</button>
            </form>
          </div>
          <p class="dashboard-sub roster-meta">
            <?= htmlspecialchars($s->dayOfWeek, ENT_QUOTES, 'UTF-8') ?>s at <?= htmlspecialchars($s->startTime, ENT_QUOTES, 'UTF-8') ?> &bull;
            <?= $s->duration ?> min &bull;
            <?= htmlspecialchars($s->locationName ?? '', ENT_QUOTES, 'UTF-8') ?> &bull;
            <?= count($roster) ?> / <?= $s->capacity ?? 0 ?> enrolled
          </p>

          <div class="activity-table-wrap">
            <table class="activity-table">
              <thead>
                <tr>
                  <th scope="col">Name</th>
                  <th scope="col">Email</th>
                  <th scope="col">Enrollment date</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($roster)) { ?>
                  <tr>
                    <td colspan="3" class="activity-empty">No members enrolled yet.</td>
                  </tr>
                <?php } else { ?>
                  <?php foreach ($roster as $member) { ?>
                    <tr>
                      <td><?= htmlspecialchars($member['FirstName'] . ' ' . $member['LastName'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td><?= htmlspecialchars($member['Email'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($member['EnrolledAt'])), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                  <?php } ?>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </article>
      <?php } ?>
    <?php } ?>
  </section>
<?php
  }
?>
