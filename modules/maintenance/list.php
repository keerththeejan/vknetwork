<?php
declare(strict_types=1);
$pageTitle = 'Maintenance contracts';
require_once dirname(__DIR__, 2) . '/includes/layout_start.php';

$q = trim((string) ($_GET['q'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$page = max(1, (int) ($_GET['p'] ?? 1));
$perPage = 15;

$where = '1=1';
$params = [];
if ($q !== '') {
    $where .= ' AND (m.contract_number LIKE ? OR m.title LIKE ? OR c.name LIKE ?)';
    $like = '%' . $q . '%';
    $params = [$like, $like, $like];
}
if ($status !== '' && in_array($status, ['active', 'paused', 'expired', 'cancelled'], true)) {
    $where .= ' AND m.status = ?';
    $params[] = $status;
}

$countSt = $pdo->prepare("SELECT COUNT(*) FROM maintenance_contracts m JOIN customers c ON c.id = m.customer_id WHERE $where");
$countSt->execute($params);
$total = (int) $countSt->fetchColumn();
$pg = paginate($total, $page, $perPage);

$sql = "SELECT m.*, c.name AS customer_name
        FROM maintenance_contracts m
        JOIN customers c ON c.id = m.customer_id
        WHERE $where
        ORDER BY m.id DESC
        LIMIT {$pg['perPage']} OFFSET {$pg['offset']}";
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();

$today = date('Y-m-d');
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
    <h1 class="h3 mb-0">Maintenance contracts</h1>
    <a class="btn btn-primary" href="<?= e(BASE_URL) ?>/modules/maintenance/add.php"><i class="bi bi-calendar-check me-1"></i>New contract</a>
</div>
<form class="row g-2 mb-3 align-items-end" method="get" action="">
    <div class="col-12 col-md-4">
        <label class="form-label small mb-0">Search</label>
        <input type="search" name="q" class="form-control" placeholder="Contract #, title, customer" value="<?= e($q) ?>">
    </div>
    <div class="col-12 col-md-3">
        <label class="form-label small mb-0">Status</label>
        <select name="status" class="form-select">
            <option value="">All</option>
            <?php foreach (['active' => 'Active', 'paused' => 'Paused', 'expired' => 'Expired', 'cancelled' => 'Cancelled'] as $k => $lab): ?>
                <option value="<?= e($k) ?>" <?= $status === $k ? 'selected' : '' ?>><?= e($lab) ?></option>
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
                    <th data-sort="0">Contract</th>
                    <th data-sort="1">Customer</th>
                    <th data-sort="2">Type</th>
                    <th data-sort="3">Next service</th>
                    <th data-sort="4">Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No contracts found.</td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                    <?php
                    $next = $r['next_service_date'] ?? null;
                    $rowClass = '';
                    if ($r['status'] === 'active' && $next && $next <= $today) {
                        $rowClass = 'table-warning';
                    }
                    ?>
                    <tr class="<?= e($rowClass) ?>">
                        <td><code><?= e($r['contract_number']) ?></code></td>
                        <td><?= e($r['customer_name']) ?></td>
                        <td><?= e(str_replace('_', ' ', $r['contract_type'])) ?></td>
                        <td><?= $next ? e($next) : '—' ?></td>
                        <td><span class="badge text-bg-<?= $r['status'] === 'active' ? 'success' : ($r['status'] === 'paused' ? 'warning text-dark' : 'secondary') ?>"><?= e($r['status']) ?></span></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="<?= e(BASE_URL) ?>/modules/maintenance/edit.php?id=<?= (int) $r['id'] ?>">Manage</a>
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
                <a class="page-link" href="?<?= e(http_build_query(['q' => $q, 'status' => $status, 'p' => $i])) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
<?php require_once dirname(__DIR__, 2) . '/includes/layout_end.php'; ?>
