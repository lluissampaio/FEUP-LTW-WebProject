<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/booking.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/bookings.php');

  $bookingId = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;

  $db = getDatabaseConnection();
  if (Booking::cancel($db, $bookingId, $session->getId())) {
    $session->addMessage('success', 'Your personal training session has been cancelled.');
  } else {
    $session->addMessage('error', 'That booking could not be cancelled.');
  }

  header('Location: ../pages/bookings.php');
  exit();
?>
