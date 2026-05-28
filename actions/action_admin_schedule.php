<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/schedule.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');

  $classId = (int)($_POST['class_id'] ?? 0);
  $back    = '../pages/admin_classes.php' . ($classId ? '?edit=' . $classId : '');
  $session->verifyCsrf($_POST['csrf_token'] ?? '', $back);

  $db = getDatabaseConnection();
  $stmt = $db->prepare('SELECT Role FROM User WHERE UserId = ?');
  $stmt->execute([$session->getId()]);
  $row = $stmt->fetch();
  if (!$row || $row['Role'] !== 'admin') {
    $session->addMessage('error', 'Admin access required.');
    header('Location: ../pages/index.php');
    exit;
  }

  $action     = (string)($_POST['action'] ?? '');
  $validDays  = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

  try {
    if ($action === 'create') {
      $day      = trim((string)($_POST['day_of_week'] ?? ''));
      $start    = trim((string)($_POST['start_time']  ?? ''));
      $duration = (int)        ($_POST['duration']    ?? 0);
      if ($classId <= 0) throw new RuntimeException('Missing class.');
      if (!in_array($day, $validDays, true)) throw new RuntimeException('Invalid day.');
      if (!preg_match('/^\d{2}:\d{2}$/', $start)) throw new RuntimeException('Invalid start time.');
      if ($duration < 15 || $duration > 240)     throw new RuntimeException('Duration must be 15–240 minutes.');
      Schedule::create($db, $classId, $day, $start, $duration);
      $session->addMessage('success', 'Session added.');
    } elseif ($action === 'delete') {
      $scheduleId = (int)($_POST['schedule_id'] ?? 0);
      if ($scheduleId <= 0) throw new RuntimeException('Missing schedule.');
      Schedule::delete($db, $scheduleId);
      $session->addMessage('success', 'Session removed.');
    } else {
      $session->addMessage('error', 'Unknown action.');
    }
  } catch (RuntimeException $e) {
    $session->addMessage('error', $e->getMessage());
  } catch (Throwable $e) {
    error_log('action_admin_schedule: ' . $e->getMessage());
    $session->addMessage('error', 'Something went wrong. Please try again.');
  }

  header('Location: ' . $back);
  exit;
?>
