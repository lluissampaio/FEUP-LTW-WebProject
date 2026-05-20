<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();

  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/classes.tpl.php');

  $classes = [
    ['name' => 'Iron Strength',       'desc' => 'Compound barbell work, progressive overload, technique-led.',        'duration' => '60 min', 'level' => 'All levels'],
    ['name' => 'Conditioning',        'desc' => 'Threshold and zone-two work on assault bikes, rowers, ski-ergs.',    'duration' => '45 min', 'level' => 'All levels'],
    ['name' => 'Olympic Lifting',     'desc' => 'Clean & jerk, snatch progressions. Coach-led, video-reviewed.',     'duration' => '75 min', 'level' => 'Intermediate +'],
    ['name' => 'Mobility & Movement', 'desc' => 'Restorative joint work, soft tissue release, breathing drills.',     'duration' => '50 min', 'level' => 'All levels'],
    ['name' => 'Combat & Boxing',     'desc' => 'Pad work and footwork with former professional boxers.',             'duration' => '60 min', 'level' => 'All levels'],
    ['name' => 'Pilates Reformer',    'desc' => 'Classical reformer work, small group. Posture, control, breath.',    'duration' => '55 min', 'level' => 'All levels'],
    ['name' => 'Recovery Lab',        'desc' => 'Sauna, cold plunge, contrast protocol. Coach-supervised.',           'duration' => '90 min', 'level' => 'All levels'],
    ['name' => 'Private 1:1',         'desc' => 'Programmed sessions with your assigned coach. Suite-based.',         'duration' => '60 min', 'level' => 'By appointment'],
  ];

  $tabs = ['New York', 'Lisbon', 'Porto', 'Viseu'];

  $days = [
    ['label' => 'Mon · 20', 'slots' => [
      ['time' => '06:30', 'class' => 'Iron Strength'],
      ['time' => '12:00', 'class' => 'Conditioning'],
      ['time' => '18:30', 'class' => 'Olympic Lifting'],
    ]],
    ['label' => 'Tue · 21', 'slots' => [
      ['time' => '07:00', 'class' => 'Mobility'],
      ['time' => '12:30', 'class' => 'Combat'],
      ['time' => '19:00', 'class' => 'Pilates Reformer'],
    ]],
    ['label' => 'Wed · 22', 'slots' => [
      ['time' => '06:30', 'class' => 'Iron Strength'],
      ['time' => '12:00', 'class' => 'Conditioning'],
      ['time' => '18:00', 'class' => 'Recovery Lab'],
    ]],
    ['label' => 'Thu · 23', 'slots' => [
      ['time' => '07:00', 'class' => 'Mobility'],
      ['time' => '12:30', 'class' => 'Olympic Lifting'],
      ['time' => '19:00', 'class' => 'Combat'],
    ]],
    ['label' => 'Fri · 24', 'slots' => [
      ['time' => '06:30', 'class' => 'Iron Strength'],
      ['time' => '12:00', 'class' => 'Pilates Reformer'],
      ['time' => '18:00', 'class' => 'Conditioning'],
    ]],
    ['label' => 'Sat · 25', 'slots' => [
      ['time' => '08:00', 'class' => 'Iron Strength'],
      ['time' => '10:00', 'class' => 'Combat'],
      ['time' => '11:30', 'class' => 'Mobility'],
    ]],
    ['label' => 'Sun · 26', 'slots' => [
      ['time' => '09:00', 'class' => 'Recovery Lab'],
      ['time' => '10:30', 'class' => 'Pilates Reformer'],
    ]],
  ];

  drawHeader($session, 'classes', 'Classes — Iron Haven');
  drawPageHero(
    'Classes',
    'Train.<br>In private.',
    'Sixty-plus weekly sessions across strength, conditioning, mobility and recovery. Capped at ten members. Booked through your concierge — never publicly listed.'
  );
  drawClassList($classes);
  drawSchedule($tabs, $days);
  drawCtaBanner(
    'Members book directly with their concierge.',
    [
      ['label' => 'Apply',       'href' => 'membership.php', 'style' => 'btn-primary'],
      ['label' => 'Speak to us', 'href' => 'contact.php',    'style' => 'btn-outline'],
    ]
  );
  drawFooter();
?>
