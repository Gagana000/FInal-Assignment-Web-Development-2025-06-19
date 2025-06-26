<?php
  session_start();
  require_once __DIR__ . '/../auth_functions.php';
  require_login();
  require_admin();

  // Admin-only access
  if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
      header("Location: ../includes/login.php?role=admin");
      exit();
  }
?>