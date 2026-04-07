<?php
declare(strict_types=1);
$pageTitle = 'Web bookings';
require_once dirname(__DIR__, 2) . '/includes/layout_start.php';

if (!db_table_exists($pdo, 'web_bookings')) {
    echo '<div class="alert alert-warning">Run <code>sql/upgrade_v4_public.sql</code> to enable bookings.</div>';
    require_once dirname(__DIR__, 2) . '/includes/layout_end.php';
    exit;
}

$q = trim((string) ($_GET['q'] ?? ''));
$em = ($_GET['emergency'] ?? '') === '1';
$page = max(1, (int) ($_GET['p'] ?? 1));
$perPage = 20;
$where = '1=1';
$params = [];
if ($q !== '') {
    $where .= ' AND (b.booking_number LIKE ? OR b.customer_name LIKE ? OR b.phone LIKE ?)';
    $like = '%' . $q . '%';
    $params = [$like, $like, $like];
}
$wbHasEmergency = db_column_exists($pdo, 'web_bookings', 'is_emergency');
if ($em && $wbHasEmergency) {
    $where .= ' AND b.is_emergency = 1';
}

$countSt = $pdo->prepare("SELECT COUNT(*) FROM web_bookings b WHERE $where");
$countSt->execute($params);
$total = (int) $countSt->fetchColumn();
$pg = paginate($total, $page, $perPage);

$wbHasAssignTech = db_column_exists($pdo, 'web_bookings', 'assigned_technician_id');
$joinTech = $wbHasAssignTech ? ' LEFT JOIN technicians t ON t.id = b.assigned_technician_id' : '';
$selTech = $wbHasAssignTech ? ', t.name AS tech_name' : '';
$orderBy = $wbHasEmergency ? 'b.is_emergency DESC, b.created_at DESC' : 'b.created_at DESC';
$sql = "SELECT b.*{$selTech}
        FROM web_bookings b{$joinTech}
        WHERE $where
        ORDER BY {$orderBy}
        LIMIT {$pg['perPage']} OFFSET {$pg['offset']}";
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-2 mb-3">
    <h1 class="h3 mb-0">Online bookings</h1>
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_URL) ?>/book.php" target="_blank" rel="noopener">Open public form</a>
</div>
<form class="row g-2 mb-3 align-items-end" method="get">
    <div class="col-md-4">
        <input type="search" name="q" class="form-control" placeholder="Booking #, name, phone" value="<?= e($q) ?>">
    </div>
    <div class="col-auto">
        <div class="form-check"<?= $wbHasEmergency ? '' : ' title="Add is_emergency column (see sql/upgrade_web_bookings_technician.sql)"' ?>>
            <input class="form-check-input" type="checkbox" name="emergency" value="1" id="emf" <?= $em ? 'checked' : '' ?> <?= $wbHasEmergency ? '' : 'disabled' ?>>
            <label class="form-check-label <?= $wbHasEmergency ? '' : 'text-muted' ?>" for="emf">Emergency only</label>
        </div>
    </div>
    <div class="col-auto"><button type="submit" class="btn btn-outline-secondary">Filter</button></div>
</form>
<div class="card vk-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 table-sm">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Booking</th>
                    <th>Customer</th>
                    <th>Service</th>
                    <th>Status</th>
                    <th class="text-nowrap">WhatsApp</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No bookings.</td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                    <?php $isEmerg = $wbHasEmergency && isset($r['is_emergency']) && (int) $r['is_emergency'] === 1; ?>
                    <tr class="<?= $isEmerg ? 'table-danger' : '' ?>">
                        <td><?= (int) $r['id'] ?></td>
                        <td><code><?= e($r['booking_number']) ?></code><?= $isEmerg ? ' <span class="badge text-bg-danger">EMERGENCY</span>' : '' ?></td>
                        <td><?= e($r['customer_name']) ?><div class="small text-muted"><?= e($r['phone']) ?></div></td>
                        <td class="text-capitalize"><?= e(str_replace('_', ' ', $r['service_type'])) ?></td>
                        <td><span class="badge text-bg-secondary"><?= e($r['status']) ?></span></td>
                        <td>
                            <?php
                            $waUrl = vk_whatsapp_me_link((string) $r['phone'], vk_whatsapp_web_booking_message($r));
                            ?>
                            <a class="btn btn-sm btn-success" href="<?= e($waUrl) ?>" target="_blank" rel="noopener noreferrer" title="Send WhatsApp to customer"><i class="bi bi-whatsapp" aria-hidden="true"></i></a>
                        </td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="<?= e(BASE_URL) ?>/modules/bookings/view.php?id=<?= (int) $r['id'] ?>">Manage</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php if ($pg['pages'] > 1): ?>
<nav class="mt-3"><ul class="pagination pagination-sm">
    <?php for ($i = 1; $i <= $pg['pages']; $i++): ?>
        <li class="page-item <?= $i === $pg['page'] ? 'active' : '' ?>">
            <a class="page-link" href="?<?= e(http_build_query(['q' => $q, 'emergency' => $em ? '1' : '', 'p' => $i])) ?>"><?= $i ?></a>
        </li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>
<?php require_once dirname(__DIR__, 2) . '/includes/layout_end.php'; ?>
