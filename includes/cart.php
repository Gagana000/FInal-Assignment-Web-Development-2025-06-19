<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $product_id = $_POST['product_id'];
  $user_id = $_SESSION['user_id']; // Assume user is logged in
  
  // Check if item already exists in cart
  $result = mysqli_query($conn, "SELECT * FROM cart WHERE product_id = $product_id AND user_id = $user_id");
  
  if (mysqli_num_rows($result) > 0) {
    mysqli_query($conn, "UPDATE cart SET quantity = quantity + 1 WHERE product_id = $product_id");
  } else {
    mysqli_query($conn, "INSERT INTO cart (product_id, user_id, quantity) VALUES ($product_id, $user_id, 1)");
  }
  header("Location: ../index.php");
}
?>