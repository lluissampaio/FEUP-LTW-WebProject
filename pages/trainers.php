<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();

  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/trainers.tpl.php');

  $strength = [
    [
      'name'  => 'Marcus Reid',
      'role'  => 'Head Coach · New York',
      'bio'   => 'Former head of S&C for two Olympic federations. 18 years coaching at international level. Co-founder of Iron Haven.',
      'photo' => 'trainer-marcus-reid.jpg',
      'tags'  => ['Olympic Lifting', 'Powerlifting', 'Periodisation'],
    ],
    [
      'name'  => 'Elena Costa',
      'role'  => 'Senior Coach · Lisbon',
      'bio'   => 'Former Portuguese national weightlifting team. PhD in exercise physiology, Universidade de Lisboa. Specialises in long-arc strength development.',
      'photo' => 'trainer-elena-costa.jpg',
      'tags'  => ['Weightlifting', 'Physiology', 'Programming'],
    ],
    [
      'name'  => 'James Okafor',
      'role'  => 'Senior Coach · New York',
      'bio'   => 'Former British Athletics sprint coach. Specialises in athletic transfer for executives returning to sport after long lay-offs.',
      'photo' => 'trainer-james-okafor.jpg',
      'tags'  => ['Sprint Mechanics', 'Return to Sport', 'Athletics'],
    ],
  ];

  $combat = [
    [
      'name'  => 'Diogo Almeida',
      'role'  => 'Head Boxing Coach · Porto',
      'bio'   => 'Former European amateur champion. Coaches our boxing programme across all four locations. Eighteen years in the corner.',
      'photo' => 'trainer-diogo-almeida.jpg',
      'tags'  => ['Boxing', 'Pad Work', 'Footwork'],
    ],
    [
      'name'  => 'Yuki Tanaka',
      'role'  => 'Senior Coach · New York',
      'bio'   => 'Brazilian jiu-jitsu black belt under Saulo Ribeiro. Former competitor on the IBJJF circuit. Teaches our private grappling sessions.',
      'photo' => 'trainer-yuki-tanaka.jpg',
      'tags'  => ['BJJ', 'Grappling', 'Self-Defence'],
    ],
    [
      'name'  => 'Anika Patel',
      'role'  => 'Coach · Lisbon',
      'bio'   => 'Former Muay Thai world title contender. Now coaches striking-for-fitness with an emphasis on technique over intensity.',
      'photo' => 'trainer-anika-patel.jpg',
      'tags'  => ['Muay Thai', 'Striking', 'Conditioning'],
    ],
  ];

  $movement = [
    [
      'name'  => 'Sofia Marchetti',
      'role'  => 'Head of Pilates · Viseu',
      'bio'   => "Classical Pilates instructor trained under Romana Kryzanowska's lineage. Twelve years teaching reformer and mat in Milan and Lisbon.",
      'photo' => 'trainer-sofia-marchetti.jpg',
      'tags'  => ['Reformer', 'Classical', 'Posture'],
    ],
    [
      'name'  => 'Dr. Henrik Vogel',
      'role'  => 'Recovery Director · All Locations',
      'bio'   => 'Sports medicine consultant. Designs protocols for our Recovery Lab — sauna, cold plunge, breath work. Former physician to two ATP tour players.',
      'photo' => 'trainer-henrik-vogel.jpg',
      'tags'  => ['Recovery', 'Sports Med', 'Breathwork'],
    ],
    [
      'name'  => 'Clara Fontes',
      'role'  => 'Mobility Lead · Porto',
      'bio'   => 'Movement coach with a background in dance and FRC. Specialises in restoring range for desk-bound professionals returning to training.',
      'photo' => 'trainer-clara-fontes.jpg',
      'tags'  => ['Mobility', 'FRC', 'Movement'],
    ],
  ];

  drawHeader($session, 'trainers', 'Trainers — Iron Haven');
  drawPageHero(
    'Trainers',
    'Coaches.<br>Not influencers.',
    'Thirty-plus full-time coaches, each with international, professional or Olympic credentials. None of them post about you. None of them post at all.'
  );
  drawTrainerSection('01', 'Strength & Conditioning', 'Strength Coaches', $strength, false);
  drawTrainerSection('02', 'Combat & Boxing',         'Combat Coaches',   $combat,   true);
  drawTrainerSection('03', 'Movement & Recovery',     'Movement Specialists', $movement, false);
  drawCtaBanner(
    'Every member is paired<br>with a primary coach.',
    [
      ['label' => 'Apply',       'href' => 'membership.php', 'style' => 'btn-primary'],
      ['label' => 'Speak to us', 'href' => 'contact.php',    'style' => 'btn-outline'],
    ]
  );
  drawFooter();
?>
