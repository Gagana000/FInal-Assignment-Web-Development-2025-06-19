<?php
session_start();
require_once __DIR__ . '/../auth_functions.php';
require_login();
require_admin();

// Database connection
require_once __DIR__ . '/../database.php';

// Generate CSRF token
$csrf_token = generate_csrf_token();

// Fetch statistics
try {
    // Total Products
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $total_products = $stmt->fetchColumn();

    // Total Users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $total_users = $stmt->fetchColumn();

    // Pending Orders
    $pending_orders = 0;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
        $pending_orders = $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Orders table not found: " . $e->getMessage());
    }

    // Out of Stock Items
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock <= 0");
    $out_of_stock = $stmt->fetchColumn();

    // Recent Products
    $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 5");
    $recent_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("Database error occurred. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - NSBM Premium</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="shortcut icon" href="assets/images/logo_brand.png" type="image/x-icon">
</head>

<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <h2><i class="fas fa-crown"></i> Admin Panel</h2>
            </div>
            <ul class="admin-menu">
                <li><a href="dashboard.php" class="active"><i class="fas fa-gauge"></i> Dashboard</a></li>
                <li><a href="product.php"><i class="fas fa-tshirt"></i> Products</a></li>
                <li><a href="orders.php"><i class="fas fa-receipt"></i> Orders</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="settings.php"><i class="fas fa-gear"></i> Settings</a></li>
            </ul>
        </aside>

        <main class="admin-main">
            <div class="admin-header">
                <h1 class="admin-title animate__animated animate__fadeIn">Dashboard Overview</h1>
                <a href="../logout.php" class="btn-admin btn-outline hover-grow">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="admin-card stats-modern animate__animated animate__fadeIn">
                <h2><i class="fas fa-chart-line"></i> Quick Stats</h2>
                <div class="stats-grid-modern">
                    <div class="stat-card-modern animate__animated animate__fadeInUp">
                        <div class="stat-icon-modern" style="background-color: var(--primary-accent);">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <div class="stat-info-modern">
                            <h3>Total Products</h3>
                            <p><?= htmlspecialchars($total_products) ?></p>
                            <div class="stat-progress">
                                <div class="progress-bar"
                                    style="width: <?= min(100, ($total_products / 50) * 100) ?>%; background-color: var(--primary-accent);">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card-modern animate__animated animate__fadeInUp animate__delay-1s">
                        <div class="stat-icon-modern" style="background-color: var(--primary-dark);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info-modern">
                            <h3>Total Users</h3>
                            <p><?= htmlspecialchars($total_users) ?></p>
                            <div class="stat-progress">
                                <div class="progress-bar"
                                    style="width: <?= min(100, ($total_users / 100) * 100) ?>%; background-color: var(--primary-dark);">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card-modern animate__animated animate__fadeInUp animate__delay-2s">
                        <div class="stat-icon-modern"
                            style="background-color: var(--primary-light); color: var(--primary-dark);">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="stat-info-modern">
                            <h3>Pending Orders</h3>
                            <p><?= htmlspecialchars($pending_orders) ?></p>
                            <div class="stat-progress">
                                <div class="progress-bar"
                                    style="width: <?= min(100, ($pending_orders / 20) * 100) ?>%; background-color: var(--primary-light);">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card-modern animate__animated animate__fadeInUp animate__delay-3s">
                        <div class="stat-icon-modern"
                            style="background-color: var(--secondary-light); color: var(--primary-dark);">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-info-modern">
                            <h3>Out of Stock</h3>
                            <p><?= htmlspecialchars($out_of_stock) ?></p>
                            <div class="stat-progress">
                                <div class="progress-bar"
                                    style="width: <?= min(100, ($out_of_stock / 10) * 100) ?>%; background-color: var(--secondary-light);">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Products -->
            <div class="admin-card products-modern animate__animated animate__fadeIn">
                <div class="card-header-modern">
                    <h2><i class="fas fa-clock-rotate-left"></i> Recent Products</h2>
                    <a href="product.php" class="btn-admin btn-primary hover-grow">
                        <i class="fas fa-arrow-right"></i> View All
                    </a>
                </div>
                <div class="table-container">
                    <table class="admin-table-modern">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Added On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_products as $product): ?>
                                <tr class="animate__animated animate__fadeIn">
                                    <td>#<?= htmlspecialchars($product['id']) ?></td>
                                    <td>
                                        <div class="product-info">
                                            <?php if (!empty($product['image_url'])): ?>
                                                <img src="../../assets/uploads/<?= htmlspecialchars($product['image_url']) ?>"
                                                    alt="<?= htmlspecialchars($product['name']) ?>" class="product-thumbnail">
                                            <?php endif; ?>
                                            <span><?= htmlspecialchars($product['name']) ?></span>
                                        </div>
                                    </td>
                                    <td>$<?= number_format($product['price'], 2) ?></td>
                                    <td>
                                        <span
                                            class="status-badge <?= $product['stock'] > 0 ? 'status-active' : 'status-inactive' ?>">
                                            <?= htmlspecialchars($product['stock']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($product['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>

</html>