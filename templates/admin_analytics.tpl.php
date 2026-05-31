<?php
  declare(strict_types = 1);

  function drawAdminAnalytics(array $overview, array $popular, array $equipment, array $retention, array $planDist) {
?>
      <ul class="dashboard__stats" aria-label="Headline metrics">
        <li class="stat-card">
          <p class="stat-card__label">Active members</p>
          <p class="stat-card__value"><?= (int)$overview['members'] ?></p>
          <p class="stat-card__note"><?= (int)$overview['activeSubs'] ?> active subscriptions</p>
        </li>
        <li class="stat-card">
          <p class="stat-card__label">Monthly revenue</p>
          <p class="stat-card__value">$<?= number_format($overview['mrr']) ?></p>
          <p class="stat-card__note">Recurring, active plans</p>
        </li>
        <li class="stat-card">
          <p class="stat-card__label">Engagement</p>
          <p class="stat-card__value"><?= (int)$overview['enrollments'] ?></p>
          <p class="stat-card__note"><?= (int)$overview['bookings'] ?> PT bookings</p>
        </li>
        <li class="stat-card">
          <p class="stat-card__label">Open disputes</p>
          <p class="stat-card__value"><?= (int)$overview['openDisputes'] ?></p>
          <p class="stat-card__note"><a href="admin_disputes.php">Review now</a></p>
        </li>
      </ul>

      <section class="analytics-block">
        <h2 class="dashboard__section-heading">Member retention</h2>
        <div class="retention-bar__wrap">
          <div class="retention-bar" style="--pct: <?= (int)$retention['retentionPct'] ?>%;">
            <span class="retention-bar__fill"></span>
          </div>
          <p class="retention-figure"><?= (int)$retention['retentionPct'] ?>% engaged
            <span class="retention-sub">(<?= (int)$retention['engaged'] ?> of <?= (int)$retention['totalMembers'] ?> members active)</span>
          </p>
        </div>
      </section>

      <section class="analytics-block">
        <h2 class="dashboard__section-heading">Most popular classes</h2>
        <div class="activity-table__wrap">
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
        <h2 class="dashboard__section-heading">Equipment usage by location</h2>
        <div class="activity-table__wrap">
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
        <h2 class="dashboard__section-heading">Plan distribution</h2>
        <ul class="plan-dist">
          <?php foreach ($planDist as $p) { ?>
            <li class="plan-dist__item">
              <p class="plan-dist__name"><?= htmlspecialchars($p['Name'], ENT_QUOTES, 'UTF-8') ?></p>
              <p class="plan-dist__count"><?= (int)$p['Subscribers'] ?></p>
              <p class="plan-dist__label">subscribers</p>
            </li>
          <?php } ?>
        </ul>
      </section>
<?php
  }
?>
