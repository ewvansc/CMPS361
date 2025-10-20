<?php require __DIR__ . '/authcheck.php'; ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Services</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
      <div class="actions">
        <a class="btn" href="products.php">Products</a>
        <a class="btn" href="addprod.php">Add Product</a>
        <a class="btn" href="logout.php">Logout</a>
      </div>
    </div>
  </div>
</body>
</html>
