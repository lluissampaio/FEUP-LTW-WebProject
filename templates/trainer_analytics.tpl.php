<?php
  declare(strict_types = 1);

  function drawTrainerAnalytics(array $overallStats, array $classStats) {
?>
  <?php if (empty($overallStats) || $overallStats['TotalClasses'] === 0) { ?>
    <section class="dashboard__activity">
      <p class="activity-empty">You do not have any assigned classes yet.</p>
    </section>
  <?php } else { ?>
    <section class="dashboard__stats" aria-label="Trainer statistics">
      <article class="stat-card">
        <p class="stat-card__label">Total Enrollments</p>
        <p class="stat-card__value"><?= htmlspecialchars((string)$overallStats['TotalEnrollments'], ENT_QUOTES, 'UTF-8') ?></p>
        <p class="stat-card__note">Across <?= htmlspecialchars((string)$overallStats['TotalSessions'], ENT_QUOTES, 'UTF-8') ?> sessions</p>
      </article>

      <article class="stat-card">
        <p class="stat-card__label">Attendance Rate</p>
        <p class="stat-card__value"><?= htmlspecialchars((string)$overallStats['AttendanceRate'], ENT_QUOTES, 'UTF-8') ?><span class="stat-unit">%</span></p>
        <p class="stat-card__note">Attended / Enrolled</p>
      </article>

      <article class="stat-card">
        <p class="stat-card__label">Average Rating</p>
        <p class="stat-card__value"><?= htmlspecialchars((string)($overallStats['AverageRating'] ?? '—'), ENT_QUOTES, 'UTF-8') ?><span class="stat-unit">/5</span></p>
        <p class="stat-card__note">From <?= htmlspecialchars((string)$overallStats['TotalReviews'], ENT_QUOTES, 'UTF-8') ?> reviews</p>
      </article>
    </section>

    <section class="dashboard__activity">
      <h2 class="dashboard__section-heading">Class Breakdown</h2>
      <?php if (empty($classStats)) { ?>
        <p class="activity-empty">No classes available for breakdown.</p>
      <?php } else { ?>
        <div class="activity-table__wrap">
          <table class="activity-table">
            <thead>
              <tr>
                <th scope="col">Class</th>
                <th scope="col">Sessions</th>
                <th scope="col">Enrollments</th>
                <th scope="col">Fill Rate</th>
                <th scope="col">Avg Rating</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($classStats as $stat) { ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars($stat['Name'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                    <span class="cell-subtext"><?= htmlspecialchars($stat['Type'], ENT_QUOTES, 'UTF-8') ?></span>
                  </td>
                  <td><?= htmlspecialchars((string)$stat['TotalSessions'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string)$stat['TotalEnrollments'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <div class="fill-bar">
                      <div class="fill-bar__track">
                        <div class="fill-bar__fill" style="--pct: <?= min($stat['FillRate'], 100) ?>%;"></div>
                      </div>
                      <span class="fill-bar__value"><?= htmlspecialchars((string)$stat['FillRate'], ENT_QUOTES, 'UTF-8') ?>%</span>
                    </div>
                  </td>
                  <td><?= htmlspecialchars((string)($stat['AverageRating'] ? $stat['AverageRating'] . ' / 5' : '—'), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      <?php } ?>
    </section>
  <?php } ?>
<?php
  }
?>
