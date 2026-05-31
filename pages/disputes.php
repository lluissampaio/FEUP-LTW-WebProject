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

  require_once(__DIR__ . '/../database/membership.class.php');
  $hasPlan   = isMemberUser($session) ? Membership::userHasActivePlan($db, $session->getId()) : true;

  drawHeader($session, '', 'Support & Disputes — Iron Haven', includeMain: false);
?>

  <div class="dashboard">
    <?php drawDashboardSidebar($user, 'disputes', $session->csrfToken()); ?>

    <main id="main-content" class="dashboard__main">
      <?php drawDashboardPageHeader('Support', 'Disputes & feedback', 'Report an equipment fault, a class issue, or a billing query. Our team responds in confidence.'); ?>
      <?php drawDisputeForm($csrf, $hasPlan); ?>
      <?php drawMemberDisputes($disputes); ?>
    </main>
  </div>

<?php drawFooter(false); ?>
