<?php
  declare(strict_types = 1);

  function drawTrainerAnalytics(array $overallStats, array $classStats) {
?>
  <?php if (empty($overallStats) || $overallStats['TotalClasses'] === 0) { ?>
    <section class="dashboard-activity">
      <p class="activity-empty">You do not have any assigned classes yet.</p>
    </section>
  <?php } else { ?>
    <section class="dashboard-stats" aria-label="Trainer statistics">
      <article class="stat-card">
        <p class="stat-card-label">Total Enrollments</p>
        <p class="stat-card-value"><?= $overallStats['TotalEnrollments'] ?></p>
        <p class="stat-card-note">Across <?= $overallStats['TotalSessions'] ?> sessions</p>
      </article>

      <article class="stat-card">
        <p class="stat-card-label">Attendance Rate</p>
        <p class="stat-card-value"><?= $overallStats['AttendanceRate'] ?><span style="font-size: 1.2rem;">%</span></p>
        <p class="stat-card-note">Attended / Enrolled</p>
      </article>

      <article class="stat-card">
        <p class="stat-card-label">Average Rating</p>
        <p class="stat-card-value"><?= $overallStats['AverageRating'] ?? '—' ?><span style="font-size: 1.2rem;">/5</span></p>
        <p class="stat-card-note">From <?= $overallStats['TotalReviews'] ?> reviews</p>
      </article>
    </section>

    <section class="dashboard-activity">
      <h2 class="dashboard-section-heading">Class Breakdown</h2>
      <?php if (empty($classStats)) { ?>
        <p class="activity-empty">No classes available for breakdown.</p>
      <?php } else { ?>
        <div class="activity-table-wrap">
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
                    <span style="font-size: 0.85rem; color: var(--color-gray-400);"><?= htmlspecialchars($stat['Type'], ENT_QUOTES, 'UTF-8') ?></span>
                  </td>
                  <td><?= $stat['TotalSessions'] ?></td>
                  <td><?= $stat['TotalEnrollments'] ?></td>
                  <td>
                    <div style="display: flex; align-items: center; gap: 8px;">
                      <div style="flex-grow: 1; height: 6px; background: var(--color-surface-hover); border-radius: 3px; overflow: hidden;">
                        <div style="height: 100%; width: <?= min($stat['FillRate'], 100) ?>%; background: var(--color-primary);"></div>
                      </div>
                      <span style="font-size: 0.85rem; min-width: 32px; text-align: right;"><?= $stat['FillRate'] ?>%</span>
                    </div>
                  </td>
                  <td><?= $stat['AverageRating'] ? $stat['AverageRating'] . ' / 5' : '—' ?></td>
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
