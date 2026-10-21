(function () {
    const data = document.getElementById('party-data');
    const clientSelect = document.getElementById('client_id');
    const contactSelect = document.getElementById('contact_id');
    if (!data || !clientSelect || !contactSelect) {
        return;
    }

    const clients = JSON.parse(data.textContent || '[]');
    const findClient = (id) => clients.find((client) => String(client.id) === String(id));

    const show = (client, contact) => {
        document.getElementById('party-legal-name').value = client?.legal_name || '';
        document.getElementById('party-first-name').value = contact?.first_name || '';
        document.getElementById('party-last-name').value = contact?.last_name || '';
        document.getElementById('party-phone').value = contact?.phone || '';
        document.getElementById('party-email').value = contact?.email || '';
    };

    const fillContacts = (keepSelection) => {
        const client = findClient(clientSelect.value);
        const current = keepSelection ? (contactSelect.value || contactSelect.dataset.selected || '') : '';
        contactSelect.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Selecciona un contacto';
        contactSelect.appendChild(placeholder);

        if (!client) {
            show(null, null);
            return;
        }

        client.contacts.forEach((contact) => {
            const option = document.createElement('option');
            option.value = contact.id;
            option.textContent = `${contact.first_name} ${contact.last_name}`;
            if (String(contact.id) === String(current)) {
                option.selected = true;
            }
            contactSelect.appendChild(option);
        });

        const chosen = client.contacts.find((contact) => String(contact.id) === String(contactSelect.value));
        show(client, chosen || null);
    };

    clientSelect.addEventListener('change', () => fillContacts(false));
    contactSelect.addEventListener('change', () => {
        const client = findClient(clientSelect.value);
        const chosen = client?.contacts.find((contact) => String(contact.id) === String(contactSelect.value));
        show(client, chosen || null);
    });

    fillContacts(true);
})();
