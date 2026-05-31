<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/trainer.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/trainers.tpl.php');

  $session  = new Session();
  $db       = getDatabaseConnection();
  $trainers = Trainer::getTrainers($db);

  drawHeader($session, 'trainers', 'Trainers — Iron Haven');
  drawPageHero(
    'Trainers',
    'Coaches.<br>Not influencers.',
    'Each trainer holds international, professional or Olympic credentials. Visit a profile to see classes they teach and book a session.'
  );
  drawTrainerGrid($trainers);
  drawCtaBanner(
    'Every member is paired<br>with a primary coach.',
    [
      ['label' => 'Apply',       'href' => 'membership.php', 'style' => 'btn--primary'],
      ['label' => 'Speak to us', 'href' => 'contact.php',    'style' => 'btn--outline'],
    ]
  );
  drawFooter();
?>
