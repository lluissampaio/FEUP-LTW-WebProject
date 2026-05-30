<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();

  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/contact.tpl.php');

  $info = [
    ['label' => 'General',    'value' => 'hello@ironhaven.club'],
    ['label' => 'Membership', 'value' => 'apply@ironhaven.club'],
    ['label' => 'Press',      'value' => 'We do not engage with press.'],
    ['label' => 'Hours',      'value' => 'Mon&ndash;Fri &middot; 06:00&ndash;22:00<br>Sat&ndash;Sun &middot; 08:00&ndash;20:00'],
  ];

  $locations = [
    [
      'name'    => 'New York',
      'region'  => 'United States · Flagship',
      'image'   => 'location-new-york.jpg',
      'alt'     => 'Iron Haven New York exterior',
      'address' => "48 Greenwich Avenue<br>West Village, NY 10014<br>+1 212 555 0184",
      'hours'   => [
        ['label' => 'Mon–Fri', 'value' => '06:00 — 22:00'],
        ['label' => 'Sat–Sun', 'value' => '08:00 — 20:00'],
      ],
    ],
    [
      'name'    => 'Lisbon',
      'region'  => 'Portugal · Urban Club',
      'image'   => 'location-lisbon.jpg',
      'alt'     => 'Iron Haven Lisbon exterior',
      'address' => "Rua Castilho 72<br>1250-070 Lisboa<br>+351 21 555 0192",
      'hours'   => [
        ['label' => 'Mon–Fri', 'value' => '06:00 — 22:00'],
        ['label' => 'Sat–Sun', 'value' => '08:00 — 20:00'],
      ],
    ],
    [
      'name'    => 'Porto',
      'region'  => 'Portugal · River Club',
      'image'   => 'location-porto.jpg',
      'alt'     => 'Iron Haven Porto exterior',
      'address' => "Rua da Boavista 188<br>4050-117 Porto<br>+351 22 555 0143",
      'hours'   => [
        ['label' => 'Mon–Fri', 'value' => '06:30 — 22:00'],
        ['label' => 'Sat–Sun', 'value' => '08:00 — 19:00'],
      ],
    ],
    [
      'name'    => 'Viseu',
      'region'  => 'Portugal · Retreat',
      'image'   => 'location-viseu.jpg',
      'alt'     => 'Iron Haven Viseu retreat',
      'address' => "Quinta do Pinheiro<br>3500-908 Viseu<br>+351 23 555 0166",
      'hours'   => [
        ['label' => 'Open',  'value' => 'By residency only'],
        ['label' => 'Stays', 'value' => '3 — 28 nights'],
      ],
    ],
  ];

  drawHeader($session, 'contact', 'Contact — Iron Haven');
  drawPageHero(
    'Contact',
    'Quietly,<br>at your pace.',
    'Reach out by form, by phone, or in person at any of our four locations. Every enquiry is read by a human, in confidence, within twenty-four hours.'
  );
  drawContactSection($info, $session->csrfToken());
  drawLocations($locations);
  drawFooter();
?>
