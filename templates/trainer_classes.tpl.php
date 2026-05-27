<?php
  declare(strict_types = 1);

  function drawTrainerClasses(array $classes, array $schedules, array $rosters, array $days, string $csrf) {
?>
  <section class="dashboard-activity" style="margin-bottom: 2rem;">
    <h2 class="dashboard-section-heading">Add a Session</h2>
    <?php if (empty($classes)) { ?>
      <p class="activity-empty">You do not have any assigned classes to add sessions to.</p>
    <?php } else { ?>
      <form class="auth-form" method="post" action="../actions/action_trainer_schedule.php" style="max-width: 800px;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="create">
        
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 1rem; align-items: end;">
          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="class_id">Class</label>
            <select class="form-input" id="class_id" name="class_id" required>
              <?php foreach ($classes as $c) { ?>
                <option value="<?= (int)$c->id ?>"><?= htmlspecialchars($c->name, ENT_QUOTES, 'UTF-8') ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="schedule_day">Day</label>
            <select class="form-input" id="schedule_day" name="schedule_day" required>
              <?php foreach ($days as $d) { ?>
                <option value="<?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="schedule_start">Start</label>
            <input class="form-input" type="time" id="schedule_start" name="schedule_start" required>
          </div>
          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="schedule_duration">Mins</label>
            <input class="form-input" type="number" id="schedule_duration" name="schedule_duration" min="15" max="240" step="5" value="60" required>
          </div>
        </div>
        <button type="submit" class="btn auth-btn" style="margin-top: 1rem;">Add Session</button>
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
        <article style="margin-bottom: 3rem; position: relative;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
            <h2 class="dashboard-section-heading" style="margin-bottom: 0;">
              <?= htmlspecialchars($s->className ?? '', ENT_QUOTES, 'UTF-8') ?> &mdash; <?= htmlspecialchars($s->classType ?? '', ENT_QUOTES, 'UTF-8') ?>
            </h2>
            <form method="post" action="../actions/action_trainer_schedule.php">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="schedule_id" value="<?= (int)$s->id ?>">
              <button type="submit" class="btn-outline" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;" onclick="return confirm('Cancel this session?')">Delete</button>
            </form>
          </div>
          <p class="dashboard-sub" style="margin-bottom: 1.5rem; font-size: 0.9rem; color: var(--color-gray-400);">
            <?= htmlspecialchars($s->dayOfWeek, ENT_QUOTES, 'UTF-8') ?>s at <?= htmlspecialchars($s->startTime, ENT_QUOTES, 'UTF-8') ?> &bull; 
            <?= $s->duration ?> min &bull; 
            <?= htmlspecialchars($s->locationName ?? '', ENT_QUOTES, 'UTF-8') ?> &bull; 
            <?= count($roster) ?> / <?= $s->capacity ?? 0 ?> Enrolled
          </p>

          <div class="activity-table-wrap">
            <table class="activity-table">
              <thead>
                <tr>
                  <th scope="col">Name</th>
                  <th scope="col">Email</th>
                  <th scope="col">Enrollment Date</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($roster)) { ?>
                  <tr>
                    <td colspan="3" class="activity-empty" style="text-align: center;">No members enrolled yet.</td>
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
