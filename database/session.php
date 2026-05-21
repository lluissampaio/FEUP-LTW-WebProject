<?php
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  // --- Authentication Helpers ---

  function isLoggedIn() {
    return isset($_SESSION['user_id']);
  }

  function requireLogin() {
    if (!isLoggedIn()) {
      $_SESSION['messages'][] = ['type' => 'error', 'text' => 'You must be logged in to access this page.'];
      header('Location: login.php');
      exit();
    }
  }

  function redirectIfLoggedIn() {
    if (isLoggedIn()) {
      header('Location: dashboard.php');
      exit();
    }
  }

  // --- Flash Messages ---

  function addMessage($type, $text) {
    if (!isset($_SESSION['messages'])) {
      $_SESSION['messages'] = array();
    }
    $_SESSION['messages'][] = ['type' => $type, 'text' => $text];
  }

  function getMessages() {
    $messages = isset($_SESSION['messages']) ? $_SESSION['messages'] : array();
    unset($_SESSION['messages']);
    return $messages;
  }

  // --- CSRF Tokens ---

  function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
  }

  function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
      addMessage('error', 'Invalid CSRF token. Please try again.');
      header('Location: ' . $_SERVER['HTTP_REFERER']);
      exit();
    }
  }
?>
