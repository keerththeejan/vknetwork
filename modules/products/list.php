<?php
declare(strict_types=1);
$pageTitle = 'Products';
require_once dirname(__DIR__, 2) . '/includes/layout_start.php';

$q = trim((string) ($_GET['q'] ?? ''));
$cat = trim((string) ($_GET['category'] ?? ''));
$page = max(1, (int) ($_GET['p'] ?? 1));
$perPage = 15;
$where = '1=1';
$params = [];
if ($q !== '') {
    $where .= ' AND (name LIKE ? OR category LIKE ?)';
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
}
if ($cat !== '') {
    $where .= ' AND category = ?';
    $params[] = $cat;
}
$countSt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE $where");
$countSt->execute($params);
$total = (int) $countSt->fetchColumn();
$pg = paginate($total, $page, $perPage);

$sql = "SELECT * FROM products WHERE $where ORDER BY id DESC LIMIT {$pg['perPage']} OFFSET {$pg['offset']}";
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();

$cats = $pdo->query('SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category <> "" ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
    <h1 class="h3 mb-0">Parts &amp; products</h1>
    <a class="btn btn-primary" href="<?= e(BASE_URL) ?>/modules/products/add.php"><i class="bi bi-plus-lg me-1"></i>Add product</a>
</div>
<form class="row g-2 mb-3 align-items-end" method="get" action="">
    <div class="col-12 col-md-4">
        <label class="form-label small mb-0">Search</label>
        <input type="search" name="q" class="form-control" placeholder="Name or category" value="<?= e($q) ?>">
    </div>
    <div class="col-12 col-md-3">
        <label class="form-label small mb-0">Category</label>
        <select name="category" class="form-select">
            <option value="">All</option>
            <?php foreach ($cats as $c): ?>
                <option value="<?= e((string) $c) ?>" <?= $cat === (string) $c ? 'selected' : '' ?>><?= e((string) $c) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary" type="submit">Filter</button>
    </div>
</form>
<div class="card vk-card">
    <div class="table-responsive table-responsive-stack">
        <table class="table table-hover mb-0 sortable">
            <thead class="table-light">
                <tr>
                    <th data-sort="0">ID</th>
                    <th data-sort="1">Name</th>
                    <th data-sort="2" data-type="number">Price</th>
                    <th data-sort="3" data-type="number">Stock</th>
                    <th data-sort="4" data-type="number">Low at</th>
                    <th data-sort="5">Category</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No products found.</td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                    <?php
                    $lowAt = isset($r['low_stock_threshold']) ? (int) $r['low_stock_threshold'] : 5;
                    $isLow = (int) $r['stock'] <= $lowAt;
                    ?>
                    <tr class="<?= $isLow ? 'table-warning' : '' ?>">
                        <td><?= (int) $r['id'] ?></td>
                        <td><?= e($r['name']) ?><?= $isLow ? ' <span class="badge text-bg-warning text-dark">Low stock</span>' : '' ?></td>
                        <td><?= e(number_format((float) $r['price'], 2)) ?></td>
                        <td><?= (int) $r['stock'] ?></td>
                        <td><?= (int) $lowAt ?></td>
                        <td><?= e($r['category'] ?? '—') ?></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="<?= e(BASE_URL) ?>/modules/products/edit.php?id=<?= (int) $r['id'] ?>">Edit</a>
                            <a class="btn btn-sm btn-outline-danger" href="<?= e(BASE_URL) ?>/modules/products/delete.php?id=<?= (int) $r['id'] ?>" onclick="return confirm('Delete this product?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php if ($pg['pages'] > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm flex-wrap">
        <?php for ($i = 1; $i <= $pg['pages']; $i++): ?>
            <li class="page-item <?= $i === $pg['page'] ? 'active' : '' ?>">
                <a class="page-link" href="?<?= e(http_build_query(['q' => $q, 'category' => $cat, 'p' => $i])) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
<?php require_once dirname(__DIR__, 2) . '/includes/layout_end.php'; ?>
