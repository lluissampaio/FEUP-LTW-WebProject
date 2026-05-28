<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/equipment.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/admin_equipment.php');

  $db = getDatabaseConnection();
  $stmt = $db->prepare('SELECT Role FROM User WHERE UserId = ?');
  $stmt->execute([$session->getId()]);
  $row = $stmt->fetch();
  if (!$row || $row['Role'] !== 'admin') {
    $session->addMessage('error', 'Admin access required.');
    header('Location: ../pages/index.php');
    exit;
  }

  $action = $_POST['action'] ?? '';

  $allowedStatus = ['operational','maintenance','out_of_order'];

  try {
    if ($action === 'create') {
      $status = (string)($_POST['status'] ?? 'operational');
      if (!in_array($status, $allowedStatus, true)) throw new RuntimeException('Invalid status.');
      Equipment::create(
        $db,
        trim((string)($_POST['name'] ?? '')),
        trim((string)($_POST['type'] ?? '')),
        (int)($_POST['location_id'] ?? 0),
        max(0, (int)($_POST['quantity']  ?? 0)),
        max(0, (int)($_POST['available'] ?? 0)),
        $status
      );
      $session->addMessage('success', 'Equipment added.');
    } elseif ($action === 'update') {
      $e = Equipment::getEquipment($db, (int)($_POST['equipment_id'] ?? 0));
      if (!$e) throw new RuntimeException('Equipment not found.');
      $status = (string)($_POST['status'] ?? 'operational');
      if (!in_array($status, $allowedStatus, true)) throw new RuntimeException('Invalid status.');
      $e->name       = trim((string)($_POST['name'] ?? ''));
      $e->type       = trim((string)($_POST['type'] ?? ''));
      $e->locationId = (int)($_POST['location_id'] ?? 0);
      $e->quantity   = max(0, (int)($_POST['quantity']  ?? 0));
      $e->available  = max(0, (int)($_POST['available'] ?? 0));
      $e->status     = $status;
      $e->save($db);
      $session->addMessage('success', 'Equipment updated.');
    } elseif ($action === 'delete') {
      Equipment::delete($db, (int)($_POST['equipment_id'] ?? 0));
      $session->addMessage('success', 'Equipment deleted.');
    } else {
      $session->addMessage('error', 'Unknown action.');
    }
  } catch (RuntimeException $e) {
    $session->addMessage('error', $e->getMessage());
  } catch (Throwable $e) {
    error_log('action_admin_equipment: ' . $e->getMessage());
    $session->addMessage('error', 'Something went wrong. Please try again.');
  }

  header('Location: ../pages/admin_equipment.php');
  exit;
?>
