<?php
  require_once __DIR__ . '/../includes/auth_functions.php';
  session_start();

  // Admin-only access
  if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
      header("Location: ../includes/login.php?role=admin");
      exit();
  }
?>