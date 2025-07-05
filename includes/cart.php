<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth_functions.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$pdo = getDBConnection();
$userId = $_SESSION['user_id'];

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $productId = $_POST['product_id'] ?? null;
  $action = $_POST['action'] ?? null;
  $quantity = $_POST['quantity'] ?? 1;

  if ($productId && $action) {
    try {
      if ($action === 'remove') {
        // Remove item from cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
      } elseif ($action === 'add') {
        // Check if product already exists in cart
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $existingItem = $stmt->fetch();

        if ($existingItem) {
          // Update quantity if item exists
          $newQuantity = $existingItem['quantity'] + 1;
          $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
          $stmt->execute([$newQuantity, $userId, $productId]);
        } else {
          // Add new item to cart
          $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
          $stmt->execute([$userId, $productId]);
        }

        // Return success response for AJAX requests
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
          header('Content-Type: application/json');
          echo json_encode(['success' => true, 'message' => 'Product added to cart']);
          exit();
        }
      } else {
        // Update quantity for existing item
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$quantity, $userId, $productId]);
      }
    } catch (PDOException $e) {
      // Handle error for AJAX requests
      if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error updating cart']);
        exit();
      }
    }
  }

  // Redirect for non-AJAX requests
  if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    header("Location: cart.php");
    exit();
  }
  return;
}

// Get cart items with product details
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.discount_percent, p.image_url, p.stock, p.category 
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
  $price = $item['discount_percent'] > 0
    ? $item['price'] * (1 - ($item['discount_percent'] / 100))
    : $item['price'];
  $subtotal += $price * $item['quantity'];
}
$tax = $subtotal * 0.05; // Example 5% tax
$total = $subtotal + $tax;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Cart - NSBM Premium</title>
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
    rel="stylesheet">
  <style>
    .cart-container {
      max-width: 1200px;
      margin: 100px auto 50px;
      padding: 0 20px;
      color: white;
    }

    .cart-header {
      margin-bottom: 2rem;
    }

    .cart-header h1 {
      font-size: 2.5rem;
      margin-bottom: 0.5rem;
      background: linear-gradient(to right, #fff, #bfdbf7);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }

    .cart-content {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 2rem;
    }

    .cart-items {
      background: rgba(2, 43, 58, 0.7);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(31, 122, 188, 0.3);
      border-radius: 12px;
      padding: 1.5rem;
    }

    .cart-item {
      display: grid;
      grid-template-columns: 100px 1fr auto;
      gap: 1.5rem;
      padding: 1.5rem 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .cart-item:last-child {
      border-bottom: none;
    }

    .cart-item-image {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 8px;
    }

    .cart-item-details h3 {
      margin: 0 0 0.5rem 0;
      font-size: 1.2rem;
    }

    .cart-item-details p {
      margin: 0.3rem 0;
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.9rem;
    }

    .price {
      font-weight: 600;
      color: var(--primary-accent);
    }

    .original-price {
      text-decoration: line-through;
      color: rgba(255, 255, 255, 0.5);
      font-size: 0.9rem;
      margin-right: 8px;
    }

    .quantity-control {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .quantity-input {
      width: 60px;
      padding: 8px;
      text-align: center;
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 6px;
      color: white;
    }

    .quantity-btn {
      background: rgba(255, 255, 255, 0.1);
      border: none;
      color: white;
      width: 30px;
      height: 30px;
      border-radius: 6px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .quantity-btn:hover {
      background: var(--primary-accent);
    }

    .remove-btn {
      background: transparent;
      border: none;
      color: #ff6b6b;
      cursor: pointer;
      font-size: 0.9rem;
      margin-top: 10px;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .remove-btn:hover {
      color: #ff3b3b;
    }

    .cart-summary {
      background: rgba(2, 43, 58, 0.7);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(31, 122, 188, 0.3);
      border-radius: 12px;
      padding: 1.5rem;
      height: fit-content;
    }

    .summary-title {
      font-size: 1.5rem;
      margin-top: 0;
      margin-bottom: 1.5rem;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 1rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .summary-total {
      font-weight: 600;
      font-size: 1.2rem;
      margin-top: 1.5rem;
    }

    .checkout-btn {
      width: 100%;
      background: var(--primary-accent);
      color: white;
      border: none;
      padding: 15px;
      border-radius: 8px;
      font-weight: 600;
      margin-top: 2rem;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .checkout-btn:hover {
      background: #1a6ba5;
      transform: translateY(-2px);
    }

    .empty-cart {
      text-align: center;
      padding: 3rem;
      color: rgba(255, 255, 255, 0.7);
    }

    .empty-cart i {
      font-size: 3rem;
      margin-bottom: 1rem;
      opacity: 0.5;
    }

    .continue-shopping {
      display: inline-block;
      margin-top: 1.5rem;
      color: var(--primary-accent);
      text-decoration: none;
      font-weight: 500;
    }

    .continue-shopping:hover {
      text-decoration: underline;
    }

    @media (max-width: 768px) {
      .cart-content {
        grid-template-columns: 1fr;
      }

      .cart-item {
        grid-template-columns: 80px 1fr;
        grid-template-rows: auto auto;
        gap: 1rem;
      }

      .cart-item-actions {
        grid-column: span 2;
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

    /* ===== Footer Section ===== */
    footer {
      background: #01141b;
      color: white;
      padding: 4rem 1.25rem 2rem;
      position: relative;
    }

    .footer-grid {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
    }

    .footer-logo {
      font-size: 1.5rem;
      font-weight: 600;
      color: white;
      text-decoration: none;
      margin-bottom: 1rem;
      display: inline-block;
    }

    .social-links {
      display: flex;
      gap: 15px;
      margin-top: 1.5rem;
    }

    .social-link {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      color: white;
      transition: all 0.3s ease;
    }

    .social-link:hover {
      background: var(--primary-accent);
      transform: translateY(-3px);
    }

    .footer-heading {
      font-size: 1.2rem;
      margin-bottom: 1.5rem;
      position: relative;
      padding-bottom: 10px;
    }

    .footer-heading::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 40px;
      height: 2px;
      background: var(--primary-accent);
    }

    .footer-links {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .footer-links li {
      margin-bottom: 12px;
    }

    .footer-links a {
      color: rgba(255, 255, 255, 0.7);
      text-decoration: none;
      transition: all 0.2s ease;
      display: inline-block;
    }

    .footer-links a:hover {
      color: var(--primary-accent);
      transform: translateX(5px);
    }

    .copyright {
      text-align: center;
      margin-top: 4rem;
      padding-top: 2rem;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      color: rgba(255, 255, 255, 0.5);
      font-size: 0.9rem;
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
              <li><a href="products.php?category=T-Shirts">T-Shirts</a></li>
              <li><a href="products.php?category=Hoodies">Hoodies</a></li>
              <li><a href="products.php?category=Accessories">Accessories</a></li>
              <li><a href="products.php?category=Electronics">Electronics</a></li>
              <li class="devider"></li>
              <li><a href="products.php">View All</a></li>
            </ul>
          </li>
          <li><a href="about.php"><i class="fa-solid fa-users"></i>About</a></li>
        </ul>
      </div>
    </div>
    <div class="right-section">
      <?php if (!isset($_SESSION['user_id'])): ?>
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

  <main class="cart-container">
    <div class="cart-header">
      <h1>Your Shopping Cart</h1>
      <p>Review and manage your items</p>
    </div>

    <div class="cart-content">
      <div class="cart-items">
        <?php if (empty($cartItems)): ?>
          <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h3>Your cart is empty</h3>
            <p>Looks like you haven't added any items yet</p>
            <a href="products.php" class="continue-shopping">Continue Shopping</a>
          </div>
        <?php else: ?>
          <?php foreach ($cartItems as $item): ?>
            <div class="cart-item">
              <img src="/Final_Assignment_Web_Development_2025-06-19/assets/uploads/<?= htmlspecialchars($item['image_url'] ?: 'default-product.jpg') ?>"
                alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item-image">

              <div class="cart-item-details">
                <h3><?= htmlspecialchars($item['name']) ?></h3>
                <p><?= htmlspecialchars($item['category']) ?></p>

                <?php if ($item['discount_percent'] > 0): ?>
                  <p class="price">
                    <span class="original-price">Rs. <?= number_format($item['price'], 2) ?></span>
                    Rs. <?= number_format($item['price'] * (1 - ($item['discount_percent'] / 100)), 2) ?>
                  </p>
                <?php else: ?>
                  <p class="price">Rs. <?= number_format($item['price'], 2) ?></p>
                <?php endif; ?>

                <button class="remove-btn" onclick="removeItem(<?= $item['product_id'] ?>)">
                  <i class="fas fa-trash"></i> Remove
                </button>
              </div>

              <div class="cart-item-actions">
                <form method="post" class="quantity-control">
                  <button type="button" class="quantity-btn"
                    onclick="updateQuantity(<?= $item['product_id'] ?>, <?= $item['quantity'] - 1 ?>)">
                    <i class="fas fa-minus"></i>
                  </button>
                  <input type="number" name="quantity" class="quantity-input" value="<?= $item['quantity'] ?>" min="1"
                    max="<?= $item['stock'] ?>" onchange="updateQuantity(<?= $item['product_id'] ?>, this.value)">
                  <button type="button" class="quantity-btn"
                    onclick="updateQuantity(<?= $item['product_id'] ?>, <?= $item['quantity'] + 1 ?>)">
                    <i class="fas fa-plus"></i>
                  </button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <?php if (!empty($cartItems)): ?>
        <div class="cart-summary">
          <h3 class="summary-title">Order Summary</h3>

          <div class="summary-row">
            <span>Subtotal</span>
            <span>Rs. <?= number_format($subtotal, 2) ?></span>
          </div>

          <div class="summary-row">
            <span>Estimated Tax</span>
            <span>Rs. <?= number_format($tax, 2) ?></span>
          </div>

          <div class="summary-row summary-total">
            <span>Total</span>
            <span>Rs. <?= number_format($total, 2) ?></span>
          </div>

          <button class="checkout-btn" onclick="window.location.href='checkout.php'">
            Proceed to Checkout
          </button>

          <a href="products.php" class="continue-shopping">
            <i class="fas fa-arrow-left"></i> Continue Shopping
          </a>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <footer>
    <div class="footer-grid">
      <div>
        <a href="#" class="footer-logo">NSBM Premium.</a>
        <p style="opacity: 0.7; line-height: 1.6;">Your one-stop shop for premium NSBM merchandise and campus
          essentials.</p>
        <div class="social-links">
          <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
          <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
          <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
        </div>
      </div>

      <div>
        <h3 class="footer-heading">Shop</h3>
        <ul class="footer-links">
          <li><a href="#">All Products</a></li>
          <li><a href="#">New Arrivals</a></li>
          <li><a href="#">Best Sellers</a></li>
          <li><a href="#">Special Offers</a></li>
        </ul>
      </div>

      <div>
        <h3 class="footer-heading">Help</h3>
        <ul class="footer-links">
          <li><a href="#">FAQs</a></li>
          <li><a href="#">Shipping</a></li>
          <li><a href="#">Returns</a></li>
          <li><a href="#">Size Guide</a></li>
        </ul>
      </div>

      <div>
        <h3 class="footer-heading">Contact</h3>
        <ul class="footer-links">
          <li><a href="#"><i class="fas fa-map-marker-alt" style="margin-right: 8px;"></i> NSBM Green University</a>
          </li>
          <li><a href="#"><i class="fas fa-phone" style="margin-right: 8px;"></i> +94 112 345 678</a></li>
          <li><a href="#"><i class="fas fa-envelope" style="margin-right: 8px;"></i> hello@nsbmpremium.lk</a></li>
        </ul>
      </div>
    </div>

    <div class="copyright">
      <p>&copy; 2025 NSBM Premium. All rights reserved.</p>
    </div>
  </footer>

  <script>
    function updateQuantity(productId, newQuantity) {
      if (newQuantity < 1) return;

      const formData = new FormData();
      formData.append('product_id', productId);
      formData.append('quantity', newQuantity);
      formData.append('action', 'update');

      fetch('cart.php', {
        method: 'POST',
        body: formData
      }).then(response => {
        if (response.ok) {
          location.reload();
        }
      });
    }

    function removeItem(productId) {
      if (confirm('Are you sure you want to remove this item from your cart?')) {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('action', 'remove');

        fetch('cart.php', {
          method: 'POST',
          body: formData
        }).then(response => {
          if (response.ok) {
            location.reload();
          }
        });
      }
    }

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