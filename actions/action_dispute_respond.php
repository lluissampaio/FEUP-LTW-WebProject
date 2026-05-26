<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/dispute.class.php');
  require_once(__DIR__ . '/../database/notification.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');
  if (!isAdminUser($session)) {
    $session->addMessage('error', 'Admin access required.');
    header('Location: ../pages/index.php');
    exit();
  }
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/admin_disputes.php');

  $disputeId = isset($_POST['dispute_id']) ? (int)$_POST['dispute_id'] : 0;
  $status    = trim($_POST['status'] ?? '');
  $response  = trim($_POST['response'] ?? '');
  if ($response === '') $response = null;

  $db = getDatabaseConnection();
  try {
    $dispute = Dispute::getDispute($db, $disputeId);
    if (!$dispute) throw new RuntimeException('Dispute not found.');

    Dispute::respond($db, $disputeId, $session->getId(), $status, $response);
    $session->addMessage('success', 'Dispute updated.');

    // Let the member know their report was addressed.
    Notification::send(
      $db, $dispute->userId, 'system', 'Update on your report',
      'Your report "' . $dispute->subject . '" is now marked ' . str_replace('_', ' ', $status) . '.',
      'disputes.php'
    );
  } catch (Throwable $e) {
    $session->addMessage('error', $e->getMessage());
  }

  header('Location: ../pages/admin_disputes.php');
  exit();
?>
