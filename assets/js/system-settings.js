(function () {
    'use strict';

    const BASE = window.VK_BASE_URL || '';

    function collectTab(tab) {
        const out = {};
        if (tab === 'general') {
            out.site_name = document.getElementById('site_name')?.value ?? '';
            out.analytics_domain = document.getElementById('analytics_domain')?.value ?? '';
            out.analytics_script_src = document.getElementById('analytics_script_src')?.value ?? '';
        } else if (tab === 'seo') {
            out.seo_site_title = document.getElementById('seo_site_title')?.value ?? '';
            out.seo_meta_description = document.getElementById('seo_meta_description')?.value ?? '';
            out.seo_meta_keywords = document.getElementById('seo_meta_keywords')?.value ?? '';
            out.seo_og_image = document.getElementById('seo_og_image')?.value ?? '';
            out.seo_auto_enabled = document.getElementById('seo_auto_enabled')?.checked ? '1' : '0';
            out.seo_locations = document.getElementById('seo_locations')?.value ?? '';
            out.seo_service_slugs = document.getElementById('seo_service_slugs')?.value ?? '';
        } else if (tab === 'whatsapp') {
            out.whatsapp_number = document.getElementById('whatsapp_number')?.value ?? '';
            out.whatsapp_default_message = document.getElementById('whatsapp_default_message')?.value ?? '';
        } else if (tab === 'email') {
            out.smtp_host = document.getElementById('smtp_host')?.value ?? '';
            out.smtp_port = String(document.getElementById('smtp_port')?.value ?? '587');
            out.smtp_username = document.getElementById('smtp_username')?.value ?? '';
            const pw = document.getElementById('smtp_password')?.value ?? '';
            if (pw !== '') {
                out.smtp_password = pw;
            }
            out.email_from = document.getElementById('email_from')?.value ?? '';
        }
        return out;
    }

    async function saveTab(tab) {
        const settings = collectTab(tab);
        const res = await fetch(BASE + '/api/settings_save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tab: tab, settings: settings }),
        });
        const data = await res.json().catch(function () {
            return { ok: false, error: 'Invalid response' };
        });
        if (!res.ok || !data.ok) {
            throw new Error(data.error || 'Save failed');
        }
    }

    document.querySelectorAll('.btn-save-tab').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            const tab = btn.getAttribute('data-tab');
            if (!tab) return;
            btn.disabled = true;
            try {
                await saveTab(tab);
                if (typeof window.showToast === 'function') {
                    window.showToast('Settings saved successfully', 'success');
                }
                const pw = document.getElementById('smtp_password');
                if (pw && tab === 'email') {
                    pw.value = '';
                }
            } catch (e) {
                const msg = e && e.message ? e.message : 'Save failed';
                if (typeof window.showToast === 'function') {
                    window.showToast(msg, 'danger');
                } else {
                    alert(msg);
                }
            } finally {
                btn.disabled = false;
            }
        });
    });

    function digitsOnly(s) {
        return String(s || '').replace(/\D+/g, '');
    }

    document.getElementById('btnTestWhatsapp')?.addEventListener('click', function () {
        const num = document.getElementById('whatsapp_number')?.value ?? '';
        const msg = document.getElementById('whatsapp_default_message')?.value ?? 'Hello';
        const d = digitsOnly(num);
        if (!d) {
            if (typeof window.showToast === 'function') {
                window.showToast('Enter a WhatsApp number first', 'warning');
            }
            return;
        }
        let n = d;
        if (n.length === 10 && n.indexOf('07') === 0) {
            n = '94' + n.slice(1);
        } else if (n.length === 9 && n.indexOf('7') === 0) {
            n = '94' + n;
        }
        const url = 'https://wa.me/' + n + '?text=' + encodeURIComponent(msg);
        window.open(url, '_blank', 'noopener,noreferrer');
    });

    document.getElementById('btnMailTest')?.addEventListener('click', async function () {
        const to = document.getElementById('mail_test_to')?.value?.trim() ?? '';
        if (!to) {
            if (typeof window.showToast === 'function') {
                window.showToast('Enter a recipient email', 'warning');
            }
            return;
        }
        const btn = document.getElementById('btnMailTest');
        if (btn) btn.disabled = true;
        try {
            const res = await fetch(BASE + '/api/mail_test.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ to: to }),
            });
            const data = await res.json().catch(function () {
                return { ok: false, error: 'Invalid response' };
            });
            if (!res.ok || !data.ok) {
                throw new Error(data.error || 'Send failed');
            }
            if (typeof window.showToast === 'function') {
                window.showToast(data.message || 'Test email sent', 'success');
            }
        } catch (e) {
            const msg = e && e.message ? e.message : 'Send failed';
            if (typeof window.showToast === 'function') {
                window.showToast(msg, 'danger');
            } else {
                alert(msg);
            }
        } finally {
            if (btn) btn.disabled = false;
        }
    });
})();
