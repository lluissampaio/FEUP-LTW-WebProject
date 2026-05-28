<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/class.class.php');
  require_once(__DIR__ . '/../database/schedule.class.php');
  require_once(__DIR__ . '/../database/enrollment.class.php');
  require_once(__DIR__ . '/../database/waitlist.class.php');
  require_once(__DIR__ . '/../database/review.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/class_detail.tpl.php');

  $session = new Session();
  $db      = getDatabaseConnection();

  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $class = GymClass::getClass($db, $id);
  if (!$class) {
    http_response_code(404);
    drawHeader($session, '', 'Not found — Iron Haven');
    echo '<section class="section-inner"><p class="empty-state">Class not found.</p></section>';
    drawFooter();
    exit;
  }

  $schedules = Schedule::getByClass($db, $class->id);
  $reviews   = Review::getForClass($db, $class->id);
  $avg       = Review::averageForClass($db, $class->id);

  $myEnrolled = array();
  $myWaiting  = array();
  $canReview  = false;
  $myReview   = null;
  if ($session->isLoggedIn()) {
    foreach (Enrollment::getForUser($db, $session->getId(), 'enrolled') as $e) $myEnrolled[$e->scheduleId] = true;
    foreach (Waitlist::getForUser($db, $session->getId())               as $w) $myWaiting[$w->scheduleId]  = true;
    // Allow review if user has any enrollment (enrolled or attended) for this class
    $canReview = Enrollment::hasEnrolledInClass($db, $session->getId(), $class->id);
    foreach ($reviews as $r) if ($r->userId === $session->getId()) { $myReview = $r; break; }
  }

  $csrf = $session->csrfToken();

  drawHeader($session, 'classes', $class->name . ' — Iron Haven');
  drawClassDetail($class, $schedules, $myEnrolled, $myWaiting, $session->isLoggedIn(), $csrf);
  drawClassReviews($class, $reviews, $avg, $canReview, $myReview, $csrf);
  drawFooter();
?>
