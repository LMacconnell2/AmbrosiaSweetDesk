const SweetDeskClients = {
    apiUrl: window.sweetdeskClients?.apiUrl || '/wp-json/sweetdesk/v1',
    nonce: window.sweetdeskClients?.nonce || '',
    clients: [],
    people: [],
    editingClientId: null,
    deleteClientId: null,
    deleteClientRow: null,
    searchTimer: null
};

function sdClientApi(path, options = {}) {
    return fetch(`${SweetDeskClients.apiUrl}${path}`, {
        ...options,
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': SweetDeskClients.nonce,
            ...(options.headers || {})
        }
    }).then(async response => {
        const data = await response.json().catch(() => null);

        if (!response.ok) {
            throw new Error(data?.message || 'Request failed.');
        }

        return data;
    });
}

function getClientPanelShell() {
    return document.getElementById('clientPanelShell');
}

function toggleClientPanel() {
    getClientPanelShell()?.classList.toggle('collapsed');
}

function closeClientSidebar() {
    getClientPanelShell()?.classList.add('collapsed');
}

function getClientMeta(client, key) {
    if (!client?.meta) return '';

    if (Array.isArray(client.meta)) {
        return client.meta.find(item => item.meta_key === key)?.meta_value || '';
    }

    return client.meta[key] || '';
}

function getPrimaryContact(client) {
    if (client.people?.length) {
        const person = client.people[0];
        return [person.first_name, person.last_name].filter(Boolean).join(' ');
    }

    return getClientMeta(client, 'primary_contact') || 'N/A';
}

function getClientStatus(client) {
    return getClientMeta(client, 'account_status') || 'active';
}

function resetClientForm() {
    SweetDeskClients.editingClientId = null;

    document.getElementById('client-panel-name').value = '';
    document.getElementById('client-panel-notes').value = '';
    document.getElementById('client-panel-contact').value = '';
}

function openClientSidebar(mode = 'create', data = {}) {
    getClientPanelShell()?.classList.remove('collapsed');

    SweetDeskClients.editingClientId = mode === 'edit' ? Number(data.id) : null;

    document.getElementById('client-panel-title').textContent =
        mode === 'create' ? 'Add New Client' : 'Edit Client';

    document.getElementById('client-panel-submit').textContent =
        mode === 'create' ? 'Add Client' : 'Save Changes';

    if (mode === 'create') {
        resetClientForm();
        return;
    }

    document.getElementById('client-panel-name').value = data.name || '';
    document.getElementById('client-panel-notes').value = data.notes || '';

    const contactSelect = document.getElementById('client-panel-contact');
    const contact = getPrimaryContact(data);

    for (const option of contactSelect.options) {
        if (option.value === contact || option.textContent === contact) {
            contactSelect.value = option.value;
            break;
        }
    }
}

function openNewClientSidebar() {
    openClientSidebar('create');
}

async function openEditClientSidebar(id) {
    try {
        const response = await sdClientApi(`/clients/${id}`);
        openClientSidebar('edit', response.data);
    } catch (error) {
        alert(error.message);
    }
}

function getClientPayload() {
    const contactSelect = document.getElementById('client-panel-contact');

    return {
        name: document.getElementById('client-panel-name').value,
        notes: document.getElementById('client-panel-notes').value,
        email: '',
        phone: '',
        website: '',
        meta: {
            primary_contact: contactSelect.value || '',
            industry: '',
            account_status: 'active'
        }
    };
}

async function saveClient() {
    const payload = getClientPayload();
    const id = SweetDeskClients.editingClientId;

    try {
        if (id) {
            await sdClientApi(`/clients/${id}`, {
                method: 'PUT',
                body: JSON.stringify(payload)
            });
        } else {
            await sdClientApi('/clients', {
                method: 'POST',
                body: JSON.stringify(payload)
            });
        }

        closeClientSidebar();
        await loadClients();
    } catch (error) {
        alert(error.message);
    }
}

function openDeleteClientModal(id, name, button) {
    SweetDeskClients.deleteClientId = Number(id);
    SweetDeskClients.deleteClientRow = button.closest('tr');

    document.getElementById('sd-delete-client-name').textContent = name;
    document.getElementById('sd-delete-client-modal').classList.add('active');
}

function closeDeleteClientModal() {
    document.getElementById('sd-delete-client-modal').classList.remove('active');

    SweetDeskClients.deleteClientId = null;
    SweetDeskClients.deleteClientRow = null;
}

async function confirmDeleteClient() {
    if (!SweetDeskClients.deleteClientId) return;

    try {
        await sdClientApi(`/clients/${SweetDeskClients.deleteClientId}`, {
            method: 'DELETE'
        });

        SweetDeskClients.deleteClientRow?.remove();
        closeDeleteClientModal();
        await loadClients();
    } catch (error) {
        alert(error.message);
    }
}

function clientActionButtons(client) {
    const safeName = String(client.name || '').replace(/'/g, '&#039;');

    return `
        <div class="row-actions">
            <button class="sd-action-btn sd-edit-btn" type="button" onclick="openEditClientSidebar(${client.id})">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </button>
            <button type="button" class="sd-action-btn sd-delete-btn" onclick="openDeleteClientModal(${client.id}, '${safeName}', this)">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </div>
    `;
}

function renderClientRow(client) {
    const industry = getClientMeta(client, 'industry');
    const status = getClientStatus(client);
    const isActive = status.toLowerCase() !== 'inactive';

    return `
        <tr>
            <td class="col-check"><input type="checkbox" /></td>
            <td class="col-name">${client.name || ''}</td>
            <td>${industry || ''}</td>
            <td>${getPrimaryContact(client)}</td>
            <td>${client.open_tickets ?? 0}</td>
            <td>${client.total_tickets ?? 0}</td>
            <td>
                <span class="status-badge ${isActive ? 'active' : 'inactive'}">
                    ${isActive ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td>${clientActionButtons(client)}</td>
        </tr>
    `;
}

function renderClients() {
    const tbody = document.querySelector('.table-wrap table tbody');

    if (!tbody) return;

    tbody.innerHTML = SweetDeskClients.clients.length
        ? SweetDeskClients.clients.map(renderClientRow).join('')
        : `<tr><td colspan="8">No clients found.</td></tr>`;
}

async function loadClients() {
    const searchInput = document.querySelector('.search-wrap input');
    const q = searchInput?.value?.trim() || '';

    const params = new URLSearchParams({
        page: '1',
        per_page: '100',
        sort: 'name',
        order: 'asc'
    });

    if (q) params.set('q', q);

    try {
        const response = await sdClientApi(`/clients?${params.toString()}`);
        SweetDeskClients.clients = response.data || [];
        renderClients();
    } catch (error) {
        alert(error.message);
    }
}

async function loadClientContacts() {
    const contactSelect = document.getElementById('client-panel-contact');

    if (!contactSelect) return;

    try {
        const response = await sdClientApi('/people?roles=client&per_page=100&sort=last_name&order=asc');
        SweetDeskClients.people = response.data || [];

        contactSelect.innerHTML = `<option value="">None</option>`;

        SweetDeskClients.people.forEach(person => {
            const name = [person.first_name, person.last_name].filter(Boolean).join(' ');

            if (!name) return;

            const option = document.createElement('option');
            option.value = name;
            option.textContent = name;
            contactSelect.appendChild(option);
        });
    } catch (error) {
        console.warn(error.message);
    }
}

function exportClientsJson() {
    window.location.href = `${SweetDeskClients.apiUrl}/clients/export?include_people=true&include_recent_tickets=true`;
}

async function importClientsJson(file) {
    const text = await file.text();
    const data = JSON.parse(text);

    return sdClientApi('/clients/import', {
        method: 'POST',
        body: JSON.stringify(data)
    });
}

function setupClientImport() {
    const importButton = document.querySelectorAll('.header-actions .btn-outline')[0];

    if (!importButton) return;

    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'application/json,.json';
    input.hidden = true;

    document.body.appendChild(input);

    importButton.addEventListener('click', () => input.click());

    input.addEventListener('change', async () => {
        const file = input.files?.[0];

        if (!file) return;

        try {
            await importClientsJson(file);
            input.value = '';
            await loadClients();
        } catch (error) {
            alert(error.message);
        }
    });
}

document.getElementById('sd-delete-client-modal')?.addEventListener('click', function (e) {
    if (e.target === this) {
        closeDeleteClientModal();
    }
});

document.addEventListener('DOMContentLoaded', async () => {
    document.getElementById('sd-new-client')?.addEventListener('click', openNewClientSidebar);
    document.getElementById('client-panel-submit')?.addEventListener('click', saveClient);

    const searchInput = document.querySelector('.search-wrap input');

    searchInput?.addEventListener('input', () => {
        clearTimeout(SweetDeskClients.searchTimer);

        SweetDeskClients.searchTimer = setTimeout(() => {
            loadClients();
        }, 300);
    });

    const exportButton = document.querySelectorAll('.header-actions .btn-outline')[1];
    exportButton?.addEventListener('click', exportClientsJson);

    setupClientImport();

    await loadClientContacts();
    await loadClients();
});
