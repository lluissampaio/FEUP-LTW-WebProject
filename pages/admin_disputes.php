<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../database/dispute.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');
  require_once(__DIR__ . '/../templates/disputes.tpl.php');

  $session = new Session();
  $session->requireLogin('login.php');
  if (!isAdminUser($session)) {
    $session->addMessage('error', 'Admin access required.');
    header('Location: index.php');
    exit();
  }

  $db       = getDatabaseConnection();
  $user     = User::getUser($db, $session->getId());
  $csrf     = $session->csrfToken();

  $filter   = $_GET['status'] ?? null;
  if (!in_array($filter, Dispute::STATUSES, true)) $filter = null;

  $disputes = Dispute::getAll($db, $filter);
  $counts   = Dispute::countByStatus($db);

  drawHeader($session, 'admin', 'Admin · Disputes — Iron Haven', includeMain: false);
?>

  <div class="dashboard">
    <?php drawDashboardSidebar($user, 'disputes', $session->csrfToken()); ?>

    <main class="dashboard-main">
      <?php drawDashboardPageHeader('Admin', 'Disputes & feedback', 'Triage member reports and respond. ' . (int)$counts['open'] . ' open · ' . (int)$counts['in_review'] . ' in review · ' . (int)$counts['resolved'] . ' resolved.'); ?>

      <nav class="dispute-filter" aria-label="Filter disputes">
        <a href="admin_disputes.php" class="<?= $filter === null ? 'is-active' : '' ?>">All</a>
        <a href="admin_disputes.php?status=open" class="<?= $filter === 'open' ? 'is-active' : '' ?>">Open</a>
        <a href="admin_disputes.php?status=in_review" class="<?= $filter === 'in_review' ? 'is-active' : '' ?>">In review</a>
        <a href="admin_disputes.php?status=resolved" class="<?= $filter === 'resolved' ? 'is-active' : '' ?>">Resolved</a>
      </nav>

      <?php drawAdminDisputes($disputes, $csrf); ?>
    </main>
  </div>

<?php drawFooter(false); ?>
