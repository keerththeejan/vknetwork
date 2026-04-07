(function () {
    'use strict';

    const base = window.VK_BASE_URL || '';
    const form = document.getElementById('invoiceForm');
    const tbody = document.getElementById('linesBody');
    const tplProduct = document.getElementById('lineTplProduct');
    const tplService = document.getElementById('lineTplService');
    const customerInput = document.getElementById('customer_search');
    const customerId = document.getElementById('customer_id');
    const customerSelected = document.getElementById('customer_selected');
    const resultsEl = document.getElementById('customer_results');
    const discountEl = document.getElementById('discount');
    const taxEl = document.getElementById('tax');

    function money(n) {
        return (Math.round(n * 100) / 100).toFixed(2);
    }

    function wireRow(row) {
        row.querySelector('.rm-line').addEventListener('click', function () {
            row.remove();
            recalc();
        });
        const kind = row.getAttribute('data-line-kind');
        if (kind === 'product') {
            row.querySelector('.product-select').addEventListener('change', recalc);
            row.querySelector('.qty-input').addEventListener('input', recalc);
        } else {
            row.querySelector('.service-unit').addEventListener('input', recalc);
            row.querySelector('.qty-input').addEventListener('input', recalc);
        }
    }

    function recalc() {
        let sub = 0;
        tbody.querySelectorAll('tr.line-row').forEach(function (row) {
            const kind = row.getAttribute('data-line-kind');
            const qty = parseInt(row.querySelector('.qty-input').value, 10) || 0;
            let price = 0;
            if (kind === 'product') {
                const sel = row.querySelector('.product-select');
                const opt = sel.options[sel.selectedIndex];
                price = opt ? parseFloat(opt.getAttribute('data-price') || '0') || 0 : 0;
                const up = row.querySelector('.unit-price');
                if (up) up.textContent = money(price);
            } else {
                const su = row.querySelector('.service-unit');
                price = su ? parseFloat(su.value) || 0 : 0;
            }
            const line = price * qty;
            sub += line;
            row.querySelector('.line-total').textContent = money(line);
        });
        const disc = parseFloat(discountEl.value) || 0;
        const tax = parseFloat(taxEl.value) || 0;
        const grand = sub - disc + tax;
        document.getElementById('disp_subtotal').textContent = money(sub);
        document.getElementById('disp_discount').textContent = money(disc);
        document.getElementById('disp_tax').textContent = money(tax);
        document.getElementById('disp_grand').textContent = money(grand);
    }

    function addProductLine() {
        tbody.appendChild(tplProduct.content.cloneNode(true));
        const row = tbody.lastElementChild;
        wireRow(row);
        recalc();
    }

    function addServiceLine() {
        tbody.appendChild(tplService.content.cloneNode(true));
        const row = tbody.lastElementChild;
        wireRow(row);
        recalc();
    }

    document.getElementById('addProductLine').addEventListener('click', addProductLine);
    document.getElementById('addServiceLine').addEventListener('click', addServiceLine);
    discountEl.addEventListener('input', recalc);
    taxEl.addEventListener('input', recalc);

    function loadCustomers(q) {
        fetch(base + '/api/customers_search.php?q=' + encodeURIComponent(q))
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                resultsEl.innerHTML = '';
                if (!data.results || !data.results.length) {
                    resultsEl.classList.add('d-none');
                    return;
                }
                data.results.forEach(function (c) {
                    const a = document.createElement('button');
                    a.type = 'button';
                    a.className = 'list-group-item list-group-item-action';
                    a.textContent = c.name + (c.phone ? ' · ' + c.phone : '');
                    a.addEventListener('click', function () {
                        customerId.value = c.id;
                        customerSelected.textContent = 'Selected: ' + c.name;
                        customerInput.value = c.name;
                        resultsEl.classList.add('d-none');
                    });
                    resultsEl.appendChild(a);
                });
                resultsEl.classList.remove('d-none');
            })
            .catch(function () {
                resultsEl.classList.add('d-none');
            });
    }

    customerInput.addEventListener('focus', function () {
        if (!customerInput.value.trim()) {
            loadCustomers('');
        }
    });

    let searchTimer;
    customerInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        const q = customerInput.value.trim();
        searchTimer = setTimeout(function () {
            loadCustomers(q);
        }, 250);
    });

    document.addEventListener('click', function (e) {
        if (!resultsEl.contains(e.target) && e.target !== customerInput) {
            resultsEl.classList.add('d-none');
        }
    });

    form.addEventListener('submit', function (e) {
        if (!customerId.value) {
            e.preventDefault();
            if (window.showToast) window.showToast('Please select a customer.', 'danger');
            return;
        }
        if (!tbody.querySelector('tr.line-row')) {
            e.preventDefault();
            if (window.showToast) window.showToast('Add at least one line.', 'danger');
            return;
        }
    });

    if (tbody.children.length === 0) {
        addProductLine();
    }
})();
