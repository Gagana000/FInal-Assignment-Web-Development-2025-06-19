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
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <!-- JavaScript Link -->
    <script src="./main.js" defer></script>
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
                    <li><a href="#"><i class="fa-solid fa-house"></i>Home</a></li>
                    <li><a href="#"><i class="fa-solid fa-store"></i>Collection</a></li>
                    <li><a href="#">Categories<i class="fa-solid fa-caret-down"></i></a>
                        <ul class="drop-down-menu">
                            <li><a href="#">T-Shirts</a></li>
                            <li><a href="#">Hoodies</a></li>
                            <li><a href="#">Accessories</a></li>
                            <li class="devider"></li>
                            <li><a href="#" class="view-all">View All</a></li>
                        </ul>
                    </li>
                    <li><a href="#"><i class="fa-solid fa-users"></i>About</a></li>
                </ul>
            </div>

            <div class="nav-search">
                <input type="text" name="search" id="search" placeholder="Search">
            </div>

        </div>
        <div class="right-section">
            <button type="button">Login<i class="fa-solid fa-user"></i></button>
            <button type="button">Register<i class="fa-solid fa-user-plus"></i></button>
            <button type="button"><i class="fa-solid fa-cart-shopping"></i></button>
            <button type="button"><i class="fa-solid fa-user"></i></button>
        </div>
    </nav>

    <!----------------------------------------- HERO SECTION ----------------------------------------->
    <section class="hero">
        <div class="hero-content">
            <h1>Discover Exclusive NSBM Merchandise</h1>
            <p>Premium quality apparel and accessories designed for the NSBM community</p>
            <div class="hero-buttons">
                <a href="#" class="btn-primary">Shop Now</a>
                <a href="#" class="btn-outline">Learn More</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="./assets/images/shopping_image.svg" alt="NSBM Merchandise">
        </div>
    </section>

    <!--------------------------------------- FONT AWESOME ICONS --------------------------------------->
    <script src="https://kit.fontawesome.com/cb8b70f796.js" crossorigin="anonymous"></script>
</body>
</html>