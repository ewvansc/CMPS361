<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$apiURL = "http://localhost:5005/accounts";
$limit  = 10; // rows per page

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// --- fetch ---
$response = @file_get_contents($apiURL);
if ($response === false) {
  http_response_code(502);
  $error = "Could not reach API at $apiURL. Is the server running?";
  $data = [];
} else {
  $data = json_decode($response, true);
  if (!is_array($data)) {
    http_response_code(500);
    $error = "API did not return a JSON array. Raw response below.";
    $data = [];
  }
}

// --- pagination ---
$totalRecords = count($data);
$totalPages   = max(1, (int)ceil($totalRecords / $limit));
$currentPage  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$currentPage  = min($currentPage, $totalPages);
$startIndex   = ($currentPage - 1) * $limit;
$pageData     = array_slice($data, $startIndex, $limit);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Accounts</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; padding: 24px; }
    table { border-collapse: collapse; width: 100%; max-width: 700px; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    th { background: #f6f6f6; }
    .pager a, .pager strong { margin: 0 6px; text-decoration: none; }
    .error { color: #b00020; margin-bottom: 16px; }
    .muted { color: #666; }
  </style>
</head>
<body>

<h1>Accounts</h1>

<?php if (!empty($error)): ?>
  <div class="error"><?= h($error) ?></div>
  <?php if (isset($response)) : ?>
    <pre class="muted"><?= h($response) ?></pre>
  <?php endif; ?>
<?php endif; ?>

<?php if ($totalRecords > 0): ?>
  <table>
    <thead>
      <tr>
        <th>id</th>
        <th>name</th>
        <th>city</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($pageData as $row): ?>
        <tr>
          <td><?= h($row['id']   ?? '') ?></td>
          <td><?= h($row['name'] ?? '') ?></td>
          <td><?= h($row['city'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="pager" style="margin-top:16px;">
    <?php if ($currentPage > 1): ?>
      <a href="?page=<?= $currentPage-1 ?>">Previous</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <?= $i === $currentPage ? "<strong>$i</strong>" : '<a href="?page='.$i.'">'.$i.'</a>' ?>
    <?php endfor; ?>

    <?php if ($currentPage < $totalPages): ?>
      <a href="?page=<?= $currentPage+1 ?>">Next</a>
    <?php endif; ?>
  </div>
<?php else: ?>
  <p class="muted">No records to display.</p>
<?php endif; ?>

</body>
</html>
