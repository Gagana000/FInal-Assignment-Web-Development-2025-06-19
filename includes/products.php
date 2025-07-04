<?php
require_once __DIR__ . '/database.php';
session_start();

// Get all distinct categories
$pdo = getDBConnection();
$categoryStmt = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL");
$categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);

// Get filtered products
$categoryFilter = $_GET['category'] ?? null;
$searchQuery = $_GET['search'] ?? '';
$minPrice = $_GET['min_price'] ?? 0;
$maxPrice = $_GET['max_price'] ?? 9999;

$query = "SELECT * FROM products WHERE stock > 0 AND price BETWEEN ? AND ?";
$params = [$minPrice, $maxPrice];

if ($categoryFilter) {
  $query .= " AND category = ?";
  $params[] = $categoryFilter;
}

if ($searchQuery) {
  $query .= " AND (name LIKE ? OR description LIKE ?)";
  $params[] = "%$searchQuery%";
  $params[] = "%$searchQuery%";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Products - NSBM Premium</title>
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>

<body>
  <!----------------------------------------- NAVIGATION BAR ----------------------------------------->
  <nav>
    <div class="left-section">
      <h1 class="Logo">NSBM Premium.</h1>
    </div>

    <div class="mobile-toggle">
      <i class="fa-solid fa-bars"></i>
    </div>

    <div class="middle-section">
      <div class="nav-buttons">
        <ul>
          <li><a href="../index.php"><i class="fa-solid fa-house"></i>Home</a></li>
          <li><a href="products.php"><i class="fa-solid fa-store"></i>Store</a></li>
          <li><a href="#">Categories<i class="fa-solid fa-caret-down"></i></a>
            <ul class="drop-down-menu">
              <li><a href="#">T-Shirts</a></li>
              <li><a href="#">Hoodies</a></li>
              <li><a href="#">Accessories</a></li>
              <li><a href="#">Electronics</a></li>
              <li class="devider"></li>
              <li><a href="products.php" class="view-all">View All</a></li>
            </ul>
          </li>
          <li><a href="about.php"><i class="fa-solid fa-users"></i>About</a></li>
        </ul>
      </div>

      <div class="nav-search">
        <input type="text" name="search" id="search" placeholder="Search">
      </div>
    </div>
    <div class="right-section">
      <?php if (!isset($_SESSION['user_id'])): ?>
      <!-- Logged OUT state -->
        <button type="button" onclick="window.location.href='admin/login.php'">
          Login <i class="fa-solid fa-user"></i>
        </button>
        <button type="button" onclick="window.location.href='register.php'">
          Register <i class="fa-solid fa-user-plus"></i>
        </button>
        <button type="button" onclick="window.location.href='cart.php'">
          <i class="fa-solid fa-cart-shopping"></i>
        </button>
      <?php else: ?>
        <!-- Logged IN state -->
        <?php if ($_SESSION['role'] === 'admin'): ?>
          <!-- Admin View -->
          <button type="button" onclick="window.location.href='admin/dashboard.php'">
            Dashboard <i class="fa-solid fa-gauge"></i>
          </button>
        <?php else: ?>
          <!-- Regular User View -->
          <button type="button" onclick="window.location.href='profile.php'">
            <i class="fa-solid fa-user"></i>
          </button>
          <button type="button" onclick="window.location.href='cart.php'">
            <i class="fa-solid fa-cart-shopping"></i>
          </button>
        <?php endif; ?>

        <!-- Logout Button (for all logged-in users) -->
        <button type="button" onclick="window.location.href='admin/logout.php'">
          <i class="fa-solid fa-right-from-bracket"></i>
        </button>
      <?php endif; ?>
    </div>
  </nav>

  <div class="products-container">
    <h1>Our Products</h1>
    <!-- Filters Section -->
    <div class="products-filters">
      <form method="GET" class="filter-form">
        <div class="filter-group">
          <label for="category">Category:</label>
          <select id="category" name="category" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat) ?>" <?= $categoryFilter === $cat ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filter-group">
          <label for="search">Search:</label>
          <input type="text" id="search" name="search" placeholder="Product name or description"
            value="<?= htmlspecialchars($searchQuery) ?>">
        </div>

        <div class="filter-group">
          <label>Price Range:</label>
          <div class="price-range">
            <input type="number" name="min_price" placeholder="Min" value="<?= htmlspecialchars($minPrice) ?>">
            <span>to</span>
            <input type="number" name="max_price" placeholder="Max" value="<?= htmlspecialchars($maxPrice) ?>">
          </div>
        </div>

        <button type="submit" class="filter-btn">Apply Filters</button>
        <a href="products.php" class="reset-btn">Reset</a>
      </form>
    </div>

    <!-- Products Grid -->
    <div class="products-grid">
      <?php if (empty($products)): ?>
        <p class="no-products">No products found matching your criteria.</p>
      <?php else: ?>
        <?php foreach ($products as $product): ?>
          <div class="product-card">
            <div class="product-image">
              <img src="../assets/uploads/<?= htmlspecialchars($product['image_url'] ?: 'default-product.jpg') ?>"
                alt="<?= htmlspecialchars($product['name']) ?>">
            </div>
            <div class="product-info">
              <h3><?= htmlspecialchars($product['name']) ?></h3>
              <p class="product-category"><?= htmlspecialchars($product['category']) ?></p>
              <p class="product-price">Rs. <?= number_format($product['price'], 2) ?></p>
              <div class="product-actions">
                <a href="view_product_user.php?id=<?= $product['id'] ?>" class="view-btn">View Details</a>
                <button class="add-to-cart" data-product-id="<?= $product['id'] ?>">
                  <i class="fas fa-shopping-cart"></i>
                </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <script src="../main.js"></script>
  <script>
    // Add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(button => {
      button.addEventListener('click', function () {
        const productId = this.getAttribute('data-product-id');

        fetch('../includes/cart.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `product_id=${productId}&action=add`
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showToast('Product added to cart!');
            } else {
              showToast(data.message || 'Error adding to cart', 'error');
            }
          });
      });
    });

    function showToast(message, type = 'success') {
      const toast = document.createElement('div');
      toast.className = `toast-message ${type}`;
      toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}"></i>
                ${message}
                <button class="close-toast">&times;</button>
            `;

      document.body.appendChild(toast);

      setTimeout(() => {
        toast.remove();
      }, 3000);

      toast.querySelector('.close-toast').addEventListener('click', () => {
        toast.remove();
      });
    }
  </script>
</body>

</html>