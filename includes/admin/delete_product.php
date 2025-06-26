<?php
$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM products WHERE id = $id");
header("Location: product.php");
?>