<?php
declare(strict_types=1);
$pageTitle = 'Repair jobs';
require_once dirname(__DIR__, 2) . '/includes/layout_start.php';

$q = trim((string) ($_GET['q'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$page = max(1, (int) ($_GET['p'] ?? 1));
$perPage = 15;

$where = '1=1';
$params = [];
if ($q !== '') {
    $where .= ' AND (r.job_number LIKE ? OR c.name LIKE ? OR r.problem_description LIKE ?)';
    $like = '%' . $q . '%';
    $params = [$like, $like, $like];
}
$statusAllowed = ['pending', 'diagnosing', 'in_progress', 'completed', 'delivered'];
if ($status !== '' && in_array($status, $statusAllowed, true)) {
    $where .= ' AND r.status = ?';
    $params[] = $status;
}

$countSt = $pdo->prepare("SELECT COUNT(*) FROM repair_jobs r JOIN customers c ON c.id = r.customer_id WHERE $where");
$countSt->execute($params);
$total = (int) $countSt->fetchColumn();
$pg = paginate($total, $page, $perPage);

$sql = "SELECT r.*, c.name AS customer_name, t.name AS technician_name
        FROM repair_jobs r
        JOIN customers c ON c.id = r.customer_id
        LEFT JOIN technicians t ON t.id = r.technician_id
        WHERE $where
        ORDER BY r.id DESC
        LIMIT {$pg['perPage']} OFFSET {$pg['offset']}";
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
    <h1 class="h3 mb-0">Repair &amp; service jobs</h1>
    <a class="btn btn-primary" href="<?= e(BASE_URL) ?>/modules/repairs/add.php"><i class="bi bi-tools me-1"></i>New repair job</a>
</div>
<form class="row g-2 mb-3 align-items-end" method="get" action="">
    <div class="col-12 col-md-4">
        <label class="form-label small mb-0">Search</label>
        <input type="search" name="q" class="form-control" placeholder="Job #, customer, problem" value="<?= e($q) ?>">
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label small mb-0">Status</label>
        <select name="status" class="form-select">
            <option value="">All</option>
            <?php foreach ($statusAllowed as $s): ?>
                <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(str_replace('_', ' ', $s)) ?></option>
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
                    <th data-sort="0">Job</th>
                    <th data-sort="1">Customer</th>
                    <th data-sort="2">Device</th>
                    <th data-sort="3">Tech</th>
                    <th data-sort="4">Status</th>
                    <th data-sort="5" data-type="number">Est.</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No jobs found.</td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                    <?php $bc = repair_status_badge_class((string) $r['status']); ?>
                    <tr>
                        <td><code><?= e($r['job_number']) ?></code></td>
                        <td><?= e($r['customer_name']) ?></td>
                        <td><?= e(repair_device_type_label((string) $r['device_type'])) ?></td>
                        <td class="small"><?= e($r['technician_name'] ?? '—') ?></td>
                        <td><span class="badge text-bg-<?= e($bc) ?>"><?= e(str_replace('_', ' ', $r['status'])) ?></span></td>
                        <td><?= e(number_format((float) $r['estimated_cost'], 2)) ?></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="<?= e(BASE_URL) ?>/modules/repairs/view.php?id=<?= (int) $r['id'] ?>">View</a>
                            <a class="btn btn-sm btn-outline-secondary" href="<?= e(BASE_URL) ?>/modules/repairs/edit.php?id=<?= (int) $r['id'] ?>">Edit</a>
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
