<?php
session_start();
require_once __DIR__ . '/../database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? '../../index.php' : '../../index.php'));
    exit();
}

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: dashboard.php");
    exit();
}


$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $login_type = $_POST['login_type'] ?? 'customer';

    if (empty($username) || empty($password)) {
        $error = "Username and password are required!";
    } else {
        // Fetch user from database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Check role if logging in as admin
                if ($login_type === 'admin' && $user['role'] !== 'admin') {
                    $error = "Admin privileges required!";
                } else {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    // Redirect based on role
                    header("Location: " . ($user['role'] === 'admin' ? '../../index.php' : '../../index.php'));
                    exit();
                }
            } else {
                $error = "Invalid username or password!";
            }
        } else {
            $error = "Invalid username or password!";
        }
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    if ($user['role'] === 'admin') {
        header("Location: dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NSBM Shop</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../../main.js" defer></script>
    <link rel="shortcut icon" href="../../assets/images/logo_brand.png" type="image/x-icon">
</head>

<body>
    <div class="login-section">
        <div class="login-container">
            <div class="login-header">
                <h2>Welcome Back</h2>
                <p>Login to access your account</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="login-tabs">
                <button class="tab-btn active" onclick="switchTab('customer')">Customer Login</button>
                <button class="tab-btn" onclick="switchTab('admin')">Admin Login</button>
            </div>

            <div class="tab-content active" id="customer-tab">
                <form action="login.php" method="post">
                    <input type="hidden" name="login_type" value="customer">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        <span class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div class="remember-forgot">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <div class="forgot-password">
                            <a href="forgot-password.php">Forgot password?</a>
                        </div>
                    </div>
                    <button type="submit" class="btn-login">Login as Customer</button>
                </form>
            </div>

            <div class="tab-content" id="admin-tab">
                <form action="login.php" method="post">
                    <input type="hidden" name="login_type" value="admin">
                    <div class="form-group">
                        <label for="admin-username">Admin Username</label>
                        <input type="text" id="admin-username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="admin-password">Admin Password</label>
                        <input type="password" id="admin-password" name="password" required>
                        <span class="password-toggle" onclick="togglePassword('admin-password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div class="remember-forgot">
                        <div class="remember-me">
                            <input type="checkbox" id="admin-remember" name="remember">
                            <label for="admin-remember">Remember me</label>
                        </div>
                    </div>
                    <button type="submit" class="btn-login">Login as Admin</button>
                </form>
            </div>

            <div class="register-link">
                Don't have an account? <a href="../register.php">Register here</a>
            </div>
        </div>
    </div>
</body>

</html>