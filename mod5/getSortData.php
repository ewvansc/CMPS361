<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$apiURL = "http://localhost:5005/accounts";
$limit  = 10;

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function qs(array $overrides = []): string {
  $params = $_GET;
  foreach ($overrides as $k => $v) {
    if ($v === null) unset($params[$k]); else $params[$k] = $v;
  }
  $q = http_build_query($params);
  return $q ? "?$q" : "";
}
function nextOrderFor(string $clicked, string $currentSort, string $currentOrder): string {
  return ($clicked === $currentSort && $currentOrder === 'asc') ? 'desc' : 'asc';
}
function arrow(string $col, string $currentSort, string $currentOrder): string {
  return $col === $currentSort ? ($currentOrder === 'asc' ? ' ▲' : ' ▼') : '';
}

/* --- fetch --- */
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


$allowed = ['id','name','city']; 
$sort   = (isset($_GET['sort']) && in_array($_GET['sort'], $allowed, true)) ? $_GET['sort'] : $allowed[0];
$order  = (isset($_GET['order']) && strtolower($_GET['order']) === 'desc') ? 'desc' : 'asc';

if (!empty($data)) {
  usort($data, function($a, $b) use ($sort, $order) {
    $av = $a[$sort] ?? '';
    $bv = $b[$sort] ?? '';

    
    if ($sort === 'id') {
      $cmp = (int)$av <=> (int)$bv;
    } else {
      $cmp = strcasecmp((string)$av, (string)$bv);
    }

    
    if ($cmp === 0) {
      $cmp = ((int)($a['id'] ?? 0)) <=> ((int)($b['id'] ?? 0));
    }
    return $order === 'desc' ? -$cmp : $cmp;
  });
}


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
    table { border-collapse: collapse; width: 100%; max-width: 820px; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    th { background: #f6f6f6; }
    th a { color: inherit; text-decoration: none; }
    th a:hover { text-decoration: underline; }
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
        <?php
          
          foreach (['id'=>'id','name'=>'name','city'=>'city'] as $key => $label) {
            $next = nextOrderFor($key, $sort, $order);
            echo '<th><a href="'.h(qs(['page'=>1,'sort'=>$key,'order'=>$next])).'">'.
                 h($label).h(arrow($key, $sort, $order)).'</a></th>';
          }
        ?>
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
      <a href="<?= h(qs(['page'=>$currentPage-1])) ?>">Previous</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <?= $i === $currentPage
           ? "<strong>$i</strong>"
           : '<a href="'.h(qs(['page'=>$i])).'">'.$i.'</a>' ?>
    <?php endfor; ?>

    <?php if ($currentPage < $totalPages): ?>
      <a href="<?= h(qs(['page'=>$currentPage+1])) ?>">Next</a>
    <?php endif; ?>
  </div>

  <p class="muted" style="margin-top:8px;">Total Records: <?= (int)$totalRecords ?></p>
<?php else: ?>
  <p class="muted">No records to display.</p>
<?php endif; ?>

</body>
</html>
