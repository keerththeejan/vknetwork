<?php
declare(strict_types=1);
$pageTitle = 'System Settings';
require_once dirname(__DIR__, 2) . '/includes/layout_start.php';

$pdo = db();
$s = vk_settings_all($pdo);
$defaults = static function (string $k, string $d = '') use ($s): string {
    return array_key_exists($k, $s) ? (string) $s[$k] : $d;
};

$hasTable = vk_settings_table_ready($pdo);
?>
<?php if (!$hasTable): ?>
<div class="alert alert-danger">
    <strong>Settings table missing.</strong> Import <code>sql/upgrade_settings.sql</code> into your database, then reload this page.
</div>
<?php endif; ?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <h1 class="h4 mb-0">System Settings</h1>
    <span class="text-muted small">Saves via AJAX — no page reload</span>
</div>

<ul class="nav nav-tabs mb-3" id="settingsTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-general" data-bs-toggle="tab" data-bs-target="#pane-general" type="button" role="tab">General</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-seo" data-bs-toggle="tab" data-bs-target="#pane-seo" type="button" role="tab">SEO</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-wa" data-bs-toggle="tab" data-bs-target="#pane-wa" type="button" role="tab">WhatsApp</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-mail" data-bs-toggle="tab" data-bs-target="#pane-mail" type="button" role="tab">Email</button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="pane-general" role="tabpanel">
        <div class="card vk-card vk-settings-card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label" for="site_name">Site name</label>
                    <input type="text" class="form-control" id="site_name" value="<?= e($defaults('site_name', 'VK Network')) ?>" maxlength="255" autocomplete="organization">
                    <div class="form-text">Shown in admin, public navbar, and SEO site name.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="analytics_domain">Plausible domain (optional)</label>
                    <input type="text" class="form-control" id="analytics_domain" value="<?= e($defaults('analytics_domain')) ?>" placeholder="example.com">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="analytics_script_src">Analytics script URL</label>
                    <input type="text" class="form-control" id="analytics_script_src" value="<?= e($defaults('analytics_script_src', 'https://plausible.io/js/script.js')) ?>">
                </div>
                <button type="button" class="btn btn-primary btn-save-tab" data-tab="general">Save</button>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="pane-seo" role="tabpanel">
        <div class="card vk-card vk-settings-card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label" for="seo_site_title">Site title (prefix)</label>
                    <input type="text" class="form-control" id="seo_site_title" value="<?= e($defaults('seo_site_title')) ?>" maxlength="255" placeholder="Optional — overrides default brand in page titles">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="seo_meta_description">Meta description</label>
                    <textarea class="form-control" id="seo_meta_description" rows="3" maxlength="1024"><?= e($defaults('seo_meta_description')) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="seo_meta_keywords">Meta keywords</label>
                    <input type="text" class="form-control" id="seo_meta_keywords" value="<?= e($defaults('seo_meta_keywords')) ?>" maxlength="512">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="seo_og_image">OG image URL</label>
                    <input type="text" class="form-control" id="seo_og_image" value="<?= e($defaults('seo_og_image')) ?>" maxlength="512" placeholder="/assets/images/... or https://...">
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="seo_auto_enabled" <?= $defaults('seo_auto_enabled', '1') === '1' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="seo_auto_enabled">Enable SEO auto-config (local keyword booster)</label>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="seo_locations">SEO locations (comma separated slugs)</label>
                    <input type="text" class="form-control" id="seo_locations" value="<?= e($defaults('seo_locations', 'jaffna,vavuniya,kilinochchi')) ?>" placeholder="jaffna,vavuniya,kilinochchi">
                    <div class="form-text">Used for auto local landing pages and sitemap.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="seo_service_slugs">SEO service slugs (comma separated)</label>
                    <input type="text" class="form-control" id="seo_service_slugs" value="<?= e($defaults('seo_service_slugs', 'computer-repair,laptop-repair,printer-repair,it-service')) ?>" placeholder="computer-repair,laptop-repair,printer-repair,it-service">
                </div>
                <button type="button" class="btn btn-primary btn-save-tab" data-tab="seo">Save</button>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="pane-wa" role="tabpanel">
        <div class="card vk-card vk-settings-card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label" for="whatsapp_number">WhatsApp number</label>
                    <input type="text" class="form-control" id="whatsapp_number" value="<?= e($defaults('whatsapp_number')) ?>" maxlength="32" placeholder="9477XXXXXXX or 077...">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="whatsapp_default_message">Default message template</label>
                    <textarea class="form-control" id="whatsapp_default_message" rows="4" maxlength="2000"><?= e($defaults('whatsapp_default_message')) ?></textarea>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-primary btn-save-tab" data-tab="whatsapp">Save</button>
                    <button type="button" class="btn btn-success" id="btnTestWhatsapp"><i class="bi bi-whatsapp me-1"></i>Test WhatsApp</button>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="pane-mail" role="tabpanel">
        <div class="card vk-card vk-settings-card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label" for="smtp_host">SMTP host</label>
                        <input type="text" class="form-control" id="smtp_host" value="<?= e($defaults('smtp_host')) ?>" placeholder="smtp.gmail.com" autocomplete="off">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="smtp_port">SMTP port</label>
                        <input type="number" class="form-control" id="smtp_port" value="<?= e($defaults('smtp_port', '587')) ?>" min="1" max="65535">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="smtp_username">SMTP username</label>
                        <input type="text" class="form-control" id="smtp_username" value="<?= e($defaults('smtp_username')) ?>" autocomplete="username">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="smtp_password">SMTP password</label>
                        <input type="password" class="form-control" id="smtp_password" value="" placeholder="Leave blank to keep current" autocomplete="new-password">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="email_from">From email</label>
                        <input type="email" class="form-control" id="email_from" value="<?= e($defaults('email_from')) ?>" placeholder="noreply@yourdomain.com">
                    </div>
                </div>
                <div class="mt-3 d-flex flex-wrap gap-2 align-items-end">
                    <button type="button" class="btn btn-primary btn-save-tab" data-tab="email">Save</button>
                    <div class="flex-grow-1" style="min-width: 200px;">
                        <label class="form-label small mb-0" for="mail_test_to">Send test to</label>
                        <input type="email" class="form-control form-control-sm" id="mail_test_to" placeholder="your@email.com">
                    </div>
                    <button type="button" class="btn btn-outline-primary" id="btnMailTest"><i class="bi bi-envelope-check me-1"></i>Send test email</button>
                </div>
                <p class="small text-muted mt-3 mb-0">Requires <code>composer install</code> for PHPMailer. Gmail often needs an app password.</p>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = '<script src="' . e(BASE_URL) . '/assets/js/system-settings.js"></script>';
require_once dirname(__DIR__, 2) . '/includes/layout_end.php';
