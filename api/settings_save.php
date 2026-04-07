<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/includes/init.php';
require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed'], JSON_THROW_ON_ERROR);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '[]', true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON'], JSON_THROW_ON_ERROR);
    exit;
}

$tab = (string) ($data['tab'] ?? '');
$settings = $data['settings'] ?? null;
if (!is_array($settings)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing settings object'], JSON_THROW_ON_ERROR);
    exit;
}

$byTab = [
    'general' => ['site_name', 'analytics_domain', 'analytics_script_src'],
    'seo' => ['seo_site_title', 'seo_meta_description', 'seo_meta_keywords', 'seo_og_image', 'seo_auto_enabled', 'seo_locations', 'seo_service_slugs'],
    'whatsapp' => ['whatsapp_number', 'whatsapp_default_message'],
    'email' => ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'email_from'],
];

if (!isset($byTab[$tab])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Unknown tab'], JSON_THROW_ON_ERROR);
    exit;
}

$pdo = db();
if (!vk_settings_table_ready($pdo)) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'Settings table missing. Import sql/upgrade_settings.sql'], JSON_THROW_ON_ERROR);
    exit;
}

$allowed = array_flip($byTab[$tab]);

foreach ($settings as $key => $value) {
    if (!is_string($key) || !isset($allowed[$key])) {
        continue;
    }
    if (!is_string($value) && !is_numeric($value)) {
        continue;
    }
    $str = (string) $value;
    if ($key === 'smtp_password' && $str === '') {
        continue;
    }
    vk_settings_set($pdo, $key, $str);
}

echo json_encode(['ok' => true, 'saved' => $byTab[$tab]], JSON_THROW_ON_ERROR);
