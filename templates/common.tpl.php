<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/notification.class.php');

  function navLink(string $href, string $label, string $current, string $slug) : string {
    $active = $current === $slug ? ' class="is-active"' : '';
    return "<a href=\"$href\"$active>$label</a>";
  }

?>

<?php function drawHeader(Session $session, string $current = '', string $title = 'Iron Haven — Private Members Gym', string $ctaLabel = 'JOIN', string $ctaHref = 'membership.php', bool $includeMain = true) { 
  $unread = 0;
  $firstName = '';
  $fullName = 'Member';
  
  if ($session->isLoggedIn()) {
    try { $unread = Notification::unreadCount(getDatabaseConnection(), $session->getId()); } catch (Throwable $e) {}
    $fullName = $session->getName() ?? 'Member';
    $firstName = explode(' ', $fullName)[0];
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;700&family=Space+Mono:wght@400&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

  <header class="site-header">
    <div class="header-inner">
      <a href="index.php" class="site-logo">IRON HAVEN</a>
      <nav id="primary-nav" class="site-nav" aria-label="Primary navigation">
        <?= navLink('index.php',      'Home',       $current, 'home') ?>
        <?= navLink('classes.php',    'Classes',    $current, 'classes') ?>
        <?= navLink('trainers.php',   'Trainers',   $current, 'trainers') ?>
        <?= navLink('about.php',      'About',      $current, 'about') ?>
        <?= navLink('membership.php', 'Membership', $current, 'membership') ?>
        <?= navLink('equipment.php', 'Equipment', $current, 'equipment') ?>
        <?= navLink('contact.php',    'Contact',    $current, 'contact') ?>
        <?php if (!$session->isLoggedIn()) { ?>
          <?= navLink('login.php', 'Sign in', $current, 'login') ?>
        <?php } ?>
      </nav>
      <?php if ($session->isLoggedIn()) { ?>
        <a href="notifications.php" class="nav-bell" aria-label="Notifications">
          <span aria-hidden="true">&#9788;</span>
          <?php if ($unread > 0) { ?>
            <span class="nav-bell-count"><?= (int)$unread ?></span>
          <?php } ?>
        </a>
        <a href="dashboard.php" class="nav-cta" title="<?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?></a>
      <?php } else { ?>
        <a href="<?= htmlspecialchars($ctaHref, ENT_QUOTES, 'UTF-8') ?>" class="nav-cta"><?= htmlspecialchars($ctaLabel, ENT_QUOTES, 'UTF-8') ?></a>
      <?php } ?>
      <button type="button" class="nav-toggle" aria-label="Toggle navigation menu" aria-controls="primary-nav" aria-expanded="false">
        <span class="nav-toggle-bar"></span>
        <span class="nav-toggle-bar"></span>
        <span class="nav-toggle-bar"></span>
      </button>
    </div>
  </header>

  <?php if (count($session->getMessages()) > 0) { ?>
    <section id="messages" aria-label="System messages">
      <?php foreach ($session->getMessages() as $message) { ?>
        <article class="message message-<?= htmlspecialchars($message['type'], ENT_QUOTES, 'UTF-8') ?>">
          <?= htmlspecialchars($message['text'], ENT_QUOTES, 'UTF-8') ?>
        </article>
      <?php } ?>
    </section>
  <?php } ?>

  <?php if ($includeMain) { ?>
  <main>
  <?php } ?>
<?php } ?>

<?php function drawFooter(bool $includeMain = true) { ?>
  <?php if ($includeMain) { ?>
  </main>
  <?php } ?>

  <footer class="site-footer">
    <div class="footer-inner">
      <span class="footer-logo">IRON HAVEN</span>
      <p class="footer-copy">&copy; 2026 Iron Haven &mdash; Private Members Only</p>
      <nav class="footer-nav" aria-label="Footer navigation">
        <a href="index.php">Home</a>
        <a href="classes.php">Classes</a>
        <a href="trainers.php">Trainers</a>
        <a href="equipment.php">Equipment</a>
        <a href="about.php">About</a>
        <a href="membership.php">Membership</a>
        <a href="contact.php">Contact</a>
      </nav>
    </div>
  </footer>
  <script src="../javascript/nav-toggle.js" defer></script>
</body>
</html>
<?php } ?>

<?php function drawBreadcrumb(string $label) { ?>
  <p class="breadcrumb">
    <a href="index.php">Home</a>
    <span class="breadcrumb-sep">/</span>
    <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
  </p>
<?php } ?>

<?php function drawPageHero(string $crumb, string $heading, string $sub) { ?>
  <section class="page-hero">
    <div class="page-hero-inner">
      <?php drawBreadcrumb($crumb); ?>
      <h1 class="page-hero-heading"><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h1>
      <p class="page-hero-sub"><?= htmlspecialchars($sub, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
  </section>
<?php } ?>

<?php function drawCtaBanner(string $heading, array $actions) { ?>
  <section class="cta-banner">
    <div class="cta-inner">
      <h2 class="cta-heading"><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h2>
      <div class="cta-actions">
        <?php foreach ($actions as $a) { ?>
          <a href="<?= htmlspecialchars($a['href'], ENT_QUOTES, 'UTF-8') ?>" class="<?= htmlspecialchars($a['style'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($a['label'], ENT_QUOTES, 'UTF-8') ?></a>
        <?php } ?>
      </div>
    </div>
  </section>
<?php } ?>
