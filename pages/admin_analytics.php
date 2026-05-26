<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../database/admin_analytics.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');

  $session = new Session();
  $session->requireLogin('login.php');
  if (!isAdminUser($session)) {
    $session->addMessage('error', 'Admin access required.');
    header('Location: index.php');
    exit();
  }

  $db   = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());

  $overview  = AdminAnalytics::getOverview($db);
  $popular   = AdminAnalytics::getPopularClasses($db);
  $equipment = AdminAnalytics::getEquipmentUsage($db);
  $retention = AdminAnalytics::getRetention($db);
  $planDist  = AdminAnalytics::getPlanDistribution($db);

  drawHeader($session, 'admin', 'Admin · Analytics — Iron Haven', 'SIGN OUT', '#');
?>

  <section class="dashboard">
    <?php drawDashboardSidebar($user, 'analytics'); ?>

    <main class="dashboard-main">
      <header class="dashboard-header">
        <p class="dashboard-eyebrow">Admin analytics</p>
        <h1 class="dashboard-heading">Gym-wide metrics</h1>
        <p class="dashboard-sub">Engagement, equipment usage, retention and revenue at a glance.</p>
      </header>

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
            <span class="retention-bar-fill" style="width: <?= (int)$retention['retentionPct'] ?>%;"></span>
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
    </main>
  </section>

<?php drawFooter(); ?>
