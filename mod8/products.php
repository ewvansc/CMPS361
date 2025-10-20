<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/authcheck.php';
require __DIR__ . '/db.php';

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }


$table = 'products';
$check = pg_query_params(
  $conn,
  "SELECT EXISTS (
     SELECT 1 FROM information_schema.tables
     WHERE table_schema='public' AND table_name=$1
   ) AS exists;",
  [$table]
);
if (!$check) die("Table check error: " . pg_last_error($conn));
if (pg_fetch_result($check, 0, 'exists') !== 't') {
  $table = 'pizzaplaces';
}


$perPage = 10;
$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset  = ($page - 1) * $perPage;


$countRes = pg_query($conn, "SELECT COUNT(*)::int AS total FROM public.$table");
if (!$countRes) die("Count error: " . pg_last_error($conn));
$total = (int)pg_fetch_result($countRes, 0, 'total');
$totalPages = max(1, (int)ceil($total / $perPage));


if ($table === 'products') {
  $sql = "SELECT id, name, price, description, created_at
          FROM public.products
          ORDER BY id DESC
          LIMIT $1 OFFSET $2";
  $res = pg_query_params($conn, $sql, [$perPage, $offset]);
} else {
  $sql = "SELECT id, name, location
          FROM public.pizzaplaces
          ORDER BY id DESC
          LIMIT $1 OFFSET $2";
  $res = pg_query_params($conn, $sql, [$perPage, $offset]);
}
if (!$res) die("Query error: " . pg_last_error($conn));


function qs(array $overrides = []) {
  $params = $_GET;
  foreach ($overrides as $k => $v) {
    if ($v === null) unset($params[$k]); else $params[$k] = $v;
  }
  $q = http_build_query($params);
  return $q ? "?$q" : "";
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?= $table === 'products' ? 'Products' : 'Pizza Places' ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <div class="header">
    <h1><?= $table === 'products' ? 'Products' : 'Pizza Places' ?></h1>
    <div class="actions">
      <?php if ($table === 'products'): ?>
        <a class="btn" href="addprod.php">Add Product</a>
      <?php endif; ?>
      <a class="btn" href="services.php">Back</a>
      <a class="btn" href="logout.php">Logout</a>
    </div>
  </div>

  <?php if (!empty($_GET['ok'])): ?>
    <div class="flash">Product saved successfully.</div>
  <?php endif; ?>

  <div class="card">
    <div class="meta">
      Showing <?= h($total ? ($offset + 1) : 0) ?>–<?= h(min($total, $offset + $perPage)) ?> of <?= h($total) ?>
      (from <code><?= h($table) ?></code>)
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <?php if ($table === 'products'): ?>
              <th style="text-align:right">Price</th>
              <th>Description</th>
              <th>Created</th>
            <?php else: ?>
              <th>Location</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if ($total === 0): ?>
            <tr>
              <td colspan="<?= $table==='products' ? 5 : 3 ?>">
                No rows yet. <?= $table==='products' ? 'Add your first product.' : 'Seed some data in pizzaplaces.' ?>
              </td>
            </tr>
          <?php else: ?>
            <?php while ($row = pg_fetch_assoc($res)): ?>
              <tr>
                <td><?= h($row['id']) ?></td>
                <td><?= h($row['name'] ?? '') ?></td>
                <?php if ($table === 'products'): ?>
                  <td style="text-align:right">$<?= number_format((float)$row['price'], 2) ?></td>
                  <td><?= h($row['description'] ?? '') ?></td>
                  <td><?= h(isset($row['created_at']) ? date('Y-m-d H:i', strtotime($row['created_at'])) : '') ?></td>
                <?php else: ?>
                  <td><?= h($row['location'] ?? '') ?></td>
                <?php endif; ?>
              </tr>
            <?php endwhile; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="pagination">
      <a class="btn" href="<?= $page>1 ? qs(['page'=>$page-1]) : '#' ?>" aria-disabled="<?= $page<=1?'true':'false' ?>">Prev</a>
      <?php
        $start = max(1, $page - 2);
        $end   = min($totalPages, $page + 2);
        for ($p = $start; $p <= $end; $p++):
      ?>
        <a class="btn <?= $p===$page?'active':'' ?>" href="<?= qs(['page'=>$p]) ?>"><?= $p ?></a>
      <?php endfor; ?>
      <a class="btn" href="<?= $page<$totalPages ? qs(['page'=>$page+1]) : '#' ?>" aria-disabled="<?= $page>=$totalPages?'true':'false' ?>">Next</a>
    </div>
  </div>
</div>
</body>
</html>
