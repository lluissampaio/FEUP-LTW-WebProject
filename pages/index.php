<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();

  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/home.tpl.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/plan.class.php');
  require_once(__DIR__ . '/../database/class.class.php');
  require_once(__DIR__ . '/../database/trainer.class.php');

  $stats = [
    ['number' => '4',      'label' => 'Locations'],
    ['number' => '2,400+', 'label' => 'Members'],
    ['number' => '60+',    'label' => 'Weekly Classes'],
    ['number' => '30+',    'label' => 'Elite Trainers'],
  ];

  $services = [
    [
      'title' => 'Private Training Suites',
      'desc'  => 'Dedicated one-on-one spaces equipped for every programme. Fully private, fully equipped — designed around you and your coach alone.',
      'link'  => 'classes.php',
    ],
    [
      'title' => 'Recovery & Spa',
      'desc'  => 'Cold plunge, infrared sauna, contrast therapy, and on-demand sports massage. Recovery is not optional — it is part of the programme.',
      'link'  => 'classes.php',
    ],
    [
      'title' => 'Members Lounge',
      'desc'  => 'A curated social space for our community. No cameras, no outsiders. Connect with peers who share your standard — entirely off the record.',
      'link'  => 'about.php',
    ],
  ];

  $testimonials = [
    [
      'quote'  => 'Iron Haven is the only place I can train without thinking about who is watching. The level of discretion here is unmatched anywhere in New York.',
      'author' => 'J.M. — New York Member',
    ],
    [
      'quote'  => 'The trainers are exceptional and the environment is unlike any gym I have used globally. It feels more like a private club than a fitness centre.',
      'author' => 'A.R. — Lisbon Member',
    ],
    [
      'quote'  => 'I joined for the training. I stayed for the community. Iron Haven has connected me with people I would never have met elsewhere — all in complete privacy.',
      'author' => 'C.F. — Porto Member',
    ],
  ];

  $db = getDatabaseConnection();
  $plansFromDB = Plan::getPlans($db);

  // Promotional features: classes/trainers an admin has flagged Featured = 1.
  $featuredClasses  = GymClass::getFeatured($db);
  $featuredTrainers = Trainer::getFeatured($db);

  $plans = [];
  foreach ($plansFromDB as $p) {
    $features = array_map('trim', explode(',', $p->description ?? ''));
    $plans[] = [
      'label'    => 'Plan 0' . $p->tier . ($p->featured ? ' — Most Popular' : ''),
      'name'     => $p->name,
      'price'    => '$' . number_format($p->price),
      'period'   => $p->period,
      'featured' => $p->featured,
      'features' => $features,
    ];
  }

  drawHeader($session, 'home', 'Iron Haven — Private Members Gym');
  drawHomeHero();
  drawHomeStats($stats);
  drawHomeServices($services);
  drawHomeTestimonials($testimonials);
  drawHomeFeatured($featuredClasses, $featuredTrainers);
  drawHomePricing($plans);
  drawFooter();
?>
