<?php
session_start();
require_once __DIR__ . '/../auth_functions.php';
require_login();
require_admin();

// Database connection
require_once __DIR__ . '/../database.php';

// Generate CSRF token
$csrf_token = generate_csrf_token();

// Initialize variables
$errors = [];
$success = false;
$product = [
    'name' => '',
    'price' => '',
    'stock' => '',
    'description' => '',
    'category' => '',
    'image_url' => 'default-product.jpg'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $errors['form'] = 'Invalid CSRF token';
    }

    // Sanitize and validate inputs
    $product['name'] = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $product['price'] = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $product['stock'] = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
    $product['description'] = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $product['category'] = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Validate inputs
    if (empty($product['name'])) {
        $errors['name'] = 'Product name is required';
    } elseif (strlen($product['name']) > 100) {
        $errors['name'] = 'Name must be less than 100 characters';
    }

    if ($product['price'] === false || $product['price'] <= 0) {
        $errors['price'] = 'Valid price greater than 0 is required';
    }

    if ($product['stock'] === false || $product['stock'] < 0) {
        $errors['stock'] = 'Valid stock quantity is required';
    }

    if (empty($product['category']) || !in_array($product['category'], ['T-Shirts', 'Hoodies', 'Accessories', 'Stationery', 'Other'])) {
        $errors['category'] = 'Valid category is required';
    }

    // Handle file upload
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Final_Assignment_Web_Development_2025-06-19/assets/uploads/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            $errors['image'] = 'Failed to create upload directory';
        }
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors['image'] = 'File upload error: ' . $_FILES['image']['error'];
        } else {
            // Verify image
            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];
            
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $_FILES['image']['tmp_name']);
            finfo_close($fileInfo);

            if (!array_key_exists($mimeType, $allowedTypes)) {
                $errors['image'] = 'Only JPG, PNG, and WEBP files are allowed';
            } else {
                // Generate secure filename
                $extension = $allowedTypes[$mimeType];
                $fileName = 'product_' . bin2hex(random_bytes(8)) . '.' . $extension;
                $targetPath = $uploadDir . $fileName;

                // Resize and move uploaded file
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $errors['image'] = 'Failed to save uploaded file';
                } else {
                    // Set permissions on uploaded file
                    chmod($targetPath, 0644);
                    $product['image_url'] = $fileName;
                }
            }
        }
    }

    // Save to database if no errors
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

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

            $pdo->commit();
            $success = true;

            // Clear form on success except category
            $product = [
                'name' => '',
                'price' => '',
                'stock' => '',
                'description' => '',
                'category' => $product['category'],
                'image_url' => 'default-product.jpg'
            ];

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Database error: " . $e->getMessage());
            $errors['database'] = 'Failed to save product. Please try again.';
            
            // Delete uploaded file if database failed
            if (isset($targetPath) && file_exists($targetPath)) {
                unlink($targetPath);
            }
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
    <link rel="shortcut icon" href="../../assets/images/logo_brand.png" type="image/x-icon">
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
                <?php if (!empty($errors['form'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errors['form']) ?>
                    </div>
                <?php endif; ?>

                <form class="crud-form" method="POST" enctype="multipart/form-data" id="productForm">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                    <div class="form-grid">
                        <div class="form-group <?= isset($errors['name']) ? 'has-error' : '' ?>">
                            <label for="name">Product Name *</label>
                            <input type="text" id="name" name="name" required maxlength="100"
                                value="<?= htmlspecialchars($product['name']) ?>">
                            <?php if (isset($errors['name'])): ?>
                                <span class="error-message"><?= htmlspecialchars($errors['name']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group <?= isset($errors['price']) ? 'has-error' : '' ?>">
                            <label for="price">Price ($) *</label>
                            <input type="number" id="price" name="price" step="0.01" min="0.01" required
                                value="<?= htmlspecialchars($product['price']) ?>">
                            <?php if (isset($errors['price'])): ?>
                                <span class="error-message"><?= htmlspecialchars($errors['price']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group <?= isset($errors['stock']) ? 'has-error' : '' ?>">
                            <label for="stock">Stock Quantity *</label>
                            <input type="number" id="stock" name="stock" min="0" required
                                value="<?= htmlspecialchars($product['stock']) ?>">
                            <?php if (isset($errors['stock'])): ?>
                                <span class="error-message"><?= htmlspecialchars($errors['stock']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group select-wrapper <?= isset($errors['category']) ? 'has-error' : '' ?>">
                            <label for="category">Category *</label>
                            <select id="category" name="category" required>
                                <option value="">Select Category</option>
                                <?php foreach (['T-Shirts', 'Hoodies', 'Accessories', 'Stationery', 'Other'] as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>" 
                                        <?= $product['category'] === $cat ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['category'])): ?>
                                <span class="error-message"><?= htmlspecialchars($errors['category']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group <?= isset($errors['description']) ? 'has-error' : '' ?>">
                        <label for="description">Product Description</label>
                        <textarea id="description" name="description" maxlength="1000"
                            placeholder="Describe the product features, materials, sizing, etc."><?= htmlspecialchars($product['description']) ?></textarea>
                        <div class="char-counter">0/1000 characters</div>
                    </div>

                    <div class="form-group <?= isset($errors['image']) ? 'has-error' : '' ?>">
                        <label>Product Image</label>
                        <div class="image-upload" id="imageUploadArea">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Drag & drop your image here or click to browse</p>
                            <p class="text-muted">(Recommended: 800x800px, JPG/PNG/WEBP, max 2MB)</p>
                            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" style="display: none;">
                        </div>
                        <div class="image-preview-container">
                            <img id="imagePreview" class="image-preview" src="" alt="Preview">
                            <div class="file-info" id="fileInfo"></div>
                        </div>
                        <?php if (isset($errors['image'])): ?>
                            <span class="error-message"><?= htmlspecialchars($errors['image']) ?></span>
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
        // Image upload handling
        const imageUploadArea = document.getElementById('imageUploadArea');
        const fileInput = document.getElementById('image');
        const imagePreview = document.getElementById('imagePreview');
        const fileInfo = document.getElementById('fileInfo');
        const description = document.getElementById('description');
        const charCounter = document.querySelector('.char-counter');

        // Update character counter
        if (description && charCounter) {
            description.addEventListener('input', () => {
                charCounter.textContent = `${description.value.length}/1000 characters`;
            });
            // Initialize counter
            charCounter.textContent = `${description.value.length}/1000 characters`;
        }

        // Image upload click handler
        imageUploadArea.addEventListener('click', () => fileInput.click());

        // Drag and drop handlers
        imageUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            imageUploadArea.style.borderColor = 'var(--primary-accent)';
            imageUploadArea.style.backgroundColor = 'rgba(31, 122, 188, 0.1)';
        });

        imageUploadArea.addEventListener('dragleave', () => {
            imageUploadArea.style.borderColor = 'rgba(31, 122, 188, 0.3)';
            imageUploadArea.style.backgroundColor = 'rgba(225, 229, 242, 0.1)';
        });

        imageUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            imageUploadArea.style.borderColor = 'rgba(31, 122, 188, 0.3)';
            imageUploadArea.style.backgroundColor = 'rgba(225, 229, 242, 0.1)';

            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                updatePreview();
            }
        });

        // File input change handler
        fileInput.addEventListener('change', updatePreview);

        function updatePreview() {
            if (fileInput.files && fileInput.files[0]) {
                const file = fileInput.files[0];
                
                // Validate file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must be less than 2MB');
                    fileInput.value = '';
                    return;
                }

                const reader = new FileReader();

                reader.onload = (e) => {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    fileInfo.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                };

                reader.readAsDataURL(file);
            }
        }

        // Success popup functionality
        function hidePopup() {
            const popup = document.getElementById('successPopup');
            popup.classList.remove('show');
        }
        <?php if ($success): ?>
            setTimeout(hidePopup, 5000);
            // Reset form on success
            document.getElementById('productForm').reset();
            imagePreview.style.display = 'none';
            fileInfo.textContent = '';
        <?php endif; ?>
    </script>
</body>
</html>