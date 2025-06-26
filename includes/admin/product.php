<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit(); // Always exit after redirect
}

require_once __DIR__ . '/../../includes/database.php'; // Use absolute path

try {
    // Fetch all products
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching products: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin Panel</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: var(--primary-dark);
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        a {
            color: var(--primary-accent);
            text-decoration: none;
            margin-right: 10px;
        }
        a:hover {
            text-decoration: underline;
        }
        .add-btn {
            display: inline-block;
            background: var(--primary-accent);
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Product Management</h1>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?= htmlspecialchars($product['id']) ?></td>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td>$<?= number_format($product['price'], 2) ?></td>
                <td><?= $product['stock'] ?? 'N/A' ?></td>
                <td>
                    <a href="edit_product.php?id=<?= $product['id'] ?>">Edit</a>
                    <a href="delete_product.php?id=<?= $product['id'] ?>" 
                       onclick="return confirm('Delete this product?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="add_product.php" class="add-btn">Add New Product</a>
</body>
</html>