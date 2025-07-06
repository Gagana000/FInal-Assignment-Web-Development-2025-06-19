<?php
require_once __DIR__ . '/database.php';
session_start();

// Get all distinct categories for navigation
$pdo = getDBConnection();
$categoryStmt = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category <> '' ORDER BY category ASC");
$categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us - NSBM Premium</title>
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="shortcut icon" href="../assets/images/logo_brand.png" type="image/x-icon">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
    rel="stylesheet">
  <style>
    /* About Page Container */
    .about-container {
      max-width: 1200px;
      margin: 100px auto 50px;
      padding: 0 20px;
      color: white;
    }

    /* Hero Section */
    .about-hero {
      background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('../assets/images/about-bg.jpg');
      background-size: cover;
      background-position: center;
      padding: 100px 0;
      text-align: center;
      border-radius: 12px;
      margin-bottom: 50px;
    }

    .about-hero h1 {
      font-size: 3.5rem;
      margin-bottom: 20px;
      background: linear-gradient(to right, #fff, #bfdbf7);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }

    .about-hero p {
      max-width: 700px;
      margin: 0 auto;
      font-size: 1.2rem;
      line-height: 1.6;
      opacity: 0.9;
    }

    /* About Content */
    .about-content {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 3rem;
      margin-bottom: 5rem;
    }

    .about-image {
      width: 100%;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    .about-text h2 {
      font-size: 2.2rem;
      margin-top: 0;
      margin-bottom: 1.5rem;
      position: relative;
      display: inline-block;
    }

    .about-text h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 0;
      width: 60px;
      height: 3px;
      background: var(--primary-accent);
    }

    .about-text p {
      line-height: 1.8;
      margin-bottom: 1.5rem;
      color: rgba(255, 255, 255, 0.8);
    }

    /* Team Section */
    .team-section {
      margin-bottom: 5rem;
    }

    .section-title {
      font-size: 2.2rem;
      text-align: center;
      margin-bottom: 3rem;
      position: relative;
      display: inline-block;
      left: 50%;
      transform: translateX(-50%);
    }

    .section-title::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 60px;
      height: 3px;
      background: var(--primary-accent);
    }

    .team-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 2rem;
    }

    .team-member {
      background: rgba(2, 43, 58, 0.7);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(31, 122, 188, 0.3);
      border-radius: 12px;
      padding: 2rem;
      text-align: center;
      transition: all 0.3s ease;
    }

    .team-member:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(31, 122, 188, 0.3);
    }

    .team-member-image {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      margin: 0 auto 1.5rem;
      border: 3px solid var(--primary-accent);
    }

    .team-member h3 {
      margin: 0 0 0.5rem 0;
      font-size: 1.3rem;
    }

    .team-member-role {
      color: var(--primary-accent);
      margin-bottom: 1rem;
      font-weight: 500;
    }

    .team-member-bio {
      color: rgba(255, 255, 255, 0.7);
      line-height: 1.6;
      margin-bottom: 1.5rem;
    }

    .social-links {
      display: flex;
      justify-content: center;
      gap: 15px;
    }

    .social-link {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 35px;
      height: 35px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      color: white;
      transition: all 0.3s ease;
    }

    .social-link:hover {
      background: var(--primary-accent);
      transform: translateY(-3px);
    }

    /* Values Section */
    .values-section {
      margin-bottom: 5rem;
    }

    .values-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 2rem;
    }

    .value-card {
      background: rgba(2, 43, 58, 0.7);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(31, 122, 188, 0.3);
      border-radius: 12px;
      padding: 2rem;
      transition: all 0.3s ease;
    }

    .value-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(31, 122, 188, 0.3);
    }

    .value-icon {
      font-size: 2.5rem;
      color: var(--primary-accent);
      margin-bottom: 1.5rem;
    }

    .value-card h3 {
      font-size: 1.5rem;
      margin: 0 0 1rem 0;
    }

    .value-card p {
      color: rgba(255, 255, 255, 0.7);
      line-height: 1.6;
    }

    /* Responsive Design */
    @media (max-width: 992px) {
      .about-content {
        gap: 2rem;
      }
    }

    @media (max-width: 768px) {
      .about-content {
        grid-template-columns: 1fr;
      }

      .about-hero h1 {
        font-size: 2.5rem;
      }

      .about-hero p {
        font-size: 1rem;
      }
    }

    @media (max-width: 480px) {
      .about-hero {
        padding: 60px 20px;
      }

      .about-hero h1 {
        font-size: 2rem;
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

  <main class="about-container">
    <section class="about-hero">
      <h1>About NSBM Premium</h1>
      <p>Your one-stop shop for premium NSBM merchandise and campus essentials. We're dedicated to providing
        high-quality products that showcase your NSBM pride.</p>
    </section>

    <section class="about-content">
      <div>
        <img src="../assets/images/nsbm_green_university.jpg" alt="NSBM Campus" class="about-image">
      </div>
      <div class="about-text">
        <h2>Our Story</h2>
        <p>Founded in 2023, NSBM Premium was created to provide students, faculty, and alumni with high-quality
          merchandise that represents the spirit and values of NSBM Green University.</p>
        <p>What started as a small initiative to provide better campus gear has grown into a full-fledged online store
          offering a wide range of products from apparel to accessories and electronics.</p>
        <p>We work directly with the university to ensure all our products meet official standards and reflect the NSBM
          brand with pride.</p>
      </div>
    </section>

    <section class="values-section">
      <h2 class="section-title">Our Values</h2>
      <div class="values-grid">
        <div class="value-card">
          <div class="value-icon">
            <i class="fas fa-medal"></i>
          </div>
          <h3>Quality</h3>
          <p>We source only the best materials and work with trusted manufacturers to ensure our products stand the test
            of time.</p>
        </div>
        <div class="value-card">
          <div class="value-icon">
            <i class="fas fa-leaf"></i>
          </div>
          <h3>Sustainability</h3>
          <p>We're committed to eco-friendly practices, from sustainable materials to responsible packaging.</p>
        </div>
        <div class="value-card">
          <div class="value-icon">
            <i class="fas fa-users"></i>
          </div>
          <h3>Community</h3>
          <p>As part of the NSBM family, we give back to student initiatives and support campus events.</p>
        </div>
      </div>
    </section>

    <section class="team-section">
      <h2 class="section-title">Meet The Team</h2>
      <div class="team-grid">
        <div class="team-member">
          <img src="../assets/images/team1.jpg" alt="Team Member" class="team-member-image">
          <h3>John Smith</h3>
          <div class="team-member-role">Founder & CEO</div>
          <div class="team-member-bio">John started NSBM Premium with a vision to create better campus merchandise for
            students.</div>
          <div class="social-links">
            <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
            <a href="#" class="social-link"><i class="fas fa-envelope"></i></a>
          </div>
        </div>
        <div class="team-member">
          <img src="../assets/images/team2.jpg" alt="Team Member" class="team-member-image">
          <h3>Sarah Johnson</h3>
          <div class="team-member-role">Head of Design</div>
          <div class="team-member-bio">Sarah leads our creative team in developing innovative NSBM-branded products.
          </div>
          <div class="social-links">
            <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
            <a href="#" class="social-link"><i class="fab fa-behance"></i></a>
            <a href="#" class="social-link"><i class="fas fa-envelope"></i></a>
          </div>
        </div>
        <div class="team-member">
          <img src="../assets/images/team3.jpg" alt="Team Member" class="team-member-image">
          <h3>Michael Brown</h3>
          <div class="team-member-role">Operations Manager</div>
          <div class="team-member-bio">Michael ensures smooth operations and timely delivery of all orders.</div>
          <div class="social-links">
            <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
            <a href="#" class="social-link"><i class="fas fa-envelope"></i></a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script>
    // Simple animation for team members on scroll
    document.addEventListener('DOMContentLoaded', function () {
      const teamMembers = document.querySelectorAll('.team-member');

      const animateOnScroll = function () {
        teamMembers.forEach(member => {
          const memberPosition = member.getBoundingClientRect().top;
          const screenPosition = window.innerHeight / 1.3;

          if (memberPosition < screenPosition) {
            member.style.opacity = '1';
            member.style.transform = 'translateY(0)';
          }
        });
      };

      // Set initial state
      teamMembers.forEach(member => {
        member.style.opacity = '0';
        member.style.transform = 'translateY(20px)';
        member.style.transition = 'all 0.6s ease';
      });

      window.addEventListener('scroll', animateOnScroll);
      animateOnScroll(); // Run once on load
    });
  </script>
</body>

</html>