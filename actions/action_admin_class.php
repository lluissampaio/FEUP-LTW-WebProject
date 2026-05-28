<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/class.class.php');
  require_once(__DIR__ . '/../database/schedule.class.php');

  $validDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

  $session = new Session();
  $session->requireLogin('../pages/login.php');
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/admin_classes.php');

  $db   = getDatabaseConnection();
  $stmt = $db->prepare('SELECT Role FROM User WHERE UserId = ?');
  $stmt->execute([$session->getId()]);
  $row  = $stmt->fetch();
  if (!$row || $row['Role'] !== 'admin') {
    $session->addMessage('error', 'Admin access required.');
    header('Location: ../pages/index.php');
    exit;
  }

  $action = $_POST['action'] ?? '';

  try {
    if ($action === 'create') {
      $id = GymClass::create(
        $db,
        trim((string)($_POST['name'] ?? '')),
        trim((string)($_POST['type'] ?? '')),
        trim((string)($_POST['description'] ?? '')) ?: null,
        (int)($_POST['trainer_id']  ?? 0),
        (int)($_POST['location_id'] ?? 0),
        max(1, (int)($_POST['capacity'] ?? 10))
      );

      $day      = trim((string)($_POST['schedule_day']      ?? ''));
      $start    = trim((string)($_POST['schedule_start']    ?? ''));
      $duration = (int)        ($_POST['schedule_duration'] ?? 0);
      if ($day !== '' && $start !== '' && $duration > 0 && in_array($day, $validDays, true)) {
        Schedule::create($db, $id, $day, $start, $duration);
        $session->addMessage('success', 'Class created with one session.');
      } else {
        $session->addMessage('success', 'Class created. Add at least one session for it to appear on the schedule.');
      }
    } elseif ($action === 'update') {
      $c = GymClass::getClass($db, (int)($_POST['class_id'] ?? 0));
      if (!$c) throw new RuntimeException('Class not found.');
      $c->name        = trim((string)($_POST['name'] ?? ''));
      $c->type        = trim((string)($_POST['type'] ?? ''));
      $c->description = trim((string)($_POST['description'] ?? '')) ?: null;
      $c->trainerId   = (int)($_POST['trainer_id']  ?? 0);
      $c->locationId  = (int)($_POST['location_id'] ?? 0);
      $c->capacity    = max(1, (int)($_POST['capacity'] ?? 10));
      $c->save($db);
      $session->addMessage('success', 'Class updated.');
    } elseif ($action === 'delete') {
      GymClass::delete($db, (int)($_POST['class_id'] ?? 0));
      $session->addMessage('success', 'Class deleted.');
    } else {
      $session->addMessage('error', 'Unknown action.');
    }
  } catch (RuntimeException $e) {
    $session->addMessage('error', $e->getMessage());
  } catch (Throwable $e) {
    error_log('action_admin_class: ' . $e->getMessage());
    $session->addMessage('error', 'Something went wrong. Please try again.');
  }

  header('Location: ../pages/admin_classes.php');
  exit;
?>
