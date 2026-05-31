<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/trainer.class.php');
  require_once(__DIR__ . '/../database/class.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/trainers.tpl.php');

  $session = new Session();
  $db      = getDatabaseConnection();

  $id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $trainer = Trainer::getTrainer($db, $id);

  if (!$trainer) {
    http_response_code(404);
    drawHeader($session, 'trainers', 'Not found — Iron Haven');
    echo '<section class="section__inner"><p class="empty-state">Trainer not found.</p></section>';
    drawFooter();
    exit;
  }

  $classes = GymClass::getByTrainer($db, $trainer->id);

  drawHeader($session, 'trainers', $trainer->name() . ' — Iron Haven');
  drawTrainerProfile($trainer, $classes);
  drawFooter();
?>
