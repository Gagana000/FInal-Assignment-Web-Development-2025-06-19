<?php
session_start();
require_once __DIR__ . '/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $productId = $_POST['product_id'] ?? null;
  $action = $_POST['action'] ?? 'add';

  if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit;
  }

  $userId = $_SESSION['user_id'];
  $pdo = getDBConnection();

  try {
    // Check product availability
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND stock > 0");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
      echo json_encode(['success' => false, 'message' => 'Product not available']);
      exit;
    }

    // Check if item exists in cart
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    $existingItem = $stmt->fetch();

    if ($existingItem) {
      $newQuantity = $action === 'add' ? $existingItem['quantity'] + 1 : max(1, $existingItem['quantity'] - 1);
      $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
      $stmt->execute([$newQuantity, $existingItem['id']]);
    } else {
      $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
      $stmt->execute([$userId, $productId]);
    }

    echo json_encode(['success' => true]);
  } catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
  }
  exit;
}
?>