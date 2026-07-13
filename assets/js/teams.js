(() => {
    'use strict';

    const apiConfig = window.sweetdeskTeams;

    if (!apiConfig?.apiBase || !apiConfig?.nonce || !apiConfig?.peopleApiUrl) {
        console.error(
            'SweetDesk teams API configuration is missing. ' +
                'Make sure teams.js is localized with apiBase, peopleApiUrl, and nonce.'
        );
        return;
    }

    const state = {
        page: 1,
        perPage: 25,
        totalPages: 1,
        search: '',
        teams: [],
        teamToDelete: null,
        selectedNewMembers: new Map(),
        selectedEditMembers: new Map(),
        allPeople: [],
        allPeopleLoaded: false,
        addMemberSearch: {
            new: '',
            edit: '',
        },
    };

    const elements = {};

    const teamIcon = `
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="1.8"
            aria-hidden="true"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857
                   M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857
                   M7 20H2v-2a3 3 0 015.356-1.857
                   M7 20v-2c0-.656.126-1.283.356-1.857
                   m0 0a5.002 5.002 0 019.288 0
                   M15 7a3 3 0 11-6 0 3 3 0 016 0z"
            />
        </svg>
    `;

    const emailIcon = `
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
            aria-hidden="true"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8
                   M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2
                   H5a2 2 0 00-2 2v10a2 2 0 002 2z"
            />
        </svg>
    `;

    const deleteIcon = `
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
            aria-hidden="true"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21
                   H7.862a2 2 0 01-1.995-1.858L5 7
                   m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1
                   h-4a1 1 0 00-1 1v3M4 7h16"
            />
        </svg>
    `;

    document.addEventListener('DOMContentLoaded', initializeTeamsPage);

    function initializeTeamsPage() {
        cacheElements();
        registerEventListeners();
        setupAddMemberPicker();
        loadTeams();
    }

    function cacheElements() {
        elements.teamsGrid = document.getElementById('sd-teams-grid');
        elements.pagination = document.getElementById('sd-teams-pagination');
        elements.previousPage = document.getElementById('sd-teams-prev');
        elements.nextPage = document.getElementById('sd-teams-next');
        elements.pageInfo = document.getElementById('sd-teams-page-info');

        elements.newTeamButton = document.getElementById('sd-new-team');
        elements.newTeamModal = document.getElementById('newTeamModal');
        elements.newTeamForm = document.getElementById('new-team-form');
        elements.newTeamClose = document.getElementById('new-team-close');
        elements.newTeamCancel = document.getElementById('new-team-cancel');
        elements.newTeamName = document.getElementById('new-team-name');
        elements.newTeamDescription =
            document.getElementById('new-team-desc');
        elements.newTeamColor = document.getElementById('new-team-color');
        elements.newTeamColorTrigger = document.getElementById(
            'new-team-color-trigger'
        );
        elements.newTeamColorPreview = document.getElementById(
            'new-team-color-preview'
        );
        elements.newTeamColorBadge = document.getElementById(
            'new-team-color-badge'
        );
        elements.newMemberAddPicker = document.getElementById(
            'new-team-member-add-picker'
        );
        elements.newMemberAddTrigger = document.getElementById(
            'new-team-member-add-trigger'
        );
        elements.newMemberAddMenu = document.getElementById(
            'new-team-member-add-menu'
        );
        elements.newMemberAddSearch = elements.newMemberAddPicker?.querySelector(
            '.team-member-add-search'
        );
        elements.newMemberAddList = elements.newMemberAddPicker?.querySelector(
            '.team-member-add-list'
        );
        elements.newMemberList = document.getElementById(
            'new-team-member-list'
        );
        elements.newMemberFilter = document.getElementById(
            'new-team-member-filter'
        );
        elements.newTeamSubmit = document.getElementById('new-team-submit');

        elements.editTeamModal = document.getElementById('editTeamModal');
        elements.editTeamForm = document.getElementById('edit-team-form');
        elements.editTeamClose = document.getElementById('edit-team-close');
        elements.editTeamCancel = document.getElementById('edit-team-cancel');
        elements.editTeamId = document.getElementById('edit-team-id');
        elements.editTeamName = document.getElementById('edit-team-name');
        elements.editTeamDescription =
            document.getElementById('edit-team-desc');
        elements.editTeamColor = document.getElementById('edit-team-color');
        elements.editTeamColorTrigger = document.getElementById(
            'edit-team-color-trigger'
        );
        elements.editTeamColorPreview = document.getElementById(
            'edit-team-color-preview'
        );
        elements.editTeamColorBadge = document.getElementById(
            'edit-team-color-badge'
        );
        elements.editMemberAddPicker = document.getElementById(
            'edit-team-member-add-picker'
        );
        elements.editMemberAddTrigger = document.getElementById(
            'edit-team-member-add-trigger'
        );
        elements.editMemberAddMenu = document.getElementById(
            'edit-team-member-add-menu'
        );
        elements.editMemberAddSearch = elements.editMemberAddPicker?.querySelector(
            '.team-member-add-search'
        );
        elements.editMemberAddList = elements.editMemberAddPicker?.querySelector(
            '.team-member-add-list'
        );
        elements.editMemberList = document.getElementById(
            'edit-team-member-list'
        );
        elements.editMemberFilter = document.getElementById(
            'edit-team-member-filter'
        );
        elements.editTeamSubmit = document.getElementById('edit-team-submit');

        elements.deleteTeamModal = document.getElementById('deleteTeamModal');
        elements.deleteTeamName = document.getElementById('delete-team-name');
        elements.confirmDeleteTeam = document.getElementById(
            'confirm-delete-team'
        );
    }

    function registerEventListeners() {
        elements.newTeamButton?.addEventListener('click', openNewTeamModal);
        elements.newTeamClose?.addEventListener('click', closeNewTeamModal);
        elements.newTeamCancel?.addEventListener('click', closeNewTeamModal);
        elements.newTeamForm?.addEventListener('submit', createTeam);
        elements.newTeamColor?.addEventListener('input', () => {
            updateTeamColorPreview('new');
        });
        elements.newTeamName?.addEventListener('input', () => {
            updateTeamColorPreview('new');
        });
        elements.newTeamColorTrigger?.addEventListener('click', () => {
            elements.newTeamColor?.click();
        });

        elements.editTeamClose?.addEventListener('click', closeEditTeamModal);
        elements.editTeamCancel?.addEventListener('click', closeEditTeamModal);
        elements.editTeamForm?.addEventListener('submit', updateTeam);
        elements.editTeamColor?.addEventListener('input', () => {
            updateTeamColorPreview('edit');
        });
        elements.editTeamName?.addEventListener('input', () => {
            updateTeamColorPreview('edit');
        });
        elements.editTeamColorTrigger?.addEventListener('click', () => {
            elements.editTeamColor?.click();
        });

        elements.confirmDeleteTeam?.addEventListener(
            'click',
            deleteSelectedTeam
        );

        elements.previousPage?.addEventListener('click', () => {
            if (state.page <= 1) {
                return;
            }

            state.page -= 1;
            loadTeams();
        });

        elements.nextPage?.addEventListener('click', () => {
            if (state.page >= state.totalPages) {
                return;
            }

            state.page += 1;
            loadTeams();
        });

        elements.newMemberFilter?.addEventListener('input', () => {
            renderSelectedMembers('new');
        });

        elements.editMemberFilter?.addEventListener('input', () => {
            renderSelectedMembers('edit');
        });

        elements.teamsGrid?.addEventListener('click', handleTeamGridClick);

        elements.newMemberList?.addEventListener(
            'click',
            handleSelectedMemberRemoval
        );

        elements.editMemberList?.addEventListener(
            'click',
            handleSelectedMemberRemoval
        );

        elements.newTeamModal?.addEventListener('click', (event) => {
            if (event.target === elements.newTeamModal) {
                closeNewTeamModal();
            }
        });

        elements.editTeamModal?.addEventListener('click', (event) => {
            if (event.target === elements.editTeamModal) {
                closeEditTeamModal();
            }
        });

        elements.deleteTeamModal?.addEventListener('click', (event) => {
            if (event.target === elements.deleteTeamModal) {
                closeDeleteTeamModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            const newMenuOpen =
                elements.newMemberAddMenu &&
                !elements.newMemberAddMenu.hidden;
            const editMenuOpen =
                elements.editMemberAddMenu &&
                !elements.editMemberAddMenu.hidden;

            if (newMenuOpen || editMenuOpen) {
                if (newMenuOpen) {
                    closeAddMemberMenu('new');
                }

                if (editMenuOpen) {
                    closeAddMemberMenu('edit');
                }

                return;
            }

            closeNewTeamModal();
            closeEditTeamModal();
            closeDeleteTeamModal();
        });
    }

    async function apiRequest(path = '', options = {}) {
        const url = `${apiConfig.apiBase}${path}`;

        const headers = {
            Accept: 'application/json',
            'X-WP-Nonce': apiConfig.nonce,
            ...options.headers,
        };

        if (options.body && !headers['Content-Type']) {
            headers['Content-Type'] = 'application/json';
        }

        const response = await fetch(url, {
            credentials: 'same-origin',
            ...options,
            headers,
        });

        const responseText = await response.text();
        let data = null;

        if (responseText) {
            try {
                data = JSON.parse(responseText);
            } catch {
                data = {
                    message: responseText,
                };
            }
        }

        if (!response.ok) {
            const message =
                data?.message ||
                data?.data?.message ||
                `Request failed with status ${response.status}.`;

            throw new Error(message);
        }

        return data;
    }

    async function loadTeams() {
        setTeamsLoadingState();

        const query = new URLSearchParams({
            page: String(state.page),
            per_page: String(state.perPage),
        });

        if (state.search) {
            query.set('q', state.search);
        }

        try {
            const response = await apiRequest(`?${query.toString()}`);

            state.teams = Array.isArray(response?.data)
                ? response.data
                : [];

            state.page = Number(response?.pagination?.page) || 1;
            state.totalPages =
                Number(response?.pagination?.total_pages) || 1;

            renderTeams();
            renderPagination(response?.pagination);
        } catch (error) {
            console.error('Unable to load teams:', error);
            renderTeamsError(error.message);
        }
    }

    function renderTeams() {
        if (!state.teams.length) {
            elements.teamsGrid.innerHTML = `
                <div class="sd-empty-state">
                    <p>No teams were found.</p>
                    <button
                        type="button"
                        class="btn-primary"
                        data-action="create-team"
                    >
                        Create your first team
                    </button>
                </div>
            `;
            return;
        }

        elements.teamsGrid.innerHTML = state.teams
            .map((team) => createTeamCardMarkup(team))
            .join('');
    }

    function createTeamCardMarkup(team) {
        const members = Array.isArray(team.members) ? team.members : [];
        const memberCount = members.length;
        const memberLabel = memberCount === 1 ? 'member' : 'members';

        const memberMarkup = members.length
            ? members
                  .map((member) => {
                      const person = member.person || {};
                      const fullName = getPersonName(person);

                      return `
                          <li>
                              ${escapeHtml(fullName)}
                          </li>
                      `;
                  })
                  .join('')
            : '<li>No members assigned</li>';

        const emails = members
            .map((member) => member.person?.email)
            .filter(Boolean)
            .join(',');

        const color = isValidHexColor(team.color)
            ? team.color
            : '#2563eb';

        return `
            <article
                class="team-card"
                data-team-id="${Number(team.id)}"
                style="--team-color: ${escapeAttribute(color)};"
            >
                <div class="team-card-header">
                    ${teamIcon}

                    <div class="team-card-header-text">
                        <h3>${escapeHtml(team.name || 'Unnamed Team')}</h3>
                        <p>
                            ${escapeHtml(
                                team.description || 'No description provided.'
                            )}
                        </p>
                    </div>
                </div>

                <div class="team-members-box">
                    <div class="team-members-heading">
                        <span>Team Members</span>
                        <span class="member-count">
                            ${memberCount} ${memberLabel}
                        </span>
                    </div>

                    <ul class="member-list">
                        ${memberMarkup}
                    </ul>
                </div>

                <div class="team-card-actions">
                    <button
                        type="button"
                        data-action="edit-team"
                        data-team-id="${Number(team.id)}"
                    >
                        Edit Team
                    </button>

                    <button
                        type="button"
                        class="email-btn"
                        data-action="email-team"
                        data-emails="${escapeAttribute(emails)}"
                        ${emails ? '' : 'disabled'}
                    >
                        ${emailIcon}
                        Email Team
                    </button>

                    <button
                        type="button"
                        class="delete-btn"
                        data-action="delete-team"
                        data-team-id="${Number(team.id)}"
                    >
                        ${deleteIcon}
                        Delete
                    </button>
                </div>
            </article>
        `;
    }

    function renderPagination(pagination = {}) {
        const totalPages = Number(pagination.total_pages) || 1;
        const currentPage = Number(pagination.page) || 1;
        const total = Number(pagination.total) || 0;

        state.totalPages = totalPages;

        elements.pagination.hidden = totalPages <= 1;
        elements.pageInfo.textContent =
            `Page ${currentPage} of ${totalPages} (${total} teams)`;

        elements.previousPage.disabled = currentPage <= 1;
        elements.nextPage.disabled = currentPage >= totalPages;
    }

    function setTeamsLoadingState() {
        elements.teamsGrid.innerHTML =
            '<p class="sd-loading-message">Loading teams...</p>';
    }

    function renderTeamsError(message) {
        elements.teamsGrid.innerHTML = `
            <div class="sd-error-message" role="alert">
                <p>${escapeHtml(message)}</p>
                <button
                    type="button"
                    class="btn-secondary"
                    data-action="retry-teams"
                >
                    Try Again
                </button>
            </div>
        `;
    }

    function handleTeamGridClick(event) {
        const actionButton = event.target.closest('[data-action]');

        if (!actionButton) {
            return;
        }

        const action = actionButton.dataset.action;
        const teamId = Number(actionButton.dataset.teamId);

        switch (action) {
            case 'create-team':
                openNewTeamModal();
                break;

            case 'retry-teams':
                loadTeams();
                break;

            case 'edit-team':
                openEditTeamModal(teamId);
                break;

            case 'delete-team':
                openDeleteTeamModal(teamId);
                break;

            case 'email-team':
                emailTeam(actionButton.dataset.emails);
                break;
        }
    }

    function openNewTeamModal() {
        elements.newTeamForm.reset();
        elements.newTeamColor.value = '#2563eb';
        if (elements.newMemberFilter) {
            elements.newMemberFilter.value = '';
        }
        state.selectedNewMembers.clear();
        state.addMemberSearch.new = '';
        closeAddMemberMenu('new');
        renderSelectedMembers('new');

        void loadAllPeople().then(() => {
            renderAddMemberPicker('new');
        });

        updateTeamColorPreview('new');

        elements.newTeamModal.classList.add('active');
        elements.newTeamName.focus();
    }

    function closeNewTeamModal() {
        elements.newTeamModal.classList.remove('active');
        if (elements.newMemberFilter) {
            elements.newMemberFilter.value = '';
        }
        closeAddMemberMenu('new');
        state.addMemberSearch.new = '';
    }

    async function createTeam(event) {
        event.preventDefault();

        const name = elements.newTeamName.value.trim();
        const description = elements.newTeamDescription.value.trim();
        const color = elements.newTeamColor.value;

        if (!name) {
            showMessage('Please enter a team name.', 'error');
            elements.newTeamName.focus();
            return;
        }

        setButtonLoading(
            elements.newTeamSubmit,
            true,
            'Saving...',
            'Save Team'
        );

        try {
            await apiRequest('', {
                method: 'POST',
                body: JSON.stringify({
                    name,
                    description,
                    color,
                    meta: [],
                    people: getSelectedPeoplePayload(
                        state.selectedNewMembers
                    ),
                }),
            });

            closeNewTeamModal();
            state.page = 1;
            await loadTeams();

            showMessage('Team created successfully.', 'success');
        } catch (error) {
            console.error('Unable to create team:', error);
            showMessage(error.message, 'error');
        } finally {
            setButtonLoading(
                elements.newTeamSubmit,
                false,
                'Saving...',
                'Save Team'
            );
        }
    }

    async function openEditTeamModal(teamId) {
        if (!Number.isInteger(teamId) || teamId <= 0) {
            return;
        }

        elements.editTeamModal.classList.add('active');
        elements.editTeamForm.classList.add('is-loading');
        elements.editTeamSubmit.disabled = true;
        if (elements.editMemberFilter) {
            elements.editMemberFilter.value = '';
        }
        state.addMemberSearch.edit = '';
        closeAddMemberMenu('edit');

        try {
            await loadAllPeople();
            const team = await apiRequest(`/${teamId}`);

            elements.editTeamId.value = String(team.id);
            elements.editTeamName.value = team.name || '';
            elements.editTeamDescription.value =
                team.description || '';
            elements.editTeamColor.value = isValidHexColor(team.color)
                ? team.color
                : '#2563eb';

            state.selectedEditMembers.clear();

            const members = Array.isArray(team.members)
                ? team.members
                : [];

            members.forEach((member) => {
                const person = member.person || {};

                if (!person.id) {
                    return;
                }

                state.selectedEditMembers.set(Number(person.id), {
                    id: Number(person.id),
                    first_name: person.first_name || '',
                    last_name: person.last_name || '',
                    email: person.email || '',
                    role: person.role || '',
                });
            });

            renderSelectedMembers('edit');
            renderAddMemberPicker('edit');
            updateTeamColorPreview('edit');
            elements.editMemberAddTrigger?.focus();
        } catch (error) {
            console.error('Unable to load team:', error);
            closeEditTeamModal();
            showMessage(error.message, 'error');
        } finally {
            elements.editTeamForm.classList.remove('is-loading');
            elements.editTeamSubmit.disabled = false;
        }
    }

    function closeEditTeamModal() {
        elements.editTeamModal.classList.remove('active');
        if (elements.editMemberFilter) {
            elements.editMemberFilter.value = '';
        }
        closeAddMemberMenu('edit');
        state.addMemberSearch.edit = '';
        state.selectedEditMembers.clear();
    }

    async function updateTeam(event) {
        event.preventDefault();

        const teamId = Number(elements.editTeamId.value);
        const name = elements.editTeamName.value.trim();
        const description = elements.editTeamDescription.value.trim();
        const color = elements.editTeamColor.value;

        if (!Number.isInteger(teamId) || teamId <= 0) {
            showMessage('A valid team ID was not provided.', 'error');
            return;
        }

        if (!name) {
            showMessage('Please enter a team name.', 'error');
            elements.editMemberAddTrigger?.focus();
            return;
        }

        setButtonLoading(
            elements.editTeamSubmit,
            true,
            'Saving...',
            'Save Changes'
        );

        try {
            await apiRequest(`/${teamId}`, {
                method: 'PUT',
                body: JSON.stringify({
                    name,
                    description,
                    color,
                    meta: [],
                    people: getSelectedPeoplePayload(
                        state.selectedEditMembers
                    ),
                }),
            });

            closeEditTeamModal();
            await loadTeams();

            showMessage('Team updated successfully.', 'success');
        } catch (error) {
            console.error('Unable to update team:', error);
            showMessage(error.message, 'error');
        } finally {
            setButtonLoading(
                elements.editTeamSubmit,
                false,
                'Saving...',
                'Save Changes'
            );
        }
    }

    function openDeleteTeamModal(teamId) {
        const team = state.teams.find(
            (currentTeam) => Number(currentTeam.id) === teamId
        );

        if (!team) {
            showMessage('The selected team could not be found.', 'error');
            return;
        }

        state.teamToDelete = {
            id: Number(team.id),
            name: team.name || 'this team',
        };

        elements.deleteTeamName.textContent = state.teamToDelete.name;
        elements.deleteTeamModal.classList.add('active');
    }

    function closeDeleteTeamModal() {
        elements.deleteTeamModal.classList.remove('active');
        state.teamToDelete = null;
    }

    async function deleteSelectedTeam() {
        if (!state.teamToDelete) {
            return;
        }

        const teamId = state.teamToDelete.id;

        setButtonLoading(
            elements.confirmDeleteTeam,
            true,
            'Deleting...',
            'Delete Team'
        );

        try {
            await apiRequest(`/${teamId}`, {
                method: 'DELETE',
            });

            closeDeleteTeamModal();

            if (state.teams.length === 1 && state.page > 1) {
                state.page -= 1;
            }

            await loadTeams();
            showMessage('Team deleted successfully.', 'success');
        } catch (error) {
            console.error('Unable to delete team:', error);
            showMessage(error.message, 'error');
        } finally {
            setButtonLoading(
                elements.confirmDeleteTeam,
                false,
                'Deleting...',
                'Delete Team'
            );
        }
    }

    async function peopleApiRequest(path = '') {
        const url = `${apiConfig.peopleApiUrl}${path}`;

        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-WP-Nonce': apiConfig.nonce,
            },
        });

        const responseText = await response.text();
        let data = null;

        if (responseText) {
            try {
                data = JSON.parse(responseText);
            } catch {
                data = {
                    message: responseText,
                };
            }
        }

        if (!response.ok) {
            const message =
                data?.message || `Request failed with status ${response.status}`;
            throw new Error(message);
        }

        return data;
    }

    async function loadAllPeople() {
        if (state.allPeopleLoaded) {
            return state.allPeople;
        }

        const allPeople = [];
        let page = 1;
        let totalPages = 1;

        do {
            const params = new URLSearchParams({
                page: String(page),
                per_page: '100',
                sort: 'last_name',
                order: 'asc',
            });

            const response = await peopleApiRequest(`/people?${params.toString()}`);
            const people = Array.isArray(response?.data) ? response.data : [];

            allPeople.push(...people);
            totalPages = Number(response?.pagination?.total_pages) || 1;
            page += 1;
        } while (page <= totalPages);

        state.allPeople = allPeople;
        state.allPeopleLoaded = true;

        return state.allPeople;
    }

    function getAddMemberElements(mode) {
        if (mode === 'new') {
            return {
                picker: elements.newMemberAddPicker,
                trigger: elements.newMemberAddTrigger,
                menu: elements.newMemberAddMenu,
                search: elements.newMemberAddSearch,
                list: elements.newMemberAddList,
            };
        }

        return {
            picker: elements.editMemberAddPicker,
            trigger: elements.editMemberAddTrigger,
            menu: elements.editMemberAddMenu,
            search: elements.editMemberAddSearch,
            list: elements.editMemberAddList,
        };
    }

    function getSelectedMembers(mode) {
        return mode === 'new'
            ? state.selectedNewMembers
            : state.selectedEditMembers;
    }

    function getAvailablePeople(mode) {
        const selectedMembers = getSelectedMembers(mode);

        return state.allPeople.filter(
            (person) => !selectedMembers.has(Number(person.id))
        );
    }

    function personMatchesAddMemberSearch(person, query) {
        if (!query) {
            return true;
        }

        const haystack = [getPersonName(person), person.email || '']
            .join(' ')
            .toLowerCase();

        return haystack.includes(query);
    }

    function closeAddMemberMenu(mode) {
        const { trigger, menu, search } = getAddMemberElements(mode);

        if (menu) {
            menu.hidden = true;
        }

        if (trigger) {
            trigger.setAttribute('aria-expanded', 'false');
        }

        if (search) {
            search.value = '';
        }

        state.addMemberSearch[mode] = '';
    }

    function renderAddMemberPicker(mode) {
        const { list } = getAddMemberElements(mode);

        if (!list) {
            return;
        }

        const query = (state.addMemberSearch[mode] || '').trim().toLowerCase();
        const availablePeople = getAvailablePeople(mode).filter((person) =>
            personMatchesAddMemberSearch(person, query)
        );

        if (!state.allPeopleLoaded) {
            list.innerHTML =
                '<li class="team-member-add-empty">Loading people...</li>';
            return;
        }

        if (!availablePeople.length) {
            list.innerHTML = query
                ? '<li class="team-member-add-empty">No matching people found.</li>'
                : '<li class="team-member-add-empty">Everyone is already on this team.</li>';
            return;
        }

        list.innerHTML = availablePeople
            .map((person) => {
                const fullName = getPersonName(person);

                return `
                    <li class="team-member-add-option" role="option">
                        <button
                            type="button"
                            class="team-member-add-option-btn"
                            data-mode="${escapeAttribute(mode)}"
                            data-person-id="${Number(person.id)}"
                        >
                            <strong>${escapeHtml(fullName)}</strong>
                            ${
                                person.email
                                    ? `<span>${escapeHtml(person.email)}</span>`
                                    : ''
                            }
                        </button>
                    </li>
                `;
            })
            .join('');
    }

    function addMemberFromPicker(mode, personId) {
        const id = Number(personId);
        const person = state.allPeople.find(
            (item) => Number(item.id) === id
        );

        if (!person) {
            return;
        }

        getSelectedMembers(mode).set(id, {
            id,
            first_name: person.first_name || '',
            last_name: person.last_name || '',
            email: person.email || '',
            role: person.role || '',
        });

        renderSelectedMembers(mode);
        renderAddMemberPicker(mode);
        closeAddMemberMenu(mode);
    }

    function setupAddMemberPicker() {
        ['new', 'edit'].forEach((mode) => {
            const { picker, trigger, menu, search, list } =
                getAddMemberElements(mode);

            if (!picker || !trigger || !menu || !list) {
                return;
            }

            trigger.addEventListener('click', (event) => {
                event.stopPropagation();
                const isOpen = !menu.hidden;
                menu.hidden = isOpen;
                trigger.setAttribute('aria-expanded', String(!isOpen));

                if (!isOpen) {
                    void loadAllPeople().then(() => {
                        renderAddMemberPicker(mode);
                        search?.focus();
                    });
                }
            });

            search?.addEventListener('input', (event) => {
                state.addMemberSearch[mode] = event.target.value;
                renderAddMemberPicker(mode);
            });

            search?.addEventListener('click', (event) => {
                event.stopPropagation();
            });

            menu.addEventListener('click', (event) => {
                event.stopPropagation();

                const button = event.target.closest('.team-member-add-option-btn');

                if (!button) {
                    return;
                }

                addMemberFromPicker(button.dataset.mode, button.dataset.personId);
            });
        });

        document.addEventListener('click', (event) => {
            ['new', 'edit'].forEach((mode) => {
                const { picker } = getAddMemberElements(mode);

                if (picker && !picker.contains(event.target)) {
                    closeAddMemberMenu(mode);
                }
            });
        });
    }

    function getMemberListFilter(mode) {
        const filterElement =
            mode === 'new'
                ? elements.newMemberFilter
                : elements.editMemberFilter;

        return (filterElement?.value || '').trim().toLowerCase();
    }

    function personMatchesMemberFilter(person, query) {
        if (!query) {
            return true;
        }

        const haystack = [getPersonName(person), person.email || '']
            .join(' ')
            .toLowerCase();

        return haystack.includes(query);
    }

    function renderSelectedMembers(mode) {
        const selectedMembers =
            mode === 'new'
                ? state.selectedNewMembers
                : state.selectedEditMembers;

        const listElement =
            mode === 'new'
                ? elements.newMemberList
                : elements.editMemberList;

        if (!selectedMembers.size) {
            listElement.innerHTML =
                '<li class="member-empty">No members selected.</li>';
            return;
        }

        const filterQuery = getMemberListFilter(mode);
        const visibleMembers = Array.from(selectedMembers.values()).filter(
            (person) => personMatchesMemberFilter(person, filterQuery)
        );

        if (!visibleMembers.length) {
            listElement.innerHTML =
                '<li class="member-empty">No members match your filter.</li>';
            return;
        }

        listElement.innerHTML = visibleMembers
            .map((person) => {
                const fullName = getPersonName(person);

                return `
                    <li class="member-item">
                        <span>
                            <strong>${escapeHtml(fullName)}</strong>
                            ${
                                person.email
                                    ? `<small>${escapeHtml(person.email)}</small>`
                                    : ''
                            }
                        </span>

                        <button
                            type="button"
                            class="member-delete"
                            data-mode="${escapeAttribute(mode)}"
                            data-person-id="${Number(person.id)}"
                            aria-label="Remove ${escapeAttribute(fullName)}"
                        >
                            ${deleteIcon}
                        </button>
                    </li>
                `;
            })
            .join('');
    }

    function handleSelectedMemberRemoval(event) {
        const button = event.target.closest('.member-delete');

        if (!button) {
            return;
        }

        const mode = button.dataset.mode;
        const personId = Number(button.dataset.personId);

        const selectedMembers =
            mode === 'new'
                ? state.selectedNewMembers
                : state.selectedEditMembers;

        selectedMembers.delete(personId);
        renderSelectedMembers(mode);
        renderAddMemberPicker(mode);
    }

    function getSelectedPeoplePayload(selectedMembers) {
        return Array.from(selectedMembers.keys()).map((personId) => ({
            person_id: personId,
        }));
    }

    function emailTeam(emailList) {
        if (!emailList) {
            showMessage(
                'This team does not have any members with email addresses.',
                'error'
            );
            return;
        }

        window.location.href = `mailto:?bcc=${encodeURIComponent(emailList)}`;
    }

    function getPersonName(person) {
        const fullName = [
            person.first_name || '',
            person.last_name || '',
        ]
            .filter(Boolean)
            .join(' ')
            .trim();

        return fullName || person.email || `Person #${person.id}`;
    }

    function setButtonLoading(
        button,
        isLoading,
        loadingText,
        defaultText
    ) {
        if (!button) {
            return;
        }

        button.disabled = isLoading;
        button.textContent = isLoading ? loadingText : defaultText;
    }

    function showMessage(message, type = 'success') {
        const existingNotice = document.getElementById(
            'sd-teams-notice'
        );

        existingNotice?.remove();

        const notice = document.createElement('div');
        notice.id = 'sd-teams-notice';
        notice.className = `notice notice-${type} is-dismissible`;
        notice.setAttribute('role', type === 'error' ? 'alert' : 'status');

        const paragraph = document.createElement('p');
        paragraph.textContent = message;

        notice.appendChild(paragraph);

        const main = document.querySelector('.main');
        const pageHeader = main?.querySelector('.page-header');

        if (main && pageHeader) {
            pageHeader.insertAdjacentElement('afterend', notice);
        }

        window.setTimeout(() => {
            notice.remove();
        }, 5000);
    }

    function updateTeamColorPreview(mode) {
        const isNew = mode === 'new';
        const colorInput = isNew
            ? elements.newTeamColor
            : elements.editTeamColor;
        const swatchFill = isNew
            ? elements.newTeamColorPreview
            : elements.editTeamColorPreview;
        const badge = isNew
            ? elements.newTeamColorBadge
            : elements.editTeamColorBadge;
        const nameInput = isNew
            ? elements.newTeamName
            : elements.editTeamName;

        if (!colorInput || !swatchFill || !badge) {
            return;
        }

        const color = isValidHexColor(colorInput.value)
            ? colorInput.value
            : '#2563eb';
        const label = nameInput?.value.trim() || 'Team Name';

        swatchFill.style.backgroundColor = color;
        badge.style.cssText = getTeamBadgeStyle(color);
        badge.textContent = label;
    }

    function isValidHexColor(value) {
        return /^#[0-9a-f]{6}$/i.test(value || '');
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function escapeAttribute(value) {
        return escapeHtml(value);
    }
})();