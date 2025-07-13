<?php
require_once __DIR__ . '/database.php';
session_start();

// Get all distinct categories
$pdo = getDBConnection();
$categoryStmt = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category <> '' ORDER BY category ASC");
$categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);

// Get filtered products
$categoryFilter = $_GET['category'] ?? null;
$searchQuery = $_GET['search'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';

// Base query
$query = "SELECT * FROM products WHERE stock > 0";
$params = [];

// Append filters if they exist
if ($categoryFilter) {
  $query .= " AND category = ?";
  $params[] = $categoryFilter;
}
if ($searchQuery) {
  $query .= " AND (name LIKE ? OR description LIKE ?)";
  $params[] = "%$searchQuery%";
  $params[] = "%$searchQuery%";
}
if ($minPrice !== '') {
  $query .= " AND price >= ?";
  $params[] = $minPrice;
}
if ($maxPrice !== '') {
  $query .= " AND price <= ?";
  $params[] = $maxPrice;
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
  <title>Store - NSBM Premium</title>
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="shortcut icon" href="../assets/images/logo_brand.png" type="image/x-icon">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
    rel="stylesheet">
  <style>
    /* Store container */
    .store-container {
      max-width: 1400px;
      margin: 100px auto 50px;
      padding: 0 20px;
      color: white;
    }

    /* Filters section */
    .products-filters {
      background: rgba(2, 43, 58, 0.7);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(31, 122, 188, 0.3);
      padding: 1.5rem;
      border-radius: 12px;
      margin-bottom: 3rem;
    }

    .filter-form {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
      align-items: flex-end;
    }

    .filter-group label {
      display: block;
      margin-bottom: 8px;
      color: var(--primary-light);
      font-weight: 500;
      font-size: 0.9rem;
    }

    .filter-group input,
    .filter-group select {
      width: 100%;
      padding: 12px 15px;
      background: rgba(225, 229, 242, 0.1);
      border: 1px solid rgba(225, 229, 242, 0.2);
      border-radius: 8px;
      color: var(--pure-white);
      font-size: 1rem;
      transition: all 0.3s ease;
    }

    .filter-group input:focus,
    .filter-group select:focus {
      outline: none;
      border-color: var(--primary-accent);
      box-shadow: 0 0 0 3px rgba(31, 122, 188, 0.3);
    }

    .price-range {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .filter-actions {
      display: flex;
      justify-content: flex-end;
      gap: 15px;
      margin-top: 1rem;
    }

    /* Product Grid */
    .products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 2rem;
    }

    /* Product Card */
    .product-card {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border-radius: 12px;
      overflow: hidden;
      transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
      border: 1px solid rgba(255, 255, 255, 0.1);
      display: flex;
      flex-direction: column;
    }

    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(31, 122, 188, 0.3);
    }

    /* Product Image */
    .product-image-container {
      height: 200px;
      position: relative;
      overflow: hidden;
    }

    .product-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s ease;
    }

    .product-card:hover .product-image {
      transform: scale(1.05);
    }

    /* Product Badges */
    .product-badge {
      position: absolute;
      top: 15px;
      right: 15px;
      display: flex;
      flex-direction: column;
      gap: 8px;
      z-index: 2;
    }

    .discount-badge {
      background: var(--primary-accent);
      color: white;
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
    }

    /* Product Info */
    .product-info {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
    }

    .product-title {
      font-size: 1.3rem !important;
      font-weight: 600;
      color: white;
      text-align: center;
    }

    .product-category {
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.6rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .product-price {
      font-weight: 600;
      font-size: 1.5rem;
      color: white;
      margin-bottom: 15px;
    }

    .original-price {
      text-decoration: line-through;
      color: rgba(255, 255, 255, 0.5);
      font-size: 0.9rem;
      margin-right: 8px;
    }

    .discounted-price {
      color: var(--primary-accent);
    }

    /* Product Actions */
    .product-actions {
      width: 100%;
      margin-top: auto;
      display: flex;
      gap: 10px;
    }

    .view-btn {
      flex: 1;
      background: var(--primary-accent);
      color: white;
      padding: 10px;
      border-radius: 6px;
      text-decoration: none;
      text-align: center;
      transition: all 0.3s ease;
    }

    .view-btn:hover {
      background: #1a6ba5;
    }

    .add-to-cart {
      background: rgba(255, 255, 255, 0.1);
      border: none;
      color: white;
      width: 40px;
      height: 40px;
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .add-to-cart:hover {
      background: var(--primary-accent);
    }

    /* No products message */
    .no-products {
      grid-column: 1 / -1;
      text-align: center;
      padding: 40px;
      color: rgba(255, 255, 255, 0.7);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .store-container {
        margin: 80px auto 30px;
        padding: 0 15px;
      }

      .filter-form {
        grid-template-columns: 1fr;
      }

      .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      }
    }

    @media (max-width: 480px) {
      .products-grid {
        grid-template-columns: 1fr;
      }
    }

    /* Toast Message */
    .toast-message {
      position: fixed;
      bottom: 20px;
      right: 20px;
      padding: 15px 25px;
      border-radius: 6px;
      color: white;
      display: flex;
      align-items: center;
      gap: 10px;
      z-index: 1000;
      animation: slideIn 0.3s forwards;
    }

    .toast-message.success {
      background: var(--primary-accent);
    }

    .toast-message.error {
      background: #dc3545;
    }

    .close-toast {
      background: none;
      border: none;
      color: white;
      margin-left: 15px;
      cursor: pointer;
      font-size: 1.2rem;
    }

    @keyframes slideIn {
      from {
        transform: translateX(100%);
        opacity: 0;
      }

      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
  </style>
</head>

<body>
  <nav>
    <div class="left-section">
      <h1 class="Logo">NSBM Premium.</h1>
    </div>
    <div class="mobile-toggle"><i class="fa-solid fa-bars"></i></div>
    <div class="middle-section">
      <div class="nav-buttons">
        <ul>
          <li><a href="../index.php"><i class="fa-solid fa-house"></i>Home</a></li>
          <li><a href="products.php"><i class="fa-solid fa-store"></i>Store</a></li>
          <li><a href="#">Categories<i class="fa-solid fa-caret-down"></i></a>
            <ul class="drop-down-menu">
              <?php foreach ($categories as $cat): ?>
                <li><a href="products.php?category=<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></a></li>
              <?php endforeach; ?>
              <li class="devider"></li>
              <li><a href="products.php" class="view-all">View All</a></li>
            </ul>
          </li>
          <li><a href="about.php"><i class="fa-solid fa-users"></i>About</a></li>
        </ul>
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
          <button type="button" onclick="window.location.href='admin/dashboard.php'">
            Dashboard <i class="fa-solid fa-gauge"></i>
          </button>
        <?php else: ?>
          <button type="button" onclick="window.location.href='profile.php'">
            <i class="fa-solid fa-user"></i>
          </button>
          <button type="button" onclick="window.location.href='cart.php'">
            <i class="fa-solid fa-cart-shopping"></i>
          </button>
        <?php endif; ?>
        <button type="button" onclick="window.location.href='admin/logout.php'">
          <i class="fa-solid fa-right-from-bracket"></i>
        </button>
      <?php endif; ?>
    </div>
  </nav>

  <main class="store-container">
    <section class="products-filters">
      <form method="GET" class="filter-form">
        <div class="filter-group">
          <label for="search">Search Products</label>
          <input type="text" id="search" name="search" placeholder="Product name or description"
            value="<?= htmlspecialchars($searchQuery) ?>">
        </div>
        <div class="filter-group">
          <label for="category">Category</label>
          <select id="category" name="category">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat) ?>" <?= ($categoryFilter === $cat) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="filter-group">
          <label for="price">Price Range (Rs.)</label>
          <div class="price-range">
            <input type="number" name="min_price" placeholder="Min" min="0" value="<?= htmlspecialchars($minPrice) ?>">
            <span>-</span>
            <input type="number" name="max_price" placeholder="Max" min="0" value="<?= htmlspecialchars($maxPrice) ?>">
          </div>
        </div>
        <div class="filter-actions">
          <a href="products.php" class="btn btn-outline">Reset</a>
          <button type="submit" class="btn btn-primary">Apply Filters</button>
        </div>
      </form>
    </section>

    <section class="products-grid">
      <?php if (empty($products)): ?>
        <div class="no-products">
          <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
          <p>No products found matching your criteria.</p>
        </div>
      <?php else: ?>
        <?php foreach ($products as $product): ?>
          <div class="product-card">
            <?php if ($product['discount_percent'] > 0): ?>
              <div class="product-badge">
                <span class="discount-badge">-<?= $product['discount_percent'] ?>%</span>
              </div>
            <?php endif; ?>

            <div class="product-image-container">
              <?php
              $imagePath = '/Final_Assignment_Web_Development_2025-06-19/assets/uploads/' . htmlspecialchars($product['image_url'] ?: 'default-product.jpg');
              $defaultImage = '../assets/uploads/default-product.jpg';
              ?>

              <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image"
                onerror="this.src='<?= $defaultImage ?>'">
            </div>

            <div class="product-info">
              <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
              <span class="product-category"><?= htmlspecialchars($product['category']) ?></span>

              <div class="product-price">
                <?php if ($product['discount_percent'] > 0): ?>
                  <?php
                  $originalPrice = $product['price'];
                  $discountedPrice = $originalPrice * (1 - ($product['discount_percent'] / 100));
                  ?>
                  <span class="original-price">Rs. <?= number_format($originalPrice, 2) ?></span>
                  <span class="discounted-price">Rs. <?= number_format($discountedPrice, 2) ?></span>
                <?php else: ?>
                  <span>Rs. <?= number_format($product['price'], 2) ?></span>
                <?php endif; ?>
              </div>

              <div class="product-actions">
                <a href="view_product_user.php?id=<?= $product['id'] ?>" class="view-btn">View</a>
                <button class="add-to-cart" data-product-id="<?= $product['id'] ?>">
                  <i class="fas fa-shopping-cart"></i>
                </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </main>

  <script src="../main.js"></script>
  <script>
    // Enhanced add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(button => {
      button.addEventListener('click', function (e) {
        e.preventDefault();
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
              if (data.message.includes('login')) {
                window.location.href = '../includes/admin/login.php';
              } else {
                showToast(data.message || 'Error adding to cart', 'error');
              }
            }
          });
      });
    });

    function showToast(message, type = 'success') {
      const toast = document.createElement('div');
      toast.className = `toast-message ${type}`;
      toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
                <button class="close-toast">&times;</button>
            `;

      document.body.appendChild(toast);

      toast.querySelector('.close-toast').addEventListener('click', () => {
        toast.remove();
      });

      setTimeout(() => {
        if (document.body.contains(toast)) {
          toast.remove();
        }
      }, 3000);
    }
  </script>
</body>

</html>