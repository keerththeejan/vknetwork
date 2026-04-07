<?php
declare(strict_types=1);
$pageTitle = 'Service gallery';
require_once dirname(__DIR__, 2) . '/includes/layout_init.php';

if (!db_table_exists($pdo, 'web_services')) {
    flash_set('warning', 'web_services table is missing.');
    redirect('/modules/dashboard.php');
}
vk_service_gallery_auto_migrate($pdo);

$serviceId = max(0, (int) ($_GET['service_id'] ?? $_POST['service_id'] ?? 0));
$services = $pdo->query('SELECT id, name, slug FROM web_services WHERE active = 1 ORDER BY sort_order ASC, id ASC')->fetchAll();
if ($serviceId <= 0 && $services) {
    $serviceId = (int) $services[0]['id'];
}

if (($_GET['action'] ?? '') === 'delete' && isset($_GET['id'])) {
    $imgId = max(0, (int) $_GET['id']);
    if ($imgId > 0) {
        $st = $pdo->prepare('SELECT image_path FROM service_gallery WHERE id = ? LIMIT 1');
        $st->execute([$imgId]);
        $row = $st->fetch();
        if ($row) {
            $path = trim((string) ($row['image_path'] ?? ''));
            if ($path !== '' && str_starts_with(str_replace('\\', '/', $path), 'uploads/services/gallery/')) {
                $full = ROOT_PATH . '/' . ltrim(str_replace('\\', '/', $path), '/');
                if (is_file($full)) {
                    @unlink($full);
                }
            }
            $pdo->prepare('DELETE FROM service_gallery WHERE id = ?')->execute([$imgId]);
            flash_set('success', 'Gallery image deleted.');
        }
    }
    redirect('/modules/web_services/gallery.php?service_id=' . $serviceId);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = max(0, (int) ($_POST['service_id'] ?? 0));
    if ($serviceId <= 0) {
        flash_set('error', 'Select a service first.');
        redirect('/modules/web_services/gallery.php');
    }

    $files = $_FILES['gallery_images'] ?? null;
    if (!is_array($files) || !isset($files['name']) || !is_array($files['name'])) {
        flash_set('error', 'Choose one or more images.');
        redirect('/modules/web_services/gallery.php?service_id=' . $serviceId);
    }

    $uploaded = 0;
    $errors = [];
    $count = min(count($files['name']), 12);
    for ($i = 0; $i < $count; $i++) {
        $f = [
            'name' => $files['name'][$i] ?? '',
            'type' => $files['type'][$i] ?? '',
            'tmp_name' => $files['tmp_name'][$i] ?? '',
            'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files['size'][$i] ?? 0,
        ];
        if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $res = vk_service_gallery_process_upload($f, $serviceId);
        if (($res['error'] ?? null) !== null) {
            $errors[] = (string) $res['error'];
            continue;
        }
        $title = trim(pathinfo((string) $f['name'], PATHINFO_FILENAME));
        $pdo->prepare('INSERT INTO service_gallery (service_id, image_path, title) VALUES (?, ?, ?)')
            ->execute([$serviceId, (string) $res['path'], $title]);
        $uploaded++;
    }
    if ($uploaded > 0) {
        flash_set('success', 'Uploaded ' . $uploaded . ' gallery image(s).');
    } elseif ($errors) {
        flash_set('error', $errors[0]);
    } else {
        flash_set('warning', 'No images selected.');
    }
    redirect('/modules/web_services/gallery.php?service_id=' . $serviceId);
}

$galleryRows = [];
if ($serviceId > 0) {
    $st = $pdo->prepare('SELECT id, image_path, title, created_at FROM service_gallery WHERE service_id = ? ORDER BY id DESC');
    $st->execute([$serviceId]);
    $galleryRows = $st->fetchAll();
}
require_once dirname(__DIR__, 2) . '/includes/layout_start.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 mb-0">Service gallery images</h1>
    <span class="text-muted small">Uploads are optimized to WebP/JPG and saved in <code>uploads/services/gallery/</code></span>
</div>

<div class="card vk-card mb-3">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="service_id">Service</label>
                <select class="form-select" id="service_id" name="service_id" required>
                    <?php foreach ($services as $svc): ?>
                        <option value="<?= (int) $svc['id'] ?>" <?= (int) $svc['id'] === $serviceId ? 'selected' : '' ?>>
                            <?= e((string) $svc['name']) ?> (<?= e((string) $svc['slug']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label" for="gallery_images">Gallery images (multiple)</label>
                <input class="form-control" id="gallery_images" name="gallery_images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple>
                <div class="form-text">Max 3MB each, JPG/PNG/WebP. Automatically resized and optimized.</div>
            </div>
            <div class="col-12">
                <button class="btn btn-primary" type="submit"><i class="bi bi-upload me-1"></i>Upload images</button>
                <a class="btn btn-outline-secondary ms-2" href="<?= e(BASE_URL) ?>/service-details.php?id=<?= (int) $serviceId ?>" target="_blank" rel="noopener">View public page</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    <?php if (!$galleryRows): ?>
        <div class="col-12"><div class="alert alert-info mb-0">No uploaded images for this service yet. Fallback gallery will be shown on the public page.</div></div>
    <?php else: ?>
        <?php foreach ($galleryRows as $g): ?>
            <?php $src = trim((string) ($g['image_path'] ?? '')); ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card vk-card h-100">
                    <div class="ratio ratio-16x9 bg-body-secondary">
                        <?php if ($src !== '' && public_asset_file_exists($src)): ?>
                            <img src="<?= e(public_asset_url($src)) ?>" alt="<?= e((string) ($g['title'] ?? 'Gallery image')) ?>" class="w-100 h-100 object-fit-cover" loading="lazy">
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="fw-semibold small"><?= e((string) ($g['title'] ?? '')) ?></div>
                        <div class="text-muted small"><?= e((string) ($g['created_at'] ?? '')) ?></div>
                    </div>
                    <div class="card-footer bg-transparent border-0 pt-0">
                        <a class="btn btn-sm btn-outline-danger" href="<?= e(BASE_URL) ?>/modules/web_services/gallery.php?service_id=<?= (int) $serviceId ?>&action=delete&id=<?= (int) $g['id'] ?>" onclick="return confirm('Delete this image?');">Delete</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php require_once dirname(__DIR__, 2) . '/includes/layout_end.php'; ?>
