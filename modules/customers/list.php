<?php
declare(strict_types=1);
$pageTitle = 'Customers';
require_once dirname(__DIR__, 2) . '/includes/layout_start.php';

$q = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['p'] ?? 1));
$perPage = 15;
$where = '1=1';
$params = [];
if ($q !== '') {
    $where .= ' AND (c.name LIKE ? OR c.phone LIKE ? OR c.email LIKE ?)';
    $like = '%' . $q . '%';
    $params = [$like, $like, $like];
}
$countSt = $pdo->prepare("SELECT COUNT(*) FROM customers c WHERE $where");
$countSt->execute($params);
$total = (int) $countSt->fetchColumn();
$pg = paginate($total, $page, $perPage);

$sql = "SELECT c.*, a.code AS account_code, a.current_balance
        FROM customers c
        JOIN accounts a ON a.customer_id = c.id
        WHERE $where
        ORDER BY c.id DESC
        LIMIT {$pg['perPage']} OFFSET {$pg['offset']}";
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
    <h1 class="h3 mb-0">Customers</h1>
    <a class="btn btn-primary" href="<?= e(BASE_URL) ?>/modules/customers/add.php"><i class="bi bi-person-plus me-1"></i>Add customer</a>
</div>
<form class="row g-2 mb-3" method="get" action="">
    <div class="col-12 col-md-6 col-lg-4">
        <input type="search" name="q" class="form-control" placeholder="Search name, phone, email" value="<?= e($q) ?>">
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary" type="submit">Search</button>
    </div>
</form>
<div class="card vk-card">
    <div class="table-responsive table-responsive-stack">
        <table class="table table-hover mb-0 sortable">
            <thead class="table-light">
                <tr>
                    <th data-sort="0">ID</th>
                    <th data-sort="1">Name</th>
                    <th data-sort="2">Phone</th>
                    <th data-sort="3">Email</th>
                    <th data-sort="4">Account</th>
                    <th data-sort="5" data-type="number">Balance due</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No customers found.</td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= (int) $r['id'] ?></td>
                        <td><?= e($r['name']) ?></td>
                        <td><?= e($r['phone'] ?? '') ?></td>
                        <td><?= e($r['email'] ?? '') ?></td>
                        <td><code><?= e($r['account_code']) ?></code></td>
                        <td><?= e(number_format((float) $r['current_balance'], 2)) ?></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-secondary" href="<?= e(BASE_URL) ?>/modules/customers/profile.php?id=<?= (int) $r['id'] ?>">Profile</a>
                            <a class="btn btn-sm btn-outline-primary" href="<?= e(BASE_URL) ?>/modules/customers/edit.php?id=<?= (int) $r['id'] ?>">Edit</a>
                            <a class="btn btn-sm btn-outline-danger" href="<?= e(BASE_URL) ?>/modules/customers/delete.php?id=<?= (int) $r['id'] ?>" onclick="return confirm('Delete this customer? Blocked if invoices exist.');">Delete</a>
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
                <a class="page-link" href="?<?= e(http_build_query(['q' => $q, 'p' => $i])) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
<?php require_once dirname(__DIR__, 2) . '/includes/layout_end.php'; ?>
