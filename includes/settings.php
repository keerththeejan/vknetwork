<?php
declare(strict_types=1);

/**
 * Key/value settings from `settings` table. Cache per request.
 */

/** @var array<string, string>|null */
$GLOBALS['_vk_settings_cache'] = null;

function vk_settings_table_ready(PDO $pdo): bool
{
    return db_table_exists($pdo, 'settings');
}

function vk_settings_invalidate_cache(): void
{
    $GLOBALS['_vk_settings_cache'] = null;
}

/**
 * @return array<string, string>
 */
function vk_settings_all(PDO $pdo): array
{
    if ($GLOBALS['_vk_settings_cache'] !== null) {
        return $GLOBALS['_vk_settings_cache'];
    }
    $out = [];
    if (!vk_settings_table_ready($pdo)) {
        $GLOBALS['_vk_settings_cache'] = $out;

        return $out;
    }
    try {
        $st = $pdo->query('SELECT key_name, `value` FROM settings');
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $k = (string) ($row['key_name'] ?? '');
            if ($k !== '') {
                $out[$k] = (string) ($row['value'] ?? '');
            }
        }
    } catch (Throwable $e) {
        $out = [];
    }
    $GLOBALS['_vk_settings_cache'] = $out;

    return $out;
}

/** Read one setting; empty DB value falls through to $default. */
function vk_settings_get(PDO $pdo, string $key, ?string $default = null): ?string
{
    $all = vk_settings_all($pdo);
    if (!array_key_exists($key, $all) || $all[$key] === '') {
        return $default;
    }

    return $all[$key];
}

function vk_settings_set(PDO $pdo, string $key, string $value): void
{
    if (!vk_settings_table_ready($pdo)) {
        return;
    }
    $st = $pdo->prepare(
        'INSERT INTO settings (key_name, `value`) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_at = CURRENT_TIMESTAMP'
    );
    $st->execute([$key, $value]);
    vk_settings_invalidate_cache();
}

/**
 * Public / front controller: uses db() when available.
 * Safe if DB is down — returns $default.
 */
function vk_app_setting(string $key, ?string $default = null): ?string
{
    try {
        if (!function_exists('db')) {
            return $default;
        }
        $pdo = db();

        return vk_settings_get($pdo, $key, $default);
    } catch (Throwable $e) {
        return $default;
    }
}
