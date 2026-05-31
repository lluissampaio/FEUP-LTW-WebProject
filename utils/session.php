<?php
  declare(strict_types = 1);

  // Keep error details out of HTTP responses; route them to the server log instead.
  ini_set('display_errors', '0');
  ini_set('log_errors', '1');
  error_reporting(E_ALL);

  class Session {
    private array $messages;

    public function __construct() {
      if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
          'lifetime' => 0,
          'path'     => '/',
          'httponly' => true,
          'samesite' => 'Lax',
          'secure'   => !empty($_SERVER['HTTPS']),
        ]);
        session_start();
      }

      $this->messages = isset($_SESSION['messages']) ? $_SESSION['messages'] : array();
      unset($_SESSION['messages']);
    }

    public function isLoggedIn() : bool {
      return isset($_SESSION['id']);
    }

    public function logout() : void {
      $_SESSION = array();
      session_regenerate_id(true);
      session_destroy();
    }

    public function getId() : ?int {
      return isset($_SESSION['id']) ? $_SESSION['id'] : null;
    }

    public function getName() : ?string {
      return isset($_SESSION['name']) ? $_SESSION['name'] : null;
    }

    public function setId(int $id) : void {
      $_SESSION['id'] = $id;
    }

    public function setName(string $name) : void {
      $_SESSION['name'] = $name;
    }

    public function getRole() : ?string {
      return isset($_SESSION['role']) ? $_SESSION['role'] : null;
    }

    public function setRole(string $role) : void {
      $_SESSION['role'] = $role;
    }

    public function getPhoto() : ?string {
      return isset($_SESSION['photo']) ? $_SESSION['photo'] : null;
    }

    public function setPhoto(?string $photo) : void {
      $_SESSION['photo'] = $photo;
    }

    public function getJoinDate() : ?string {
      return isset($_SESSION['join_date']) ? $_SESSION['join_date'] : null;
    }

    public function setJoinDate(string $date) : void {
      $_SESSION['join_date'] = $date;
    }

    public function addMessage(string $type, string $text) : void {
      $_SESSION['messages'][] = array('type' => $type, 'text' => $text);
    }

    public function getMessages() : array {
      return $this->messages;
    }

    public function requireLogin(string $redirectTo = '../pages/login.php') : void {
      if (!$this->isLoggedIn()) {
        $this->addMessage('error', 'You must be logged in to access this page.');
        header('Location: ' . $redirectTo);
        exit();
      }
    }

    public function redirectIfLoggedIn(string $redirectTo = 'dashboard.php') : void {
      if ($this->isLoggedIn()) {
        header('Location: ' . $redirectTo);
        exit();
      }
    }

    public function csrfToken() : string {
      if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
      }
      return $_SESSION['csrf_token'];
    }

    public function verifyCsrf(string $token, string $redirectTo) : void {
      if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $this->addMessage('error', 'Invalid CSRF token. Please try again.');
        header('Location: ' . $redirectTo);
        exit();
      }
    }
  }

  // Role lookups are cached in the session at login time. For accounts that
  // existed before this cache was added we fall back to a single BD query
  // and then promote the value into the session.
  function sessionRole(Session $session) : ?string {
    if (!$session->isLoggedIn()) return null;
    $role = $session->getRole();
    if ($role !== null) return $role;
    try {
      // Assuming getDatabaseConnection is available in the calling context.
      // Usually required by the controllers before calling these functions.
      $db   = getDatabaseConnection();
      $stmt = $db->prepare('SELECT Role FROM User WHERE UserId = ?');
      $stmt->execute([$session->getId()]);
      $row  = $stmt->fetch();
      if ($row) { $session->setRole($row['Role']); return $row['Role']; }
    } catch (Throwable $e) {}
    return null;
  }

  function isAdminUser(Session $session) : bool {
    return sessionRole($session) === 'admin';
  }

  function isMemberUser(Session $session) : bool {
    return sessionRole($session) === 'member';
  }

  function isTrainerUser(Session $session) : bool {
    return sessionRole($session) === 'trainer';
  }

  // ---------------------------------------------------------------------------
  // Global fatal-error handling.
  //
  // Goal: an uncaught throwable or a fatal PHP error must show a branded error
  // page instead of a blank screen. Normal pages are unaffected — these handlers
  // only fire on uncaught throwables / fatal errors. Non-fatal notices/warnings
  // are left to the default reporting (logged, since log_errors is on above).
  //
  // Registered only once, guarded so repeated require_once of this file is safe.
  // ---------------------------------------------------------------------------
  if (!defined('IRON_HAVEN_ERROR_HANDLERS_REGISTERED')) {
    define('IRON_HAVEN_ERROR_HANDLERS_REGISTERED', true);

    function ironHavenRenderError(int $code, string $title, string $message) : void {
      // Send an appropriate status only if the response has not started yet.
      if (!headers_sent()) {
        http_response_code($code);
      }

      // Render the standalone branded error page. Guard the include so a missing
      // template can never itself throw and produce a true blank screen.
      $template = __DIR__ . '/../templates/error.tpl.php';
      if (is_file($template)) {
        require_once($template);
      }

      if (function_exists('drawErrorPage')) {
        drawErrorPage($code, $title, $message);
      } else {
        // Last-resort fallback: minimal escaped output, never a blank page.
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
           . '<title>' . (int) $code . ' Error</title></head><body>'
           . '<h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>'
           . '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>'
           . '<p><a href="/pages/index.php">Back to home</a></p>'
           . '</body></html>';
      }
    }

    // Handle any throwable that escapes to the top level.
    set_exception_handler(function (Throwable $e) : void {
      error_log(sprintf(
        'Uncaught %s: %s in %s:%d',
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
      ));

      ironHavenRenderError(
        500,
        'Something went wrong',
        'An unexpected error occurred while processing your request. Our team has been notified — please try again shortly.'
      );
    });

    // Convert fatal runtime errors (E_ERROR, E_PARSE, E_CORE_ERROR, etc.) that
    // would otherwise leave a blank screen into the branded page. set_error_handler
    // does not catch true fatals, so the work happens in the shutdown function;
    // this handler is kept only to capture E_USER_ERROR and re-throw it.
    set_error_handler(function (int $severity, string $message, string $file = '', int $line = 0) : bool {
      // Respect the error_reporting level (and the @-operator, which sets it to 0).
      if (!(error_reporting() & $severity)) {
        return false;
      }
      // Only escalate the fatal user-error level; let everything else fall through
      // to PHP's default handling so normal warnings/notices keep being logged.
      if ($severity === E_USER_ERROR) {
        throw new ErrorException($message, 0, $severity, $file, $line);
      }
      return false;
    });

    // Catch genuine fatal errors (parse/compile/out-of-memory/uncaught) on shutdown.
    register_shutdown_function(function () : void {
      $error = error_get_last();
      if ($error === null) {
        return;
      }

      $fatalLevels = E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING
                   | E_COMPILE_ERROR | E_COMPILE_WARNING;

      if (($error['type'] & $fatalLevels) === 0) {
        return;
      }

      error_log(sprintf(
        'Fatal error: %s in %s:%d',
        $error['message'],
        $error['file'],
        $error['line']
      ));

      // If a partial successful response already started, do not overwrite it.
      if (headers_sent()) {
        return;
      }

      ironHavenRenderError(
        500,
        'Something went wrong',
        'An unexpected error occurred while processing your request. Our team has been notified — please try again shortly.'
      );
    });
  }
?>
