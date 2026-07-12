const SweetDeskPeople = {
    apiUrl: window.sweetdeskPeople?.apiUrl || '/wp-json/sweetdesk/v1',
    nonce: window.sweetdeskPeople?.nonce || '',
    people: [],
    clients: [],
    teams: [],
    selectedTeamIds: [],
    filterTeamIds: [],
    editingPersonId: null,
    editingPerson: null,
    deletePersonId: null,
    deletePersonRow: null,
    currentType: 'client',
    currentPage: 1,
    totalPages: 1,
    perPage: 25,
    searchTimer: null,
    sort: { field: 'last_name', order: 'asc' }
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
    return SweetDeskPeople.currentType;
}

function setFieldHidden(element, hidden) {
    if (!element) {
        return;
    }

    element.hidden = hidden;
    element.classList.toggle('sd-form-hidden', hidden);
}

function setType(type) {
    SweetDeskPeople.currentType = type;

    const typeInternal = document.getElementById('typeInternal');
    const typeClient = document.getElementById('typeClient');

    typeInternal?.classList.toggle('active', type === 'internal');
    typeClient?.classList.toggle('active', type === 'client');

    setFieldHidden(document.getElementById('companyField'), type === 'internal');
    setFieldHidden(document.getElementById('roleField'), type === 'client');
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

function getPeopleSectionElement() {
    return document.querySelector('.section-people');
}

function personRowCheckbox(person) {
    return `<input type="checkbox" class="person-row-check" data-person-id="${person.id}" aria-label="Select ${escapeHtml(fullName(person))}" />`;
}

function getClientName(clientId) {
    if (!clientId) {
        return '';
    }

    const client = SweetDeskPeople.clients.find(item => Number(item.id) === Number(clientId));
    return client?.name || '';
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function getPersonTeams(person) {
    return Array.isArray(person.teams) ? person.teams : [];
}

function getPrimaryTeamName(person) {
    const teams = getPersonTeams(person);
    return teams[0]?.name || '';
}

function renderTeamBadges(person, options = {}) {
    const max = options.max ?? null;
    const teams = getPersonTeams(person);

    if (!teams.length) {
        return '';
    }

    const visibleTeams = max ? teams.slice(0, max) : teams;
    const overflow = max && teams.length > max ? teams.length - max : 0;

    const badges = visibleTeams.map(team => {
        const name = escapeHtml(team.name || 'Unnamed Team');

        return `<span class="team-badge" style="${getTeamBadgeStyle(team.color)}">${name}</span>`;
    }).join('');

    const overflowBadge = overflow
        ? `<span class="team-badge team-badge-overflow">+${overflow}</span>`
        : '';

    return `<span class="person-team-badges">${badges}${overflowBadge}</span>`;
}

function getTeamById(teamId) {
    return SweetDeskPeople.teams.find(team => Number(team.id) === Number(teamId)) || null;
}

function getSelectedTeamIds() {
    return [...SweetDeskPeople.selectedTeamIds];
}

function setSelectedTeamIds(teamIds) {
    SweetDeskPeople.selectedTeamIds = [...new Set(
        (teamIds || []).map(id => Number(id)).filter(id => id > 0)
    )];
    renderPersonTeamPicker();
}

function addSelectedTeam(teamId) {
    const id = Number(teamId);

    if (!id || SweetDeskPeople.selectedTeamIds.includes(id)) {
        return;
    }

    SweetDeskPeople.selectedTeamIds.push(id);
    renderPersonTeamPicker();
}

function removeSelectedTeam(teamId) {
    const id = Number(teamId);
    SweetDeskPeople.selectedTeamIds = SweetDeskPeople.selectedTeamIds.filter(item => item !== id);
    renderPersonTeamPicker();
}

function renderPersonTeamPicker() {
    const selectedContainer = document.getElementById('person-panel-teams-selected');
    const addSelect = document.getElementById('person-panel-teams-add');

    if (!selectedContainer || !addSelect) {
        return;
    }

    const selectedIds = getSelectedTeamIds();
    const availableTeams = SweetDeskPeople.teams.filter(
        team => !selectedIds.includes(Number(team.id))
    );

    if (!selectedIds.length) {
        selectedContainer.innerHTML = '<p class="person-team-picker-empty">No teams assigned.</p>';
    } else {
        selectedContainer.innerHTML = selectedIds.map(teamId => {
            const team = getTeamById(teamId);
            const name = escapeHtml(team?.name || `Team #${teamId}`);

            return `
                <span class="team-picker-chip" style="${getTeamBadgeStyle(team?.color)}">
                    <span class="team-picker-chip-label">${name}</span>
                    <button
                        type="button"
                        class="team-picker-chip-remove"
                        data-team-id="${teamId}"
                        aria-label="Remove ${name}"
                    >✕</button>
                </span>
            `;
        }).join('');
    }

    addSelect.innerHTML = `<option value="">Add a team...</option>`;

    availableTeams.forEach(team => {
        const option = document.createElement('option');
        option.value = String(team.id);
        option.textContent = team.name || `Team #${team.id}`;
        addSelect.appendChild(option);
    });

    addSelect.disabled = availableTeams.length === 0;
}

function getFilterTeamIds() {
    return [...SweetDeskPeople.filterTeamIds];
}

function setFilterTeamIds(teamIds) {
    SweetDeskPeople.filterTeamIds = [...new Set(
        (teamIds || []).map(id => Number(id)).filter(id => id > 0)
    )];
    renderPeopleTeamFilter();
}

function getTeamFilterLabelText() {
    const selectedIds = getFilterTeamIds();

    if (!selectedIds.length) {
        return 'All Teams';
    }

    const selectedTeams = selectedIds
        .map(id => getTeamById(id))
        .filter(Boolean)
        .sort((left, right) =>
            (left.name || '').localeCompare(right.name || '', undefined, { sensitivity: 'base' })
        );

    const firstName = selectedTeams[0]?.name || `Team #${selectedIds[0]}`;

    if (selectedTeams.length === 1) {
        return firstName;
    }

    return `${firstName} (+${selectedTeams.length - 1})`;
}

function updateTeamFilterLabel() {
    const label = document.getElementById('people-team-filter-label');
    const trigger = document.getElementById('people-team-filter-trigger');

    if (!label) {
        return;
    }

    const selectedIds = getFilterTeamIds();

    if (!selectedIds.length) {
        label.innerHTML = '<span class="people-team-filter-label-text">All Teams</span>';
    } else {
        const selectedTeams = selectedIds
            .map(id => getTeamById(id))
            .filter(Boolean)
            .sort((left, right) =>
                (left.name || '').localeCompare(right.name || '', undefined, { sensitivity: 'base' })
            );

        const firstName = escapeHtml(selectedTeams[0]?.name || `Team #${selectedIds[0]}`);

        if (selectedTeams.length === 1) {
            label.innerHTML = `<span class="people-team-filter-label-text">${firstName}</span>`;
        } else {
            const moreCount = selectedTeams.length - 1;
            label.innerHTML = `
                <span class="people-team-filter-label-text">${firstName}</span>
                <span class="people-team-filter-label-more">(+${moreCount})</span>
            `;
        }
    }

    if (trigger) {
        trigger.setAttribute('aria-label', `Filter by team: ${getTeamFilterLabelText()}`);
    }
}

function toggleFilterTeam(teamId, selected) {
    const id = Number(teamId);

    if (!id) {
        return;
    }

    if (selected) {
        if (!SweetDeskPeople.filterTeamIds.includes(id)) {
            SweetDeskPeople.filterTeamIds.push(id);
        }
    } else {
        SweetDeskPeople.filterTeamIds = SweetDeskPeople.filterTeamIds.filter(item => item !== id);
    }

    updateTeamFilterLabel();

    const checkbox = document.querySelector(
        `#people-team-filter-list input[value="${id}"]`
    );

    if (checkbox) {
        checkbox.checked = selected;
    }

    resetToFirstPageAndLoad();
}

function closePeopleTeamFilterMenu() {
    const menu = document.getElementById('people-team-filter-menu');
    const trigger = document.getElementById('people-team-filter-trigger');

    if (menu) {
        menu.hidden = true;
    }

    if (trigger) {
        trigger.setAttribute('aria-expanded', 'false');
    }
}

function renderPeopleTeamFilter() {
    const list = document.getElementById('people-team-filter-list');

    if (!list) {
        return;
    }

    const selectedIds = getFilterTeamIds();

    updateTeamFilterLabel();

    if (!SweetDeskPeople.teams.length) {
        list.innerHTML = '<li class="people-team-filter-empty">No teams available</li>';
        return;
    }

    list.innerHTML = SweetDeskPeople.teams.map(team => {
        const id = Number(team.id);
        const name = escapeHtml(team.name || `Team #${id}`);
        const checked = selectedIds.includes(id) ? 'checked' : '';

        return `
            <li class="people-team-filter-option" role="option">
                <label>
                    <input type="checkbox" value="${id}" ${checked}>
                    <span>${name}</span>
                </label>
            </li>
        `;
    }).join('');
}

function getPersonTeamIds(person) {
    const teams = getPersonTeams(person);

    if (teams.length) {
        return teams.map(team => Number(team.team_id ?? team.id)).filter(Boolean);
    }

    if (Array.isArray(person.team_ids)) {
        return person.team_ids.map(id => Number(id)).filter(Boolean);
    }

    return [];
}

async function loadTeamsForPicker() {
    try {
        const response = await sdApi('/teams?per_page=100');
        SweetDeskPeople.teams = (response.data || []).sort((left, right) =>
            (left.name || '').localeCompare(right.name || '', undefined, { sensitivity: 'base' })
        );
        renderPersonTeamPicker();
        renderPeopleTeamFilter();
    } catch (error) {
        console.warn(error.message);
    }
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
    setSelectedTeamIds([]);

    setType('client');
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
    setSelectedTeamIds(getPersonTeamIds(person));

    setType(personType);
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
        meta: {
            phone,
            notes
        },
        team_ids: getSelectedTeamIds()
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
        payload.role = 'client';
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

        closeDeletePersonModal();

        if (SweetDeskPeople.people.length <= 1 && SweetDeskPeople.currentPage > 1) {
            SweetDeskPeople.currentPage--;
        }

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

async function bulkDeletePeople() {
    const checked = getCheckedBoxes();

    if (!checked.length) {
        return;
    }

    const count = checked.length;
    const confirmed = confirm(`Delete ${count} ${count === 1 ? 'person' : 'people'}? This cannot be undone.`);

    if (!confirmed) {
        return;
    }

    try {
        await Promise.all(
            checked.map(checkbox => sdApi(`/people/${checkbox.dataset.personId}`, {
                method: 'DELETE'
            }))
        );

        if (SweetDeskPeople.people.length <= count && SweetDeskPeople.currentPage > 1) {
            SweetDeskPeople.currentPage--;
        }

        await loadPeople();
    } catch (error) {
        alert(error.message);
    }
}

function getCheckedBoxes() {
    const sectionEl = getPeopleSectionElement();

    if (!sectionEl) {
        return [];
    }

    return [...sectionEl.querySelectorAll('.person-row-check:checked')];
}

function syncSelectAllCheckbox() {
    const sectionEl = getPeopleSectionElement();
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

function updateBulkActions() {
    const bulkBar = document.getElementById('people-bulk-actions');
    const deleteBtn = document.getElementById('people-bulk-delete');
    const checked = getCheckedBoxes();

    setFieldHidden(bulkBar, checked.length === 0);

    if (deleteBtn && checked.length > 0) {
        deleteBtn.textContent = `Delete Selected (${checked.length})`;
    }

    syncSelectAllCheckbox();
}

function setupTableSelection() {
    const sectionEl = getPeopleSectionElement();

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

        updateBulkActions();
    });

    tbody?.addEventListener('change', event => {
        if (event.target.classList.contains('person-row-check')) {
            updateBulkActions();
        }
    });

    document.getElementById('people-bulk-delete')?.addEventListener('click', () => {
        bulkDeletePeople();
    });
}

function personNameCell(person) {
    const name = escapeHtml(fullName(person));
    return `<span class="person-name-cell"><span class="person-name-text">${name}</span></span>`;
}

function renderPersonRow(person) {
    const company = getClientName(person.client_id);

    return `
        <tr>
            <td class="col-check">${personRowCheckbox(person)}</td>
            <td class="col-name">${personNameCell(person)}</td>
            <td>${escapeHtml(person.role || '')}</td>
            <td>${escapeHtml(company)}</td>
            <td class="col-teams">${renderTeamBadges(person, { max: 3 })}</td>
            <td><a href="mailto:${escapeHtml(person.email || '')}" class="email-link">${escapeHtml(person.email || '')}</a></td>
            <td>${escapeHtml(getMetaValue(person, 'phone') || '')}</td>
            <td>${personActionButtons(person)}</td>
        </tr>
    `;
}

function sortPeopleList(people) {
    const { field, order } = SweetDeskPeople.sort;
    const direction = order === 'asc' ? 1 : -1;

    return [...people].sort((a, b) => {
        let left;
        let right;

        if (field === 'company') {
            left = getClientName(a.client_id).toLowerCase();
            right = getClientName(b.client_id).toLowerCase();
        } else if (field === 'teams') {
            left = getPrimaryTeamName(a).toLowerCase();
            right = getPrimaryTeamName(b).toLowerCase();
        } else {
            left = String(a[field] ?? '').toLowerCase();
            right = String(b[field] ?? '').toLowerCase();
        }

        if (left < right) return -1 * direction;
        if (left > right) return 1 * direction;
        return 0;
    });
}

function renderPeople() {
    const tbody = document.getElementById('people-table-body');

    if (!tbody) {
        return;
    }

    const rows = sortPeopleList(SweetDeskPeople.people);

    tbody.innerHTML = rows.length
        ? rows.map(renderPersonRow).join('')
        : `<tr><td colspan="8">No people found.</td></tr>`;

    updateBulkActions();
    syncSelectAllCheckbox();
}

function buildPeopleQuery() {
    const sortState = SweetDeskPeople.sort;

    const params = new URLSearchParams({
        page: String(SweetDeskPeople.currentPage),
        per_page: String(SweetDeskPeople.perPage),
        sort: sortState.field === 'company' || sortState.field === 'teams'
            ? 'last_name'
            : sortState.field,
        order: sortState.order
    });

    const q = document.getElementById('people-search')?.value?.trim();

    if (q) {
        params.set('q', q);
    }

    const role = document.getElementById('people-role-filter')?.value;

    if (role) {
        params.set('roles', role);
    }

    const clientId = document.getElementById('people-company-filter')?.value;

    if (clientId) {
        params.set('client_ids', clientId);
    }

    const teamIds = getFilterTeamIds();

    if (teamIds.length) {
        params.set('team_ids', teamIds.join(','));
    }

    return params;
}

function updatePagination() {
    const pageInfo = document.getElementById('sd-people-page-info');
    const prevBtn = document.getElementById('sd-people-prev-page');
    const nextBtn = document.getElementById('sd-people-next-page');

    if (pageInfo) {
        pageInfo.textContent = `Page ${SweetDeskPeople.currentPage} of ${SweetDeskPeople.totalPages}`;
    }

    if (prevBtn) {
        prevBtn.disabled = SweetDeskPeople.currentPage <= 1;
    }

    if (nextBtn) {
        nextBtn.disabled = SweetDeskPeople.currentPage >= SweetDeskPeople.totalPages;
    }
}

async function loadPeople() {
    try {
        const params = buildPeopleQuery();
        const response = await sdApi(`/people?${params.toString()}`);

        SweetDeskPeople.people = response.data || [];
        SweetDeskPeople.currentPage = Number(response?.pagination?.page) || 1;
        SweetDeskPeople.totalPages = Number(response?.pagination?.total_pages) || 1;

        renderPeople();
        updatePagination();
        populateRoleFilter();
    } catch (error) {
        alert(error.message);
    }
}

function populateRoleFilter() {
    const roleFilter = document.getElementById('people-role-filter');

    if (!roleFilter) {
        return;
    }

    const knownRoles = ['client', 'manager', 'staff'];
    const roles = [...new Set([
        ...knownRoles,
        ...SweetDeskPeople.people.map(person => person.role).filter(Boolean)
    ])].sort();

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

        const companyFilter = document.getElementById('people-company-filter');
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
    const q = document.getElementById('people-search')?.value?.trim();
    const role = document.getElementById('people-role-filter')?.value;
    const clientId = document.getElementById('people-company-filter')?.value;

    if (q) {
        params.set('q', q);
    }

    if (role) {
        params.set('roles', role);
    }

    if (clientId) {
        params.set('client_ids', clientId);
    }

    const teamIds = getFilterTeamIds();

    if (teamIds.length) {
        params.set('team_ids', teamIds.join(','));
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
    const importButton = document.getElementById('sd-import-people');

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
            SweetDeskPeople.currentPage = 1;
            await loadPeople();
        } catch (error) {
            alert(error.message);
        }
    });
}

function resetToFirstPageAndLoad() {
    SweetDeskPeople.currentPage = 1;
    loadPeople();
}

function setupPeopleSearch() {
    document.getElementById('people-search')?.addEventListener('input', () => {
        clearTimeout(SweetDeskPeople.searchTimer);
        SweetDeskPeople.searchTimer = setTimeout(() => resetToFirstPageAndLoad(), 300);
    });

    document.getElementById('people-role-filter')?.addEventListener('change', () => resetToFirstPageAndLoad());
    document.getElementById('people-company-filter')?.addEventListener('change', () => resetToFirstPageAndLoad());
}

function setupPeopleSort() {
    const sectionEl = getPeopleSectionElement();

    if (!sectionEl) {
        return;
    }

    const sortMap = {
        1: 'last_name',
        2: 'role',
        3: 'company',
        4: 'teams',
        5: 'email'
    };

    const headers = sectionEl.querySelectorAll('thead th');

    headers.forEach((header, headerIndex) => {
        const sortField = sortMap[headerIndex];

        if (!sortField) {
            return;
        }

        header.style.cursor = 'pointer';

        header.addEventListener('click', () => {
            const current = SweetDeskPeople.sort;

            if (current.field === sortField) {
                current.order = current.order === 'asc' ? 'desc' : 'asc';
            } else {
                current.field = sortField;
                current.order = 'asc';
            }

            if (sortField === 'company' || sortField === 'teams') {
                renderPeople();
                return;
            }

            loadPeople();
        });
    });
}

function setupPagination() {
    document.getElementById('sd-people-prev-page')?.addEventListener('click', () => {
        if (SweetDeskPeople.currentPage > 1) {
            SweetDeskPeople.currentPage--;
            loadPeople();
        }
    });

    document.getElementById('sd-people-next-page')?.addEventListener('click', () => {
        if (SweetDeskPeople.currentPage < SweetDeskPeople.totalPages) {
            SweetDeskPeople.currentPage++;
            loadPeople();
        }
    });
}

function setupPersonTeamPicker() {
    document.getElementById('person-panel-teams-add')?.addEventListener('change', event => {
        const teamId = event.target.value;

        if (!teamId) {
            return;
        }

        addSelectedTeam(teamId);
        event.target.value = '';
    });

    document.getElementById('person-panel-teams-selected')?.addEventListener('click', event => {
        const button = event.target.closest('.team-picker-chip-remove');

        if (!button) {
            return;
        }

        removeSelectedTeam(button.dataset.teamId);
    });
}

function setupPeopleTeamFilter() {
    const filter = document.getElementById('people-team-filter');
    const trigger = document.getElementById('people-team-filter-trigger');
    const menu = document.getElementById('people-team-filter-menu');
    const list = document.getElementById('people-team-filter-list');

    if (!filter || !trigger || !menu || !list) {
        return;
    }

    trigger.addEventListener('click', event => {
        event.stopPropagation();
        const isOpen = !menu.hidden;
        menu.hidden = isOpen;
        trigger.setAttribute('aria-expanded', String(!isOpen));
    });

    list.addEventListener('change', event => {
        const checkbox = event.target;

        if (checkbox.type !== 'checkbox') {
            return;
        }

        toggleFilterTeam(checkbox.value, checkbox.checked);
    });

    menu.addEventListener('click', event => {
        event.stopPropagation();
    });

    document.addEventListener('click', event => {
        if (!filter.contains(event.target)) {
            closePeopleTeamFilterMenu();
        }
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

    setType('client');

    document.getElementById('sd-new-person')?.addEventListener('click', openNewPersonSidebar);
    document.getElementById('person-panel-submit')?.addEventListener('click', savePerson);

    document.getElementById('sd-export-people')?.addEventListener('click', exportPeopleCsv);

    setupPeopleImport();
    setupPeopleSearch();
    setupPeopleSort();
    setupPagination();
    setupPersonTeamPicker();
    setupPeopleTeamFilter();
    setupTableSelection();

    await loadTeamsForPicker();
    await loadClientsForFilters();
    await loadPeople();
});
