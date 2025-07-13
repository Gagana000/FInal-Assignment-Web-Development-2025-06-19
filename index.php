<?php
require_once 'includes/database.php';
$pdo = getDBConnection();

session_start();
$stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - NSBM Premium</title>
    <!-- CSS Link -->
    <link rel="stylesheet" href="./style.css">
    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">
    <!-- JavaScript Link -->
    <script src="./main.js" defer></script>
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/cb8b70f796.js" crossorigin="anonymous"></script>
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <!-- Shortcut icon -->
    <link rel="shortcut icon" href="assets/images/logo_brand.png" type="image/x-icon">
    <style>
        /* Featured Products Section */
        .featured-products {
            padding: 5rem 1.25rem;
            background: radial-gradient(circle at center, #01141b 0%, #000 100%);
            color: #fff;
        }

        .featured-products .container {
            max-width: 75rem;
            margin: 0 auto;
        }

        .featured-products h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            background: linear-gradient(to right, #fff, #bfdbf7);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.5px;
        }

        .featured-products .subtitle {
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            max-width: 43.75rem;
            margin: 0 auto 3rem;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(17.5rem, 1fr));
            gap: 1.875rem;
        }

        .product-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(0.625rem);
            border-radius: 0.75rem;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        /* Product Image Section */
        .product-image-container {
            height: 15.625rem;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        /* Product Badges */
        .product-badge {
            position: absolute;
            top: 0.9375rem;
            right: 0.9375rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            z-index: 2;
        }

        .discount-badge {
            background: var(--primary-accent);
            color: white;
            padding: 0.3125rem 0.625rem;
            border-radius: 1.25rem;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .stock-badge {
            background: #ffc107;
            color: #212529;
            padding: 0.3125rem 0.625rem;
            border-radius: 1.25rem;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Product Info Section */
        .product-info {
            padding: 1.25rem;
            color: #fff;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        /* Row 1: Product Name */
        .product-name-row {
            margin-bottom: 0.5rem;
            min-height: 2.8rem;
        }

        .product-info h3 {
            font-size: 1.4rem;
            margin: 0;
            font-weight: 500;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Row 2: Category */
        .product-category-row {
            margin-bottom: 0.75rem;
        }

        .product-category {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
        }

        /* Row 3: Pricing */
        .product-pricing-row {
            margin: 0.75rem 0;
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .price-container {
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        .original-price {
            text-decoration: line-through;
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
        }

        .discounted-price {
            color: var(--primary-accent);
            font-weight: 600;
            font-size: 1.2rem;
        }

        .current-price {
            color: #fff;
            font-weight: 600;
            font-size: 1.2rem;
        }

        /* Row 4: Actions */
        .product-actions-row {
            margin-top: auto;
            padding-top: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .view-btn {
            background: var(--primary-accent);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: 1px solid transparent;
            flex-grow: 1;
            text-align: center;
            margin-right: 0.75rem;
        }

        .view-btn:hover {
            background: transparent;
            border-color: var(--primary-accent);
        }

        .add-to-cart {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: #fff;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .add-to-cart:hover {
            background: var(--primary-accent);
            transform: scale(1.1);
        }

        /* View All Button */
        .view-all-container {
            text-align: center;
            margin-top: 2.5rem;
        }

        .view-all-btn {
            display: inline-block;
            padding: 0.75rem 1.875rem;
            background: var(--primary-accent);
            color: white;
            border-radius: 1.875rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .view-all-btn:hover {
            background: transparent;
            border-color: var(--primary-accent);
            transform: translateY(-0.1875rem);
        }

        /* Responsive Design */
        @media (max-width: 48rem) {
            .featured-products {
                padding: 3rem 1.25rem;
            }

            .featured-products h2 {
                font-size: 2rem;
            }

            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(15.625rem, 1fr));
                gap: 1.25rem;
            }

            .product-info h3 {
                -webkit-line-clamp: 2;
                min-height: auto;
            }
        }

        /* Animation Classes */
        .animate-pop-in {
            animation: popIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
        }

        @keyframes popIn {
            0% {
                opacity: 0;
                transform: scale(0.5);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Categories Showcase Section */
        .categories-showcase {
            padding: 5rem 1.25rem;
            background: radial-gradient(circle at center, #01141b 0%, #000 100%);
            color: white;
        }

        .categories-showcase .container {
            max-width: 75rem;
            margin: 0 auto;
        }

        .categories-showcase h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            background: linear-gradient(to right, #fff, #bfdbf7);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.5px;
        }

        .categories-showcase .subtitle {
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            max-width: 43.75rem;
            margin: 0 auto 3rem;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
        }

        .category-card {
            text-decoration: none;
            color: white;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            height: 300px;
            display: flex;
            flex-direction: column;
        }

        .category-image {
            position: relative;
            height: 100%;
            overflow: hidden;
            border-radius: 12px;
        }

        .category-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to top, rgba(2, 43, 58, 0.9) 0%, transparent 50%);
            z-index: 1;
        }

        .category-card h3 {
            position: absolute;
            bottom: 20px;
            left: 20px;
            font-size: 1.5rem;
            font-weight: 600;
            z-index: 2;
            margin: 0;
            transform: translateY(10px);
            transition: transform 0.3s ease;
        }

        /* Hover Effects */
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(31, 122, 188, 0.3);
        }

        .category-card:hover img {
            transform: scale(1.05);
        }

        .category-card:hover h3 {
            transform: translateY(0);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .category-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 1.5rem;
            }

            .category-card {
                height: 250px;
            }
        }

        @media (max-width: 480px) {
            .category-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Testimonials Section */
        .testimonials {
            padding: 5rem 1.25rem;
            background: radial-gradient(circle at center, #01141b 0%, #000 100%);
            color: white;
        }

        .testimonials .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .testimonials h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            background: linear-gradient(to right, #fff, #bfdbf7);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.5px;
        }

        .testimonials .subtitle {
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            max-width: 700px;
            margin: 0 auto 3rem;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .testimonial-slider {
            display: flex;
            gap: 2rem;
            overflow-x: auto;
            padding: 1rem 0;
            scroll-snap-type: x mandatory;
            scrollbar-width: none;
        }

        .testimonial-slider::-webkit-scrollbar {
            display: none;
        }

        .testimonial-card {
            min-width: 350px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 0.75rem;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            scroll-snap-align: start;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(31, 122, 188, 0.3);
        }

        .testimonial-card img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }

        .testimonial-card h4 {
            margin: 0;
            color: white;
        }

        .testimonial-card p {
            margin: 0 0 15px 0;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
        }

        .testimonial-card .stars {
            color: gold;
            margin-top: 15px;
        }

        .testimonial-card .stars i {
            margin-right: 3px;
        }

        /* ===== Newsletter Section ===== */
        .newsletter {
            padding: 5rem 1.25rem;
            background: linear-gradient(135deg, #022b3a 0%, #000 100%);
            color: white;
        }

        .newsletter .container {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }

        .newsletter h2 {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            background: linear-gradient(to right, #fff, #bfdbf7);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.5px;
        }

        .newsletter p {
            color: rgba(255, 255, 255, 0.7);
            max-width: 600px;
            margin: 0 auto 2rem;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .newsletter-form {
            display: flex;
            max-width: 500px;
            margin: 0 auto;
            gap: 10px;
        }

        .newsletter-input {
            flex: 1;
            padding: 15px 20px;
            border-radius: 50px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .newsletter-input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 0 3px rgba(31, 122, 188, 0.3);
        }

        .newsletter-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .newsletter-btn {
            padding: 15px 30px;
            border-radius: 50px;
            background: var(--primary-accent);
            color: white;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .newsletter-btn:hover {
            background: #1a6ba5;
            transform: translateY(-2px);
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .testimonial-slider {
                gap: 1rem;
            }

            .testimonial-card {
                min-width: 280px;
            }

            .newsletter-form {
                flex-direction: column;
            }

            .newsletter-btn {
                width: 100%;
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
                    <li><a href="./index.php"><i class="fa-solid fa-house"></i>Home</a></li>
                    <li><a href="./includes/products.php"><i class="fa-solid fa-store"></i>Store</a></li>
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
                            <li><a href="./includes/products.php" class="view-all">View All</a></li>
                        </ul>
                    </li>
                    <li><a href="#"><i class="fa-solid fa-users"></i>About</a></li>
                </ul>
            </div>
        </div>
        <div class="right-section">
            <?php if (!isset($_SESSION['user_id'])): ?>
            <!-- Logged OUT state -->
                <button type="button" onclick="window.location.href='includes/admin/login.php'">
                    Login <i class="fa-solid fa-user"></i>
                </button>
                <button type="button" onclick="window.location.href='includes/register.php'">
                    Register <i class="fa-solid fa-user-plus"></i>
                </button>
                <button type="button" onclick="window.location.href='includes/cart.php'">
                    <i class="fa-solid fa-cart-shopping"></i>
                </button>
            <?php else: ?>
                <!-- Logged IN state -->
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <!-- Admin View -->
                    <button type="button" onclick="window.location.href='includes/admin/dashboard.php'">
                        Dashboard <i class="fa-solid fa-gauge"></i>
                    </button>
                <?php else: ?>
                    <!-- Regular User View -->
                    <button type="button" onclick="window.location.href='includes/profile.php'">
                        <i class="fa-solid fa-user"></i>
                    </button>
                    <button type="button" onclick="window.location.href='includes/cart.php'">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </button>
                <?php endif; ?>

                <!-- Logout Button (for all logged-in users) -->
                <button type="button" onclick="window.location.href='includes/admin/logout.php'">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            <?php endif; ?>
        </div>
    </nav>

    <!----------------------------------------- HERO SECTION ----------------------------------------->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="animate__animated animate__fadeInDown">Elevate Your NSBM Experience</h1>
                <p class="hero-subtitle animate__animated animate__fadeIn animate__delay-1s">Premium quality
                    merchandise, carefully designed to showcase your NSBM pride and enhance your campus life.</p>
                <div class="hero-cta animate__animated animate__fadeIn animate__delay-2s">
                    <a href="#" class="btn-primary animate-pulse">Shop Collection</a>
                    <a href="#" class="btn-outline">Explore More</a>
                </div>
            </div>
            <div class="hero-features">
                <div class="feature-card animate__animated animate__fadeInLeft">
                    <i class="fas fa-truck animate-float"></i>
                    <h3>Free Campus Delivery</h3>
                </div>
                <div class="feature-card animate__animated animate__fadeInUp animate__delay-1s">
                    <i class="fas fa-tshirt animate-float" style="animation-delay: 0.2s"></i>
                    <h3>Premium Quality</h3>
                </div>
                <div class="feature-card animate__animated animate__fadeInRight animate__delay-2s">
                    <i class="fas fa-award animate-float" style="animation-delay: 0.4s"></i>
                    <h3>Official Merchandise</h3>
                </div>
            </div>
        </div>
    </section>

    <!----------------------------------------- FEATURED PRODUCTS ----------------------------------------->
    <section class="featured-products">
        <div class="container">
            <h2>Featured Products</h2>
            <p class="subtitle">Discover our most popular items with special discounts</p>

            <div class="product-grid">
                <?php
                require_once 'includes/database.php';

                // Get featured products with optional discounts
                $stmt = $pdo->query("
                SELECT * FROM products 
                WHERE is_featured = TRUE AND stock > 0
                ORDER BY created_at DESC 
                LIMIT 6
            ");
                $featuredProducts = $stmt->fetchAll();

                if (empty($featuredProducts)) {
                    echo "<p class='no-products'>No featured products available at the moment.</p>";
                } else {
                    foreach ($featuredProducts as $product) {
                        $originalPrice = $product['price'];
                        $discountPrice = $product['discount_percent'] > 0
                            ? $originalPrice * (1 - ($product['discount_percent'] / 100))
                            : null;
                        ?>
                <div class="product-card">
                    <div class="product-badge">
                        <?php if ($product['discount_percent'] > 0): ?>
                        <span class="discount-badge">-
                            <?= $product['discount_percent'] ?>%
                        </span>
                        <?php endif; ?>
                        <?php if ($product['stock'] < 5 && $product['stock'] > 0): ?>
                        <span class="stock-badge">Low Stock</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-image-container">
                        <img src="assets/uploads/<?= htmlspecialchars($product['image_url'] ?: 'default-product.jpg') ?>"
                            alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                    </div>

                    <div class="product-info">
                        <h3>
                            <?= htmlspecialchars($product['name']) ?>
                        </h3>
                        <p class="product-category">
                            <?= htmlspecialchars($product['category']) ?>
                        </p>

                        <div class="product-pricing">
                            <?php if ($discountPrice): ?>
                            <span class="original-price">Rs.
                                <?= number_format($originalPrice, 2) ?>
                            </span>
                            <span class="discounted-price">Rs.
                                <?= number_format($discountPrice, 2) ?>
                            </span>
                            <?php else: ?>
                            <span class="current-price">Rs.
                                <?= number_format($originalPrice, 2) ?>
                            </span>
                            <?php endif; ?>
                        </div>

                        <div class="product-actions">
                            <a href="includes/view_product_user.php?id=<?= $product['id'] ?>" class="view-btn">View
                                Details</a>
                            <button class="add-to-cart" data-product-id="<?= $product['id'] ?>">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php }
                } ?>
            </div>

            <div class="view-all-container">
                <a href="includes/products.php" class="view-all-btn">View All Products</a>
            </div>
        </div>
    </section>

    <!----------------------------------------- CATEGORIES ----------------------------------------->
    <section class="categories-showcase">
        <div class="container">
            <h2>Shop By Category</h2>
            <p class="subtitle">Browse our premium collections</p>

            <div class="category-grid">
                <a href="./includes/products.php?search=&category=T-Shirts&min_price=&max_price=" class="category-card">
                    <div class="category-image">
                        <img src="assets/images/category-tshirts.jpg" alt="T-Shirts">
                        <div class="overlay"></div>
                    </div>
                    <h3>T-Shirts</h3>
                </a>

                <a href="./includes/products.php?search=&category=Hoodies&min_price=&max_price=" class="category-card">
                    <div class="category-image">
                        <img src="assets/images/category-hoodies.jpg" alt="Hoodies">
                        <div class="overlay"></div>
                    </div>
                    <h3>Hoodies</h3>
                </a>

                <a href="./includes/products.php?search=&category=Other&min_price=&max_price=" class="category-card">
                    <div class="category-image">
                        <img src="assets/images/category-accessories.jpg" alt="Accessories">
                        <div class="overlay"></div>
                    </div>
                    <h3>Accessories</h3>
                </a>

                <a href="./includes/products.php?search=&category=Electronics&min_price=&max_price="
                    class="category-card">
                    <div class="category-image">
                        <img src="assets/images/category-electronics.jpg" alt="Stationery">
                        <div class="overlay"></div>
                    </div>
                    <h3>Electronics</h3>
                </a>
            </div>
        </div>
    </section>

    <!----------------------------------------- TESTIMONIALS ----------------------------------------->
    <section class="testimonials">
        <div class="container" style="max-width: 1200px; margin: 0 auto;">
            <h2 style="color: var(--pure-white); text-align: center; font-size: 2.5rem;">What Students Say</h2>
            <p
                style="color: var(--secondary-light); text-align: center; max-width: 700px; margin: 20px auto; opacity: 0.7;">
                Hear from our satisfied NSBM community</p>

            <div class="testimonial-slider">
                <div class="testimonial-card">
                    <div style="display: flex; align-items: center; margin-bottom: 20px;">
                        <img src="https://randomuser.me/api/portraits/women/43.jpg"
                            style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-right: 15px;">
                        <div>
                            <h4 style="margin: 0;">Sarah Perera</h4>
                            <p style="margin: 0; opacity: 0.7;">Computer Science</p>
                        </div>
                    </div>
                    <p>"The quality of the hoodies is amazing! I wear mine all the time on campus. The fabric is so
                        comfortable and the print hasn't faded after multiple washes."</p>
                    <div style="color: gold; margin-top: 15px;">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div style="display: flex; align-items: center; margin-bottom: 20px;">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg"
                            style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-right: 15px;">
                        <div>
                            <h4 style="margin: 0;">Rajiv Fernando</h4>
                            <p style="margin: 0; opacity: 0.7;">Business Management</p>
                        </div>
                    </div>
                    <p>"I bought the NSBM backpack and it's been perfect for carrying all my books and laptop. The
                        delivery was super fast too - got it the next day!"</p>
                    <div style="color: gold; margin-top: 15px;">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div style="display: flex; align-items: center; margin-bottom: 20px;">
                        <img src="https://randomuser.me/api/portraits/women/65.jpg"
                            style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-right: 15px;">
                        <div>
                            <h4 style="margin: 0;">Nadia Silva</h4>
                            <p style="margin: 0; opacity: 0.7;">Engineering</p>
                        </div>
                    </div>
                    <p>"The t-shirts are my favorite! Great fit and the designs are so unique. I've already bought three
                        different ones to show my NSBM pride."</p>
                    <div style="color: gold; margin-top: 15px;">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!----------------------------------------- NEWSLETTER ----------------------------------------->
    <section class="newsletter">
        <div class="container" style="max-width: 1200px; margin: 0 auto;">
            <h2 style="font-size: 2.5rem;">Stay Updated</h2>
            <p>Subscribe to get exclusive offers and new product announcements</p>

            <form class="newsletter-form">
                <input type="email" class="newsletter-input" placeholder="Your email address" required>
                <button type="submit" class="newsletter-btn">Subscribe</button>
            </form>
        </div>
    </section>

    <!----------------------------------------- FOOTER ----------------------------------------->
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
                    <li><a href="#"><i class="fas fa-map-marker-alt" style="margin-right: 8px;"></i> NSBM Green
                            University</a></li>
                    <li><a href="#"><i class="fas fa-phone" style="margin-right: 8px;"></i> +94 112 345 678</a></li>
                    <li><a href="#"><i class="fas fa-envelope" style="margin-right: 8px;"></i> hello@nsbmpremium.lk</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="copyright">
            <p>&copy; 2025 NSBM Premium. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Additional animations
        document.addEventListener('DOMContentLoaded', function () {
            // Animate elements when they come into view
            const animateOnScroll = function () {
                const elements = document.querySelectorAll('.product-card, .category-card, .testimonial-card');

                elements.forEach(element => {
                    const elementPosition = element.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.3;

                    if (elementPosition < screenPosition) {
                        element.classList.add('animate__animated', 'animate__fadeInUp');
                    }
                });
            };

            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll();

            // Auto-scroll testimonials
            const slider = document.querySelector('.testimonial-slider');
            if (slider) {
                setInterval(() => {
                    slider.scrollBy({
                        left: 300,
                        behavior: 'smooth'
                    });

                    if (slider.scrollLeft + slider.clientWidth >= slider.scrollWidth - 100) {
                        setTimeout(() => {
                            slider.scrollTo({
                                left: 0,
                                behavior: 'smooth'
                            });
                        }, 2000);
                    }
                }, 5000);
            }
        });

        document.querySelectorAll('.category-tab').forEach(tab => {
            tab.addEventListener('click', function () {
                // Remove active class from all tabs and content
                document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.category-products').forEach(c => c.classList.remove('active'));

                // Add active class to clicked tab
                this.classList.add('active');

                // Show corresponding content
                const category = this.getAttribute('data-category');
                document.getElementById(`${category}-products`).classList.add('active');
            });
        });

        // Testimonial slider auto-scroll
        document.addEventListener('DOMContentLoaded', function () {
            const slider = document.querySelector('.testimonial-slider');
            if (slider) {
                let scrollAmount = 0;
                const scrollStep = 370;

                function autoScroll() {
                    scrollAmount += scrollStep;
                    if (scrollAmount >= slider.scrollWidth - slider.clientWidth) {
                        scrollAmount = 0;
                    }
                    slider.scrollTo({
                        left: scrollAmount,
                        behavior: 'smooth'
                    });
                }
                setInterval(autoScroll, 5000);
            }

            // Newsletter form submission
            const newsletterForm = document.querySelector('.newsletter-form');
            if (newsletterForm) {
                newsletterForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const email = this.querySelector('input[type="email"]').value;
                    alert('Thank you for subscribing! We\'ll keep you updated.');
                    this.reset();
                });
            }
        });

        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');

                fetch('includes/cart.php', {
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
                                window.location.href = 'includes/admin/login.php';
                            } else {
                                showToast(data.message || 'Error adding to cart', 'error');
                            }
                        }
                    });
            });
        });

        function showToast(message, type = 'success') {
            // Remove any existing toasts first
            document.querySelectorAll('.toast-message').forEach(toast => toast.remove());

            const toast = document.createElement('div');
            toast.className = `toast-message ${type}`;
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
                <button class="close-toast">&times;</button>
            `;

            document.body.appendChild(toast);

            // Close button functionality
            toast.querySelector('.close-toast').addEventListener('click', () => {
                toast.remove();
            });

            // Auto-remove after 3 seconds
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    toast.remove();
                }
            }, 3000);
        }
    </script>
</body>

</html>