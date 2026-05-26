<?php
  declare(strict_types = 1);

  function getDatabaseConnection() : PDO {
    $path = __DIR__ . '/../database/database.db';

    // database.db is not tracked in git (it holds per-developer test data), so a
    // fresh clone won't have it. Build it from database.sql on first connection.
    $needsInit = !file_exists($path) || filesize($path) === 0;

    $db = new PDO('sqlite:' . $path);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA foreign_keys = ON');

    if ($needsInit) {
      $schema = @file_get_contents(__DIR__ . '/../database/database.sql');
      if ($schema !== false && $schema !== '') {
        $db->exec($schema);
      }
    }

    return $db;
  }
?>
