(function () {
    'use strict';

    const BASE = window.VK_BASE_URL || '';

    function showLoader(show) {
        const el = document.getElementById('pageLoader');
        if (!el) return;
        el.classList.toggle('d-none', !show);
    }

    window.showLoader = showLoader;

    window.showToast = function (message, type) {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        const t = document.createElement('div');
        t.className = 'toast align-items-center text-bg-' + (type || 'info') + ' border-0';
        t.setAttribute('role', 'alert');
        t.innerHTML =
            '<div class="d-flex">' +
            '<div class="toast-body">' +
            escapeHtml(String(message)) +
            '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
            '</div>';
        container.appendChild(t);
        const toast = new bootstrap.Toast(t, { delay: 4500 });
        toast.show();
        t.addEventListener('hidden.bs.toast', function () {
            t.remove();
        });
    };

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    /* Theme */
    const themeKey = 'vk_billing_theme';
    function applyTheme(mode) {
        const html = document.documentElement;
        if (mode === 'dark') {
            html.setAttribute('data-bs-theme', 'dark');
        } else {
            html.setAttribute('data-bs-theme', 'light');
        }
        const darkIcon = document.getElementById('themeIconDark');
        const lightIcon = document.getElementById('themeIconLight');
        if (darkIcon && lightIcon) {
            darkIcon.classList.toggle('d-none', mode === 'dark');
            lightIcon.classList.toggle('d-none', mode !== 'dark');
        }
        localStorage.setItem(themeKey, mode);
    }

    const saved = localStorage.getItem(themeKey);
    if (saved === 'dark' || saved === 'light') {
        applyTheme(saved);
    } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        applyTheme('dark');
    }

    document.getElementById('themeToggle')?.addEventListener('click', function () {
        const cur = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
        applyTheme(cur === 'dark' ? 'light' : 'dark');
    });

    /* Table sort (client-side) */
    document.querySelectorAll('table.sortable').forEach(function (table) {
        const tbody = table.querySelector('tbody');
        if (!tbody) return;
        table.querySelectorAll('th[data-sort]').forEach(function (th) {
            th.addEventListener('click', function () {
                const col = parseInt(th.getAttribute('data-sort'), 10);
                const type = th.getAttribute('data-type') || 'string';
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const asc = th.classList.toggle('sort-asc');
                th.classList.toggle('sort-desc', !asc);
                table.querySelectorAll('th[data-sort]').forEach(function (h) {
                    if (h !== th) h.classList.remove('sort-asc', 'sort-desc');
                });
                rows.sort(function (a, b) {
                    const ta = a.children[col] ? a.children[col].textContent.trim() : '';
                    const tb = b.children[col] ? b.children[col].textContent.trim() : '';
                    let va = ta;
                    let vb = tb;
                    if (type === 'number') {
                        va = parseFloat(ta.replace(/[^0-9.-]/g, '')) || 0;
                        vb = parseFloat(tb.replace(/[^0-9.-]/g, '')) || 0;
                    }
                    if (va < vb) return asc ? -1 : 1;
                    if (va > vb) return asc ? 1 : -1;
                    return 0;
                });
                rows.forEach(function (r) {
                    tbody.appendChild(r);
                });
            });
        });
    });

    /* Form submit loading */
    document.querySelectorAll('form[data-loading]').forEach(function (form) {
        form.addEventListener('submit', function () {
            showLoader(true);
        });
    });

    window.VK_BASE_URL = BASE;
})();
