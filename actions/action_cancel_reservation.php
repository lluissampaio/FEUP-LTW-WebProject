<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/reservation.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/reservations.php');

  $db = getDatabaseConnection();

  if (!isMemberUser($session)) {
    $session->addMessage('error', 'Only members can manage equipment reservations.');
    header('Location: ../pages/dashboard.php');
    exit();
  }

  $reservationId = isset($_POST['reservation_id']) ? (int)$_POST['reservation_id'] : 0;

  // cancel() is owner-checked (matches on ReservationId AND UserId), so a member
  // can never cancel a reservation that is not theirs — no IDOR.
  if (EquipmentReservation::cancel($db, $reservationId, $session->getId())) {
    $session->addMessage('success', 'Your equipment reservation has been cancelled.');
  } else {
    $session->addMessage('error', 'That reservation could not be cancelled.');
  }

  header('Location: ../pages/reservations.php');
  exit();
?>
