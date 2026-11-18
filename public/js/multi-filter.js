(function () {
    if (typeof TomSelect === 'undefined') {
        return;
    }

    document.querySelectorAll('select.js-multi-filter').forEach((element) => {
        if (element.tomselect) {
            return;
        }

        let syncing = false;

        const control = new TomSelect(element, {
            plugins: ['checkbox_options', 'remove_button'],
            maxItems: null,
            maxOptions: null,
            closeAfterSelect: false,
            hideSelected: false,
            placeholder: 'Todos',
            onChange(values) {
                if (syncing) {
                    return;
                }

                const selected = Array.isArray(values) ? values : [];

                if (selected.length === 0) {
                    syncing = true;
                    this.addItem('all');
                    syncing = false;
                    return;
                }

                if (!selected.includes('all') || selected.length === 1) {
                    return;
                }

                const keepAll = selected[selected.length - 1] === 'all';
                const next = keepAll ? ['all'] : selected.filter((value) => value !== 'all');
                syncing = true;
                this.setValue(next);
                syncing = false;
            },
        });

        if (control.items.length === 0) {
            control.addItem('all');
        }
    });
})();
