<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  $session = new Session();

  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/about.tpl.php');

  $story = [
    'label'      => '01 — Our Story',
    'heading'    => "A refuge for the world's most discerning.",
    'image'      => 'about-founder-portrait.jpg',
    'alt_text'   => 'Iron Haven founder portrait',
    'paragraphs' => [
      'Iron Haven was born from a conversation in 2019, between a former Olympic strength coach and a hedge-fund principal who could not find a single gym in Manhattan where he could train undisturbed. Not for lack of money — for lack of discretion.',
      'Five years and four cities later, Iron Haven is the answer to that conversation: a private club designed for people who carry their public lives heavily, and want one room where they can put it down.',
    ],
  ];

  $values = [
    ['title' => 'Absolute Discretion', 'body' => 'No cameras on the gym floor. No press. No tagged photographs. Membership lists are not published. Our staff sign NDAs upon hire. What happens at Iron Haven stays there.'],
    ['title' => 'Elite Standard',      'body' => 'Every coach has trained at international or professional level. Every facility is built and equipped to the standard of a national training centre. Nothing is rented, nothing is shared.'],
    ['title' => 'Vetted Community',    'body' => 'Membership is by referral and interview only. We curate the room as carefully as we curate the equipment. You will meet peers — never an audience.'],
    ['title' => 'Quiet Service',       'body' => 'Concierge that anticipates rather than asks. A locker that knows your kit. A coach who knows your schedule. Service that is felt, never seen.'],
  ];

  $timeline = [
    ['year' => '2019', 'title' => 'The First Conversation', 'body' => 'Iron Haven is conceived in a hotel suite in TriBeCa. The first private programme is run for a single member, in a leased loft.'],
    ['year' => '2021', 'title' => 'New York Flagship',      'body' => 'The Manhattan flagship opens on a quiet side street in the West Village. Twenty-two founding members. No signage on the door.'],
    ['year' => '2023', 'title' => 'Iberian Expansion',      'body' => "Lisbon and Porto open within four months of each other. The club's first overseas members join under reciprocal access."],
    ['year' => '2024', 'title' => 'Viseu — A Retreat',      'body' => 'Our fourth location opens as a dedicated retreat: extended-stay programmes in the Portuguese highlands, away from the city entirely.'],
    ['year' => '2026', 'title' => 'Today',                  'body' => 'Over 2,400 members across four cities. Still by invitation. Still without a press release.'],
  ];

  $founders = [
    'label'      => '04 — Leadership',
    'heading'    => 'Built by the<br>people who use it.',
    'image'      => 'about-founders-pair.jpg',
    'alt_text'   => 'Iron Haven founding partners',
    'reverse'    => true,
    'alt'        => false,
    'paragraphs' => [
      'Our founding partners are members first. The standards you experience are not theoretical — they are the standards our partners apply to their own training, their own privacy, and their own time.',
      'Iron Haven is led by Marcus Reid, formerly head of strength & conditioning for two Olympic federations, and Sofia Almeida, principal at Atlas Capital and a founding member since 2019.',
    ],
  ];

  drawHeader($session, 'about', 'About — Iron Haven');
  drawPageHero(
    'About',
    'Privacy is<br>not a luxury.',
    "Iron Haven was founded on a simple belief: the world's most successful people deserve a space that respects their time, ambition, and right to be left alone."
  );
  drawTwoCol($story);
  drawValues($values);
  drawTimeline($timeline);
  drawTwoCol($founders);
  drawCtaBanner(
    'Membership is by referral.<br>Apply for an interview.',
    [
      ['label' => 'Apply',       'href' => 'membership.php', 'style' => 'btn--primary'],
      ['label' => 'Get in touch','href' => 'contact.php',    'style' => 'btn--outline'],
    ]
  );
  drawFooter();
?>
