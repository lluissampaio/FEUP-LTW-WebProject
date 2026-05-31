<?php
  declare(strict_types = 1);

  /**
   * Centralised, best-effort email channel for Iron Haven.
   *
   * Development boxes typically have no MTA configured, so PHP's mail() will
   * fail or warn. sendEmail() is therefore a SAFE no-op in that situation:
   * it NEVER throws, never blocks the request, and simply returns false (after
   * logging) when delivery is unavailable. Callers should treat the boolean
   * return as "did we hand this off to the MTA?", not "was it definitely read".
   */

  // The single place to configure the From: identity for all outbound mail.
  if (!defined('SITE_FROM')) {
    define('SITE_FROM', 'Iron Haven <no-reply@ironhaven.local>');
  }

  if (!function_exists('sendEmail')) {
    /**
     * Send a plain-text email best-effort.
     *
     * @param string $to      Recipient email address.
     * @param string $subject Subject line.
     * @param string $body    Plain-text message body.
     * @return bool           true if mail() accepted the message for delivery,
     *                        false if email is unavailable / invalid / failed.
     */
    function sendEmail(string $to, string $subject, string $body) : bool {
      // Guard: an invalid or empty recipient can never be delivered.
      $to = trim($to);
      if ($to === '' || filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
        error_log('sendEmail: skipped, invalid recipient "' . $to . '"');
        return false;
      }

      // RFC-safe-ish header. Keep the subject on a single line.
      $subject = str_replace(["\r", "\n"], ' ', $subject);

      $headers = implode("\r\n", [
        'From: ' . SITE_FROM,
        'Content-Type: text/plain; charset=UTF-8',
        'MIME-Version: 1.0',
      ]);

      try {
        // mail() emits a warning (not an exception) when no MTA is present, so
        // silence it with @ and rely on the boolean return. Anything unexpected
        // that DOES throw is caught below so a notification can never fatal the
        // request that triggered it.
        $ok = @mail($to, $subject, $body, $headers);
        if ($ok !== true) {
          error_log('sendEmail: mail() reported failure for "' . $to . '" (no MTA?)');
          return false;
        }
        return true;
      } catch (\Throwable $e) {
        error_log('sendEmail: exception for "' . $to . '": ' . $e->getMessage());
        return false;
      }
    }
  }
?>
