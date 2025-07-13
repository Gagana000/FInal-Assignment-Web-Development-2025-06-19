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

// Get cart items
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.discount_percent, p.image_url, p.stock 
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
$tax = $subtotal * 0.05;
$total = $subtotal + $tax;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $pdo->beginTransaction();

    // Create order
    $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, total_amount, status) 
            VALUES (?, ?, 'pending')
        ");
    $stmt->execute([$userId, $total]);
    $orderId = $pdo->lastInsertId();

    // Add order items
    $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");

    foreach ($cartItems as $item) {
      $price = $item['discount_percent'] > 0
        ? $item['price'] * (1 - ($item['discount_percent'] / 100))
        : $item['price'];

      $stmt->execute([
        $orderId,
        $item['product_id'],
        $item['quantity'],
        $price
      ]);

      // Update product stock
      $updateStmt = $pdo->prepare("
                UPDATE products SET stock = stock - ? WHERE id = ?
            ");
      $updateStmt->execute([$item['quantity'], $item['product_id']]);
    }

    // Clear cart
    $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$userId]);

    $pdo->commit();

    // Redirect to success page
    header("Location: order_success.php?order_id=$orderId");
    exit();
  } catch (PDOException $e) {
    $pdo->rollBack();
    $error = "Error processing your order. Please try again.";
  }
}

// Get user details
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - NSBM Premium</title>
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="shortcut icon" href="../assets/images/logo_brand.png" type="image/x-icon">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
    rel="stylesheet">
  <style>
    /* Checkout Container */
    .checkout-container {
      max-width: 1200px;
      margin: 100px auto 50px;
      padding: 0 20px;
      color: white;
    }

    /* Checkout Header */
    .checkout-header {
      margin-bottom: 2rem;
    }

    .checkout-header h1 {
      font-size: 2.5rem;
      margin-bottom: 0.5rem;
      background: linear-gradient(to right, #fff, #bfdbf7);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }

    /* Checkout Steps */
    .checkout-steps {
      display: flex;
      justify-content: space-between;
      margin-bottom: 3rem;
      position: relative;
    }

    .checkout-steps::before {
      content: '';
      position: absolute;
      top: 15px;
      left: 0;
      right: 0;
      height: 2px;
      background: rgba(255, 255, 255, 0.1);
      z-index: 1;
    }

    .step {
      display: flex;
      flex-direction: column;
      align-items: center;
      position: relative;
      z-index: 2;
    }

    .step-number {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 10px;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .step.active .step-number {
      background: var(--primary-accent);
      color: white;
    }

    .step.completed .step-number {
      background: #4bb543;
      color: white;
    }

    .step-label {
      font-size: 0.9rem;
      color: rgba(255, 255, 255, 0.7);
    }

    .step.active .step-label {
      color: var(--primary-accent);
      font-weight: 500;
    }

    .step.completed .step-label {
      color: #4bb543;
    }

    /* Checkout Content */
    .checkout-content {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 3rem;
    }

    /* Billing Form */
    .billing-form {
      background: rgba(2, 43, 58, 0.7);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(31, 122, 188, 0.3);
      border-radius: 12px;
      padding: 2rem;
    }

    .section-title {
      font-size: 1.5rem;
      margin-top: 0;
      margin-bottom: 1.5rem;
      position: relative;
      padding-bottom: 10px;
    }

    .section-title::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 60px;
      height: 3px;
      background: var(--primary-accent);
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: var(--primary-light);
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 12px 15px;
      background: rgba(225, 229, 242, 0.1);
      border: 1px solid rgba(225, 229, 242, 0.2);
      border-radius: 8px;
      color: white;
      font-size: 1rem;
      transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: var(--primary-accent);
      box-shadow: 0 0 0 3px rgba(31, 122, 188, 0.3);
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
    }

    /* Payment Methods */
    .payment-methods {
      margin-top: 2rem;
    }

    .payment-method {
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 15px;
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 8px;
      margin-bottom: 15px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .payment-method:hover {
      border-color: var(--primary-accent);
    }

    .payment-method.active {
      border-color: var(--primary-accent);
      background: rgba(31, 122, 188, 0.1);
    }

    .payment-method input {
      margin: 0;
    }

    .payment-method-label {
      flex: 1;
    }

    .payment-method-icon {
      font-size: 1.5rem;
    }

    /* Order Summary */
    .order-summary {
      background: rgba(2, 43, 58, 0.7);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(31, 122, 188, 0.3);
      border-radius: 12px;
      padding: 2rem;
      height: fit-content;
    }

    .order-items {
      margin-bottom: 2rem;
    }

    .order-item {
      display: flex;
      gap: 15px;
      padding: 15px 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .order-item:last-child {
      border-bottom: none;
    }

    .order-item-image {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 6px;
    }

    .order-item-details {
      flex: 1;
    }

    .order-item-name {
      margin: 0 0 5px 0;
      font-size: 1rem;
    }

    .order-item-price {
      color: var(--primary-accent);
      font-weight: 600;
    }

    .order-item-quantity {
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.9rem;
    }

    /* Order Totals */
    .order-totals {
      margin-top: 2rem;
    }

    .order-total-row {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .order-total-row:last-child {
      border-bottom: none;
      font-weight: 600;
      font-size: 1.1rem;
    }

    /* Place Order Button */
    .place-order-btn {
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
      font-size: 1.1rem;
    }

    .place-order-btn:hover {
      background: #1a6ba5;
      transform: translateY(-2px);
    }

    /* Error Message */
    .error-message {
      color: #ff6b6b;
      background: rgba(255, 107, 107, 0.1);
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      border-left: 4px solid #ff6b6b;
    }

    /* Responsive Design */
    @media (max-width: 992px) {
      .checkout-content {
        gap: 2rem;
      }
    }

    @media (max-width: 768px) {
      .checkout-content {
        grid-template-columns: 1fr;
      }

      .checkout-steps {
        margin-bottom: 2rem;
      }

      .form-row {
        grid-template-columns: 1fr;
        gap: 1rem;
      }
    }

    @media (max-width: 480px) {
      .checkout-steps {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
      }

      .checkout-steps::before {
        display: none;
      }

      .step {
        flex-direction: row;
        gap: 15px;
        align-items: center;
      }

      .step-number {
        margin-bottom: 0;
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
              <li><a href="./includes/products.php?search=&category=T-Shirts&min_price=&max_price=">T-Shirts</a>
              </li>
              <li><a href="./includes/products.php?search=&category=Hoodies&min_price=&max_price=">Hoodies</a>
              </li>
              <li><a href="./includes/products.php?search=&category=Other&min_price=&max_price=">Accessories</a>
              </li>
              <li><a href="./includes/products.php?search=&category=Electronics&min_price=&max_price=">Electronics</a>
              </li>
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

  <main class="checkout-container">
    <div class="checkout-header">
      <h1>Checkout</h1>
      <p>Complete your purchase</p>
    </div>

    <div class="checkout-steps">
      <div class="step completed">
        <div class="step-number">1</div>
        <div class="step-label">Cart</div>
      </div>
      <div class="step active">
        <div class="step-number">2</div>
        <div class="step-label">Details</div>
      </div>
      <div class="step">
        <div class="step-number">3</div>
        <div class="step-label">Payment</div>
      </div>
      <div class="step">
        <div class="step-number">4</div>
        <div class="step-label">Complete</div>
      </div>
    </div>

    <?php if (empty($cartItems)): ?>
      <div class="empty-cart-message">
        <p>Your cart is empty. <a href="products.php">Continue shopping</a></p>
      </div>
    <?php else: ?>
      <?php if (isset($error)): ?>
        <div class="error-message">
          <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="checkout-content">
        <div class="billing-form">
          <h2 class="section-title">Billing Details</h2>

          <div class="form-row">
            <div class="form-group">
              <label for="first_name">First Name</label>
              <input type="text" id="first_name" name="first_name"
                value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label for="last_name">Last Name</label>
              <input type="text" id="last_name" name="last_name" required>
            </div>
          </div>

          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
          </div>

          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" required>
          </div>

          <div class="form-group">
            <label for="address">Street Address</label>
            <input type="text" id="address" name="address" required>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="city">City</label>
              <input type="text" id="city" name="city" required>
            </div>
            <div class="form-group">
              <label for="zip">Postal Code</label>
              <input type="text" id="zip" name="zip" required>
            </div>
          </div>

          <div class="form-group">
            <label for="country">Country</label>
            <select id="country" name="country" required>
              <option value="">Select Country</option>
              <option value="Sri Lanka" selected>Sri Lanka</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <h2 class="section-title">Payment Method</h2>
          <div class="payment-methods">
            <label class="payment-method active">
              <input type="radio" name="payment_method" value="credit_card" checked>
              <div class="payment-method-label">
                <h4>Credit/Debit Card</h4>
                <p>Pay with Visa, MasterCard, or American Express</p>
              </div>
              <div class="payment-method-icon">
                <i class="far fa-credit-card"></i>
              </div>
            </label>

            <label class="payment-method">
              <input type="radio" name="payment_method" value="paypal">
              <div class="payment-method-label">
                <h4>PayPal</h4>
                <p>Pay securely with your PayPal account</p>
              </div>
              <div class="payment-method-icon">
                <i class="fab fa-paypal"></i>
              </div>
            </label>

            <label class="payment-method">
              <input type="radio" name="payment_method" value="cash">
              <div class="payment-method-label">
                <h4>Cash on Delivery</h4>
                <p>Pay when you receive your order</p>
              </div>
              <div class="payment-method-icon">
                <i class="fas fa-money-bill-wave"></i>
              </div>
            </label>
          </div>
        </div>

        <div class="order-summary">
          <h2 class="section-title">Order Summary</h2>
          <div class="order-items">
            <?php foreach ($cartItems as $item): ?>
              <div class="order-item">
                <?php
                $imagePath = '/Final_Assignment_Web_Development_2025-06-19/assets/uploads/' . htmlspecialchars($item['image_url'] ?: 'default-product.jpg');
                $defaultImage = '../assets/uploads/default-product.jpg';
                ?>
                <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="order-item-image"
                  onerror="this.src='<?= $defaultImage ?>'">
                <div class="order-item-details">
                  <h4 class="order-item-name"><?= htmlspecialchars($item['name']) ?></h4>
                  <div class="order-item-price">
                    <?php if ($item['discount_percent'] > 0): ?>
                      Rs. <?= number_format($item['price'] * (1 - ($item['discount_percent'] / 100)), 2) ?>
                    <?php else: ?>
                      Rs. <?= number_format($item['price'], 2) ?>
                    <?php endif; ?>
                  </div>
                  <div class="order-item-quantity">Quantity: <?= $item['quantity'] ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="order-totals">
            <div class="order-total-row">
              <span>Subtotal</span>
              <span>Rs. <?= number_format($subtotal, 2) ?></span>
            </div>
            <div class="order-total-row">
              <span>Tax (5%)</span>
              <span>Rs. <?= number_format($tax, 2) ?></span>
            </div>
            <div class="order-total-row">
              <span>Shipping</span>
              <span>Free</span>
            </div>
            <div class="order-total-row">
              <span>Total</span>
              <span>Rs. <?= number_format($total, 2) ?></span>
            </div>
          </div>

          <button type="submit" class="place-order-btn">Place Order</button>
        </div>
      </form>
    <?php endif; ?>
  </main>

  <script>
    // Payment method selection
    document.querySelectorAll('.payment-method').forEach(method => {
      method.addEventListener('click', function () {
        document.querySelectorAll('.payment-method').forEach(m => {
          m.classList.remove('active');
        });
        this.classList.add('active');
        this.querySelector('input').checked = true;
      });
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function (e) {
      const requiredFields = this.querySelectorAll('[required]');
      let isValid = true;

      requiredFields.forEach(field => {
        if (!field.value.trim()) {
          field.style.borderColor = '#ff6b6b';
          isValid = false;
        } else {
          field.style.borderColor = '';
        }
      });

      if (!isValid) {
        e.preventDefault();
        alert('Please fill in all required fields.');
      }
    });
  </script>
</body>

</html>