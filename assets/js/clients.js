const SweetDeskClients = {
    apiUrl: window.sweetdeskClients?.apiUrl || '/wp-json/sweetdesk/v1',
    nonce: window.sweetdeskClients?.nonce || '',
    clients: [],
    people: [],
    editingClientId: null,
    editingClientData: null,
    deleteClientId: null,
    deleteClientRow: null,
    searchTimer: null,
    sort: { field: 'name', order: 'asc' },
    statusFilter: ''
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

function getPersonName(person) {
    return [person.first_name, person.last_name].filter(Boolean).join(' ');
}

function getPrimaryContactPerson(client) {
    const primaryId = getClientMeta(client, 'primary_contact_id');

    if (primaryId && client.people?.length) {
        const linked = client.people.find(person => Number(person.id) === Number(primaryId));

        if (linked) {
            return linked;
        }
    }

    if (client.people?.length) {
        return client.people[0];
    }

    return null;
}

function getPrimaryContact(client) {
    const person = getPrimaryContactPerson(client);

    if (person) {
        return getPersonName(person);
    }

    return getClientMeta(client, 'primary_contact') || 'N/A';
}

function getClientStatus(client) {
    return getClientMeta(client, 'account_status') || 'active';
}

function resetClientForm() {
    SweetDeskClients.editingClientId = null;
    SweetDeskClients.editingClientData = null;

    document.getElementById('client-panel-name').value = '';
    document.getElementById('client-panel-email').value = '';
    document.getElementById('client-panel-phone').value = '';
    document.getElementById('client-panel-website').value = '';
    document.getElementById('client-panel-industry').value = '';
    document.getElementById('client-panel-status').value = 'active';
    document.getElementById('client-panel-notes').value = '';
    document.getElementById('client-panel-contact').value = '';
}

function openClientSidebar(mode = 'create', data = {}) {
    getClientPanelShell()?.classList.remove('collapsed');

    SweetDeskClients.editingClientId = mode === 'edit' ? Number(data.id) : null;
    SweetDeskClients.editingClientData = mode === 'edit' ? data : null;

    document.getElementById('client-panel-title').textContent =
        mode === 'create' ? 'Add New Client' : 'Edit Client';

    document.getElementById('client-panel-submit').textContent =
        mode === 'create' ? 'Add Client' : 'Save Changes';

    if (mode === 'create') {
        resetClientForm();
        return;
    }

    document.getElementById('client-panel-name').value = data.name || '';
    document.getElementById('client-panel-email').value = data.email || '';
    document.getElementById('client-panel-phone').value = data.phone || '';
    document.getElementById('client-panel-website').value = data.website || '';
    document.getElementById('client-panel-industry').value = getClientMeta(data, 'industry');
    document.getElementById('client-panel-status').value = getClientStatus(data);
    document.getElementById('client-panel-notes').value = data.notes || '';

    const contactSelect = document.getElementById('client-panel-contact');
    const primaryId = getClientMeta(data, 'primary_contact_id');
    const linkedPerson = getPrimaryContactPerson(data);

    if (primaryId) {
        contactSelect.value = String(primaryId);
    } else if (linkedPerson) {
        contactSelect.value = String(linkedPerson.id);
    } else {
        contactSelect.value = '';
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

function getClientPayload(personId) {
    const contactSelect = document.getElementById('client-panel-contact');
    const contactOption = contactSelect.selectedOptions[0];
    const contactName = contactOption && contactOption.value ? contactOption.textContent : '';

    return {
        name: document.getElementById('client-panel-name').value,
        email: document.getElementById('client-panel-email').value,
        phone: document.getElementById('client-panel-phone').value,
        website: document.getElementById('client-panel-website').value,
        notes: document.getElementById('client-panel-notes').value,
        meta: {
            primary_contact: contactName,
            primary_contact_id: personId ? String(personId) : '',
            industry: document.getElementById('client-panel-industry').value,
            account_status: document.getElementById('client-panel-status').value
        }
    };
}

async function linkPrimaryContact(clientId, personId, previousPersonId) {
    if (previousPersonId && Number(previousPersonId) !== Number(personId)) {
        await sdClientApi(`/people/${previousPersonId}`, {
            method: 'PUT',
            body: JSON.stringify({ client_id: null })
        });
    }

    if (personId) {
        await sdClientApi(`/people/${personId}`, {
            method: 'PUT',
            body: JSON.stringify({ client_id: clientId, role: 'client' })
        });
    }
}

async function saveClient() {
    const contactSelect = document.getElementById('client-panel-contact');
    const personId = contactSelect.value ? Number(contactSelect.value) : null;
    const previousPersonId = getClientMeta(SweetDeskClients.editingClientData || {}, 'primary_contact_id')
        || SweetDeskClients.editingClientData?.people?.[0]?.id
        || null;
    const id = SweetDeskClients.editingClientId;

    try {
        let clientId = id;

        if (id) {
            await sdClientApi(`/clients/${id}`, {
                method: 'PUT',
                body: JSON.stringify(getClientPayload(personId))
            });
        } else {
            const response = await sdClientApi('/clients', {
                method: 'POST',
                body: JSON.stringify(getClientPayload(personId))
            });

            clientId = response.data?.id;
        }

        if (clientId) {
            await linkPrimaryContact(clientId, personId, previousPersonId);
        }

        closeClientSidebar();
        await loadClients();
        await loadClientContacts();
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
        <div class="sd-actions">
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

function applyStatusFilter(clients) {
    if (!SweetDeskClients.statusFilter) {
        return clients;
    }

    return clients.filter(client => {
        const status = getClientStatus(client).toLowerCase();
        return status === SweetDeskClients.statusFilter;
    });
}

function sortClients(clients) {
    const { field, order } = SweetDeskClients.sort;
    const direction = order === 'asc' ? 1 : -1;
    const apiSortFields = ['name', 'email', 'id'];

    if (apiSortFields.includes(field)) {
        return clients;
    }

    return [...clients].sort((left, right) => {
        let leftValue;
        let rightValue;

        switch (field) {
            case 'primary_contact':
                leftValue = getPrimaryContact(left).toLowerCase();
                rightValue = getPrimaryContact(right).toLowerCase();
                break;
            case 'open_tickets':
                leftValue = Number(left.open_tickets ?? 0);
                rightValue = Number(right.open_tickets ?? 0);
                break;
            case 'total_tickets':
                leftValue = Number(left.total_tickets ?? 0);
                rightValue = Number(right.total_tickets ?? 0);
                break;
            default:
                leftValue = String(left[field] ?? '').toLowerCase();
                rightValue = String(right[field] ?? '').toLowerCase();
        }

        if (leftValue < rightValue) return -1 * direction;
        if (leftValue > rightValue) return 1 * direction;
        return 0;
    });
}

function renderClients() {
    const tbody = document.querySelector('.table-wrap table tbody');

    if (!tbody) return;

    const filtered = sortClients(applyStatusFilter(SweetDeskClients.clients));

    tbody.innerHTML = filtered.length
        ? filtered.map(renderClientRow).join('')
        : `<tr><td colspan="7">No clients found.</td></tr>`;
}

async function loadClients() {
    const searchInput = document.getElementById('client-search');
    const q = searchInput?.value?.trim() || '';
    const apiSortFields = ['name', 'email', 'id'];
    const sortField = apiSortFields.includes(SweetDeskClients.sort.field)
        ? SweetDeskClients.sort.field
        : 'name';

    const params = new URLSearchParams({
        page: '1',
        per_page: '100',
        sort: sortField,
        order: SweetDeskClients.sort.order
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

function isClientContactPerson(person) {
    return person.role === 'client' || Boolean(person.client_id);
}

async function loadClientContacts() {
    const contactSelect = document.getElementById('client-panel-contact');

    if (!contactSelect) return;

    try {
        const response = await sdClientApi('/people?per_page=100&sort=last_name&order=asc');
        SweetDeskClients.people = (response.data || []).filter(isClientContactPerson);

        const current = contactSelect.value;
        contactSelect.innerHTML = `<option value="">None</option>`;

        SweetDeskClients.people.forEach(person => {
            const name = getPersonName(person);

            if (!name) return;

            const option = document.createElement('option');
            option.value = String(person.id);
            option.textContent = name;
            contactSelect.appendChild(option);
        });

        if (current) {
            contactSelect.value = current;
        }
    } catch (error) {
        console.warn(error.message);
    }
}

function getClientExportParams() {
    const params = new URLSearchParams({
        include_people: 'true',
        include_recent_tickets: 'true'
    });

    const q = document.getElementById('client-search')?.value?.trim();

    if (q) {
        params.set('q', q);
    }

    return params;
}

async function exportClientsJson() {
    const params = getClientExportParams();
    const url = `${SweetDeskClients.apiUrl}/clients/export?${params.toString()}`;

    try {
        const response = await fetch(url, {
            headers: {
                'X-WP-Nonce': SweetDeskClients.nonce
            }
        });

        if (!response.ok) {
            const data = await response.json().catch(() => null);
            throw new Error(data?.message || 'Export failed.');
        }

        const data = await response.json();
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const objectUrl = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = objectUrl;
        link.download = 'sweetdesk-clients-export.json';
        link.click();
        URL.revokeObjectURL(objectUrl);
    } catch (error) {
        alert(error.message);
    }
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
    const importButton = document.getElementById('sd-import-clients');

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
            const result = await importClientsJson(file);
            const summary = result.data || {};

            alert(
                `Import complete.\nCreated: ${summary.created ?? 0}\nUpdated: ${summary.updated ?? 0}\nSkipped: ${summary.skipped ?? 0}\nErrors: ${summary.errors?.length ?? 0}`
            );

            input.value = '';
            await loadClients();
        } catch (error) {
            alert(error.message);
        }
    });
}

function setupClientSort() {
    const sortMap = {
        0: 'name',
        2: 'primary_contact',
        3: 'open_tickets',
        4: 'total_tickets'
    };

    const headers = document.querySelectorAll('.table-wrap thead th');
    const clientSideSortFields = ['primary_contact', 'open_tickets', 'total_tickets'];

    headers.forEach((header, index) => {
        const sortField = sortMap[index];

        if (!sortField) {
            return;
        }

        header.style.cursor = 'pointer';

        header.addEventListener('click', () => {
            if (SweetDeskClients.sort.field === sortField) {
                SweetDeskClients.sort.order = SweetDeskClients.sort.order === 'asc' ? 'desc' : 'asc';
            } else {
                SweetDeskClients.sort.field = sortField;
                SweetDeskClients.sort.order = 'asc';
            }

            if (clientSideSortFields.includes(sortField)) {
                renderClients();
                return;
            }

            loadClients();
        });
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

    document.getElementById('client-search')?.addEventListener('input', () => {
        clearTimeout(SweetDeskClients.searchTimer);

        SweetDeskClients.searchTimer = setTimeout(() => {
            loadClients();
        }, 300);
    });

    document.getElementById('client-status-filter')?.addEventListener('change', event => {
        SweetDeskClients.statusFilter = event.target.value;
        renderClients();
    });

    document.getElementById('sd-export-clients')?.addEventListener('click', exportClientsJson);

    setupClientImport();
    setupClientSort();

    await loadClientContacts();
    await loadClients();
});
