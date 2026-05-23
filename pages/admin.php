<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');

  $session = new Session();
  $session->requireLogin('login.php');
  if (!isAdminUser($session)) {
    $session->addMessage('error', 'Admin access required.');
    header('Location: index.php');
    exit;
  }

  $db = getDatabaseConnection();
  $counts = [];
  foreach (['User','Class','Schedule','Enrollment','Equipment','Waitlist','Review'] as $t) {
    $counts[$t] = (int)$db->query("SELECT COUNT(*) FROM $t")->fetchColumn();
  }

  drawHeader($session, 'admin', 'Admin — Iron Haven');
?>
  <section class="page-hero">
    <div class="page-hero-inner">
      <p class="breadcrumb"><a href="index.php">Home</a> <span class="breadcrumb-sep">/</span> <span>Admin</span></p>
      <h1 class="page-hero-heading">Admin console</h1>
      <p class="page-hero-sub">Manage the class catalog, schedules and equipment.</p>
    </div>
  </section>

  <section class="admin-section">
    <div class="section-inner admin-cards">
      <article class="card">
        <h2 class="card-title">Classes</h2>
        <p class="card-body"><?= $counts['Class'] ?> in catalog &middot; <?= $counts['Schedule'] ?> sessions</p>
        <a class="btn-primary" href="admin_classes.php">Manage classes</a>
      </article>
      <article class="card">
        <h2 class="card-title">Equipment</h2>
        <p class="card-body"><?= $counts['Equipment'] ?> items tracked</p>
        <a class="btn-primary" href="admin_equipment.php">Manage equipment</a>
      </article>
      <article class="card">
        <h2 class="card-title">Activity</h2>
        <p class="card-body"><?= $counts['Enrollment'] ?> enrolments &middot; <?= $counts['Waitlist'] ?> waitlist entries &middot; <?= $counts['Review'] ?> reviews</p>
        <a class="btn-outline" href="classes.php">View public schedule</a>
      </article>
    </div>
  </section>
<?php
  drawFooter();
?>
