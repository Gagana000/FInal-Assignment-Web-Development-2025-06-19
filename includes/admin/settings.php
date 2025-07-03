<?php
session_start();
require_once __DIR__ . '/../auth_functions.php';
require_login();
require_admin();

// Database connection
require_once __DIR__ . '/../database.php';

// Generate CSRF token
$csrf_token = generate_csrf_token();

// Default settings
$default_settings = [
  'store_name' => 'NSBM Premium',
  'store_email' => 'admin@nsbmpremium.lk',
  'store_currency' => 'LKR',
  'maintenance_mode' => '0',
  'products_per_page' => '12',
  'shipping_fee' => '250.00'
];

// Load current settings from database
try {
  $stmt = $pdo->query("SELECT * FROM settings");
  $db_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

  // Merge with defaults (database values take precedence)
  $settings = array_merge($default_settings, $db_settings);
} catch (PDOException $e) {
  // If settings table doesn't exist yet, use defaults
  $settings = $default_settings;
  $error = "Settings table not found, using defaults. Create the table to save settings.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Verify CSRF token
  if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
    die("Invalid CSRF token");
  }

  // Prepare settings to save
  $new_settings = [
    'store_name' => filter_input(INPUT_POST, 'store_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
    'store_email' => filter_input(INPUT_POST, 'store_email', FILTER_SANITIZE_EMAIL),
    'store_currency' => filter_input(INPUT_POST, 'store_currency', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
    'maintenance_mode' => isset($_POST['maintenance_mode']) ? '1' : '0',
    'products_per_page' => filter_input(INPUT_POST, 'products_per_page', FILTER_VALIDATE_INT, [
      'options' => ['min_range' => 1, 'max_range' => 100]
    ]),
    'shipping_fee' => filter_input(INPUT_POST, 'shipping_fee', FILTER_VALIDATE_FLOAT, [
      'options' => ['min_range' => 0]
    ])
  ];

  // Validate inputs
  $errors = [];
  if (!$new_settings['store_name']) {
    $errors['store_name'] = "Store name is required";
  }
  if (!$new_settings['store_email'] || !filter_var($new_settings['store_email'], FILTER_VALIDATE_EMAIL)) {
    $errors['store_email'] = "Valid email is required";
  }
  if ($new_settings['products_per_page'] === false) {
    $errors['products_per_page'] = "Must be between 1-100";
  }
  if ($new_settings['shipping_fee'] === false) {
    $errors['shipping_fee'] = "Must be a positive number";
  }

  if (empty($errors)) {
    try {
      $pdo->beginTransaction();

      // Ensure settings table exists
      $pdo->exec("
                CREATE TABLE IF NOT EXISTS settings (
                    setting_key VARCHAR(50) PRIMARY KEY,
                    setting_value TEXT NOT NULL
                )
            ");

      // Delete all existing settings
      $pdo->exec("DELETE FROM settings");

      // Insert new settings
      $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
      foreach ($new_settings as $key => $value) {
        $stmt->execute([$key, $value]);
      }

      $pdo->commit();
      $_SESSION['success'] = "Settings saved successfully";
      header("Location: settings.php");
      exit();
    } catch (PDOException $e) {
      $pdo->rollBack();
      error_log("Database error: " . $e->getMessage());
      $_SESSION['error'] = "Failed to save settings";
    }
  } else {
    // Use validated values even if some failed
    $settings = array_merge($settings, $new_settings);
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>System Settings - NSBM Premium</title>
  <link rel="stylesheet" href="../../style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        <li><a href="product.php"><i class="fas fa-tshirt"></i> Products</a></li>
        <li><a href="orders.php"><i class="fas fa-receipt"></i> Orders</a></li>
        <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
        <li><a href="settings.php" class="active"><i class="fas fa-gear"></i> Settings</a></li>
      </ul>
    </aside>

    <main class="admin-main">
      <div class="admin-header">
        <h1 class="admin-title">
          <i class="fas fa-gear"></i> System Settings
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
        <form class="settings-form" method="POST">
          <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

          <div class="form-section">
            <h3><i class="fas fa-store"></i> Store Information</h3>

            <div class="form-group <?= isset($errors['store_name']) ? 'has-error' : '' ?>">
              <label for="store_name">Store Name</label>
              <input type="text" id="store_name" name="store_name"
                value="<?= htmlspecialchars($settings['store_name']) ?>" required>
              <?php if (isset($errors['store_name'])): ?>
                <span class="error-message"><?= htmlspecialchars($errors['store_name']) ?></span>
              <?php endif; ?>
            </div>

            <div class="form-group <?= isset($errors['store_email']) ? 'has-error' : '' ?>">
              <label for="store_email">Store Email</label>
              <input type="email" id="store_email" name="store_email"
                value="<?= htmlspecialchars($settings['store_email']) ?>" required>
              <?php if (isset($errors['store_email'])): ?>
                <span class="error-message"><?= htmlspecialchars($errors['store_email']) ?></span>
              <?php endif; ?>
            </div>

            <div class="form-group">
              <label for="store_currency">Currency</label>
              <select id="store_currency" name="store_currency">
                <option value="LKR" <?= $settings['store_currency'] === 'LKR' ? 'selected' : '' ?>>Sri Lankan Rupee (LKR)
                </option>
                <option value="USD" <?= $settings['store_currency'] === 'USD' ? 'selected' : '' ?>>US Dollar (USD)</option>
                <option value="EUR" <?= $settings['store_currency'] === 'EUR' ? 'selected' : '' ?>>Euro (EUR)</option>
              </select>
            </div>
          </div>

          <div class="form-section">
            <h3><i class="fas fa-shopping-cart"></i> Product Settings</h3>

            <div class="form-group <?= isset($errors['products_per_page']) ? 'has-error' : '' ?>">
              <label for="products_per_page">Products Per Page</label>
              <input type="number" id="products_per_page" name="products_per_page"
                value="<?= htmlspecialchars($settings['products_per_page']) ?>" min="1" max="100" required>
              <?php if (isset($errors['products_per_page'])): ?>
                <span class="error-message"><?= htmlspecialchars($errors['products_per_page']) ?></span>
              <?php endif; ?>
            </div>
          </div>

          <div class="form-section">
            <h3><i class="fas fa-truck"></i> Shipping Settings</h3>

            <div class="form-group <?= isset($errors['shipping_fee']) ? 'has-error' : '' ?>">
              <label for="shipping_fee">Shipping Fee</label>
              <div class="currency-input">
                <span class="currency-symbol">
                  <?= htmlspecialchars($settings['store_currency'] === 'LKR' ? 'Rs.' : '$') ?>
                </span>
                <input type="number" id="shipping_fee" name="shipping_fee"
                  value="<?= htmlspecialchars($settings['shipping_fee']) ?>" min="0" step="0.01" required>
              </div>
              <?php if (isset($errors['shipping_fee'])): ?>
                <span class="error-message"><?= htmlspecialchars($errors['shipping_fee']) ?></span>
              <?php endif; ?>
            </div>
          </div>

          <div class="form-section">
            <h3><i class="fas fa-tools"></i> System Mode</h3>

            <div class="form-group">
              <div class="checkbox-group">
                <input type="checkbox" id="maintenance_mode" name="maintenance_mode"
                  <?= $settings['maintenance_mode'] === '1' ? 'checked' : '' ?>>
                <label for="maintenance_mode">Enable Maintenance Mode</label>
              </div>
              <small>When enabled, only administrators can access the store.</small>
            </div>
          </div>

          <div class="form-actions">
            <button type="reset" class="btn-admin btn-outline">
              <i class="fas fa-undo"></i> Reset Changes
            </button>
            <button type="submit" class="btn-admin btn-primary">
              <i class="fas fa-save"></i> Save Settings
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>

  <script>
    // Update currency symbol when currency changes
    document.getElementById('store_currency').addEventListener('change', function () {
      const currency = this.value;
      const symbol = currency === 'LKR' ? 'Rs.' : '$';
      document.querySelector('.currency-symbol').textContent = symbol;
    });
  </script>
</body>

</html>