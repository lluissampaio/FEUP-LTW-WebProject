<?php
  declare(strict_types = 1);

  function drawAdminAnalytics(array $overview, array $popular, array $equipment, array $retention, array $planDist) {
?>
      <section class="dashboard-stats" aria-label="Headline metrics">
        <article class="stat-card">
          <p class="stat-card-label">Active members</p>
          <p class="stat-card-value"><?= (int)$overview['members'] ?></p>
          <p class="stat-card-note"><?= (int)$overview['activeSubs'] ?> active subscriptions</p>
        </article>
        <article class="stat-card">
          <p class="stat-card-label">Monthly revenue</p>
          <p class="stat-card-value">$<?= number_format($overview['mrr']) ?></p>
          <p class="stat-card-note">Recurring, active plans</p>
        </article>
        <article class="stat-card">
          <p class="stat-card-label">Engagement</p>
          <p class="stat-card-value"><?= (int)$overview['enrollments'] ?></p>
          <p class="stat-card-note"><?= (int)$overview['bookings'] ?> PT bookings</p>
        </article>
        <article class="stat-card">
          <p class="stat-card-label">Open disputes</p>
          <p class="stat-card-value"><?= (int)$overview['openDisputes'] ?></p>
          <p class="stat-card-note"><a href="admin_disputes.php">Review now</a></p>
        </article>
      </section>

      <section class="analytics-block">
        <h2 class="dashboard-section-heading">Member retention</h2>
        <div class="retention-bar-wrap">
          <div class="retention-bar" style="--pct: <?= (int)$retention['retentionPct'] ?>%;">
            <span class="retention-bar-fill"></span>
          </div>
          <p class="retention-figure"><?= (int)$retention['retentionPct'] ?>% engaged
            <span class="retention-sub">(<?= (int)$retention['engaged'] ?> of <?= (int)$retention['totalMembers'] ?> members active)</span>
          </p>
        </div>
      </section>

      <section class="analytics-block">
        <h2 class="dashboard-section-heading">Most popular classes</h2>
        <div class="activity-table-wrap">
          <table class="activity-table">
            <thead>
              <tr><th scope="col">Class</th><th scope="col">Type</th><th scope="col">Enrolments</th><th scope="col">Avg rating</th></tr>
            </thead>
            <tbody>
              <?php foreach ($popular as $c) { ?>
                <tr>
                  <td><?= htmlspecialchars($c['Name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($c['Type'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= (int)$c['Enrolled'] ?></td>
                  <td><?= $c['AvgRating'] !== null ? htmlspecialchars((string)$c['AvgRating'], ENT_QUOTES, 'UTF-8') . ' / 5' : '—' ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </section>

      <section class="analytics-block">
        <h2 class="dashboard-section-heading">Equipment usage by location</h2>
        <div class="activity-table-wrap">
          <table class="activity-table">
            <thead>
              <tr><th scope="col">Location</th><th scope="col">Units</th><th scope="col">Available</th><th scope="col">In use</th><th scope="col">Down</th></tr>
            </thead>
            <tbody>
              <?php foreach ($equipment as $e) { ?>
                <tr>
                  <td><?= htmlspecialchars($e['Location'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= (int)$e['TotalUnits'] ?></td>
                  <td><?= (int)$e['AvailableUnits'] ?></td>
                  <td><?= (int)$e['UtilisationPct'] ?>%</td>
                  <td><?= (int)$e['NonOperational'] ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </section>

      <section class="analytics-block">
        <h2 class="dashboard-section-heading">Plan distribution</h2>
        <div class="plan-dist">
          <?php foreach ($planDist as $p) { ?>
            <article class="plan-dist-item">
              <p class="plan-dist-name"><?= htmlspecialchars($p['Name'], ENT_QUOTES, 'UTF-8') ?></p>
              <p class="plan-dist-count"><?= (int)$p['Subscribers'] ?></p>
              <p class="plan-dist-label">subscribers</p>
            </article>
          <?php } ?>
        </div>
      </section>
<?php
  }
?>
