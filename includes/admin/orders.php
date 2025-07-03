<?php
session_start();
require_once __DIR__ . '/../auth_functions.php';
require_login();
require_admin();

// Database connection
require_once __DIR__ . '/../database.php';

// Generate CSRF token
$csrf_token = generate_csrf_token();

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
  // Verify CSRF token
  if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
    die("Invalid CSRF token");
  }

  $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
  $new_status = filter_input(INPUT_POST, 'new_status', FILTER_SANITIZE_STRING);

  try {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);

    $_SESSION['success'] = "Order status updated successfully";
    header("Location: orders.php");
    exit();
  } catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error'] = "Failed to update order status";
  }
}

// Fetch all orders with user information
try {
  $stmt = $pdo->query("
        SELECT o.*, u.username, u.email 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.order_date DESC
    ");
  $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // For each order, fetch its items
  foreach ($orders as &$order) {
    $stmt = $pdo->prepare("
            SELECT oi.*, p.name, p.image_url 
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
    $stmt->execute([$order['id']]);
    $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  unset($order);

} catch (PDOException $e) {
  error_log("Database error: " . $e->getMessage());
  $orders = [];
  $error = "Failed to load orders. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order Management - NSBM Premium</title>
  <link rel="stylesheet" href="../../style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="shortcut icon" href="assets/images/logo_brand.png" type="image/x-icon">
</head>

<body>
  <div class="admin-container">
    <aside class="admin-sidebar">
      <div class="admin-brand">
        <h2><i class="fas fa-crown"></i> Admin Panel</h2>
      </div>
      <ul class="admin-menu">
        <li><a href="dashboard.php"><i class="fas fa-gauge"></i> Dashboard</a></li>
        <li><a href="product.php"><i class="fas fa-tshirt"></i> Products</a></li>
        <li><a href="orders.php" class="active"><i class="fas fa-receipt"></i> Orders</a></li>
        <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
        <li><a href="settings.php"><i class="fas fa-gear"></i> Settings</a></li>
      </ul>
    </aside>

    <main class="admin-main">
      <div class="admin-header">
        <h1 class="admin-title">
          <i class="fas fa-receipt"></i> Order Management
        </h1>
      </div>

      <?php if (isset($_SESSION['success'])): ?>
        <div class="admin-card success-message">
          <i class="fas fa-check-circle"></i>
          <?= htmlspecialchars($_SESSION['success']); ?>
          <?php unset($_SESSION['success']); ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="admin-card error-message">
          <i class="fas fa-exclamation-circle"></i>
          <?= htmlspecialchars($_SESSION['error']); ?>
          <?php unset($_SESSION['error']); ?>
        </div>
      <?php endif; ?>

      <?php if (isset($error)): ?>
        <div class="admin-card error-message">
          <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <div class="admin-card">
        <?php if (empty($orders)): ?>
          <div class="no-products">
            <i class="fas fa-box-open"></i>
            <h3>No Orders Found</h3>
            <p>There are no orders to display at this time.</p>
          </div>
        <?php else: ?>
          <?php foreach ($orders as $order): ?>
            <div class="order-card">
              <div class="order-header">
                <div>
                  <h3>Order #<?= htmlspecialchars($order['id']) ?></h3>
                  <p>
                    Customer: <?= htmlspecialchars($order['username']) ?>
                    (<?= htmlspecialchars($order['email']) ?>)
                  </p>
                </div>
                <div>
                  <span class="status-<?= htmlspecialchars($order['status']) ?>">
                    <?= strtoupper(htmlspecialchars($order['status'])) ?>
                  </span>
                  <p>Date: <?= date('M d, Y h:i A', strtotime($order['order_date'])) ?></p>
                  <p>Total: $<?= number_format($order['total_amount'], 2) ?></p>
                </div>
              </div>

              <div class="order-body">
                <h4>Order Items:</h4>
                <?php foreach ($order['items'] as $item): ?>
                  <div class="order-item">
                    <img src="../../assets/uploads/<?= htmlspecialchars($item['image_url'] ?: 'default-product.jpg') ?>"
                      alt="<?= htmlspecialchars($item['name']) ?>">
                    <div style="flex-grow: 1;">
                      <h4><?= htmlspecialchars($item['name']) ?></h4>
                      <p>Quantity: <?= htmlspecialchars($item['quantity']) ?></p>
                    </div>
                    <div>
                      $<?= number_format($item['price'], 2) ?>
                    </div>
                  </div>
                <?php endforeach; ?>

                <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
                  <div>
                    <strong>Order Total: $<?= number_format($order['total_amount'], 2) ?></strong>
                  </div>
                  <form class="status-form" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

                    <select name="new_status" class="btn-admin">
                      <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                      <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                      <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>

                    <button type="submit" name="update_status" class="btn-admin btn-primary">
                      <i class="fas fa-save"></i> Update Status
                    </button>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>

</html>