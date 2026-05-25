<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/notification.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');

  $session = new Session();
  $session->requireLogin('login.php');
  $db = getDatabaseConnection();

  $notifications = Notification::getForUser($db, $session->getId(), 50);
  Notification::markAllRead($db, $session->getId());

  drawHeader($session, 'notifications', 'Notifications — Iron Haven');
?>
  <section class="page-hero">
    <div class="page-hero-inner">
      <p class="breadcrumb"><a href="index.php">Home</a> <span class="breadcrumb-sep">/</span> <span>Notifications</span></p>
      <h1 class="page-hero-heading">Notifications</h1>
      <p class="page-hero-sub">Booking confirmations, waitlist updates and reminders.</p>
    </div>
  </section>

  <section class="notifications-section">
    <div class="section-inner">
      <?php if (count($notifications) === 0) { ?>
        <p class="empty-state">No notifications yet.</p>
      <?php } else { ?>
        <ul class="notifications-list">
          <?php foreach ($notifications as $n) { ?>
            <li class="notification-item notification-item--<?= htmlspecialchars($n->type, ENT_QUOTES, 'UTF-8') ?>">
              <p class="notification-title"><?= htmlspecialchars($n->title, ENT_QUOTES, 'UTF-8') ?></p>
              <p class="notification-message"><?= htmlspecialchars($n->message, ENT_QUOTES, 'UTF-8') ?></p>
              <p class="notification-meta">
                <time><?= htmlspecialchars(date('d M Y · H:i', strtotime($n->createdAt)), ENT_QUOTES, 'UTF-8') ?></time>
                <?php if ($n->link) { ?>
                  &middot; <a href="<?= htmlspecialchars($n->link, ENT_QUOTES, 'UTF-8') ?>">View</a>
                <?php } ?>
              </p>
            </li>
          <?php } ?>
        </ul>
      <?php } ?>
    </div>
  </section>
<?php
  drawFooter();
?>
