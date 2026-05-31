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
    $session->addMessage('error', 'Only members can reserve equipment.');
    header('Location: ../pages/dashboard.php');
    exit();
  }

  $equipmentId = isset($_POST['equipment_id']) ? (int)$_POST['equipment_id'] : 0;
  $date        = trim((string)($_POST['reservation_date'] ?? ''));
  $startTime   = trim((string)($_POST['start_time'] ?? ''));
  $duration    = isset($_POST['duration']) ? (int)$_POST['duration'] : 0;

  try {
    if ($equipmentId <= 0) {
      throw new RuntimeException('Please choose a piece of equipment to reserve.');
    }
    $reservation = EquipmentReservation::create($db, $session->getId(), $equipmentId, $date, $startTime, $duration);
    $session->addMessage('success', 'Equipment reserved for ' . $reservation->reservationDate . ' at ' . $reservation->startTime . '.');
  } catch (RuntimeException $e) {
    $session->addMessage('error', $e->getMessage());
  } catch (Throwable $e) {
    error_log('action_reserve_equipment: ' . $e->getMessage());
    $session->addMessage('error', 'Something went wrong. Please try again.');
  }

  header('Location: ../pages/reservations.php');
  exit();
?>
