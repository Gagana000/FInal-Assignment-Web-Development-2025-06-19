<?php
session_start();
require_once __DIR__ . '/../auth_functions.php';
require_login();
require_admin();

// Database connection
require_once __DIR__ . '/../database.php';

// Handle delete action
if (isset($_GET['delete'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: products.php?success=Product+deleted");
    exit();
}

// Fetch all products
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <li><a href="../admin/product.php" class="active"><i class="fas fa-tshirt"></i> Products</a></li>
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

            <?php if (isset($_GET['success'])): ?>
                <div class="admin-card success-message">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($_GET['success']) ?>
                </div>
            <?php endif; ?>

            <div class="admin-card">
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
                                <td>#<?= $product['id'] ?></td>
                                <td>
                                    <div class="product-info">
                                        <?php if (!empty($product['image_url'])): ?>
                                            <img src="../../assets/uploads/<?= $product['image_url'] ?>"
                                                alt="<?= $product['name'] ?>" class="product-thumbnail">
                                        <?php endif; ?>
                                        <span><?= htmlspecialchars($product['name']) ?></span>
                                    </div>
                                </td>
                                <td>$<?= number_format($product['price'], 2) ?></td>
                                <td><?= $product['stock'] ?></td>
                                <td>
                                    <span
                                        class="status-badge <?= $product['stock'] > 0 ? 'status-active' : 'status-inactive' ?>">
                                        <?= $product['stock'] > 0 ? 'In Stock' : 'Out of Stock' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="view_product.php?id=<?= $product['id'] ?>" class="action-btn view-btn">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="edit_product.php?id=<?= $product['id'] ?>" class="action-btn edit-btn">
                                        <i class="fas fa-pen"></i> Edit
                                    </a>
                                    <a href="products.php?delete=true&id=<?= $product['id'] ?>"
                                        class="action-btn delete-btn" onclick="return confirm('Delete this product?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>

</html>