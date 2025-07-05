<?php
session_start();
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth_functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters!";
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            $error = "Username or email already exists!";
        } else {
            // Create new user
            $hashed_password = hash_password($password);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'customer')");

            if ($stmt->execute([$username, $email, $hashed_password])) {
                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: admin/login.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../main.js"></script>
    <style>
        body {
            background: radial-gradient(circle, rgb(1, 20, 27), rgb(0, 0, 0) 88%);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100vh;
        }

        label {
            color: white !important;
        }

        input {
            color: white !important;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <h1>Register</h1>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success">
                <?php echo htmlspecialchars($_SESSION['success']); ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password (min 8 characters)</label>
                <input type="password" id="password" name="password" required>
                <span class="password-toggle" onclick="togglePassword('password')">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <span class="password-toggle" onclick="togglePassword('confirm_password')">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
            <button type="submit" class="btn">Register</button>
        </form>
        <p>Already have an account? <a href="../includes/admin/login.php">Login here</a></p>
    </div>
</body>

</html>