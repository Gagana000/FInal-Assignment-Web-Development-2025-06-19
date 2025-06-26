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
    <title>Admin Dashboard - NSBM Premium</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <h2><i class="fas fa-crown"></i> Admin Panel</h2>
            </div>
            <ul class="admin-menu">
                <li><a href="dashboard.php" class="active"><i class="fas fa-gauge"></i> Dashboard</a></li>
                <li><a href="../admin/product.php"><i class="fas fa-tshirt"></i> Products</a></li>
                <li><a href="orders.php"><i class="fas fa-receipt"></i> Orders</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="settings.php"><i class="fas fa-gear"></i> Settings</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1 class="admin-title">Dashboard Overview</h1>
                <a href="../logout.php" class="btn-outline">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="admin-card">
                <h2><i class="fas fa-chart-line"></i> Quick Stats</h2>
                <div class="stats-grid">
                    <!-- Will be populated with PHP -->
                </div>
            </div>

            <!-- Recent Products -->
            <div class="admin-card">
                <div class="card-header">
                    <h2><i class="fas fa-clock-rotate-left"></i> Recent Products</h2>
                    <a href="products.php" class="btn-primary">View All</a>
                </div>
                <table class="admin-table">
                    <!-- Will be populated with PHP -->
                </table>
            </div>
        </main>
    </div>
</body>
</html>