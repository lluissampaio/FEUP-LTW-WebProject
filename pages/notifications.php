<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/notification.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/notifications.tpl.php');

  $session = new Session();
  $session->requireLogin('login.php');
  $db = getDatabaseConnection();

  $notifications = Notification::getForUser($db, $session->getId(), 50);
  Notification::markAllRead($db, $session->getId());

  drawHeader($session, 'notifications', 'Notifications — Iron Haven');
?>
  <?php drawNotificationsPage($notifications); ?>
<?php
  drawFooter();
?>
