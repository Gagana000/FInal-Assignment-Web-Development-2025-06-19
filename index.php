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

    <!----------------------------------------- HERO SLIDER ----------------------------------------->
    <section class="hero">
    <div class="hero-content">
        <h1>Discover Exclusive NSBM Merchandise</h1>
        <p>Premium quality apparel and accessories designed for the NSBM community</p>
        <div class="hero-buttons">
        <a href="#" class="btn-primary">Shop Now</a>
        <a href="#" class="btn-outline">Learn More</a>
        </div>
    </div>

    <!-- Hero Slider -->
    <div class="hero-slider">
        <div class="slider-container">
        <!-- Slide 1 -->
        <div class="slide active">
            <div class="product-info-overlay">
            <h3>NSBM T-Shirt</h3>
            <div class="price">Rs. 1,500 <span class="original-price">Rs. 2,000</span></div>
            <div class="deal-badge">25% OFF</div>
            <button class="shop-now-btn">Shop Now <i class="fas fa-arrow-right"></i></button>
            </div>
            <img src="./assets/images/T-Shirt.jpg" alt="NSBM T-Shirt">
        </div>
        
        <!-- Slide 2 -->
        <div class="slide">
            <div class="product-info-overlay">
            <h3>NSBM Hoodie</h3>
            <div class="price">Rs. 3,500</div>
            <div class="shipping-badge">Free Shipping</div>
            <button class="shop-now-btn">Shop Now <i class="fas fa-arrow-right"></i></button>
            </div>
            <img src="./assets/images/Hoodie 2.jpg" alt="NSBM Hoodie">
        </div>
        
        <!-- Slide 3 -->
        <div class="slide">
            <div class="product-info-overlay">
            <h3>NSBM Cap</h3>
            <div class="price">Rs. 800</div>
            <div class="stock-info">Only 3 left!</div>
            <button class="shop-now-btn">Shop Now <i class="fas fa-arrow-right"></i></button>
            </div>
            <img src="./assets/images/Cap.jpg" alt="NSBM Cap">
        </div>
        
        <!-- Slide 4 -->
        <div class="slide">
            <div class="product-info-overlay">
            <h3>NSBM Notebook</h3>
            <div class="price">Rs. 500</div>
            <div class="rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
                <span>(12)</span>
            </div>
            <button class="shop-now-btn">Shop Now <i class="fas fa-arrow-right"></i></button>
            </div>
            <img src="./assets/images/Notebook.jpg" alt="NSBM Notebook">
        </div>
        </div>
        
        <!-- Slider Controls -->
        <div class="slider-controls">
        <button class="prev-slide"><i class="fas fa-chevron-left"></i></button>
        <div class="slide-indicators"></div>
        <button class="next-slide"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
    </section>

    <!--------------------------------------- FONT AWESOME ICONS --------------------------------------->
    <script src="https://kit.fontawesome.com/cb8b70f796.js" crossorigin="anonymous"></script>
</body>
</html>