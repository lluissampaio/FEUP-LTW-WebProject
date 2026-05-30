<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/application.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/admin_applications.php');

  $db   = getDatabaseConnection();
  $stmt = $db->prepare('SELECT Role FROM User WHERE UserId = ?');
  $stmt->execute([$session->getId()]);
  $row  = $stmt->fetch();
  if (!$row || $row['Role'] !== 'admin') {
    $session->addMessage('error', 'Admin access required.');
    header('Location: ../pages/index.php');
    exit;
  }

  $action        = (string)($_POST['action'] ?? '');
  $applicationId = (int)   ($_POST['application_id'] ?? 0);

  try {
    if ($action === 'accept') {
      MembershipApplication::decide($db, $applicationId, 'accepted', $session->getId());
      $session->addMessage('success', 'Application accepted. The applicant can now register with their email.');
    } elseif ($action === 'reject') {
      MembershipApplication::decide($db, $applicationId, 'rejected', $session->getId());
      $session->addMessage('success', 'Application rejected.');
    } else {
      $session->addMessage('error', 'Unknown action.');
    }
  } catch (RuntimeException $e) {
    $session->addMessage('error', $e->getMessage());
  } catch (Throwable $e) {
    error_log('action_admin_application: ' . $e->getMessage());
    $session->addMessage('error', 'Something went wrong. Please try again.');
  }

  header('Location: ../pages/admin_applications.php');
  exit;
?>
