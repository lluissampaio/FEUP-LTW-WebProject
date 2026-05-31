<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/class.class.php');
  require_once(__DIR__ . '/../database/trainer.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/admin_promotions.php');

  $db = getDatabaseConnection();
  $stmt = $db->prepare('SELECT Role FROM User WHERE UserId = ?');
  $stmt->execute([$session->getId()]);
  $row = $stmt->fetch();
  if (!$row || $row['Role'] !== 'admin') {
    $session->addMessage('error', 'Admin access required.');
    header('Location: ../pages/index.php');
    exit;
  }

  $type     = (string)($_POST['entity_type'] ?? '');
  $id       = (int)($_POST['entity_id'] ?? 0);
  $featured = ($_POST['featured'] ?? '0') === '1';

  try {
    if ($id < 1) throw new RuntimeException('Invalid item.');

    if ($type === 'class') {
      $class = GymClass::getClass($db, $id);
      if (!$class) throw new RuntimeException('Class not found.');
      GymClass::setFeatured($db, $id, $featured);
      $session->addMessage('success', $featured
        ? 'Class featured on the homepage.'
        : 'Class removed from the homepage.');
    } elseif ($type === 'trainer') {
      $trainer = Trainer::getTrainer($db, $id);
      if (!$trainer) throw new RuntimeException('Trainer not found.');
      Trainer::setFeatured($db, $id, $featured);
      $session->addMessage('success', $featured
        ? 'Trainer featured on the homepage.'
        : 'Trainer removed from the homepage.');
    } else {
      throw new RuntimeException('Unknown item type.');
    }
  } catch (RuntimeException $e) {
    $session->addMessage('error', $e->getMessage());
  } catch (Throwable $e) {
    error_log('action_admin_promo: ' . $e->getMessage());
    $session->addMessage('error', 'Something went wrong. Please try again.');
  }

  header('Location: ../pages/admin_promotions.php');
  exit;
?>
