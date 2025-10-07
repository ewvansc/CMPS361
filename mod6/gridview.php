<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

/* - CONFIG - */
$apiURL = "http://localhost:5005/accounts";


function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function qs(array $overrides = []): string {
  $params = $_GET;
  foreach ($overrides as $k => $v) { if ($v === null) unset($params[$k]); else $params[$k] = $v; }
  $q = http_build_query($params);
  return $q ? "?$q" : "";
}
function nextOrderFor(string $clicked, string $currentSort, string $currentOrder): string {
  return ($clicked === $currentSort && $currentOrder === 'asc') ? 'desc' : 'asc';
}
function arrow(string $col, string $currentSort, string $currentOrder): string {
  return $col === $currentSort ? ($currentOrder === 'asc' ? ' ▲' : ' ▼') : '';
}

/* - FETCH - */
$response = @file_get_contents($apiURL);
if ($response === false) { http_response_code(502); $error = "Could not reach API at $apiURL."; $data = []; }
else {
  $data = json_decode($response, true);
  if (!is_array($data)) { http_response_code(500); $error = "API did not return a JSON array."; $data = []; }
}

/* - SEARCH - */
$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
if ($q !== '' && !empty($data)) {
  $needle = mb_strtolower($q);
  $data = array_values(array_filter($data, function ($row) use ($needle) {
    return (mb_stripos((string)($row['name'] ?? ''), $needle) !== false)
        || (mb_stripos((string)($row['city'] ?? ''), $needle) !== false);
  }));
}

/* - SORT - */
$allowed = ['id','name','city'];
$sort    = (isset($_GET['sort']) && in_array($_GET['sort'], $allowed, true)) ? $_GET['sort'] : 'id';
$order   = (isset($_GET['order']) && strtolower($_GET['order']) === 'desc') ? 'desc' : 'asc';
if (!empty($data)) {
  usort($data, function($a,$b) use($sort,$order){
    $av = $a[$sort] ?? ''; $bv = $b[$sort] ?? '';
    $cmp = ($sort === 'id') ? ((int)$av <=> (int)$bv) : strcasecmp((string)$av, (string)$bv);
    if ($cmp===0) $cmp = ((int)($a['id']??0)) <=> ((int)($b['id']??0));
    return $order==='desc' ? -$cmp : $cmp;
  });
}

/* - PAGING - */
$limit = isset($_GET['limit']) ? max(1,(int)$_GET['limit']) : 10;
$totalRecords = count($data);
$totalPages   = max(1,(int)ceil($totalRecords/$limit));
$page         = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
$page         = min($page,$totalPages);
$startIndex   = ($page-1)*$limit;
$pageData     = array_slice($data,$startIndex,$limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>GridView</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="./styles.css" />
</head>
<body>
  <h1>Accounts</h1>

  
  <div class="controls">
    <form method="get">
      <input type="hidden" name="sort"  value="<?= h($sort) ?>">
      <input type="hidden" name="order" value="<?= h($order) ?>">
      <input type="hidden" name="page"  value="1">

      <label for="searchInput"><strong>Search:</strong></label>
      <input id="searchInput" name="q" type="text" placeholder="Search for something..." value="<?= h($q) ?>" />

      <label class="muted">Rows:
        <select name="limit" onchange="this.form.submit()">
          <?php foreach([5,10,25,50,100] as $opt): ?>
            <option value="<?= $opt ?>" <?= $opt===$limit ? 'selected':'' ?>><?= $opt ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <button class="btn btn-primary" type="submit">Search</button>
      <?php if ($q !== ''): ?>
        <a class="btn" href="<?= h(qs(['q'=>null,'page'=>1])) ?>">Clear</a>
      <?php endif; ?>

  
      <button type="button" id="clearSearch" class="btn">Clear (client)</button>
    </form>
  </div>

  <?php if (!empty($error)): ?>
    <div class="error"><?= h($error) ?></div>
  <?php endif; ?>

  <?php if ($totalRecords > 0): ?>
    <div class="muted info-line">
      Showing <?= ($startIndex+1) ?>–<?= min($startIndex+$limit, $totalRecords) ?> of <?= (int)$totalRecords ?>
      <?php if ($q !== ''): ?> • Filter: “<?= h($q) ?>”<?php endif; ?>
    </div>

    <table id="dataGrid">
      <thead>
        <tr>
          <?php foreach (['id'=>'id','name'=>'name','city'=>'city'] as $key=>$label): $next = nextOrderFor($key,$sort,$order); ?>
            <th><a href="<?= h(qs(['page'=>1,'sort'=>$key,'order'=>$next])) ?>"><?= h($label) ?><?= h(arrow($key,$sort,$order)) ?></a></th>
          <?php endforeach; ?>
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

    <div class="pager">
      <?php if ($page>1): ?><a href="<?= h(qs(['page'=>$page-1])) ?>">Previous</a><?php endif; ?>
      <?php for ($i=1;$i<=$totalPages;$i++): ?>
        <?= $i===$page ? "<strong>$i</strong>" : '<a href="'.h(qs(['page'=>$i])).'">'.$i.'</a>' ?>
      <?php endfor; ?>
      <?php if ($page<$totalPages): ?><a href="<?= h(qs(['page'=>$page+1])) ?>">Next</a><?php endif; ?>
    </div>
  <?php else: ?>
    <p class="muted">No records to display.</p>
  <?php endif; ?>

  <script src="./searchTable.js" defer></script>
</body>
</html>
