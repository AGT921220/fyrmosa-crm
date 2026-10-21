(function () {
    const source = document.getElementById('line-items-data');
    const tbody = document.querySelector('#items-table tbody');
    if (!source || !tbody) {
        return;
    }

    const items = JSON.parse(source.textContent || '[]');

    function scaled(value, scale) {
        const normalized = String(value ?? '0').replace(/,/g, '').trim();
        const negative = normalized.startsWith('-');
        const raw = negative ? normalized.slice(1) : normalized;
        const parts = raw.split('.');
        const whole = parts[0] || '0';
        const fraction = parts[1] || '';
        const padded = (fraction + '0'.repeat(scale + 1)).slice(0, scale + 1);
        let result = BigInt(whole) * (10n ** BigInt(scale)) + BigInt(padded.slice(0, scale) || '0');
        if (padded[scale] >= '5') {
            result += 1n;
        }
        return negative ? -result : result;
    }

    function money(cents) {
        const negative = cents < 0n;
        const abs = negative ? -cents : cents;
        const whole = abs / 100n;
        const fraction = (abs % 100n).toString().padStart(2, '0');
        return (negative ? '-' : '') + whole.toString() + '.' + fraction;
    }

    function rowTemplate(item, index) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input class="form-control" name="items[${index}][sku]" value="${escape(item.sku || '')}"></td>
            <td><input class="form-control" name="items[${index}][description]" value="${escape(item.description || '')}" required></td>
            <td><input class="form-control" name="items[${index}][quantity]" value="${escape(item.quantity ?? '1')}" inputmode="decimal" required></td>
            <td><input class="form-control" name="items[${index}][unit]" value="${escape(item.unit || 'PZA')}" required></td>
            <td><input class="form-control" name="items[${index}][unit_price]" value="${escape(item.unit_price ?? '0')}" inputmode="decimal" required></td>
            <td><input class="form-control" name="items[${index}][discount_value]" value="${escape(item.discount_value ?? '0')}" inputmode="decimal"></td>
            <td>
                <select class="form-select" name="items[${index}][discount_type]">
                    <option value="percent" ${item.discount_type === 'percent' ? 'selected' : ''}>%</option>
                    <option value="amount" ${item.discount_type !== 'percent' ? 'selected' : ''}>Importe</option>
                </select>
            </td>
            <td><input class="form-control" name="items[${index}][tax_rate]" value="${escape(item.tax_rate ?? '0')}" inputmode="decimal"></td>
            <td class="line-total text-nowrap">0.00</td>
            <td><button class="btn btn-outline-danger btn-icon" type="button" data-remove><i class="ti ti-trash"></i></button></td>
        `;
        return row;
    }

    function escape(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;');
    }

    function reindex() {
        tbody.querySelectorAll('tr').forEach((row, index) => {
            row.querySelectorAll('[name]').forEach((input) => {
                input.name = input.name.replace(/items\[\d+]/, `items[${index}]`);
            });
        });
    }

    function recalculate() {
        let subtotal = 0n;
        let discount = 0n;
        let tax = 0n;
        let total = 0n;

        tbody.querySelectorAll('tr').forEach((row) => {
            const quantity = scaled(row.querySelector('[name$="[quantity]"]').value, 4);
            const price = scaled(row.querySelector('[name$="[unit_price]"]').value, 4);
            const product = quantity * price;
            const gross = (product + 500000n) / 1000000n;
            const type = row.querySelector('[name$="[discount_type]"]').value;
            const discountValue = scaled(row.querySelector('[name$="[discount_value]"]').value || '0', 4);
            let discountAmount = type === 'percent'
                ? (gross * discountValue + 500000n) / 1000000n
                : (discountValue + 50n) / 100n;
            if (discountAmount > gross) {
                discountAmount = gross;
            }
            const net = gross - discountAmount;
            const taxRate = scaled(row.querySelector('[name$="[tax_rate]"]').value || '0', 4);
            const taxAmount = (net * taxRate + 500000n) / 1000000n;
            const lineTotal = net + taxAmount;
            row.querySelector('.line-total').textContent = money(lineTotal);
            subtotal += net;
            discount += discountAmount;
            tax += taxAmount;
            total += lineTotal;
        });

        document.getElementById('sum-discount').textContent = money(discount);
        document.getElementById('sum-subtotal').textContent = money(subtotal);
        document.getElementById('sum-tax').textContent = money(tax);
        document.getElementById('sum-total').textContent = money(total);
    }

    function addRow(item) {
        tbody.appendChild(rowTemplate(item, tbody.children.length));
        recalculate();
    }

    (items.length ? items : [{}]).forEach(addRow);

    document.getElementById('add-item').addEventListener('click', () => addRow({
        quantity: '1',
        unit: 'PZA',
        unit_price: '0',
        discount_type: 'amount',
        discount_value: '0',
        tax_rate: '0',
    }));

    tbody.addEventListener('input', recalculate);
    tbody.addEventListener('change', recalculate);
    tbody.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove]');
        if (!button || tbody.children.length === 1) {
            return;
        }
        button.closest('tr').remove();
        reindex();
        recalculate();
    });

})();
