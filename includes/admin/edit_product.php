<?php
require_once __DIR__ . '/../auth_functions.php';
require_login();
require_admin();

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products");
$stmt->execute();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = $_POST['name'];
  $price = $_POST['price'];
  $stock = $_POST['stock'];

  mysqli_query($conn, "UPDATE products SET 
      name = '$name', 
      price = $price, 
      stock = $stock 
      WHERE id = $id");
  header("Location: product.php");
}
?>

<form method="POST">
  <input type="text" name="name" value="<?= $product['name'] ?>" required>
  <input type="number" name="price" value="<?= $product['price'] ?>" step="0.01" required>
  <input type="number" name="stock" value="<?= $product['stock'] ?>" required>
  <button type="submit">Update</button>
</form>