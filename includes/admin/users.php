<?php
session_start();
require_once __DIR__ . '/../auth_functions.php';
require_login();
require_admin();

// Database connection
require_once __DIR__ . '/../database.php';

// Generate CSRF token
$csrf_token = generate_csrf_token();

// Handle user role updates and deletions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Verify CSRF token
  if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
    die("Invalid CSRF token");
  }

  if (isset($_POST['update_role'])) {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $new_role = filter_input(INPUT_POST, 'new_role', FILTER_SANITIZE_STRING);

    try {
      $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
      $stmt->execute([$new_role, $user_id]);

      $_SESSION['success'] = "User role updated successfully";
      header("Location: users.php");
      exit();
    } catch (PDOException $e) {
      error_log("Database error: " . $e->getMessage());
      $_SESSION['error'] = "Failed to update user role";
    }
  } elseif (isset($_POST['delete_user'])) {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    try {
      $pdo->beginTransaction();

      // First delete dependent records (cart items, orders, etc.)
      $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);

      // Then delete the user
      $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
      $stmt->execute([$user_id]);

      $pdo->commit();
      $_SESSION['success'] = "User deleted successfully";
      header("Location: users.php");
      exit();
    } catch (PDOException $e) {
      $pdo->rollBack();
      error_log("Database error: " . $e->getMessage());
      $_SESSION['error'] = "Failed to delete user";
    }
  }
}

// Fetch all users
try {
  $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
  $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  error_log("Database error: " . $e->getMessage());
  $users = [];
  $error = "Failed to load users. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Management - NSBM Premium</title>
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
        <li><a href="orders.php"><i class="fas fa-receipt"></i> Orders</a></li>
        <li><a href="users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
        <li><a href="settings.php"><i class="fas fa-gear"></i> Settings</a></li>
      </ul>
    </aside>

    <main class="admin-main">
      <div class="admin-header">
        <h1 class="admin-title">
          <i class="fas fa-users"></i> User Management
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
        <div class="search-bar">
          <input type="text" id="userSearch" placeholder="Search users...">
          <button class="btn-admin btn-primary" onclick="searchUsers()">
            <i class="fas fa-search"></i> Search
          </button>
        </div>

        <?php if (empty($users)): ?>
          <div class="no-products">
            <i class="fas fa-user-slash"></i>
            <h3>No Users Found</h3>
            <p>There are no users registered in the system.</p>
          </div>
        <?php else: ?>
          <table class="user-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Joined</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $user): ?>
                <tr>
                  <td>#<?= htmlspecialchars($user['id']) ?></td>
                  <td><?= htmlspecialchars($user['username']) ?></td>
                  <td><?= htmlspecialchars($user['email']) ?></td>
                  <td>
                    <span class="role-<?= htmlspecialchars($user['role']) ?>">
                      <?= ucfirst(htmlspecialchars($user['role'])) ?>
                    </span>
                  </td>
                  <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                  <td>
                    <div class="action-buttons">
                      <form method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

                        <select name="new_role" class="btn-admin btn-sm">
                          <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                          <option value="customer" <?= $user['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                        </select>

                        <button type="submit" name="update_role" class="btn-admin btn-primary btn-sm">
                          <i class="fas fa-save"></i> Update
                        </button>
                      </form>

                      <?php if ($user['id'] != $_SESSION['user_id']): ?>
                        <form method="POST" style="display: inline;">
                          <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                          <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                          <button type="submit" name="delete_user" class="btn-admin btn-danger btn-sm"
                            onclick="return confirm('Are you sure you want to delete this user?')">
                            <i class="fas fa-trash"></i> Delete
                          </button>
                        </form>
                      <?php endif; ?>
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

  <script>
    function searchUsers() {
      const searchTerm = document.getElementById('userSearch').value.toLowerCase();
      const rows = document.querySelectorAll('.user-table tbody tr');

      rows.forEach(row => {
        const username = row.cells[1].textContent.toLowerCase();
        const email = row.cells[2].textContent.toLowerCase();

        if (username.includes(searchTerm) || email.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }

    // Trigger search when pressing Enter in search field
    document.getElementById('userSearch').addEventListener('keyup', function (event) {
      if (event.key === 'Enter') {
        searchUsers();
      }
    });
  </script>
</body>

</html>