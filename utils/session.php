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
?>
