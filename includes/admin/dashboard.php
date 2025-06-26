<?php
  session_start();
  require_once __DIR__ . '/../auth_functions.php';
  require_login();
  require_admin();

  if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?redirect=admin");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | NSBM Premium</title>
</head>
<body>
  
</body>
</html>