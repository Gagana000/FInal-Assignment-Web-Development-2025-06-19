<?php
session_start();
require_once __DIR__ . '/../auth_functions.php';
require_login();
require_admin();

// Database connection
require_once __DIR__ . '/../database.php';

// Get and validate product ID
$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$product_id) {
  header("Location: product.php?error=Invalid+product+ID");
  exit();
}

// Fetch product details with error handling
try {
  $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
  $stmt->execute([$product_id]);
  $product = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$product) {
    header("Location: product.php?error=Product+not+found");
    exit();
  }
} catch (PDOException $e) {
  error_log("Database error: " . $e->getMessage());
  header("Location: product.php?error=Database+error");
  exit();
}

// Define base paths
$base_url = '/Final_Assignment_Web_Development_2025-06-19/';
$uploads_path = $base_url . 'assets/uploads/';
$default_image = $base_url . 'assets/default-product.jpg';

// Generate CSRF token for delete action
$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($product['name']) ?> - NSBM Premium</title>
  <link rel="stylesheet" href="../../style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
  <div class="admin-container">
    <aside class="admin-sidebar">
      <div class="admin-brand">
        <h2><i class="fas fa-crown"></i> Admin Panel</h2>
      </div>
      <ul class="admin-menu">
        <li><a href="dashboard.php"><i class="fas fa-gauge"></i> Dashboard</a></li>
        <li><a href="product.php" class="active"><i class="fas fa-tshirt"></i> Products</a></li>
        <li><a href="orders.php"><i class="fas fa-receipt"></i> Orders</a></li>
        <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
        <li><a href="settings.php"><i class="fas fa-gear"></i> Settings</a></li>
      </ul>
    </aside>

    <main class="admin-main">
      <div class="product-view-container">
        <div class="product-header">
          <h1 class="admin-title">
            <i class="fas fa-tshirt"></i> <?= htmlspecialchars($product['name']) ?>
          </h1>
          <a href="product.php" class="btn-admin btn-back">
            <i class="fas fa-arrow-left"></i> Back to Products
          </a>
        </div>

        <div class="admin-card">
          <div class="product-content">
            <div class="product-image-container">
              <?php
              $image_path = !empty($product['image_url']) ? $uploads_path . htmlspecialchars($product['image_url']) : $default_image;
              $full_path = $_SERVER['DOCUMENT_ROOT'] . parse_url($image_path, PHP_URL_PATH);

              if (!empty($product['image_url']) && file_exists($full_path)): ?>
                <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image"
                  onerror="this.src='<?= $default_image ?>'; this.onerror=null;">
              <?php else: ?>
                <i class="fas fa-tshirt no-image-icon"></i>
              <?php endif; ?>
            </div>

            <div class="product-details">
              <div class="product-price">$<?= number_format($product['price'], 2) ?></div>

              <div class="product-meta">
                <div class="meta-item">
                  <div class="meta-label">Product ID</div>
                  <div class="meta-value">#<?= htmlspecialchars($product['id']) ?></div>
                </div>

                <div class="meta-item">
                  <div class="meta-label">Stock</div>
                  <div class="meta-value"><?= htmlspecialchars($product['stock']) ?> available</div>
                </div>

                <div class="meta-item">
                  <div class="meta-label">Category</div>
                  <div class="meta-value"><?= htmlspecialchars($product['category']) ?></div>
                </div>

                <div class="meta-item">
                  <div class="meta-label">Status</div>
                  <div class="meta-value">
                    <span class="<?= $product['stock'] > 0 ? 'status-in-stock' : 'status-out-of-stock' ?>">
                      <?= $product['stock'] > 0 ? 'In Stock' : 'Out of Stock' ?>
                    </span>
                  </div>
                </div>
              </div>

              <div class="product-description">
                <h3>Description</h3>
                <p>
                  <?= !empty($product['description']) ? nl2br(htmlspecialchars($product['description'])) : 'No description available.' ?>
                </p>
              </div>

              <div class="product-actions">
                <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn-admin btn-edit">
                  <i class="fas fa-edit"></i> Edit Product
                </a>
                <a href="product.php?delete=true&id=<?= $product['id'] ?>&csrf_token=<?= $csrf_token ?>"
                  class="btn-admin btn-delete"
                  onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars(addslashes($product['name'])) ?>? This action cannot be undone.')">
                  <i class="fas fa-trash"></i> Delete Product
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>

</html>