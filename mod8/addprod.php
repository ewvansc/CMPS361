<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/authcheck.php';
require __DIR__ . '/db.php';
session_start();

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        $errors[] = 'Invalid form token.';
    }

    
    $name        = trim($_POST['name'] ?? '');
    $priceStr    = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');

   
    if ($name === '') $errors[] = 'Name is required.';
    if ($priceStr === '' || !is_numeric($priceStr) || (float)$priceStr < 0) {
        $errors[] = 'Price must be a valid non-negative number.';
    }

  
    if (!$errors) {
        $price = (float)$priceStr;
        $sql   = "INSERT INTO public.products (name, price, description) VALUES ($1, $2, $3)";
        $ok    = pg_query_params($conn, $sql, [$name, $price, $description]);
        if ($ok) {
            
            $_SESSION['csrf'] = bin2hex(random_bytes(16));
            header('Location: products.php?ok=1');
            exit;
        } else {
            $errors[] = 'Database insert error: ' . pg_last_error($conn);
        }
    }
}

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Add Product</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <div class="header">
    <h1>Add Product</h1>
    <div class="actions">
      <a class="btn" href="products.php">Back to Products</a>
      <a class="btn" href="logout.php">Logout</a>
    </div>
  </div>

  <?php if ($errors): ?>
    <div class="flash error">
      <?php foreach ($errors as $e): ?>
        <div><?= h($e) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="card">
    <form method="post" action="">
      <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">

      <label>Name</label>
      <input type="text" name="name" required value="<?= h($_POST['name'] ?? '') ?>">

      <label style="margin-top:10px;">Price</label>
      <input type="number" step="0.01" min="0" name="price" required value="<?= h($_POST['price'] ?? '') ?>">

      <label style="margin-top:10px;">Description</label>
      <textarea name="description"><?= h($_POST['description'] ?? '') ?></textarea>

      <div class="actions" style="margin-top:14px;">
        <button class="btn" type="submit">Save Product</button>
        <a class="btn" href="products.php">Cancel</a>
      </div>
    </form>
  </div>
</div>
</body>
</html>
