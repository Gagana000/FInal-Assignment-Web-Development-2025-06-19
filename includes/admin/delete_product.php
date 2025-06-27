<?php
require_once __DIR__ . '/../auth_functions.php';
require_login();
require_admin();

$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM products WHERE id = $id");
header("Location: product.php");
?>