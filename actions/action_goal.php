<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/goal.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/workouts.php');

  // Members own fitness goals; reject any other role.
  if (!isMemberUser($session)) {
    $session->addMessage('error', 'Only members can manage fitness goals.');
    header('Location: ../pages/dashboard.php');
    exit();
  }

  $action = (string)($_POST['action'] ?? 'create');
  $userId = (int)$session->getId();
  $db     = getDatabaseConnection();

  try {
    if ($action === 'delete') {
      $goalId = (int)($_POST['goal_id'] ?? 0);
      // Owner-checked: delete only succeeds when the goal belongs to this user.
      if (!Goal::delete($db, $goalId, $userId)) {
        throw new RuntimeException('That goal could not be found.');
      }
      $session->addMessage('success', 'Goal deleted.');
    } else {
      $title   = trim((string)($_POST['title'] ?? ''));
      $metric  = (string)($_POST['metric'] ?? 'custom');
      $unit    = trim((string)($_POST['unit'] ?? ''));
      $deadline = trim((string)($_POST['deadline'] ?? ''));
      $status  = (string)($_POST['status'] ?? 'active');

      $targetRaw  = $_POST['target_value']  ?? '';
      $currentRaw = $_POST['current_value'] ?? '';
      if (!is_numeric($targetRaw))  throw new RuntimeException('Target value must be a number.');
      if ($currentRaw !== '' && !is_numeric($currentRaw)) throw new RuntimeException('Current value must be a number.');

      $target  = (float)$targetRaw;
      $current = $currentRaw === '' ? 0.0 : (float)$currentRaw;

      if ($title === '')                throw new RuntimeException('Goal title is required.');
      if (mb_strlen($title) > 120)      throw new RuntimeException('Goal title is too long.');
      if ($target <= 0)                 throw new RuntimeException('Target value must be greater than zero.');
      if ($current < 0)                 throw new RuntimeException('Current value cannot be negative.');
      if (mb_strlen($unit) > 20)        throw new RuntimeException('Unit is too long.');
      if (!in_array($status, array('active', 'abandoned'), true)) $status = 'active';
      if ($deadline !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline)) {
        throw new RuntimeException('Deadline must be a valid date.');
      }

      if ($action === 'update') {
        $goalId = (int)($_POST['goal_id'] ?? 0);
        // Owner-checked update; rowCount() is 0 when the goal is not theirs.
        if (!Goal::update($db, $goalId, $userId, $title, $metric, $target, $current, $unit, $deadline, $status)) {
          throw new RuntimeException('That goal could not be updated.');
        }
        $session->addMessage('success', 'Goal updated.');
      } else {
        Goal::create($db, $userId, $title, $metric, $target, $current, $unit, $deadline, $status);
        $session->addMessage('success', 'Goal added.');
      }
    }
  } catch (RuntimeException $e) {
    $session->addMessage('error', $e->getMessage());
  } catch (Throwable $e) {
    error_log('action_goal: ' . $e->getMessage());
    $session->addMessage('error', 'Something went wrong. Please try again.');
  }

  header('Location: ../pages/workouts.php');
  exit();
?>
