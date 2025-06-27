<?php
session_start();
require_once __DIR__ . '/../auth_functions.php';
require_login();
require_admin();

// Database connection
require_once __DIR__ . '/../database.php';

// Generate CSRF token
$csrf_token = generate_csrf_token();

// Handle delete action
if (isset($_GET['delete'])) {
    // Verify CSRF token
    if (!isset($_GET['csrf_token']) || !validate_csrf_token($_GET['csrf_token'])) {
        header("Location: products.php?error=Invalid+CSRF+token");
        exit();
    }

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        header("Location: products.php?error=Invalid+product+ID");
        exit();
    }

    try {
        // First get image path to delete file
        $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);

        // Delete associated image file if not default
        if ($product && $product['image_url'] !== 'default-product.jpg') {
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/Final_Assignment_Web_Development_2025-06-19/assets/uploads/' . $product['image_url'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        header("Location: products.php?success=Product+deleted+successfully");
        exit();
    } catch (PDOException $e) {
        error_log("Delete error: " . $e->getMessage());
        header("Location: products.php?error=Failed+to+delete+product");
        exit();
    }
}

// Fetch all products with error handling
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $products = [];
    $error = "Failed to load products. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - NSBM Premium</title>
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
            <div class="admin-header">
                <h1 class="admin-title">
                    <i class="fas fa-tshirt"></i> Product Management
                </h1>
                <a href="add_product.php" class="btn-admin btn-primary">
                    <i class="fas fa-plus"></i> Add Product
                </a>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="admin-card error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars(urldecode($_GET['error'])) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="admin-card success-message">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars(urldecode($_GET['success'])) ?>
                </div>
            <?php endif; ?>

            <div class="admin-card">
                <?php if (empty($products)): ?>
                    <div class="no-products">
                        <i class="fas fa-box-open"></i>
                        <h3>No Products Found</h3>
                        <p>You haven't added any products yet. Click the "Add Product" button to get started.</p>
                    </div>
                <?php else: ?>
                    <table class="crud-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>#<?= htmlspecialchars($product['id']) ?></td>
                                    <td>
                                        <div class="product-info">
                                            <?php
                                            $imagePath = '/Final_Assignment_Web_Development_2025-06-19/assets/uploads/' . htmlspecialchars($product['image_url']);
                                            $defaultImage = '../../assets/uploads/default-product.jpg';
                                            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
                                            ?>

                                            <?php if (!empty($product['image_url']) && file_exists($fullPath)): ?>
                                                <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['name']) ?>"
                                                    class="product-thumbnail" onerror="this.src='<?= $defaultImage ?>'">
                                            <?php else: ?>
                                                <img src="<?= $defaultImage ?>" alt="Default product image"
                                                    class="product-thumbnail">
                                            <?php endif; ?>
                                            <p><?= htmlspecialchars($product['name']) ?></p>
                                        </div>
                                    </td>
                                    <td>$<?= number_format($product['price'], 2) ?></td>
                                    <td><?= htmlspecialchars($product['stock']) ?></td>
                                    <td>
                                        <span
                                            class="status-badge <?= $product['stock'] > 0 ? 'status-active' : 'status-inactive' ?>">
                                            <?= $product['stock'] > 0 ? 'In Stock' : 'Out of Stock' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="view_product.php?id=<?= $product['id'] ?>" class="action-btn view-btn">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="edit_product.php?id=<?= $product['id'] ?>" class="action-btn edit-btn">
                                                <i class="fas fa-pen"></i> Edit
                                            </a>
                                            <a href="products.php?delete=true&id=<?= $product['id'] ?>&csrf_token=<?= $csrf_token ?>"
                                                class="action-btn delete-btn"
                                                onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars(addslashes($product['name'])) ?>?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>

</html>