<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();

  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../database/plan.class.php');
  require_once(__DIR__ . '/../database/membership.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/membership.tpl.php');

  $db = getDatabaseConnection();

  // A logged-in member manages a real subscription; everyone else sees marketing.
  $currentUser = $session->isLoggedIn() ? User::getUser($db, $session->getId()) : null;
  $isMember    = $currentUser && $currentUser->role === 'member';

  // Feature bullets keyed by plan name (display only).
  $featureBullets = [
    'Essential' => ['Full gym floor access', 'Group classes (60+ weekly)', 'Locker & towel service', 'Access to one location', 'Member app access'],
    'Premium'   => ['Everything in Essential', '4 personal training sessions', 'Recovery & spa access', 'Multi-location access', 'Guest privileges (2/month)', 'Priority class booking'],
    'Elite'     => ['Everything in Premium', 'Unlimited personal training', 'Private suite reservation', 'Dedicated concierge', 'All-location global access', 'Invite-only members events', 'Nutritionist consultation'],
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

  $columns = [
    ['name' => 'Essential', 'featured' => false],
    ['name' => 'Premium',   'featured' => true],
    ['name' => 'Elite',     'featured' => false],
  ];

  $features = [
    ['name' => 'Gym floor access',            'values' => [true, true, true]],
    ['name' => 'Group classes',               'values' => ['Unlimited', 'Unlimited & priority booking', 'Unlimited & priority booking']],
    ['name' => 'Personal training sessions',  'values' => [false, '4 / month', 'Unlimited']],
    ['name' => 'Recovery & spa access',       'values' => [false, true, true]],
    ['name' => 'Private suite reservation',   'values' => [false, false, true]],
    ['name' => 'Location access',             'values' => ['1 home location', 'All 4 locations', 'All locations + global reciprocal']],
    ['name' => 'Guest privileges',            'values' => [false, '2 / month', 'Unlimited']],
    ['name' => 'Concierge',                   'values' => ['Shared', 'Shared', 'Dedicated']],
    ['name' => 'Nutritionist consultation',   'values' => [false, false, true]],
    ['name' => 'Members-only events',         'values' => [false, 'Selected', 'All']],
  ];

  $faqs = [
    ['q' => 'How does the application process work?',          'a' => 'Applications are submitted by referral from a current member. A member of our team reviews each application within seventy-two hours. If accepted to the next stage, you will be invited to an in-person interview at your nearest location.'],
    ['q' => 'Do I need a member referral?',                    'a' => 'In nearly all cases, yes. For applicants without an existing connection, we accept a small number of direct enquiries each quarter and consider them on a case-by-case basis. Use the contact form to introduce yourself.'],
    ['q' => 'What does "no cameras" mean in practice?',        'a' => 'No mobile-phone photography is permitted on the gym floor, in the locker rooms, or in the lounge. CCTV is limited to entrances for security and is not monitored on the floor. Staff sign non-disclosure agreements upon hire.'],
    ['q' => 'Can I move between locations?',                   'a' => 'Premium and Elite members may train at any of our four locations. Elite members additionally enjoy reciprocal access at selected partner clubs internationally — your concierge will confirm availability.'],
    ['q' => 'Is there a minimum commitment?',                  'a' => "All memberships are annual, billed monthly. Cancellation requires sixty days' notice. We do not offer trial memberships — the interview process serves that purpose."],
    ['q' => 'Can I pause my membership?',                      'a' => 'Yes. Members may pause once per year for up to three months — for travel, recovery, or any reason at all. Your concierge will arrange this without question.'],
  ];

  drawHeader($session, 'membership', 'Membership — Iron Haven', 'APPLY', 'contact.php');
  drawPageHero(
    'Membership',
    'Three plans.<br>One standard.',
    'All Iron Haven memberships are by referral. Application is reviewed within seventy-two hours. An interview follows. There is no waiting list — only the right fit.'
  );

  if ($isMember) {
    // Functional subscribe / upgrade / cancel for signed-in members.
    $dbPlans = Plan::getPlans($db);
    $current = Membership::getForUser($db, $currentUser->id);
    drawMembershipManager($dbPlans, $current, $featureBullets, $session->csrfToken());
  } else {
    // Public marketing pricing → routes to the contact/apply flow.
    drawPricing($plans, 'contact.php');
  }

  drawCompareTable($features, $columns);
  drawFaq($faqs);
  drawCtaBanner(
    'Ready to apply?<br>Start the conversation.',
    [
      ['label' => 'Begin Application', 'href' => 'contact.php', 'style' => 'btn-primary'],
      ['label' => 'Read our story',    'href' => 'about.php',   'style' => 'btn-outline'],
    ]
  );
  drawFooter();
?>
