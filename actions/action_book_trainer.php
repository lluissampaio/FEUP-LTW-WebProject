<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/booking.class.php');
  require_once(__DIR__ . '/../database/notification.class.php');
  require_once(__DIR__ . '/../database/membership.class.php');
  require_once(__DIR__ . '/../database/plan.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/bookings.php');

  $availabilityId = isset($_POST['availability_id']) ? (int)$_POST['availability_id'] : 0;
  $notes          = trim($_POST['notes'] ?? '');
  if ($notes === '') $notes = null;

  $db = getDatabaseConnection();
  try {
    $membership = Membership::getForUser($db, $session->getId());
    if (!$membership || !$membership->isActive()) {
      throw new RuntimeException('You must have an active membership to book personal training.');
    }
    
    $limit = $membership->plan->ptSessionsLimit;
    if ($limit === 0) {
      throw new RuntimeException('Your current plan does not include personal training sessions. Please upgrade to book.');
    }

    if ($limit > 0) {
      $stmt = $db->prepare('SELECT DayOfWeek FROM TrainerAvailability WHERE AvailabilityId = ?');
      $stmt->execute([$availabilityId]);
      $slot = $stmt->fetch();
      if (!$slot) throw new RuntimeException('That availability slot no longer exists.');
      
      $date = Booking::nextDateFor($slot['DayOfWeek']);
      $yearMonth = substr($date, 0, 7); // YYYY-MM
      
      $bookedThisMonth = Booking::countSessionsInMonth($db, $session->getId(), $yearMonth);
      if ($bookedThisMonth >= $limit) {
        throw new RuntimeException('You have reached your limit of ' . $limit . ' personal training sessions for the month of ' . date('F', strtotime($date)) . '.');
      }
    }

    $booking = Booking::book($db, $session->getId(), $availabilityId, $notes);
    $session->addMessage('success', 'Personal training session booked for ' . date('l j M', strtotime($booking->bookingDate)) . '.');
    Notification::send(
      $db, $session->getId(), 'enrollment', 'Personal training booked',
      'Your one-on-one session is confirmed for ' . date('l j M', strtotime($booking->bookingDate)) . '.',
      'bookings.php'
    );
  } catch (RuntimeException $e) {
    $session->addMessage('error', $e->getMessage());
  } catch (Throwable $e) {
    error_log('action_book_trainer: ' . $e->getMessage());
    $session->addMessage('error', 'Something went wrong. Please try again.');
  }

  header('Location: ../pages/bookings.php');
  exit();
?>
