<?php
// includes/database.php
$host = 'localhost';      // Usually 'localhost'
$dbname = 'nsbm_shop';    // Your database name
$username = 'gagana';     // Username (as per your GRANT command)
$password = 'gagana123';  // Password (as per your GRANT command)    

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>