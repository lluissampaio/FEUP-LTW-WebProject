<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/format.php');

  function drawNotificationsPage(array $notifications) {
?>
  <section class="page-hero">
    <div class="page-hero__inner">
      <p class="breadcrumb"><a href="index.php">Home</a> <span class="breadcrumb__sep">/</span> <span>Notifications</span></p>
      <h1 class="page-hero__heading">Notifications</h1>
      <p class="page-hero__sub">Booking confirmations, waitlist updates and reminders.</p>
    </div>
  </section>

  <section class="notifications-section">
    <div class="section__inner">
      <?php if (count($notifications) === 0) { ?>
        <p class="empty-state">No notifications yet.</p>
      <?php } else { ?>
        <ul class="notifications-list">
          <?php foreach ($notifications as $n) { ?>
            <li class="notification-item notification-item--<?= htmlspecialchars($n->type, ENT_QUOTES, 'UTF-8') ?>">
              <p class="notification-title"><?= htmlspecialchars($n->title, ENT_QUOTES, 'UTF-8') ?></p>
              <p class="notification-message"><?= htmlspecialchars($n->message, ENT_QUOTES, 'UTF-8') ?></p>
              <p class="notification-meta">
                <?php $createdTs = strtotime((string)$n->createdAt); ?>
                <time datetime="<?= htmlspecialchars($createdTs !== false ? date('c', $createdTs) : '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(formatDateTime($n->createdAt), ENT_QUOTES, 'UTF-8') ?></time>
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
  }
?>
