<?php
session_start();
require_once __DIR__ . '/../auth_functions.php';
require_login();
require_admin();

// Database connection
require_once __DIR__ . '/../database.php';

// Generate CSRF token
$csrf_token = generate_csrf_token();

// Get product ID from URL
$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$product_id) {
  header("Location: product.php?error=Invalid+product+ID");
  exit();
}

// Initialize variables
$error = '';
$success = false;

// Handle form submission (for direct access without AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Verify CSRF token
  if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
    $error = "Invalid CSRF token";
  } else {
    try {
      $pdo->beginTransaction();

      // Get product image path
      $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
      $stmt->execute([$product_id]);
      $product = $stmt->fetch();

      if (!$product) {
        throw new Exception("Product not found");
      }

      // Delete from database
      $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
      if (!$stmt->execute([$product_id])) {
        throw new Exception("Database delete failed");
      }

      // Delete associated image file if not default
      if ($product['image_url'] && $product['image_url'] !== 'default-product.jpg') {
        $imagePath = realpath(__DIR__ . '/../../assets/uploads/' . $product['image_url']);
        if ($imagePath && file_exists($imagePath)) {
          if (!unlink($imagePath)) {
            error_log("Failed to delete image: $imagePath");
          }
        }
      }

      $pdo->commit();
      $success = true;

      // Redirect if not AJAX request
      if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
        header("Location: product.php?success=Product+deleted+successfully");
        exit();
      }

    } catch (Exception $e) {
      $pdo->rollBack();
      $error = $e->getMessage();

      if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
        header("Location: product.php?error=" . urlencode($error));
        exit();
      }
    }
  }
}

// If AJAX request, return JSON response
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
  header('Content-Type: application/json');
  if ($success) {
    echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
  } else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $error]);
  }
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Delete Product - NSBM Premium</title>
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
        <li><a href="product.php"><i class="fas fa-tshirt"></i> Products</a></li>
        <li><a href="orders.php"><i class="fas fa-receipt"></i> Orders</a></li>
        <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
        <li><a href="settings.php"><i class="fas fa-gear"></i> Settings</a></li>
      </ul>
    </aside>

    <main class="admin-main">
      <div class="admin-header">
        <h1 class="admin-title">
          <i class="fas fa-trash"></i> Delete Product
        </h1>
        <a href="product.php" class="btn-admin btn-outline">
          <i class="fas fa-arrow-left"></i> Back to Products
        </a>
      </div>

      <div class="admin-card">
        <?php if ($error): ?>
          <div class="error-message">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="success-message">
            <i class="fas fa-check-circle"></i> Product deleted successfully
          </div>
        <?php else: ?>
          <form method="POST" id="deleteForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div class="confirmation-message">
              <i class="fas fa-exclamation-triangle"></i>
              <h3>Are you sure you want to delete this product?</h3>
              <p>This action cannot be undone. All product data including images will be permanently removed.</p>
            </div>

            <div class="form-actions">
              <a href="product.php" class="btn-admin btn-outline">
                <i class="fas fa-times"></i> Cancel
              </a>
              <button type="submit" class="btn-admin btn-danger">
                <i class="fas fa-trash"></i> Confirm Delete
              </button>
            </div>
          </form>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>

</html>