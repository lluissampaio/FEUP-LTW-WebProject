<?php
  declare(strict_types = 1);

  // Shared date-formatting helpers. See CONTRACTS #1: every template that prints a
  // date should require_once this file and use these helpers instead of ad-hoc date().

  // Format an ISO date string (e.g. '2026-05-31') as '31 May 2026'.
  // Returns '' for null, empty, or unparseable input.
  function formatDate(?string $iso): string {
    if ($iso === null || trim($iso) === '') {
      return '';
    }
    try {
      $date = new DateTime($iso);
      return $date->format('j M Y');
    } catch (Exception $e) {
      return '';
    }
  }

  // Format an ISO datetime string (e.g. '2026-05-31 14:30') as '31 May 2026 · 14:30'.
  // Returns '' for null, empty, or unparseable input.
  function formatDateTime(?string $iso): string {
    if ($iso === null || trim($iso) === '') {
      return '';
    }
    try {
      $date = new DateTime($iso);
      return $date->format('j M Y · H:i');
    } catch (Exception $e) {
      return '';
    }
  }
?>
