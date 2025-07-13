<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth_functions.php';
session_start();

$pdo = getDBConnection();

// Get product ID from URL
$productId = $_GET['id'] ?? null;

if (!$productId) {
    header("Location: products.php");
    exit();
}

// Fetch product details
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: products.php");
    exit();
}

// Calculate discounted price if applicable
$originalPrice = $product['price'];
$discountPrice = $product['discount_percent'] > 0
    ? $originalPrice * (1 - ($product['discount_percent'] / 100))
    : null;

// Fetch related products (same category)
$relatedStmt = $pdo->prepare("SELECT * FROM products WHERE category = ? AND id != ? LIMIT 4");
$relatedStmt->execute([$product['category'], $productId]);
$relatedProducts = $relatedStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - NSBM Premium</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="shortcut icon" href="../assets/images/logo_brand.png" type="image/x-icon">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">
    <style>
        /* Product View Container */
        .product-view-container {
            max-width: 1200px;
            margin: 100px auto 50px;
            padding: 0 20px;
            color: white;
        }

        /* Breadcrumb Navigation */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .breadcrumb a {
            color: var(--primary-light);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .breadcrumb a:hover {
            color: var(--primary-accent);
            text-decoration: underline;
        }

        .breadcrumb .separator {
            opacity: 0.5;
        }

        /* Main Product Content */
        .product-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-top: 2rem;
        }

        /* Image Gallery */
        .product-gallery {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .main-image {
            width: 100%;
            height: 500px;
            object-fit: contain;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .thumbnail-container {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .thumbnail:hover {
            border-color: var(--primary-accent);
            transform: scale(1.05);
        }

        /* Product Details */
        .product-details {
            display: flex;
            flex-direction: column;
        }

        .product-title {
            font-size: 2.2rem;
            margin: 0 0 1rem 0;
            font-weight: 600;
        }

        .product-category {
            color: var(--primary-accent);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1.5rem;
            display: inline-block;
        }

        .product-price {
            margin: 1.5rem 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .current-price {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
        }

        .original-price {
            text-decoration: line-through;
            color: rgba(255, 255, 255, 0.5);
            font-size: 1.3rem;
        }

        .discount-badge {
            background: var(--primary-accent);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .product-stock {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 1.5rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .in-stock {
            color: #4bb543;
        }

        .low-stock {
            color: #ffc107;
        }

        .out-of-stock {
            color: #dc3545;
        }

        .product-description {
            line-height: 1.7;
            margin-bottom: 2rem;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Product Actions */
        .product-actions {
            display: flex;
            gap: 15px;
            margin-top: 2rem;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-right: 15px;
        }

        .quantity-btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .quantity-input {
            width: 60px;
            padding: 10px;
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: white;
        }

        .add-to-cart-btn {
            flex: 1;
            background: var(--primary-accent);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .add-to-cart-btn:hover {
            background: #1a6ba5;
            transform: translateY(-2px);
        }

        .wishlist-btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .wishlist-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #dc3545;
        }

        /* Related Products */
        .related-products {
            margin-top: 5rem;
            padding-top: 3rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .section-title {
            font-size: 1.8rem;
            margin-bottom: 2rem;
            position: relative;
            display: inline-block;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--primary-accent);
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .product-content {
                gap: 2rem;
            }
        }

        @media (max-width: 768px) {
            .product-content {
                grid-template-columns: 1fr;
            }

            .main-image {
                height: 400px;
            }
        }

        @media (max-width: 480px) {
            .product-actions {
                flex-direction: column;
            }

            .quantity-control {
                margin-right: 0;
                margin-bottom: 10px;
                justify-content: center;
            }

            .add-to-cart-btn {
                width: 100%;
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
                            <li><a
                                    href="./includes/products.php?search=&category=T-Shirts&min_price=&max_price=">T-Shirts</a>
                            </li>
                            <li><a
                                    href="./includes/products.php?search=&category=Hoodies&min_price=&max_price=">Hoodies</a>
                            </li>
                            <li><a
                                    href="./includes/products.php?search=&category=Other&min_price=&max_price=">Accessories</a>
                            </li>
                            <li><a
                                    href="./includes/products.php?search=&category=Electronics&min_price=&max_price=">Electronics</a>
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

    <main class="product-view-container">
        <div class="breadcrumb">
            <a href="../index.php">Home</a>
            <span class="separator">/</span>
            <a href="products.php">Store</a>
            <span class="separator">/</span>
            <a
                href="products.php?category=<?= urlencode($product['category']) ?>"><?= htmlspecialchars($product['category']) ?></a>
            <span class="separator">/</span>
            <span><?= htmlspecialchars($product['name']) ?></span>
        </div>

        <div class="product-content">
            <div class="product-gallery">
                <?php
                $imagePath = '/Final_Assignment_Web_Development_2025-06-19/assets/uploads/' . htmlspecialchars($product['image_url'] ?: 'default-product.jpg');
                $defaultImage = '../assets/uploads/default-product.jpg';
                ?>
                <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="main-image"
                    id="mainImage" onerror="this.src='<?= $defaultImage ?>'">

                <div class="thumbnail-container">
                    <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="thumbnail"
                        onerror="this.src='<?= $defaultImage ?>'" onclick="changeMainImage(this.src)">
                </div>
            </div>

            <div class="product-details">
                <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
                <span class="product-category"><?= htmlspecialchars($product['category']) ?></span>

                <div class="product-price">
                    <?php if ($discountPrice): ?>
                        <span class="original-price">Rs. <?= number_format($originalPrice, 2) ?></span>
                        <span class="current-price">Rs. <?= number_format($discountPrice, 2) ?></span>
                        <span class="discount-badge">-<?= $product['discount_percent'] ?>% OFF</span>
                    <?php else: ?>
                        <span class="current-price">Rs. <?= number_format($originalPrice, 2) ?></span>
                    <?php endif; ?>
                </div>

                <div class="product-stock">
                    <i class="fas fa-box"></i>
                    <?php if ($product['stock'] > 10): ?>
                        <span class="in-stock">In Stock (<?= $product['stock'] ?> available)</span>
                    <?php elseif ($product['stock'] > 0): ?>
                        <span class="low-stock">Low Stock (Only <?= $product['stock'] ?> left)</span>
                    <?php else: ?>
                        <span class="out-of-stock">Out of Stock</span>
                    <?php endif; ?>
                </div>

                <div class="product-description">
                    <h3 style="color: black;">Description</h3>
                    <p style="color: black;">
                        <?= nl2br(htmlspecialchars($product['description'] ?: 'No description available.')) ?></p>
                </div>

                <div class="product-actions">
                    <div class="quantity-control">
                        <button class="quantity-btn" onclick="updateQuantity(-1)"><i class="fas fa-minus"></i></button>
                        <input type="number" class="quantity-input" id="quantity" value="1" min="1"
                            max="<?= $product['stock'] ?>">
                        <button class="quantity-btn" onclick="updateQuantity(1)"><i class="fas fa-plus"></i></button>
                    </div>
                    <button class="add-to-cart-btn" id="addToCartBtn" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                </div>
            </div>
        </div>

        <?php if (!empty($relatedProducts)): ?>
            <section class="related-products">
                <h2 class="section-title">You May Also Like</h2>
                <div class="related-grid">
                    <?php foreach ($relatedProducts as $related): ?>
                        <div class="product-card">
                            <?php if ($related['discount_percent'] > 0): ?>
                                <div class="product-badge">
                                    <span class="discount-badge">-<?= $related['discount_percent'] ?>%</span>
                                </div>
                            <?php endif; ?>
                            <div class="product-image-container">
                                <?php
                                $relatedImagePath = '/Final_Assignment_Web_Development_2025-06-19/assets/uploads/' . htmlspecialchars($related['image_url'] ?: 'default-product.jpg');
                                ?>
                                <img src="<?= $relatedImagePath ?>" alt="<?= htmlspecialchars($related['name']) ?>"
                                    class="product-image" onerror="this.src='<?= $defaultImage ?>'">
                            </div>
                            <div class="product-info">
                                <h3><?= htmlspecialchars($related['name']) ?></h3>
                                <span class="product-category"><?= htmlspecialchars($related['category']) ?></span>
                                <div class="product-price">
                                    <?php if ($related['discount_percent'] > 0): ?>
                                        <?php
                                        $relatedOriginalPrice = $related['price'];
                                        $relatedDiscountedPrice = $relatedOriginalPrice * (1 - ($related['discount_percent'] / 100));
                                        ?>
                                        <span class="original-price">Rs. <?= number_format($relatedOriginalPrice, 2) ?></span>
                                        <span class="discounted-price">Rs. <?= number_format($relatedDiscountedPrice, 2) ?></span>
                                    <?php else: ?>
                                        <span>Rs. <?= number_format($related['price'], 2) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-actions">
                                    <a href="view_product_user.php?id=<?= $related['id'] ?>" class="view-btn">View</a>
                                    <button class="add-to-cart" data-product-id="<?= $related['id'] ?>" <?= $related['stock'] <= 0 ? 'disabled' : '' ?>>
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <script>
        // Change main image when thumbnail is clicked
        function changeMainImage(src) {
            document.getElementById('mainImage').src = src;
        }

        // Update quantity
        function updateQuantity(change) {
            const quantityInput = document.getElementById('quantity');
            let newQuantity = parseInt(quantityInput.value) + change;
            if (newQuantity < 1) newQuantity = 1;
            if (newQuantity > <?= $product['stock'] ?>) newQuantity = <?= $product['stock'] ?>;
            quantityInput.value = newQuantity;
        }

        // Add to cart functionality
        document.getElementById('addToCartBtn').addEventListener('click', function () {
            const productId = <?= $product['id'] ?>;
            const quantity = document.getElementById('quantity').value;

            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=${quantity}&action=add`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Product added to cart!');
                    } else {
                        if (data.message.includes('login')) {
                            window.location.href = 'admin/login.php';
                        } else {
                            showToast(data.message || 'Error adding to cart', 'error');
                        }
                    }
                });
        });

        // Add to cart for related products
        document.querySelectorAll('.related-grid .add-to-cart').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');

                fetch('cart.php', {
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
                                window.location.href = 'admin/login.php';
                            } else {
                                showToast(data.message || 'Error adding to cart', 'error');
                            }
                        }
                    });
            });
        });

        // Toast message function
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