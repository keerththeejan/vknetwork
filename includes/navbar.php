<?php
declare(strict_types=1);
/** @var array|null $currentUser */
$cu = $currentUser ?? null;
?>
<nav class="navbar navbar-expand-lg navbar-dark vk-navbar border-bottom border-secondary border-opacity-25 sticky-top">
    <div class="container-fluid px-3">
        <button class="btn btn-outline-light d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas" aria-label="Open menu">
            <i class="bi bi-list fs-4"></i>
        </button>
        <a class="navbar-brand d-flex align-items-center gap-2 py-1" href="<?= e(BASE_URL) ?>/modules/dashboard.php">
            <span class="vk-logo-sm rounded-circle d-flex align-items-center justify-content-center fw-bold text-white">VK</span>
            <span class="d-none d-sm-inline">
                <span class="vk-brand-red fw-bold">IT Network</span>
                <small class="d-block text-white-50 text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.08em;">Service desk</small>
            </span>
        </a>
        <div class="ms-auto d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-light" id="themeToggle" title="Toggle theme" aria-label="Toggle dark mode">
                <i class="bi bi-moon-stars-fill" id="themeIconDark"></i>
                <i class="bi bi-sun-fill d-none" id="themeIconLight"></i>
            </button>
            <div class="dropdown">
                <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle me-1"></i>
                    <span class="d-none d-md-inline"><?= e($cu['fullname'] ?? $cu['username'] ?? 'User') ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><span class="dropdown-item-text small text-muted"><?= e($cu['username'] ?? '') ?></span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" target="_blank" rel="noopener" href="<?= e(BASE_URL) ?>/index.php"><i class="bi bi-globe2 me-2"></i>Public website</a></li>
                    <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
