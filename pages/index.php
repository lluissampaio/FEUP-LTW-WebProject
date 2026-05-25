<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();

  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/home.tpl.php');

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

  $plans = [
    [
      'label'    => 'Plan 01',
      'name'     => 'Essential',
      'price'    => '$450',
      'period'   => 'per month',
      'featured' => false,
      'features' => [
        'Full gym floor access',
        'Group classes (60+ weekly)',
        'Locker & towel service',
        'Access to one location',
        'Member app access',
      ],
    ],
    [
      'label'    => 'Plan 02 — Most Popular',
      'name'     => 'Premium',
      'price'    => '$950',
      'period'   => 'per month',
      'featured' => true,
      'features' => [
        'Everything in Essential',
        '4 personal training sessions',
        'Recovery & spa access',
        'Multi-location access',
        'Guest privileges (2/month)',
        'Priority class booking',
      ],
    ],
    [
      'label'    => 'Plan 03',
      'name'     => 'Elite',
      'price'    => '$2,400',
      'period'   => 'per month',
      'featured' => false,
      'features' => [
        'Everything in Premium',
        'Unlimited personal training',
        'Private suite reservation',
        'Dedicated concierge',
        'All-location global access',
        'Invite-only members events',
        'Nutritionist consultation',
      ],
    ],
  ];

  drawHeader($session, 'home', 'Iron Haven — Private Members Gym');
  drawHomeHero();
  drawHomeStats($stats);
  drawHomeServices($services);
  drawHomeTestimonials($testimonials);
  drawHomePricing($plans);
  drawFooter();
?>
