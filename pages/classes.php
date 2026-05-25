<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/class.class.php');
  require_once(__DIR__ . '/../database/schedule.class.php');
  require_once(__DIR__ . '/../database/trainer.class.php');
  require_once(__DIR__ . '/../database/location.class.php');
  require_once(__DIR__ . '/../database/enrollment.class.php');
  require_once(__DIR__ . '/../database/waitlist.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/classes.tpl.php');

  $session = new Session();
  $session->requireLogin('login.php');
   
  $db      = getDatabaseConnection();

  $type       = isset($_GET['type'])     ? trim($_GET['type'])     : '';
  $day        = isset($_GET['day'])      ? trim($_GET['day'])      : '';
  $trainerId  = isset($_GET['trainer'])  && $_GET['trainer']  !== '' ? (int)$_GET['trainer']  : null;
  $locationId = isset($_GET['location']) && $_GET['location'] !== '' ? (int)$_GET['location'] : null;
  $time       = isset($_GET['time'])     ? trim($_GET['time'])     : '';   // 'morning'|'afternoon'|'evening'

  $schedules = Schedule::search($db, $type ?: null, $trainerId, $locationId, $day ?: null);

  if ($time !== '') {
    $schedules = array_values(array_filter($schedules, function($s) use ($time) {
      $h = (int)substr($s->startTime, 0, 2);
      if ($time === 'morning')   return $h < 12;
      if ($time === 'afternoon') return $h >= 12 && $h < 17;
      if ($time === 'evening')   return $h >= 17;
      return true;
    }));
  }

  $myEnrolled = array();
  $myWaiting  = array();
  if ($session->isLoggedIn()) {
    foreach (Enrollment::getForUser($db, $session->getId(), 'enrolled') as $e) $myEnrolled[$e->scheduleId] = true;
    foreach (Waitlist::getForUser($db, $session->getId())               as $w) $myWaiting[$w->scheduleId]  = true;
  }

  $types     = GymClass::getTypes($db);
  $trainers  = Trainer::getTrainers($db);
  $locations = Location::getLocations($db);
  $days      = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
  $csrf      = $session->csrfToken();

  drawHeader($session, 'classes', 'Classes — Iron Haven');
  drawPageHero(
    'Classes',
    'Train.<br>In private.',
    'Browse our weekly schedule. Capped capacities. Filter by class type, trainer, day or time.'
  );
  drawClassFilters([
    'type' => $type, 'day' => $day, 'trainer' => $trainerId, 'location' => $locationId, 'time' => $time,
    'types' => $types, 'trainers' => $trainers, 'locations' => $locations, 'days' => $days,
  ]);
  drawScheduleList($schedules, $myEnrolled, $myWaiting, $session->isLoggedIn(), $csrf);
  drawFooter();
?>
