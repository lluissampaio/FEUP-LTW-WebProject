<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/schedule.class.php');
  require_once(__DIR__ . '/../database/class.class.php');
  require_once(__DIR__ . '/../database/user.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');

  $db = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());

  if (!$user || $user->role !== 'trainer') {
    $session->addMessage('error', 'Unauthorized access.');
    header('Location: ../pages/dashboard.php');
    exit;
  }

  $action = $_POST['action'] ?? '';

  try {
    $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/trainer_classes.php');

    if ($action === 'create') {
      $classId  = (int)($_POST['class_id'] ?? 0);
      $c = GymClass::getClass($db, $classId);

      if (!$c || $c->trainerId !== $user->id) {
        throw new RuntimeException('Unauthorized class access.');
      }

      $validDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
      $day      = trim((string)($_POST['schedule_day']      ?? ''));
      $start    = trim((string)($_POST['schedule_start']    ?? ''));
      $duration = (int)        ($_POST['schedule_duration'] ?? 0);

      if ($day !== '' && $start !== '' && $duration > 0 && in_array($day, $validDays, true)) {
        Schedule::create($db, $classId, $day, $start, $duration);
        $session->addMessage('success', 'Class session added successfully.');
      } else {
        throw new RuntimeException('Invalid schedule parameters.');
      }

    } elseif ($action === 'edit') {
      $scheduleId = (int)($_POST['schedule_id'] ?? 0);
      $s = Schedule::getSchedule($db, $scheduleId);

      if (!$s) {
        throw new RuntimeException('Schedule not found.');
      }

      $c = GymClass::getClass($db, $s->classId);
      if (!$c || $c->trainerId !== $user->id) {
        throw new RuntimeException('Unauthorized access.');
      }

      $validDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
      $day      = trim((string)($_POST['schedule_day']      ?? ''));
      $start    = substr(trim((string)($_POST['schedule_start'] ?? '')), 0, 5);
      $duration = (int)        ($_POST['schedule_duration'] ?? 0);

      if ($day !== '' && $start !== '' && $duration > 0 && in_array($day, $validDays, true)) {
        Schedule::update($db, $scheduleId, $day, $start, $duration);
        $session->addMessage('success', 'Class session updated successfully.');
      } else {
        throw new RuntimeException('Invalid schedule parameters.');
      }

    } elseif ($action === 'delete') {
      $scheduleId = (int)($_POST['schedule_id'] ?? 0);
      $s = Schedule::getSchedule($db, $scheduleId);

      if (!$s) {
        throw new RuntimeException('Schedule not found.');
      }

      $c = GymClass::getClass($db, $s->classId);
      if (!$c || $c->trainerId !== $user->id) {
        throw new RuntimeException('Unauthorized access.');
      }

      Schedule::delete($db, $scheduleId);
      $session->addMessage('success', 'Class session deleted.');
    } else {
      $session->addMessage('error', 'Unknown action.');
    }
  } catch (RuntimeException $e) {
    $session->addMessage('error', $e->getMessage());
  } catch (InvalidArgumentException $e) {
    $session->addMessage('error', $e->getMessage());
  } catch (Throwable $e) {
    error_log('action_trainer_schedule: ' . $e->getMessage());
    $session->addMessage('error', 'Something went wrong. Please try again.');
  }

  header('Location: ../pages/trainer_classes.php');
  exit;
?>
