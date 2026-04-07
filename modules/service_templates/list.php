<?php
declare(strict_types=1);
$pageTitle = 'Service charge templates';
require_once dirname(__DIR__, 2) . '/includes/layout_start.php';

$hasImg = db_table_exists($pdo, 'service_images');
$hasTplImage = db_column_exists($pdo, 'service_templates', 'image');
$hasTplThumb = db_column_exists($pdo, 'service_templates', 'image_thumb');

$cat = trim((string) ($_GET['category'] ?? ''));
$page = max(1, (int) ($_GET['p'] ?? 1));
$perPage = 25;
$where = '1=1';
$params = [];
$allowedCat = ['printer', 'computer', 'cctv', 'general'];
if ($cat !== '' && in_array($cat, $allowedCat, true)) {
    $where .= ' AND category = ?';
    $params[] = $cat;
}
$countSt = $pdo->prepare("SELECT COUNT(*) FROM service_templates WHERE $where");
$countSt->execute($params);
$total = (int) $countSt->fetchColumn();
$pg = paginate($total, $page, $perPage);

if ($hasTplImage && $hasTplThumb) {
    $sql = "SELECT t.*, COALESCE(NULLIF(t.image_thumb, ''), t.image) AS thumb_path FROM service_templates t WHERE $where ORDER BY t.category, t.name LIMIT {$pg['perPage']} OFFSET {$pg['offset']}";
} elseif ($hasTplImage) {
    $sql = "SELECT t.*, t.image AS thumb_path FROM service_templates t WHERE $where ORDER BY t.category, t.name LIMIT {$pg['perPage']} OFFSET {$pg['offset']}";
} elseif ($hasImg) {
    $sql = "SELECT t.*, si.image_path AS thumb_path FROM service_templates t
        LEFT JOIN service_images si ON si.service_id = t.id AND si.is_primary = 1
        WHERE $where ORDER BY t.category, t.name LIMIT {$pg['perPage']} OFFSET {$pg['offset']}";
} else {
    $sql = "SELECT t.*, NULL AS thumb_path FROM service_templates t WHERE $where ORDER BY t.category, t.name LIMIT {$pg['perPage']} OFFSET {$pg['offset']}";
}
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
    <h1 class="h3 mb-0">Service charge templates</h1>
    <a class="btn btn-primary" href="<?= e(BASE_URL) ?>/modules/service_templates/add.php"><i class="bi bi-plus-lg me-1"></i>Add template</a>
</div>
<form class="row g-2 mb-3 align-items-end" method="get" action="">
    <div class="col-12 col-md-4">
        <label class="form-label small mb-0">Category</label>
        <select name="category" class="form-select">
            <option value="">All</option>
            <option value="printer" <?= $cat === 'printer' ? 'selected' : '' ?>>Printer</option>
            <option value="computer" <?= $cat === 'computer' ? 'selected' : '' ?>>Computer</option>
            <option value="cctv" <?= $cat === 'cctv' ? 'selected' : '' ?>>CCTV</option>
            <option value="general" <?= $cat === 'general' ? 'selected' : '' ?>>General</option>
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
                    <th class="text-center" style="width:3.5rem">Thumb</th>
                    <th data-sort="1">Name</th>
                    <th data-sort="2">Category</th>
                    <th data-sort="3" data-type="number">Default amount</th>
                    <th data-sort="4">Description</th>
                    <th class="text-end">Public</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No templates found.</td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td class="text-center align-middle">
                            <?php if (!empty($r['thumb_path'])): ?>
                                <img src="<?= e(BASE_URL) ?>/<?= e(ltrim((string) $r['thumb_path'], '/')) ?>" alt="" width="40" height="40" class="rounded border object-fit-cover" style="width:40px;height:40px;object-fit:cover">
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($r['name']) ?></td>
                        <td><span class="badge text-bg-light text-dark border"><?= e($r['category']) ?></span></td>
                        <td><?= e(number_format((float) $r['default_amount'], 2)) ?></td>
                        <td class="small text-muted"><?= e($r['description'] ?? '') ?></td>
                        <td class="text-end small">
                            <a class="text-decoration-none" href="<?= e(BASE_URL) ?>/service-template-detail.php?id=<?= (int) $r['id'] ?>" target="_blank" rel="noopener">View</a>
                        </td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="<?= e(BASE_URL) ?>/modules/service_templates/edit.php?id=<?= (int) $r['id'] ?>">Edit</a>
                            <a class="btn btn-sm btn-outline-danger" href="<?= e(BASE_URL) ?>/modules/service_templates/delete.php?id=<?= (int) $r['id'] ?>" onclick="return confirm('Delete this template?');">Delete</a>
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
                <a class="page-link" href="?<?= e(http_build_query(['category' => $cat, 'p' => $i])) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
<?php require_once dirname(__DIR__, 2) . '/includes/layout_end.php'; ?>
