<?php declare(strict_types = 1); ?>

<?php
  function drawWorkoutsHistory(array $workouts, string $csrf) {
?>
    <section class="dashboard-activity">
      <div class="activity-table-wrap">
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
                <td><?= htmlspecialchars(date('d/m/Y', strtotime($w->date)), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($w->workoutType, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int)$w->durationMinutes ?> min</td>
                <td><?= $w->calories ? (int)$w->calories : '—' ?></td>
                <td><span class="status-tag status-tag-<?= htmlspecialchars(strtolower($w->status), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($w->status, ENT_QUOTES, 'UTF-8') ?></span></td>
                <td>
                  <form method="post" action="../actions/action_workout.php" class="inline-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="workout_id" value="<?= (int)$w->id ?>">
                    <button type="submit" class="btn-outline btn-sm" onclick="return confirm('Are you sure you want to remove this workout?');">Remove</button>
                  </form>
                </td>
              </tr>
            <?php } ?>
            <?php if (empty($workouts)) { ?>
              <tr>
                <td colspan="6" class="activity-empty">No activity recorded yet. Start training.</td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </section>
<?php
  }
?>
