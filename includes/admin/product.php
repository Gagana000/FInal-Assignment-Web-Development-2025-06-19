<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: login.php");

include('../../includes/database.php');
$stmt = $pdo->prepare("SELECT * FROM products");
$stmt->execute();
?>

<table>
  <tr>
    <th>ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Actions</th>
  </tr>
  <?php while ($row = mysqli_fetch_assoc($products)): ?>
  <tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['name'] ?></td>
    <td>$<?= $row['price'] ?></td>
    <td><?= $row['stock'] ?? 'N/A' ?></td>
    <td>
      <a href="edit_product.php?id=<?= $row['id'] ?>">Edit</a>
      <a href="delete_product.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete?')">Delete</a>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<a href="add_product.php">Add New Product</a>