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

// Fetch existing product data
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

  // Handle file upload and removal
  $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Final_Assignment_Web_Development_2025-06-19/assets/uploads/';

  // Handle image removal
  if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
    if ($product['image_url'] && $product['image_url'] !== 'default-product.jpg') {
      $oldImagePath = $uploadDir . $product['image_url'];
      if (file_exists($oldImagePath)) {
        unlink($oldImagePath);
      }
    }
    $product['image_url'] = 'default-product.jpg';
  }

  // Handle new image upload
  if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
      $errors['image'] = 'File upload error: ' . $_FILES['image']['error'];
    } else {
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
        $extension = $allowedTypes[$mimeType];
        $fileName = 'product_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
          // Delete old image if it's not the default
          if ($product['image_url'] !== 'default-product.jpg') {
            $oldImagePath = $uploadDir . $product['image_url'];
            if (file_exists($oldImagePath)) {
              unlink($oldImagePath);
            }
          }
          $product['image_url'] = $fileName;
        } else {
          $errors['image'] = 'Failed to save uploaded file';
        }
      }
    }
  }

  // Update database if no errors
  if (empty($errors)) {
    try {
      $pdo->beginTransaction();

      $stmt = $pdo->prepare("
                UPDATE products 
                SET name = ?, price = ?, stock = ?, 
                    description = ?, category = ?, image_url = ?
                WHERE id = ?
            ");
      $stmt->execute([
        $product['name'],
        $product['price'],
        $product['stock'],
        $product['description'],
        $product['category'],
        $product['image_url'],
        $product_id
      ]);

      $pdo->commit();
      $success = true;

      // Refresh product data
      $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
      $stmt->execute([$product_id]);
      $product = $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
      $pdo->rollBack();
      error_log("Database error: " . $e->getMessage());
      $errors['database'] = 'Failed to update product. Please try again.';

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
  <title>Edit Product - NSBM Premium</title>
  <link rel="stylesheet" href="../../style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="../../main.js" defer></script>
  <style>
    /* Image Management Styles */
    .current-image-container {
      margin-bottom: 20px;
    }

    .current-image-wrapper {
      position: relative;
      max-width: 300px;
      margin-bottom: 15px;
      transition: opacity 0.3s ease;
    }

    .current-image {
      max-width: 100%;
      max-height: 200px;
      border-radius: 8px;
      border: 1px solid rgba(0, 0, 0, 0.1);
      display: block;
    }

    .image-actions {
      margin-top: 8px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .btn-image-action {
      background: rgba(220, 53, 69, 0.1);
      color: #dc3545;
      border: 1px solid rgba(220, 53, 69, 0.2);
      border-radius: 4px;
      padding: 5px 10px;
      font-size: 0.85rem;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .btn-image-action:hover {
      background: rgba(220, 53, 69, 0.2);
    }

    .remove-new-image {
      background: rgba(108, 117, 125, 0.1);
      color: #6c757d;
      border-color: rgba(108, 117, 125, 0.2);
    }

    .remove-new-image:hover {
      background: rgba(108, 117, 125, 0.2);
    }

    .no-image-message {
      color: #6c757d;
      padding: 15px;
      text-align: center;
      background: rgba(0, 0, 0, 0.03);
      border-radius: 8px;
      margin-bottom: 15px;
    }

    .no-image-message i {
      font-size: 2rem;
      display: block;
      margin-bottom: 5px;
      opacity: 0.5;
    }
  </style>
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
        <span>Product updated successfully!</span>
        <div class="close-btn" onclick="hidePopup()">
          <i class="fas fa-times"></i>
        </div>
      </div>

      <div class="admin-header">
        <h1 class="admin-title">
          <i class="fas fa-edit"></i> Edit Product: <?= htmlspecialchars($product['name']) ?>
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
          <input type="hidden" id="remove_image" name="remove_image" value="0">

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
                  <option value="<?= htmlspecialchars($cat) ?>" <?= $product['category'] === $cat ? 'selected' : '' ?>>
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
            <div class="char-counter"><?= strlen($product['description']) ?>/1000 characters</div>
          </div>

          <div class="form-group <?= isset($errors['image']) ? 'has-error' : '' ?>">
            <label>Product Image</label>

            <!-- Current Image Display -->
            <div class="current-image-container">
              <?php if (!empty($product['image_url']) && $product['image_url'] !== 'default-product.jpg'): ?>
                <div class="current-image-wrapper">
                  <img
                    src="/Final_Assignment_Web_Development_2025-06-19/assets/uploads/<?= htmlspecialchars($product['image_url']) ?>"
                    alt="Current product image" class="current-image"
                    onerror="this.src='../../assets/uploads/default-product.jpg'">
                  <div class="image-actions">
                    <button type="button" class="btn-image-action remove-image" data-product-id="<?= $product_id ?>">
                      <i class="fas fa-trash"></i> Remove
                    </button>
                    <span class="file-info">Current: <?= htmlspecialchars($product['image_url']) ?></span>
                  </div>
                </div>
              <?php else: ?>
                <div class="no-image-message">
                  <i class="fas fa-image"></i>
                  <span>No custom image uploaded (using default)</span>
                </div>
              <?php endif; ?>
            </div>

            <!-- Image Upload Area -->
            <div class="image-upload" id="imageUploadArea">
              <i class="fas fa-cloud-upload-alt"></i>
              <p>Drag & drop a new image here or click to browse</p>
              <p class="text-muted">(Recommended: 800x800px, JPG/PNG/WEBP, max 2MB)</p>
              <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp"
                style="display: none;">
            </div>

            <!-- New Image Preview -->
            <div class="image-preview-container" id="newImagePreviewContainer" style="display: none;">
              <img id="imagePreview" class="image-preview">
              <div class="file-info" id="fileInfo"></div>
            </div>

            <?php if (isset($errors['image'])): ?>
              <span class="error-message"><?= htmlspecialchars($errors['image']) ?></span>
            <?php endif; ?>
          </div>

          <div class="form-actions">
            <button type="reset" class="btn-admin btn-outline">
              <i class="fas fa-eraser"></i> Reset Changes
            </button>
            <button type="submit" class="btn-admin btn-primary">
              <i class="fas fa-save"></i> Update Product
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const imageUploadArea = document.getElementById('imageUploadArea');
      const fileInput = document.getElementById('image');
      const newImageContainer = document.getElementById('newImagePreviewContainer');
      const fileInfo = document.getElementById('fileInfo');
      const description = document.getElementById('description');
      const charCounter = document.querySelector('.char-counter');
      const removeImageInput = document.getElementById('remove_image');

      // Update character counter
      if (description && charCounter) {
        description.addEventListener('input', () => {
          charCounter.textContent = `${description.value.length}/1000 characters`;
        });
      }

      // Image upload click handler
      imageUploadArea.addEventListener('click', () => fileInput.click());

      // Drag and drop handlers
      ['dragover', 'dragleave'].forEach(event => {
        imageUploadArea.addEventListener(event, (e) => {
          e.preventDefault();
          imageUploadArea.style.borderColor = event === 'dragover' ?
            'var(--primary-accent)' : 'rgba(31, 122, 188, 0.3)';
          imageUploadArea.style.backgroundColor = event === 'dragover' ?
            'rgba(31, 122, 188, 0.1)' : 'rgba(225, 229, 242, 0.1)';
        });
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

      // Remove image button handler
      const removeButtons = document.querySelectorAll('.remove-image');
      removeButtons.forEach(button => {
        button.addEventListener('click', function () {
          if (confirm('Are you sure you want to remove this image? The default product image will be used instead.')) {
            removeImageInput.value = '1';
            this.closest('.current-image-wrapper').style.opacity = '0.5';
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-check"></i> Will be removed';
          }
        });
      });

      function updatePreview() {
        if (fileInput.files && fileInput.files[0]) {
          const file = fileInput.files[0];

          if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            fileInput.value = '';
            return;
          }

          const reader = new FileReader();
          reader.onload = (e) => {
            newImageContainer.style.display = 'block';
            document.getElementById('imagePreview').src = e.target.result;
            fileInfo.innerHTML = `New image: ${file.name} (${(file.size / 1024).toFixed(1)} KB) 
                            <button id="removeNewImageBtn" class="btn-image-action remove-new-image">
                                <i class="fas fa-times"></i> Cancel upload
                            </button>`;

            // Add remove button for new image
            document.getElementById('removeNewImageBtn').addEventListener('click', function (e) {
              e.preventDefault();
              fileInput.value = '';
              newImageContainer.style.display = 'none';
            });
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
      <?php endif; ?>
    });
  </script>
</body>

</html>