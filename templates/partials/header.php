<?php
/**
 * Shared site header.
 *
 * Variables consumed (set them BEFORE including this file):
 *   string $page_title         — <title> text. Defaults to "Iron Haven".
 *   string $page_description   — <meta name="description"> text.
 *   string $body_class         — class attribute for <body> (optional).
 *   string $nav_cta_label      — text on the nav CTA button. Defaults to "Join".
 *   string $nav_cta_href       — href for the nav CTA. Defaults to "register.php".
 *
 * All output is HTML-escaped at the point of interpolation.
 */

$page_title       = $page_title       ?? 'Iron Haven';
$page_description = $page_description ?? 'Iron Haven — Elite training and uncompromised privacy for private members.';
$body_class       = $body_class       ?? '';
$nav_cta_label    = $nav_cta_label    ?? 'Join';
$nav_cta_href     = $nav_cta_href     ?? 'register.php';

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $h($page_title) ?></title>
  <meta name="description" content="<?= $h($page_description) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;700&family=Space+Mono:wght@400&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="../css/main.css">
</head>

<body<?= $body_class ? ' class="' . $h($body_class) . '"' : '' ?>>

  <header class="nav">
    <div class="nav__inner">
      <a href="index.php" class="nav__logo">IRON HAVEN</a>
      <input type="checkbox" id="nav-toggle-state" class="nav__state">
      <label for="nav-toggle-state" class="nav__toggle">
        <span class="sr-only">Toggle navigation menu</span>
        <span aria-hidden="true">&#9776;</span>
      </label>
      <nav class="nav__links" id="primary-nav" aria-label="Primary navigation">
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="classes.php">Classes</a></li>
          <li><a href="trainers.php">Trainers</a></li>
          <li><a href="index.php#pricing">Membership</a></li>
          <li><a href="index.php#contact">Contact</a></li>
          <li><a href="<?= $h($nav_cta_href) ?>" class="btn btn--nav"><?= $h($nav_cta_label) ?></a></li>
        </ul>
      </nav>
    </div>
  </header>
