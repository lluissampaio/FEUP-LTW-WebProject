<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();
  $session->requireLogin('login.php');

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../database/dispute.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');
  require_once(__DIR__ . '/../templates/disputes.tpl.php');

  $db        = getDatabaseConnection();
  $user      = User::getUser($db, $session->getId());
  $csrf      = $session->csrfToken();
  $disputes  = Dispute::getForUser($db, $session->getId());

  drawHeader($session, '', 'Support & Disputes — Iron Haven', 'SIGN OUT', '#');
?>

  <section class="dashboard">
    <?php drawDashboardSidebar($user, 'disputes'); ?>

    <main class="dashboard-main">
      <header class="dashboard-header">
        <p class="dashboard-eyebrow">Support</p>
        <h1 class="dashboard-heading">Disputes & feedback</h1>
        <p class="dashboard-sub">Report an equipment fault, a class issue, or a billing query. Our team responds in confidence.</p>
      </header>

      <?php drawDisputeForm($csrf); ?>
      <?php drawMemberDisputes($disputes); ?>
    </main>
  </section>

<?php drawFooter(); ?>
