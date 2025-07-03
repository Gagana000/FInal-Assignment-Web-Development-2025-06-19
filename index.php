<?php
session_start();
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
    <!-- Shortcut icon -->
    <link rel="shortcut icon" href="assets/images/logo_brand.png" type="image/x-icon">
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
                    <li><a href="./includes/collection.php"><i class="fa-solid fa-store"></i>Collection</a></li>
                    <li><a href="#">Categories<i class="fa-solid fa-caret-down"></i></a>
                        <ul class="drop-down-menu">
                            <li><a href="#">T-Shirts</a></li>
                            <li><a href="#">Hoodies</a></li>
                            <li><a href="#">Accessories</a></li>
                            <li class="devider"></li>
                            <li><a href="./includes/products.php" class="view-all">View All</a></li>
                        </ul>
                    </li>
                    <li><a href="./includes/about.php"><i class="fa-solid fa-users"></i>About</a></li>
                </ul>
            </div>

            <div class="nav-search">
                <input type="text" name="search" id="search" placeholder="Search">
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
                <h1>Elevate Your NSBM Experience</h1>
                <p class="hero-subtitle">Premium quality merchandise, carefully designed to showcase your NSBM pride and
                    enhance your campus life.</p>
                <div class="hero-cta">
                    <a href="./includes/collection.php" class="btn-primary">Shop Collection</a>
                    <a href="./includes/products.php" class="btn-outline">Explore More</a>
                </div>
            </div>
            <div class="hero-features">
                <div class="feature-card">
                    <i class="fas fa-truck"></i>
                    <h3>Free Campus Delivery</h3>
                </div>
                <div class="feature-card">
                    <i class="fas fa-tshirt"></i>
                    <h3>Premium Quality</h3>
                </div>
                <div class="feature-card">
                    <i class="fas fa-award"></i>
                    <h3>Official Merchandise</h3>
                </div>
            </div>
        </div>
    </section>

    <!--------------------------------------- FONT AWESOME ICONS --------------------------------------->
    <script src="https://kit.fontawesome.com/cb8b70f796.js" crossorigin="anonymous"></script>
</body>

</html>