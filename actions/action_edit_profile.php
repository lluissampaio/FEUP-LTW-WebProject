<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');

  $session = new Session();
  $session->requireLogin('../pages/login.php');
  $session->verifyCsrf($_POST['csrf_token'] ?? '', '../pages/profile.php');

  $db   = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());
  if (!$user) {
    $session->addMessage('error', 'Account not found.');
    header('Location: ../pages/login.php');
    exit();
  }

  $firstName = trim($_POST['first_name'] ?? '');
  $lastName  = trim($_POST['last_name'] ?? '');
  $username  = trim($_POST['username'] ?? '');
  $email     = trim($_POST['email'] ?? '');

  if ($firstName === '' || $lastName === '' || $username === '' || $email === '') {
    $session->addMessage('error', 'Name, username and email are required.');
    header('Location: ../pages/profile.php');
    exit();
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $session->addMessage('error', 'Please enter a valid email address.');
    header('Location: ../pages/profile.php');
    exit();
  }

  // --- Optional password change ------------------------------------------------
  $newPassword     = $_POST['new_password'] ?? '';
  $confirmPassword = $_POST['confirm_password'] ?? '';
  $changePassword  = $newPassword !== '' || $confirmPassword !== '';

  if ($changePassword) {
    if (strlen($newPassword) < 8) {
      $session->addMessage('error', 'New password must be at least 8 characters.');
      header('Location: ../pages/profile.php');
      exit();
    }
    if ($newPassword !== $confirmPassword) {
      $session->addMessage('error', 'New passwords do not match.');
      header('Location: ../pages/profile.php');
      exit();
    }
  }

  // --- Optional photo upload ---------------------------------------------------
  $newPhotoName = null;
  if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['photo'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
      $session->addMessage('error', 'Photo upload failed. Please try again.');
      header('Location: ../pages/profile.php');
      exit();
    }
    if ($file['size'] > 3 * 1024 * 1024) {
      $session->addMessage('error', 'Photo must be 3 MB or smaller.');
      header('Location: ../pages/profile.php');
      exit();
    }

    // Validate the real MIME type, not the client-supplied name.
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $finfo   = new finfo(FILEINFO_MIME_TYPE);
    $mime    = $finfo->file($file['tmp_name']);

    if (!isset($allowed[$mime])) {
      $session->addMessage('error', 'Photo must be a JPG, PNG or WebP image.');
      header('Location: ../pages/profile.php');
      exit();
    }

    $ext      = $allowed[$mime];
    $filename = 'user' . $user->id . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $destDir  = __DIR__ . '/../uploads';
    $dest     = $destDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
      $session->addMessage('error', 'Could not save the uploaded photo.');
      header('Location: ../pages/profile.php');
      exit();
    }

    // Remove the previous upload (only files we created live in uploads/).
    if ($user->photo !== null && is_file($destDir . '/' . $user->photo)) {
      @unlink($destDir . '/' . $user->photo);
    }
    $newPhotoName = $filename;
  }

  // --- Persist -----------------------------------------------------------------
  $user->firstName = $firstName;
  $user->lastName  = $lastName;
  $user->username  = $username;
  $user->email     = $email;
  if ($newPhotoName !== null) $user->photo = $newPhotoName;

  try {
    $user->save($db);
    if ($changePassword) $user->updatePassword($db, $newPassword);
  } catch (PDOException $e) {
    // UNIQUE violation on Username or Email.
    $session->addMessage('error', 'That username or email is already taken.');
    header('Location: ../pages/profile.php');
    exit();
  }

  $session->setName($user->name());
  $session->setPhoto($user->photo);
  $session->addMessage('success', 'Your profile has been updated.');
  header('Location: ../pages/profile.php');
  exit();
?>
