const SweetDeskPeople = {
    apiUrl: window.sweetdeskPeople?.apiUrl || '/wp-json/sweetdesk/v1',
    nonce: window.sweetdeskPeople?.nonce || '',
    people: [],
    editingPersonId: null,
    deletePersonId: null,
    deletePersonRow: null,
    currentType: 'internal'
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

function setType(type) {
    SweetDeskPeople.currentType = type;

    document.getElementById('typeInternal')?.classList.toggle('active', type === 'internal');
    document.getElementById('typeClient')?.classList.toggle('active', type === 'client');

    const companyField = document.getElementById('companyField');

    if (companyField) {
        companyField.hidden = type === 'internal';
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

function resetPersonForm() {
    SweetDeskPeople.editingPersonId = null;

    document.getElementById('person-panel-name').value = '';
    document.getElementById('person-panel-role').value = '';
    document.getElementById('person-panel-email').value = '';
    document.getElementById('person-panel-phone').value = '';
    document.getElementById('person-panel-company').value = '';
    document.getElementById('person-panel-notes').value = '';

    setType('internal');
}

function openPersonSidebar(mode = 'create', person = {}) {
    const shell = getPersonPanelShell();
    shell?.classList.remove('collapsed');

    SweetDeskPeople.editingPersonId = mode === 'edit' ? Number(person.id) : null;

    document.getElementById('person-panel-title').textContent =
        mode === 'create' ? 'Add New Person' : 'Edit Person';

    document.getElementById('person-panel-submit').textContent =
        mode === 'create' ? 'Add Person' : 'Save Changes';

    if (mode === 'create') {
        resetPersonForm();
        return;
    }

    document.getElementById('person-panel-name').value = fullName(person);
    document.getElementById('person-panel-role').value = person.role || '';
    document.getElementById('person-panel-email').value = person.email || '';
    document.getElementById('person-panel-phone').value = getMetaValue(person, 'phone');
    document.getElementById('person-panel-company').value = getMetaValue(person, 'company');
    document.getElementById('person-panel-notes').value = getMetaValue(person, 'notes');

    setType(person.wp_user_id ? 'internal' : 'client');
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

function getPersonPayload() {
    const name = document.getElementById('person-panel-name').value;
    const role = document.getElementById('person-panel-role').value;
    const email = document.getElementById('person-panel-email').value;
    const phone = document.getElementById('person-panel-phone').value;
    const company = document.getElementById('person-panel-company').value;
    const notes = document.getElementById('person-panel-notes').value;

    const parsedName = splitName(name);

    return {
        wp_user_id: SweetDeskPeople.currentType === 'internal' ? null : null,
        client_id: null,
        first_name: parsedName.first_name,
        last_name: parsedName.last_name,
        email,
        role,
        avatar_url: null,
        is_active: true,
        meta: {
            phone,
            company,
            notes
        },
        team_ids: []
    };
}

async function savePerson() {
    const payload = getPersonPayload();
    const id = SweetDeskPeople.editingPersonId;

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
        <div class="row-actions">
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

function renderInternalRow(person) {
    const phone = getMetaValue(person, 'phone');

    return `
        <tr>
            <td class="col-check"><input type="checkbox" /></td>
            <td class="col-name">${fullName(person)}</td>
            <td>${person.role || ''}</td>
            <td><a href="mailto:${person.email || ''}" class="email-link">${person.email || ''}</a></td>
            <td>${phone || ''}</td>
            <td>${personActionButtons(person)}</td>
        </tr>
    `;
}

function renderClientRow(person) {
    const phone = getMetaValue(person, 'phone');
    const company = getMetaValue(person, 'company');

    return `
        <tr>
            <td class="col-check"><input type="checkbox" /></td>
            <td class="col-name">${fullName(person)}</td>
            <td>${person.role || ''}</td>
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

    const internalPeople = SweetDeskPeople.people.filter(person => person.wp_user_id || person.role === 'staff');
    const clientPeople = SweetDeskPeople.people.filter(person => !person.wp_user_id && person.role !== 'staff');

    tables.internal.innerHTML = internalPeople.length
        ? internalPeople.map(renderInternalRow).join('')
        : `<tr><td colspan="6">No Ambrosia personnel found.</td></tr>`;

    tables.clients.innerHTML = clientPeople.length
        ? clientPeople.map(renderClientRow).join('')
        : `<tr><td colspan="7">No client contacts found.</td></tr>`;
}

async function loadPeople() {
    try {
        const response = await sdApi('/people?per_page=100&sort=last_name&order=asc');
        SweetDeskPeople.people = response.data || [];
        renderPeople();
    } catch (error) {
        alert(error.message);
    }
}

function exportPeopleCsv() {
    window.location.href = `${SweetDeskPeople.apiUrl}/people/export`;
}

document.getElementById('sd-delete-person-modal')?.addEventListener('click', function (e) {
    if (e.target === this) {
        closeDeletePersonModal();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    setType('internal');

    document.getElementById('sd-new-person')?.addEventListener('click', openNewPersonSidebar);
    document.getElementById('person-panel-submit')?.addEventListener('click', savePerson);

    const headerButtons = document.querySelectorAll('.header-actions .btn-outline');
    headerButtons[1]?.addEventListener('click', exportPeopleCsv);

    loadPeople();
});
