<?php declare(strict_types = 1); ?>

<?php require_once(__DIR__ . '/../utils/format.php'); ?>
<?php require_once(__DIR__ . '/../database/goal.class.php'); ?>

<?php
  // Build the JSON payload for the workout-history chart from recent workouts.
  // We take the most recent sessions, oldest-to-newest for left-to-right reading,
  // and chart calories (falling back to duration when no calories were logged).
  function workoutsChartPayload(array $workouts) : array {
    $recent = array_slice($workouts, 0, 12);   // already DESC by date
    $recent = array_reverse($recent);           // oldest -> newest
    $useCalories = false;
    foreach ($recent as $w) { if ((int)$w->calories > 0) { $useCalories = true; break; } }
    $points = array();
    foreach ($recent as $w) {
      $value = $useCalories ? (int)$w->calories : (int)$w->durationMinutes;
      // Short date label (day + month) so the axis stays compact.
      $label = '';
      $ts = strtotime($w->date);
      if ($ts !== false) $label = date('j M', $ts);
      $points[] = array('label' => $label, 'value' => $value);
    }
    return array(
      'type'       => 'bar',
      'valueLabel' => $useCalories ? 'calories' : 'minutes',
      'points'     => $points,
    );
  }

  // Build the JSON payload for the goal-progress chart.
  function goalsChartPayload(array $goals) : array {
    $out = array();
    foreach ($goals as $g) {
      $out[] = array(
        'title'    => $g->title,
        'progress' => $g->progress(),
        'status'   => $g->status,
      );
    }
    return array('goals' => $out);
  }
?>

<?php
  function drawWorkoutsCharts(array $workouts, array $goals) {
    $workoutData = workoutsChartPayload($workouts);
    $goalData    = goalsChartPayload($goals);
    $hasWorkouts = !empty($workoutData['points']);
    $hasGoals    = !empty($goalData['goals']);
    $valueLabel  = $workoutData['valueLabel'];
?>
    <section class="dashboard__activity" aria-labelledby="charts-heading">
      <h2 class="dashboard__section-heading" id="charts-heading">Progress at a glance</h2>
      <div class="chart-grid">
        <article class="dashboard__card chart-card">
          <h3 class="chart-title">Recent workouts<?= $hasWorkouts ? ' (' . htmlspecialchars($valueLabel, ENT_QUOTES, 'UTF-8') . ')' : '' ?></h3>
          <?php if ($hasWorkouts) { ?>
            <div class="chart-canvas__wrap">
              <canvas class="chart-canvas" data-chart="workout-history" data-source="#chart-workouts-data"></canvas>
            </div>
            <script type="application/json" id="chart-workouts-data"><?= htmlspecialchars(json_encode($workoutData), ENT_QUOTES, 'UTF-8') ?></script>
          <?php } else { ?>
            <p class="empty-state">No workouts to chart yet. Log a session to see your trend.</p>
          <?php } ?>
        </article>

        <article class="dashboard__card chart-card">
          <h3 class="chart-title">Goal progress</h3>
          <?php if ($hasGoals) { ?>
            <div class="chart-canvas__wrap">
              <canvas class="chart-canvas" data-chart="goal-progress" data-source="#chart-goals-data"></canvas>
            </div>
            <script type="application/json" id="chart-goals-data"><?= htmlspecialchars(json_encode($goalData), ENT_QUOTES, 'UTF-8') ?></script>
          <?php } else { ?>
            <p class="empty-state">No goals to chart yet. Add a goal below to track it.</p>
          <?php } ?>
        </article>
      </div>
    </section>
<?php
  }
?>

<?php
  function drawWorkoutsGoals(array $goals, string $csrf) {
?>
    <section class="dashboard__activity" aria-labelledby="goals-heading">
      <h2 class="dashboard__section-heading" id="goals-heading">Fitness goals</h2>

      <?php if (empty($goals)) { ?>
        <p class="empty-state">No fitness goals yet. Set one below to start tracking your progress.</p>
      <?php } else { ?>
        <div class="goal-list">
          <?php foreach ($goals as $g) {
            $pct      = $g->progressPercent();
            $titleEsc = htmlspecialchars($g->title, ENT_QUOTES, 'UTF-8');
            $unitEsc  = htmlspecialchars((string)($g->unit ?? ''), ENT_QUOTES, 'UTF-8');
            $statusEsc = htmlspecialchars($g->status, ENT_QUOTES, 'UTF-8');
            // Trim trailing zeros from REAL values for cleaner display.
            $cur = rtrim(rtrim(number_format($g->currentValue, 2, '.', ''), '0'), '.');
            $tgt = rtrim(rtrim(number_format($g->targetValue, 2, '.', ''), '0'), '.');
          ?>
            <article class="goal-card goal-card--<?= $statusEsc ?>">
              <div class="goal-card__head">
                <h3 class="goal-card__title"><?= $titleEsc ?></h3>
                <span class="status-tag status-tag--<?= $statusEsc ?>"><?= $statusEsc ?></span>
              </div>

              <p class="goal-card__values">
                <?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?>
                <?php if ($unitEsc !== '') { ?><?= $unitEsc ?><?php } ?>
                of
                <?= htmlspecialchars($tgt, ENT_QUOTES, 'UTF-8') ?>
                <?php if ($unitEsc !== '') { ?><?= $unitEsc ?><?php } ?>
              </p>

              <div class="goal-progress" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?= $titleEsc ?> progress">
                <span class="goal-progress__fill" style="width: <?= $pct ?>%;"></span>
                <span class="goal-progress__label"><?= $pct ?>%</span>
              </div>

              <?php if ($g->deadline !== null && $g->deadline !== '') { ?>
                <p class="goal-card__meta">Deadline: <?= htmlspecialchars(formatDate($g->deadline), ENT_QUOTES, 'UTF-8') ?></p>
              <?php } ?>

              <details class="goal-card__edit">
                <summary class="btn--outline btn--sm">Edit</summary>
                <form method="post" action="../actions/action_goal.php" class="admin-form goal-form">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="action"  value="update">
                  <input type="hidden" name="goal_id" value="<?= (int)$g->id ?>">
                  <label class="form-field">
                    Title <span class="required-mark" aria-hidden="true">*</span>
                    <input type="text" name="title" maxlength="120" required value="<?= $titleEsc ?>">
                  </label>
                  <label class="form-field">
                    Metric
                    <select name="metric">
                      <?php foreach (Goal::METRICS as $m) { ?>
                        <option value="<?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?>"<?= $g->metric === $m ? ' selected' : '' ?>><?= htmlspecialchars(str_replace('_', ' ', $m), ENT_QUOTES, 'UTF-8') ?></option>
                      <?php } ?>
                    </select>
                  </label>
                  <label class="form-field">
                    Current value
                    <input type="number" name="current_value" step="any" min="0" value="<?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?>">
                  </label>
                  <label class="form-field">
                    Target value <span class="required-mark" aria-hidden="true">*</span>
                    <input type="number" name="target_value" step="any" min="0.01" required value="<?= htmlspecialchars($tgt, ENT_QUOTES, 'UTF-8') ?>">
                  </label>
                  <label class="form-field">
                    Unit
                    <input type="text" name="unit" maxlength="20" value="<?= $unitEsc ?>" placeholder="kg, kcal, sessions">
                  </label>
                  <label class="form-field">
                    Deadline
                    <input type="date" name="deadline" value="<?= htmlspecialchars((string)($g->deadline ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                  </label>
                  <label class="form-field">
                    Status
                    <select name="status">
                      <option value="active"<?= $g->status !== 'abandoned' ? ' selected' : '' ?>>Active</option>
                      <option value="abandoned"<?= $g->status === 'abandoned' ? ' selected' : '' ?>>Abandoned</option>
                    </select>
                  </label>
                  <div class="form-actions">
                    <button type="submit" class="btn--primary btn--sm">Save changes</button>
                  </div>
                </form>
                <form method="post" action="../actions/action_goal.php" class="inline-form">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="action"  value="delete">
                  <input type="hidden" name="goal_id" value="<?= (int)$g->id ?>">
                  <button type="submit" class="btn--outline btn--sm" data-confirm="Delete this goal? This cannot be undone.">Delete</button>
                </form>
              </details>
            </article>
          <?php } ?>
        </div>
      <?php } ?>

      <details class="goal-add">
        <summary class="btn--primary btn--sm">Add a goal</summary>
        <form method="post" action="../actions/action_goal.php" class="admin-form goal-form">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="action" value="create">
          <label class="form-field">
            Title <span class="required-mark" aria-hidden="true">*</span>
            <input type="text" name="title" maxlength="120" required placeholder="e.g. Bench press 100kg">
          </label>
          <label class="form-field">
            Metric
            <select name="metric">
              <?php foreach (Goal::METRICS as $m) { ?>
                <option value="<?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(str_replace('_', ' ', $m), ENT_QUOTES, 'UTF-8') ?></option>
              <?php } ?>
            </select>
          </label>
          <label class="form-field">
            Current value
            <input type="number" name="current_value" step="any" min="0" value="0">
          </label>
          <label class="form-field">
            Target value <span class="required-mark" aria-hidden="true">*</span>
            <input type="number" name="target_value" step="any" min="0.01" required placeholder="e.g. 100">
          </label>
          <label class="form-field">
            Unit
            <input type="text" name="unit" maxlength="20" placeholder="kg, kcal, sessions">
          </label>
          <label class="form-field">
            Deadline
            <input type="date" name="deadline">
          </label>
          <div class="form-actions">
            <button type="submit" class="btn--primary">Add goal</button>
          </div>
        </form>
      </details>
    </section>
<?php
  }
?>

<?php
  function drawWorkoutsLogForm(bool $hasPlan, string $csrf) {
?>
    <section class="dashboard__activity" aria-labelledby="log-heading">
      <h2 class="dashboard__section-heading" id="log-heading">Log a workout</h2>
      <?php if (!$hasPlan) { ?>
        <p class="empty-state">Choose a membership plan to start logging workouts.</p>
      <?php } else { ?>
        <form class="admin-form" method="post" action="../actions/action_workout.php">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="action"     value="log">
          <label class="form-field">
            Type <span class="required-mark" aria-hidden="true">*</span>
            <input type="text" name="workout_type" required maxlength="60" placeholder="e.g. Strength, Cardio, Yoga">
          </label>
          <label class="form-field">
            Duration (min) <span class="required-mark" aria-hidden="true">*</span>
            <input type="number" name="duration" min="1" max="600" step="1" value="45" required>
          </label>
          <label class="form-field">
            Calories (optional)
            <input type="number" name="calories" min="0" max="5000" step="10" value="0">
          </label>
          <div class="form-actions">
            <button type="submit" class="btn--primary">Log workout</button>
          </div>
        </form>
      <?php } ?>
    </section>
<?php
  }
?>

<?php
  function drawWorkoutsHistory(array $workouts, string $csrf) {
    require_once(__DIR__ . '/../utils/format.php');
?>
    <section class="dashboard__activity" aria-labelledby="history-heading">
      <h2 class="dashboard__section-heading" id="history-heading">Workout log</h2>
      <div class="activity-table__wrap">
        <table class="activity-table">
          <thead>
            <tr>
              <th scope="col">Date</th>
              <th scope="col">Workout type</th>
              <th scope="col">Duration</th>
              <th scope="col">Calories</th>
              <th scope="col">Status</th>
              <th scope="col"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($workouts as $w) { ?>
              <tr>
                <td><?= htmlspecialchars(formatDate($w->date), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($w->workoutType, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int)$w->durationMinutes ?> min</td>
                <td><?= $w->calories ? (int)$w->calories : '—' ?></td>
                <td><span class="status-tag status-tag--<?= htmlspecialchars(strtolower($w->status), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($w->status, ENT_QUOTES, 'UTF-8') ?></span></td>
                <td>
                  <form method="post" action="../actions/action_workout.php" class="inline-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="workout_id" value="<?= (int)$w->id ?>">
                    <button type="submit" class="btn--outline btn--sm" data-confirm="Remove this workout? This cannot be undone.">Remove</button>
                  </form>
                </td>
              </tr>
            <?php } ?>
            <?php if (empty($workouts)) { ?>
              <tr>
                <td colspan="6"><p class="empty-state">No activity recorded yet. Start training.</p></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </section>

    <script src="../javascript/charts.js" defer></script>
<?php
  }
?>
