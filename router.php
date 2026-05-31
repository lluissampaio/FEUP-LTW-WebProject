<?php
  declare(strict_types = 1);

  /*
   * Dev-server router for the PHP built-in server:
   *
   *     php -S localhost:8000 router.php
   *
   * Without a router, `php -S` falls back to a directory's index.php for unknown
   * URLs, so a mistyped path silently serves the homepage with HTTP 200. This
   * router serves real files as-is and renders a branded 404 for anything else.
   */

  $root = __DIR__;
  $uri  = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
  $uri  = rawurldecode($uri);

  // Resolve the request to a path inside the document root (guard against traversal).
  $target = realpath($root . $uri);
  $inRoot = $target !== false && str_starts_with($target, $root);

  // Markdown docs (README, working notes) are tracked but never web-served.
  $isMarkdown = (bool) preg_match('/\.md$/i', $uri);

  // 1. Existing file (page, asset, action, api) -> let the built-in server serve it.
  if ($inRoot && is_file($target) && !$isMarkdown) {
    return false;
  }

  // 2. Root or a directory with an index.php -> serve that index (normal behaviour).
  if ($uri === '/' || $uri === '') {
    require $root . '/index.php';
    return true;
  }
  if ($inRoot && is_dir($target) && is_file($target . '/index.php')) {
    require $target . '/index.php';
    return true;
  }

  // 3. Anything else is genuinely missing -> branded 404 (not the homepage).
  http_response_code(404);
  require $root . '/templates/error.tpl.php';
  drawErrorPage(
    404,
    'Page not found',
    'The page you requested does not exist or may have moved. Use the navigation to find your way back.'
  );
  return true;
?>
