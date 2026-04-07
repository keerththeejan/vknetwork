<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/layout_init.php';
require_once __DIR__ . '/service_image_upload.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    flash_set('error', 'Invalid template.');
    redirect('/modules/service_templates/list.php');
}
try {
    if (db_column_exists($pdo, 'service_templates', 'image')) {
        $cols = 'image';
        if (db_column_exists($pdo, 'service_templates', 'image_thumb')) {
            $cols = 'image, image_thumb';
        }
        $imSt = $pdo->prepare("SELECT $cols FROM service_templates WHERE id = ?");
        $imSt->execute([$id]);
        $row = $imSt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $main = (string) ($row['image'] ?? '');
            $thumb = isset($row['image_thumb']) ? (string) $row['image_thumb'] : '';
            st_service_image_unlink_pair($main !== '' ? $main : null, $thumb !== '' ? $thumb : null);
        }
    }
    if (db_table_exists($pdo, 'service_images')) {
        $paths = $pdo->prepare('SELECT image_path FROM service_images WHERE service_id = ?');
        $paths->execute([$id]);
        while ($row = $paths->fetch(PDO::FETCH_ASSOC)) {
            $rel = (string) ($row['image_path'] ?? '');
            if ($rel === '') {
                continue;
            }
            $norm = ltrim(str_replace('\\', '/', $rel), '/');
            if (!str_starts_with($norm, 'uploads/services/')) {
                continue;
            }
            $full = ROOT_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $norm);
            if (is_file($full)) {
                @unlink($full);
            }
        }
    }
    $pdo->prepare('DELETE FROM service_templates WHERE id = ?')->execute([$id]);
    flash_set('success', 'Template removed.');
} catch (Throwable $e) {
    flash_set('error', 'Could not delete template.');
}
redirect('/modules/service_templates/list.php');
