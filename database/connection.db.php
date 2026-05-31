<?php
  declare(strict_types = 1);

  /**
   * The list of tables the application expects to exist. If ANY of these is
   * missing from a connected database we treat the schema as "drifted" and run a
   * lightweight, data-preserving self-heal that creates only the missing tables
   * (plus their indexes) from database.sql. Keep this list in sync with the
   * CREATE TABLE statements in database/database.sql.
   */
  const EXPECTED_TABLES = [
    'User',
    'Location',
    'Trainer',
    'Class',
    'Schedule',
    'Enrollment',
    'Equipment',
    'Review',
    'Waitlist',
    'Notification',
    'Workout',
    'Plan',
    'Membership',
    'TrainerAvailability',
    'Booking',
    'Dispute',
    'NutritionPlan',
    'MembershipApplication',
    'EquipmentReservation',
    'FitnessGoal',
    'NutritionMeal',
    'NutritionLog',
  ];

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
      // Brand-new (or empty) database: run the full schema + seed data once.
      $schema = @file_get_contents(__DIR__ . '/../database/database.sql');
      if ($schema !== false && $schema !== '') {
        $db->exec($schema);
      }
    } else {
      // Existing database: it might be STALE (built before newer tables were
      // added to database.sql). Detect drift and create just the missing tables
      // so we never silently run against a half-broken schema. This is the bug
      // that previously caused live 500s.
      healSchema($db);
    }

    return $db;
  }

  /**
   * Detect schema drift on an already-initialised database and create any
   * missing tables (and their indexes) from database.sql.
   *
   * Data-preserving guarantees:
   *  - We never run the DROP TABLE statements from the SQL file.
   *  - We never run the INSERT (seed) statements — those belong only to a fresh
   *    init and would duplicate / collide with existing data.
   *  - We only execute CREATE TABLE statements for tables that are actually
   *    missing, so existing tables (and their rows) are left completely untouched.
   *  - Indexes are only created for tables we just created, avoiding "index
   *    already exists" errors on the established part of the schema.
   *
   * If the schema is already complete this function does nothing and has no
   * side effects.
   */
  function healSchema(PDO $db) : void {
    // Which expected tables are present right now?
    $existing = [];
    $rows = $db->query("SELECT name FROM sqlite_master WHERE type = 'table'")->fetchAll();
    foreach ($rows as $row) {
      $existing[$row['name']] = true;
    }

    $missing = [];
    foreach (EXPECTED_TABLES as $table) {
      if (!isset($existing[$table])) {
        $missing[$table] = true;
      }
    }

    // Fast path: schema is complete, nothing to do.
    if ($missing === []) {
      return;
    }

    // Load the canonical schema so we can lift the relevant CREATE statements.
    $schema = @file_get_contents(__DIR__ . '/../database/database.sql');
    if ($schema === false || $schema === '') {
      // Without the source SQL we cannot heal; leave the DB as-is rather than
      // guessing at table definitions.
      return;
    }

    // Strip SQL comments so they don't confuse statement parsing:
    //  - block comments  /* ... */  (used as section banners in database.sql)
    //  - line comments   -- ...     (inline column notes)
    $schema = preg_replace('#/\*.*?\*/#s', '', $schema) ?? $schema;
    $schema = preg_replace('/--[^\n]*/', '', $schema) ?? $schema;

    // Split into individual statements on the semicolon terminator. The schema
    // file has no semicolons inside string literals, so a plain split is safe.
    $statements = array_filter(
      array_map('trim', explode(';', $schema)),
      static fn(string $s): bool => $s !== ''
    );

    foreach ($statements as $statement) {
      // Only ever run CREATE statements during a heal — skip DROP and INSERT.
      $target = createStatementTarget($statement);
      if ($target === null) {
        continue;
      }

      // Run the statement only when its target table is one we're creating.
      // For CREATE TABLE: the table itself must be missing.
      // For CREATE INDEX: the table it indexes must be one we just created,
      // so we don't collide with indexes on pre-existing tables.
      if (isset($missing[$target])) {
        $db->exec($statement);
      }
    }
  }

  /**
   * For a CREATE TABLE / CREATE INDEX statement, return the name of the table it
   * concerns (the table being created, or the table an index sits on). Returns
   * null for anything that is not a CREATE TABLE / CREATE INDEX statement
   * (e.g. DROP, INSERT, UPDATE, PRAGMA) so the caller can skip it safely.
   */
  function createStatementTarget(string $statement) : ?string {
    // CREATE TABLE [IF NOT EXISTS] <name> (...)
    if (preg_match('/^CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?["`\[]?([A-Za-z_][A-Za-z0-9_]*)/i', $statement, $m) === 1) {
      return $m[1];
    }

    // CREATE [UNIQUE] INDEX [IF NOT EXISTS] <index> ON <table> (...)
    if (preg_match('/^CREATE\s+(?:UNIQUE\s+)?INDEX\s+(?:IF\s+NOT\s+EXISTS\s+)?[A-Za-z0-9_]+\s+ON\s+["`\[]?([A-Za-z_][A-Za-z0-9_]*)/i', $statement, $m) === 1) {
      return $m[1];
    }

    return null;
  }
?>
