<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/booking.class.php');
  require_once(__DIR__ . '/../database/notification.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/bookings.php');

  $availabilityId = isset($_POST['availability_id']) ? (int)$_POST['availability_id'] : 0;
  $notes          = trim($_POST['notes'] ?? '');
  if ($notes === '') $notes = null;

  $db = getDatabaseConnection();
  try {
    $booking = Booking::book($db, $session->getId(), $availabilityId, $notes);
    $session->addMessage('success', 'Personal training session booked for ' . date('l j M', strtotime($booking->bookingDate)) . '.');
    Notification::send(
      $db, $session->getId(), 'enrollment', 'Personal training booked',
      'Your one-on-one session is confirmed for ' . date('l j M', strtotime($booking->bookingDate)) . '.',
      'bookings.php'
    );
  } catch (Throwable $e) {
    $session->addMessage('error', $e->getMessage());
  }

  header('Location: ../pages/bookings.php');
  exit();
?>
