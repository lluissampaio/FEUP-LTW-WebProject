<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/admin_users.php');

  $db   = getDatabaseConnection();
  $stmt = $db->prepare('SELECT Role FROM User WHERE UserId = ?');
  $stmt->execute([$session->getId()]);
  $row  = $stmt->fetch();
  if (!$row || $row['Role'] !== 'admin') {
    $session->addMessage('error', 'Admin access required.');
    header('Location: ../pages/index.php');
    exit;
  }

  $action  = (string)($_POST['action']  ?? '');
  $userId  = (int)   ($_POST['user_id'] ?? 0);
  $allowedRoles = ['member', 'trainer', 'admin'];

  if ($userId === $session->getId() && in_array($action, ['delete', 'deactivate', 'role'], true)) {
    $session->addMessage('error', 'You cannot modify your own admin account from this screen.');
    header('Location: ../pages/admin_users.php');
    exit;
  }

  try {
    if ($action === 'role') {
      $role = (string)($_POST['role'] ?? '');
      if (!in_array($role, $allowedRoles, true)) throw new RuntimeException('Invalid role.');
      $u = User::getUser($db, $userId);
      if (!$u) throw new RuntimeException('User not found.');
      $u->role = $role;
      $u->save($db);
      $session->addMessage('success', 'Role updated for ' . $u->name() . '.');
    } elseif ($action === 'activate') {
      $u = User::getUser($db, $userId);
      if (!$u) throw new RuntimeException('User not found.');
      $u->active = true;
      $u->save($db);
      $session->addMessage('success', 'Account reactivated.');
    } elseif ($action === 'deactivate') {
      User::deactivate($db, $userId);
      $session->addMessage('success', 'Account deactivated.');
    } elseif ($action === 'delete') {
      User::delete($db, $userId);
      $session->addMessage('success', 'Account deleted.');
    } else {
      $session->addMessage('error', 'Unknown action.');
    }
  } catch (Throwable $e) {
    $session->addMessage('error', $e->getMessage());
  }

  header('Location: ../pages/admin_users.php');
  exit;
?>
