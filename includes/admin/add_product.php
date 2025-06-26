<?php
// Start session and include database connection
session_start();
require_once __DIR__ . '/../../includes/database.php';

// Debugging: Uncomment to see errors during development
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    // Insert into database
    $query = "INSERT INTO products (name, price, stock, category, description) 
              VALUES ('$name', $price, $stock, '$category', '$description')";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        header("Location: product.php");
        exit();
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background: #3182CE; color: white; border: none; padding: 10px 15px; cursor: pointer; }
        button:hover { background: #2c5282; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Add New Product</h1>
    
    <?php if (isset($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="price">Price ($)</label>
            <input type="number" id="price" name="price" step="0.01" min="0" required>
        </div>
        
        <div class="form-group">
            <label for="stock">Stock</label>
            <input type="number" id="stock" name="stock" min="0" required>
        </div>
        
        <div class="form-group">
            <label for="category">Category</label>
            <input type="text" id="category" name="category">
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"></textarea>
        </div>
        
        <button type="submit">Add Product</button>
    </form>
    
    <p><a href="product.php">← Back to Product List</a></p>
</body>
</html>