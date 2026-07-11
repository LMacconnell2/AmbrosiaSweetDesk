const SweetDeskPeople = {
    apiUrl: window.sweetdeskPeople?.apiUrl || '/wp-json/sweetdesk/v1',
    nonce: window.sweetdeskPeople?.nonce || '',
    internalPeople: [],
    clientPeople: [],
    clients: [],
    editingPersonId: null,
    editingPerson: null,
    deletePersonId: null,
    deletePersonRow: null,
    currentType: 'internal',
    searchTimer: null,
    sort: {
        internal: { field: 'last_name', order: 'asc' },
        client: { field: 'last_name', order: 'asc' }
    }
};

function sdApi(path, options = {}) {
    return fetch(`${SweetDeskPeople.apiUrl}${path}`, {
        ...options,
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': SweetDeskPeople.nonce,
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

function getPersonPanelShell() {
    return document.getElementById('personPanelShell');
}

function togglePersonPanel() {
    getPersonPanelShell()?.classList.toggle('collapsed');
}

function closePersonSidebar() {
    getPersonPanelShell()?.classList.add('collapsed');
}

function isClientContact(person) {
    return person.role === 'client' || Boolean(person.client_id);
}

function getPersonFormType() {
    if (SweetDeskPeople.editingPerson) {
        return isClientContact(SweetDeskPeople.editingPerson) ? 'client' : 'internal';
    }

    return SweetDeskPeople.currentType;
}

function setFieldHidden(element, hidden) {
    if (!element) {
        return;
    }

    element.hidden = hidden;
    element.classList.toggle('sd-form-hidden', hidden);
}

function setType(type, { isEdit = false } = {}) {
    SweetDeskPeople.currentType = type;

    const typeToggleField = document.getElementById('typeToggleField');
    const companyField = document.getElementById('companyField');
    const roleField = document.getElementById('roleField');
    const typeInternal = document.getElementById('typeInternal');
    const typeClient = document.getElementById('typeClient');

    setFieldHidden(typeToggleField, isEdit);
    setFieldHidden(companyField, type === 'internal');
    setFieldHidden(roleField, type === 'client');

    if (!isEdit) {
        typeInternal?.classList.toggle('active', type === 'internal');
        typeClient?.classList.toggle('active', type === 'client');
    }

    if (typeInternal) {
        typeInternal.disabled = isEdit;
    }

    if (typeClient) {
        typeClient.disabled = isEdit;
    }
}

function splitName(fullName) {
    const parts = fullName.trim().split(/\s+/);

    return {
        first_name: parts.shift() || '',
        last_name: parts.join(' ')
    };
}

function fullName(person) {
    return [person.first_name, person.last_name].filter(Boolean).join(' ') || 'Unnamed Person';
}

function getMetaValue(person, key) {
    if (!person.meta) {
        return '';
    }

    if (Array.isArray(person.meta)) {
        return person.meta.find(item => item.meta_key === key)?.meta_value || '';
    }

    return person.meta[key] || '';
}

function isPersonActive(person) {
    return person.is_active !== false && person.is_active !== 0 && person.is_active !== '0';
}

function getSectionElement(section) {
    const sections = document.querySelectorAll('.section');
    return sections[section === 'internal' ? 0 : 1] || null;
}

function personRowCheckbox(person) {
    const active = isPersonActive(person) ? '1' : '0';

    return `<input type="checkbox" class="person-row-check" data-person-id="${person.id}" data-is-active="${active}" aria-label="Select ${fullName(person)}" />`;
}

function getClientName(clientId) {
    if (!clientId) {
        return '';
    }

    const client = SweetDeskPeople.clients.find(item => Number(item.id) === Number(clientId));
    return client?.name || '';
}

function resetPersonForm() {
    SweetDeskPeople.editingPersonId = null;
    SweetDeskPeople.editingPerson = null;

    document.getElementById('person-panel-name').value = '';
    document.getElementById('person-panel-role').value = '';
    document.getElementById('person-panel-email').value = '';
    document.getElementById('person-panel-phone').value = '';
    document.getElementById('person-panel-client').value = '';
    document.getElementById('person-panel-notes').value = '';

    setType('internal');
    setFieldHidden(document.getElementById('person-panel-toggle-active'), true);
}

function updateSidebarActiveButton() {
    const button = document.getElementById('person-panel-toggle-active');
    const person = SweetDeskPeople.editingPerson;

    if (!button || !person) {
        return;
    }

    setFieldHidden(button, false);

    if (isPersonActive(person)) {
        button.textContent = 'Deactivate Person';
        button.dataset.action = 'deactivate';
    } else {
        button.textContent = 'Activate Person';
        button.dataset.action = 'activate';
    }
}

function openPersonSidebar(mode = 'create', person = {}) {
    const shell = getPersonPanelShell();
    shell?.classList.remove('collapsed');

    SweetDeskPeople.editingPersonId = mode === 'edit' ? Number(person.id) : null;
    SweetDeskPeople.editingPerson = mode === 'edit' ? person : null;

    document.getElementById('person-panel-title').textContent =
        mode === 'create' ? 'Add New Person' : 'Edit Person';

    document.getElementById('person-panel-submit').textContent =
        mode === 'create' ? 'Add Person' : 'Save Changes';

    if (mode === 'create') {
        resetPersonForm();
        return;
    }

    const personType = isClientContact(person) ? 'client' : 'internal';

    document.getElementById('person-panel-name').value = fullName(person);
    document.getElementById('person-panel-role').value = person.role || '';
    document.getElementById('person-panel-email').value = person.email || '';
    document.getElementById('person-panel-phone').value = getMetaValue(person, 'phone');
    document.getElementById('person-panel-client').value = person.client_id ? String(person.client_id) : '';
    document.getElementById('person-panel-notes').value = getMetaValue(person, 'notes');

    setType(personType, { isEdit: true });
    updateSidebarActiveButton();
}

function openNewPersonSidebar() {
    openPersonSidebar('create');
}

async function openEditPersonSidebar(id) {
    try {
        const response = await sdApi(`/people/${id}`);
        openPersonSidebar('edit', response.data);
    } catch (error) {
        alert(error.message);
    }
}

function getPersonPayload(isEdit) {
    const name = document.getElementById('person-panel-name').value;
    const role = document.getElementById('person-panel-role').value;
    const email = document.getElementById('person-panel-email').value;
    const phone = document.getElementById('person-panel-phone').value;
    const clientId = document.getElementById('person-panel-client').value;
    const notes = document.getElementById('person-panel-notes').value;
    const parsedName = splitName(name);
    const formType = getPersonFormType();
    const isInternal = formType === 'internal';

    const payload = {
        first_name: parsedName.first_name,
        last_name: parsedName.last_name,
        email,
        is_active: isEdit && SweetDeskPeople.editingPerson
            ? isPersonActive(SweetDeskPeople.editingPerson)
            : true,
        meta: {
            phone,
            notes
        }
    };

    if (isInternal) {
        payload.role = role || 'staff';
        payload.client_id = null;

        if (isEdit && SweetDeskPeople.editingPerson) {
            payload.wp_user_id = SweetDeskPeople.editingPerson.wp_user_id ?? null;
        } else {
            payload.wp_user_id = null;
        }
    } else {
        payload.role = isEdit && SweetDeskPeople.editingPerson?.role
            ? SweetDeskPeople.editingPerson.role
            : 'client';
        payload.wp_user_id = null;
        payload.client_id = clientId ? Number(clientId) : null;
    }

    return payload;
}

async function savePerson() {
    const id = SweetDeskPeople.editingPersonId;
    const payload = getPersonPayload(Boolean(id));

    try {
        if (id) {
            await sdApi(`/people/${id}`, {
                method: 'PUT',
                body: JSON.stringify(payload)
            });
        } else {
            await sdApi('/people', {
                method: 'POST',
                body: JSON.stringify(payload)
            });
        }

        closePersonSidebar();
        await loadPeople();
    } catch (error) {
        alert(error.message);
    }
}

function openDeletePersonModal(id, name, button) {
    SweetDeskPeople.deletePersonId = Number(id);
    SweetDeskPeople.deletePersonRow = button.closest('tr');

    document.getElementById('sd-delete-person-name').textContent = name;
    document.getElementById('sd-delete-person-modal').classList.add('active');
}

function closeDeletePersonModal() {
    document.getElementById('sd-delete-person-modal').classList.remove('active');

    SweetDeskPeople.deletePersonId = null;
    SweetDeskPeople.deletePersonRow = null;
}

async function confirmDeletePerson() {
    if (!SweetDeskPeople.deletePersonId) {
        return;
    }

    try {
        await sdApi(`/people/${SweetDeskPeople.deletePersonId}`, {
            method: 'DELETE'
        });

        SweetDeskPeople.deletePersonRow?.remove();
        closeDeletePersonModal();
        await loadPeople();
    } catch (error) {
        alert(error.message);
    }
}

function personActionButtons(person) {
    const name = fullName(person).replace(/'/g, '&#039;');

    return `
        <div class="sd-actions">
            <button class="sd-action-btn sd-edit-btn" type="button" onclick="openEditPersonSidebar(${person.id})">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </button>
            <button type="button" class="sd-action-btn sd-delete-btn" onclick="openDeletePersonModal(${person.id}, '${name}', this)">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </div>
    `;
}

async function setPersonActive(id, isActive) {
    await sdApi(`/people/${id}`, {
        method: 'PUT',
        body: JSON.stringify({ is_active: isActive })
    });
}

async function toggleSidebarPersonActive() {
    const id = SweetDeskPeople.editingPersonId;
    const person = SweetDeskPeople.editingPerson;

    if (!id || !person) {
        return;
    }

    const makeActive = !isPersonActive(person);

    try {
        await setPersonActive(id, makeActive);
        closePersonSidebar();
        await loadPeople();
    } catch (error) {
        alert(error.message);
    }
}

async function bulkSetPersonsActive(section, isActive) {
    const sectionEl = getSectionElement(section);

    if (!sectionEl) {
        return;
    }

    const checks = [...sectionEl.querySelectorAll('.person-row-check:checked')].filter(checkbox => {
        return checkbox.dataset.isActive === (isActive ? '0' : '1');
    });

    if (!checks.length) {
        return;
    }

    try {
        await Promise.all(
            checks.map(checkbox => setPersonActive(Number(checkbox.dataset.personId), isActive))
        );

        await loadPeople();
    } catch (error) {
        alert(error.message);
    }
}

function getCheckedBoxes(section) {
    const sectionEl = getSectionElement(section);

    if (!sectionEl) {
        return [];
    }

    return [...sectionEl.querySelectorAll('.person-row-check:checked')];
}

function syncSelectAllCheckbox(section) {
    const sectionEl = getSectionElement(section);
    const selectAll = sectionEl?.querySelector('.select-all-check');
    const rowChecks = sectionEl ? [...sectionEl.querySelectorAll('.person-row-check')] : [];

    if (!selectAll || !rowChecks.length) {
        if (selectAll) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }

        return;
    }

    const checkedCount = rowChecks.filter(checkbox => checkbox.checked).length;

    selectAll.checked = checkedCount === rowChecks.length;
    selectAll.indeterminate = checkedCount > 0 && checkedCount < rowChecks.length;
}

function updateBulkActions(section) {
    const bulkBar = document.getElementById(`${section}-bulk-actions`);
    const deactivateBtn = document.getElementById(`${section}-bulk-deactivate`);
    const activateBtn = document.getElementById(`${section}-bulk-activate`);
    const checked = getCheckedBoxes(section);

    setFieldHidden(bulkBar, checked.length === 0);

    if (!checked.length) {
        syncSelectAllCheckbox(section);
        return;
    }

    const activeCount = checked.filter(checkbox => checkbox.dataset.isActive === '1').length;
    const inactiveCount = checked.length - activeCount;

    setFieldHidden(deactivateBtn, activeCount === 0);
    setFieldHidden(activateBtn, inactiveCount === 0);

    if (deactivateBtn && activeCount > 0) {
        deactivateBtn.textContent = activeCount === checked.length
            ? `Deactivate Selected (${activeCount})`
            : `Deactivate Selected (${activeCount})`;
    }

    if (activateBtn && inactiveCount > 0) {
        activateBtn.textContent = inactiveCount === checked.length
            ? `Activate Selected (${inactiveCount})`
            : `Activate Selected (${inactiveCount})`;
    }

    syncSelectAllCheckbox(section);
}

function setupTableSelection(section) {
    const sectionEl = getSectionElement(section);

    if (!sectionEl || sectionEl.dataset.selectionBound === 'true') {
        return;
    }

    sectionEl.dataset.selectionBound = 'true';

    const selectAll = sectionEl.querySelector('.select-all-check');
    const tbody = sectionEl.querySelector('tbody');

    selectAll?.addEventListener('change', () => {
        tbody?.querySelectorAll('.person-row-check').forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });

        updateBulkActions(section);
    });

    tbody?.addEventListener('change', event => {
        if (event.target.classList.contains('person-row-check')) {
            updateBulkActions(section);
        }
    });

    document.getElementById(`${section}-bulk-deactivate`)?.addEventListener('click', () => {
        bulkSetPersonsActive(section, false);
    });

    document.getElementById(`${section}-bulk-activate`)?.addEventListener('click', () => {
        bulkSetPersonsActive(section, true);
    });
}

function personNameCell(person, section) {
    const name = fullName(person);
    const showDeactivated = document.getElementById(
        section === 'internal' ? 'internal-show-deactivated' : 'client-show-deactivated'
    )?.checked;

    if (!showDeactivated) {
        return `<span class="person-name-cell"><span class="person-name-text">${name}</span></span>`;
    }

    const active = isPersonActive(person);
    const badgeClass = active ? 'status-badge inactive is-reserved' : 'status-badge inactive';

    return `<span class="person-name-cell"><span class="person-name-text">${name}</span><span class="${badgeClass}"${active ? ' aria-hidden="true"' : ''}>Deactivated</span></span>`;
}

function renderInternalRow(person) {
    const phone = getMetaValue(person, 'phone');
    const inactiveClass = isPersonActive(person) ? '' : ' person-row-inactive';

    return `
        <tr class="${inactiveClass.trim()}">
            <td class="col-check">${personRowCheckbox(person)}</td>
            <td class="col-name">${personNameCell(person, 'internal')}</td>
            <td>${person.role || ''}</td>
            <td><a href="mailto:${person.email || ''}" class="email-link">${person.email || ''}</a></td>
            <td>${phone || ''}</td>
            <td>${personActionButtons(person)}</td>
        </tr>
    `;
}

function renderClientRow(person) {
    const phone = getMetaValue(person, 'phone');
    const company = getClientName(person.client_id) || getMetaValue(person, 'company');
    const inactiveClass = isPersonActive(person) ? '' : ' person-row-inactive';

    return `
        <tr class="${inactiveClass.trim()}">
            <td class="col-check">${personRowCheckbox(person)}</td>
            <td class="col-name">${personNameCell(person, 'client')}</td>
            <td>${company || ''}</td>
            <td><a href="mailto:${person.email || ''}" class="email-link">${person.email || ''}</a></td>
            <td>${phone || ''}</td>
            <td>${personActionButtons(person)}</td>
        </tr>
    `;
}

function getPeopleTables() {
    const tables = document.querySelectorAll('.section table tbody');

    return {
        internal: tables[0],
        clients: tables[1]
    };
}

function renderPeople() {
    const tables = getPeopleTables();

    if (!tables.internal || !tables.clients) {
        return;
    }

    tables.internal.innerHTML = SweetDeskPeople.internalPeople.length
        ? SweetDeskPeople.internalPeople.map(renderInternalRow).join('')
        : `<tr><td colspan="6">No Ambrosia personnel found.</td></tr>`;

    tables.clients.innerHTML = SweetDeskPeople.clientPeople.length
        ? SweetDeskPeople.clientPeople.map(renderClientRow).join('')
        : `<tr><td colspan="6">No client contacts found.</td></tr>`;

    updateBulkActions('internal');
    updateBulkActions('client');
    syncSelectAllCheckbox('internal');
    syncSelectAllCheckbox('client');
}

function buildPeopleQuery(section) {
    const isInternal = section === 'internal';
    const searchInput = document.getElementById(isInternal ? 'internal-search' : 'client-search');
    const filterSelect = document.getElementById(isInternal ? 'internal-role-filter' : 'client-company-filter');
    const sortState = SweetDeskPeople.sort[section];
    const showDeactivated = document.getElementById(
        isInternal ? 'internal-show-deactivated' : 'client-show-deactivated'
    )?.checked;

    const params = new URLSearchParams({
        page: '1',
        per_page: '100',
        sort: sortState.field,
        order: sortState.order
    });

    if (!showDeactivated) {
        params.set('is_active', '1');
    }

    const q = searchInput?.value?.trim();

    if (q) {
        params.set('q', q);
    }

    if (isInternal) {
        const role = filterSelect?.value;

        if (role) {
            params.set('roles', role);
        }
    } else {
        const clientId = filterSelect?.value;

        if (clientId) {
            params.set('client_ids', clientId);
        }
    }

    return params;
}

async function loadPeopleSection(section) {
    const params = buildPeopleQuery(section);
    const response = await sdApi(`/people?${params.toString()}`);
    const people = response.data || [];

    if (section === 'internal') {
        SweetDeskPeople.internalPeople = people.filter(person => !isClientContact(person));
    } else {
        SweetDeskPeople.clientPeople = people.filter(person => isClientContact(person));
    }
}

async function loadPeople() {
    try {
        await Promise.all([
            loadPeopleSection('internal'),
            loadPeopleSection('client')
        ]);

        renderPeople();
        populateRoleFilter();
    } catch (error) {
        alert(error.message);
    }
}

function populateRoleFilter() {
    const roleFilter = document.getElementById('internal-role-filter');

    if (!roleFilter) {
        return;
    }

    const roles = [...new Set(
        SweetDeskPeople.internalPeople
            .map(person => person.role)
            .filter(Boolean)
    )].sort();

    const current = roleFilter.value;
    roleFilter.innerHTML = `<option value="">All Roles</option>`;

    roles.forEach(role => {
        const option = document.createElement('option');
        option.value = role;
        option.textContent = role;
        roleFilter.appendChild(option);
    });

    if (current && roles.includes(current)) {
        roleFilter.value = current;
    }
}

async function loadClientsForFilters() {
    try {
        const response = await sdApi('/clients?per_page=100&sort=name&order=asc');
        SweetDeskPeople.clients = response.data || [];

        const companyFilter = document.getElementById('client-company-filter');
        const clientSelect = document.getElementById('person-panel-client');

        if (companyFilter) {
            const current = companyFilter.value;
            companyFilter.innerHTML = `<option value="">All Companies</option>`;

            SweetDeskPeople.clients.forEach(client => {
                const option = document.createElement('option');
                option.value = String(client.id);
                option.textContent = client.name;
                companyFilter.appendChild(option);
            });

            if (current) {
                companyFilter.value = current;
            }
        }

        if (clientSelect) {
            const current = clientSelect.value;
            clientSelect.innerHTML = `<option value="">None</option>`;

            SweetDeskPeople.clients.forEach(client => {
                const option = document.createElement('option');
                option.value = String(client.id);
                option.textContent = client.name;
                clientSelect.appendChild(option);
            });

            if (current) {
                clientSelect.value = current;
            }
        }
    } catch (error) {
        console.warn(error.message);
    }
}

function getExportQueryParams() {
    const params = new URLSearchParams();
    const internalQ = document.getElementById('internal-search')?.value?.trim();
    const clientQ = document.getElementById('client-search')?.value?.trim();
    const role = document.getElementById('internal-role-filter')?.value;
    const clientId = document.getElementById('client-company-filter')?.value;

    if (internalQ || clientQ) {
        params.set('q', internalQ || clientQ);
    }

    if (role) {
        params.set('roles', role);
    }

    if (clientId) {
        params.set('client_ids', clientId);
    }

    return params;
}

async function exportPeopleCsv() {
    const params = getExportQueryParams();
    const query = params.toString();
    const url = `${SweetDeskPeople.apiUrl}/people/export${query ? `?${query}` : ''}`;

    try {
        const response = await fetch(url, {
            headers: {
                'X-WP-Nonce': SweetDeskPeople.nonce
            }
        });

        if (!response.ok) {
            const data = await response.json().catch(() => null);
            throw new Error(data?.message || 'Export failed.');
        }

        const blob = await response.blob();
        const objectUrl = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = objectUrl;
        link.download = 'sweetdesk-people-export.csv';
        link.click();
        URL.revokeObjectURL(objectUrl);
    } catch (error) {
        alert(error.message);
    }
}

async function importPeopleCsv(file) {
    const formData = new FormData();
    formData.append('file', file);

    const response = await fetch(`${SweetDeskPeople.apiUrl}/people/import`, {
        method: 'POST',
        headers: {
            'X-WP-Nonce': SweetDeskPeople.nonce
        },
        body: formData
    });

    const data = await response.json().catch(() => null);

    if (!response.ok) {
        throw new Error(data?.message || 'Import failed.');
    }

    return data;
}

function setupPeopleImport() {
    const importButton = document.querySelectorAll('.header-actions .btn-outline')[0];

    if (!importButton) {
        return;
    }

    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.csv,text/csv';
    input.hidden = true;
    document.body.appendChild(input);

    importButton.addEventListener('click', () => input.click());

    input.addEventListener('change', async () => {
        const file = input.files?.[0];

        if (!file) {
            return;
        }

        try {
            const result = await importPeopleCsv(file);
            const summary = result.data || {};
            const errorCount = summary.errors?.length || 0;

            alert(
                `Import complete.\nCreated: ${summary.created ?? 0}\nUpdated: ${summary.updated ?? 0}\nSkipped: ${summary.skipped ?? 0}\nErrors: ${errorCount}`
            );

            input.value = '';
            await loadPeople();
        } catch (error) {
            alert(error.message);
        }
    });
}

function setupPeopleSearch() {
    ['internal-search', 'client-search'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', () => {
            clearTimeout(SweetDeskPeople.searchTimer);
            SweetDeskPeople.searchTimer = setTimeout(() => loadPeople(), 300);
        });
    });

    document.getElementById('internal-role-filter')?.addEventListener('change', () => loadPeople());
    document.getElementById('client-company-filter')?.addEventListener('change', () => loadPeople());
    document.getElementById('internal-show-deactivated')?.addEventListener('change', () => loadPeople());
    document.getElementById('client-show-deactivated')?.addEventListener('change', () => loadPeople());
}

function setupPeopleSort() {
    const sortMap = {
        internal: {
            1: 'last_name',
            2: 'role',
            3: 'email'
        },
        client: {
            1: 'last_name',
            2: 'last_name',
            3: 'email'
        }
    };

    document.querySelectorAll('.section').forEach((sectionEl, index) => {
        const section = index === 0 ? 'internal' : 'client';
        const headers = sectionEl.querySelectorAll('thead th');

        headers.forEach((header, headerIndex) => {
            const sortField = sortMap[section][headerIndex];

            if (!sortField) {
                return;
            }

            header.style.cursor = 'pointer';

            header.addEventListener('click', () => {
                const current = SweetDeskPeople.sort[section];

                if (current.field === sortField) {
                    current.order = current.order === 'asc' ? 'desc' : 'asc';
                } else {
                    current.field = sortField;
                    current.order = 'asc';
                }

                loadPeople();
            });
        });
    });
}

document.getElementById('sd-delete-person-modal')?.addEventListener('click', function (e) {
    if (e.target === this) {
        closeDeletePersonModal();
    }
});

document.addEventListener('DOMContentLoaded', async () => {
    document.getElementById('typeInternal')?.addEventListener('click', () => setType('internal'));
    document.getElementById('typeClient')?.addEventListener('click', () => setType('client'));

    setType('internal');

    document.getElementById('sd-new-person')?.addEventListener('click', openNewPersonSidebar);
    document.getElementById('person-panel-submit')?.addEventListener('click', savePerson);
    document.getElementById('person-panel-toggle-active')?.addEventListener('click', toggleSidebarPersonActive);

    const headerButtons = document.querySelectorAll('.header-actions .btn-outline');
    headerButtons[1]?.addEventListener('click', exportPeopleCsv);

    setupPeopleImport();
    setupPeopleSearch();
    setupPeopleSort();
    setupTableSelection('internal');
    setupTableSelection('client');

    await loadClientsForFilters();
    await loadPeople();
});
