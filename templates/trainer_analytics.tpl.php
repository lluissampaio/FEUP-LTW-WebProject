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
        <p class="stat-card-value"><?= htmlspecialchars((string)$overallStats['TotalEnrollments'], ENT_QUOTES, 'UTF-8') ?></p>
        <p class="stat-card-note">Across <?= htmlspecialchars((string)$overallStats['TotalSessions'], ENT_QUOTES, 'UTF-8') ?> sessions</p>
      </article>

      <article class="stat-card">
        <p class="stat-card-label">Attendance Rate</p>
        <p class="stat-card-value"><?= htmlspecialchars((string)$overallStats['AttendanceRate'], ENT_QUOTES, 'UTF-8') ?><span style="font-size: 1.2rem;">%</span></p>
        <p class="stat-card-note">Attended / Enrolled</p>
      </article>

      <article class="stat-card">
        <p class="stat-card-label">Average Rating</p>
        <p class="stat-card-value"><?= htmlspecialchars((string)($overallStats['AverageRating'] ?? '—'), ENT_QUOTES, 'UTF-8') ?><span style="font-size: 1.2rem;">/5</span></p>
        <p class="stat-card-note">From <?= htmlspecialchars((string)$overallStats['TotalReviews'], ENT_QUOTES, 'UTF-8') ?> reviews</p>
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
                  <td><?= htmlspecialchars((string)$stat['TotalSessions'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string)$stat['TotalEnrollments'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <div style="display: flex; align-items: center; gap: 8px;">
                      <div style="flex-grow: 1; height: 6px; background: var(--color-surface-hover); border-radius: 3px; overflow: hidden;">
                        <div style="height: 100%; width: <?= min($stat['FillRate'], 100) ?>%; background: var(--color-primary);"></div>
                      </div>
                      <span style="font-size: 0.85rem; min-width: 32px; text-align: right;"><?= htmlspecialchars((string)$stat['FillRate'], ENT_QUOTES, 'UTF-8') ?>%</span>
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
