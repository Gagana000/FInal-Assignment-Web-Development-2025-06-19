<?php
session_start();
require_once __DIR__ . '/../auth_functions.php';
require_login();
require_admin();

// Database connection
require_once __DIR__ . '/../database.php';

// Initialize variables
$errors = [];
$product = [
    'name' => '',
    'price' => '',
    'stock' => '',
    'description' => '',
    'category' => '',
    'image_url' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $product['name'] = trim($_POST['name']);
    $product['price'] = (float) $_POST['price'];
    $product['stock'] = (int) $_POST['stock'];
    $product['description'] = trim($_POST['description']);
    $product['category'] = $_POST['category'];

    // Validate inputs
    if (empty($product['name'])) {
        $errors['name'] = 'Product name is required';
    }

    if ($product['price'] <= 0) {
        $errors['price'] = 'Price must be greater than 0';
    }

    if ($product['stock'] < 0) {
        $errors['stock'] = 'Stock cannot be negative';
    }

    if (empty($product['category'])) {
        $errors['category'] = 'Category is required';
    }

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType = $_FILES['image']['type'];

        if (in_array($fileType, $allowedTypes)) {
            $uploadDir = '../../assets/uploads/';
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $product['image_url'] = $fileName;
            } else {
                $errors['image'] = 'Failed to upload image';
            }
        } else {
            $errors['image'] = 'Only JPG, PNG, and WEBP files are allowed';
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO products 
                (name, price, stock, description, category, image_url) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $product['name'],
                $product['price'],
                $product['stock'],
                $product['description'],
                $product['category'],
                $product['image_url']
            ]);

            $success = true;
            // Clear form on success
            $product = [
                'name' => '',
                'price' => '',
                'stock' => '',
                'description' => '',
                'category' => '',
                'image_url' => ''
            ];
        } catch (PDOException $e) {
            $errors['database'] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - NSBM Premium</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../../main.js" defer></script>
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
            <div class="success-popup <?= $success ? 'show' : '' ?>" id="successPopup">
                <i class="fas fa-check"></i>
                <span>Product added successfully!</span>
                <div class="close-btn" onclick="hidePopup()">
                    <i class="fas fa-times"></i>
                </div>
            </div>

            <div class="admin-header">
                <h1 class="admin-title">
                    <i class="fas fa-plus-circle"></i> Add New Product
                </h1>
                <a href="product.php" class="btn-admin btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>

            <div class="admin-card">
                <form class="crud-form" method="POST" enctype="multipart/form-data">
                    <?php if (!empty($errors['database'])): ?>
                        <div class="error-message" style="margin-bottom: 30px;">
                            <i class="fas fa-exclamation-circle"></i> <?= $errors['database'] ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-grid">
                        <div class="form-group <?= isset($errors['name']) ? 'has-error' : '' ?>">
                            <label for="name">Product Name</label>
                            <input type="text" id="name" name="name" required
                                value="<?= htmlspecialchars($product['name']) ?>">
                            <?php if (isset($errors['name'])): ?>
                                <span class="error-message"><?= $errors['name'] ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group <?= isset($errors['price']) ? 'has-error' : '' ?>">
                            <label for="price">Price ($)</label>
                            <input type="number" id="price" name="price" step="0.01" min="0.01" required
                                value="<?= htmlspecialchars($product['price']) ?>">
                            <?php if (isset($errors['price'])): ?>
                                <span class="error-message"><?= $errors['price'] ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group <?= isset($errors['stock']) ? 'has-error' : '' ?>">
                            <label for="stock">Stock Quantity</label>
                            <input type="number" id="stock" name="stock" min="0" required
                                value="<?= htmlspecialchars($product['stock']) ?>">
                            <?php if (isset($errors['stock'])): ?>
                                <span class="error-message"><?= $errors['stock'] ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group select-wrapper <?= isset($errors['category']) ? 'has-error' : '' ?>">
                            <label for="category">Category</label>
                            <select id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="T-Shirts" <?= $product['category'] === 'T-Shirts' ? 'selected' : '' ?>>
                                    T-Shirts</option>
                                <option value="Hoodies" <?= $product['category'] === 'Hoodies' ? 'selected' : '' ?>>Hoodies
                                </option>
                                <option value="Accessories" <?= $product['category'] === 'Accessories' ? 'selected' : '' ?>>Accessories</option>
                                <option value="Stationery" <?= $product['category'] === 'Stationery' ? 'selected' : '' ?>>
                                    Stationery</option>
                                <option value="Other" <?= $product['category'] === 'Other' ? 'selected' : '' ?>>Other
                                </option>
                            </select>
                            <?php if (isset($errors['category'])): ?>
                                <span class="error-message"><?= $errors['category'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group <?= isset($errors['description']) ? 'has-error' : '' ?>">
                        <label for="description">Product Description</label>
                        <textarea id="description" name="description"
                            placeholder="Describe the product features, materials, sizing, etc."><?= htmlspecialchars($product['description']) ?></textarea>
                    </div>

                    <div class="form-group <?= isset($errors['image']) ? 'has-error' : '' ?>">
                        <label>Product Image</label>
                        <div class="image-upload" id="imageUploadArea">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Drag & drop your image here or click to browse</p>
                            <p class="text-muted">(Recommended: 800x800px, JPG/PNG/WEBP)</p>
                            <input type="file" id="image" name="image" accept="image/*" style="display: none;">
                            <img id="imagePreview" class="image-preview" src="" alt="Preview">
                        </div>
                        <?php if (isset($errors['image'])): ?>
                            <span class="error-message"><?= $errors['image'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-actions">
                        <button type="reset" class="btn-admin btn-outline">
                            <i class="fas fa-eraser"></i> Clear Form
                        </button>
                        <button type="submit" class="btn-admin btn-primary">
                            <i class="fas fa-save"></i> Save Product
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Success popup functionality
        function hidePopup() {
            const popup = document.getElementById('successPopup');
            popup.classList.remove('show');
        }

        // Auto-hide after 5 seconds
        <?php if ($success): ?>
            setTimeout(hidePopup, 5000);
        <?php endif; ?>

        // Reset form on success if needed
        <?php if ($success): ?>
            document.getElementById('productForm').reset();
        <?php endif; ?>
    </script>
</body>

</html>