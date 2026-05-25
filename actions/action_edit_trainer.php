<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../database/trainer.class.php');
  require_once(__DIR__ . '/../utils/session.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/edit_trainer.php');

  $db = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());

  if (!$user || $user->role !== 'trainer') {
    $session->addMessage('error', 'Unauthorized access.');
    header('Location: ../pages/dashboard.php');
    exit();
  }

  $specialty      = trim($_POST['specialty'] ?? '');
  $certifications = trim($_POST['certifications'] ?? '');
  $bio            = trim($_POST['bio'] ?? '');

  try {
    $trainer = Trainer::getTrainer($db, $session->getId());

    if ($trainer) {
      $trainer->specialty      = $specialty !== '' ? $specialty : null;
      $trainer->certifications = $certifications !== '' ? $certifications : null;
      $trainer->bio            = $bio !== '' ? $bio : null;
      $trainer->save($db);
    } else {
      Trainer::create(
        $db,
        $session->getId(),
        $bio !== '' ? $bio : null,
        $specialty !== '' ? $specialty : null,
        $certifications !== '' ? $certifications : null
      );
    }

    $session->addMessage('success', 'Profile updated successfully.');
    header('Location: ../pages/trainer.php?id=' . $session->getId());
    exit();
  } catch (Exception $e) {
    $session->addMessage('error', 'An error occurred while updating your profile.');
    header('Location: ../pages/edit_trainer.php');
    exit();
  }
?>
