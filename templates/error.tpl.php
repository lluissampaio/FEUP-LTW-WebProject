<?php
  declare(strict_types = 1);

  // Standalone error page. Deliberately self-contained: it must NOT depend on
  // common.tpl.php (drawHeader/drawFooter), because errors can fire before those
  // templates are loaded, or from within an action/api script that never loads them.
  // Only the shared stylesheet is referenced so the page still looks branded.
  if (!function_exists('drawErrorPage')) {
    function drawErrorPage(int $code, string $title, string $message) : void {
      $safeTitle   = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
      $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
      $safeCode    = (int) $code;
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $safeCode ?> &mdash; <?= $safeTitle ?> | Iron Haven</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;700&family=Space+Mono:wght@400&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
  <main class="error-page">
    <div class="error-page-inner">
      <p class="error-page-code"><?= $safeCode ?></p>
      <h1 class="error-page-title"><?= $safeTitle ?></h1>
      <p class="error-page-message"><?= $safeMessage ?></p>
      <a href="/pages/index.php" class="btn-primary">Back to home</a>
    </div>
  </main>
</body>
</html>
<?php
    }
  }
?>
