(() => {
    'use strict';

    const apiConfig = window.sweetdeskTeams;

    if (!apiConfig?.apiBase || !apiConfig?.nonce) {
        console.error(
            'SweetDesk teams API configuration is missing. ' +
                'Make sure teams.js is localized with apiBase and nonce.'
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
        memberSearchTimers: {
            new: null,
            edit: null,
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
        elements.newMemberSearch = document.getElementById(
            'new-team-member-search'
        );
        elements.newMemberResults = document.getElementById(
            'new-team-member-results'
        );
        elements.newMemberList = document.getElementById(
            'new-team-member-list'
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
        elements.editMemberSearch = document.getElementById(
            'edit-team-member-search'
        );
        elements.editMemberResults = document.getElementById(
            'edit-team-member-results'
        );
        elements.editMemberList = document.getElementById(
            'edit-team-member-list'
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

        elements.editTeamClose?.addEventListener('click', closeEditTeamModal);
        elements.editTeamCancel?.addEventListener('click', closeEditTeamModal);
        elements.editTeamForm?.addEventListener('submit', updateTeam);

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

        elements.newMemberSearch?.addEventListener('input', (event) => {
            debounceMemberSearch('new', event.target.value);
        });

        elements.editMemberSearch?.addEventListener('input', (event) => {
            debounceMemberSearch('edit', event.target.value);
        });

        elements.teamsGrid?.addEventListener('click', handleTeamGridClick);

        elements.newMemberResults?.addEventListener(
            'click',
            handleMemberResultClick
        );

        elements.editMemberResults?.addEventListener(
            'click',
            handleMemberResultClick
        );

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
        elements.newMemberResults.innerHTML = '';
        state.selectedNewMembers.clear();
        renderSelectedMembers('new');

        elements.newTeamModal.classList.add('active');
        elements.newTeamName.focus();
    }

    function closeNewTeamModal() {
        elements.newTeamModal.classList.remove('active');
        elements.newMemberResults.innerHTML = '';
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
        elements.editMemberResults.innerHTML = '';

        try {
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
            elements.editTeamName.focus();
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
        elements.editMemberResults.innerHTML = '';
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
            elements.editTeamName.focus();
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

    function debounceMemberSearch(mode, rawQuery) {
        const query = rawQuery.trim();

        window.clearTimeout(state.memberSearchTimers[mode]);

        const resultsElement =
            mode === 'new'
                ? elements.newMemberResults
                : elements.editMemberResults;

        if (query.length < 2) {
            resultsElement.innerHTML = '';
            return;
        }

        resultsElement.innerHTML =
            '<p class="member-search-status">Searching...</p>';

        state.memberSearchTimers[mode] = window.setTimeout(() => {
            searchPeople(query, mode);
        }, 300);
    }

    async function searchPeople(query, mode) {
        const resultsElement =
            mode === 'new'
                ? elements.newMemberResults
                : elements.editMemberResults;

        try {
            const searchParams = new URLSearchParams({
                q: query,
            });

            const response = await apiRequest(
                `/people?${searchParams.toString()}`
            );

            const people = Array.isArray(response?.data)
                ? response.data
                : [];

            renderMemberSearchResults(people, mode);
        } catch (error) {
            console.error('Unable to search people:', error);

            resultsElement.innerHTML = `
                <p class="member-search-status error">
                    ${escapeHtml(error.message)}
                </p>
            `;
        }
    }

    function renderMemberSearchResults(people, mode) {
        const resultsElement =
            mode === 'new'
                ? elements.newMemberResults
                : elements.editMemberResults;

        const selectedMembers =
            mode === 'new'
                ? state.selectedNewMembers
                : state.selectedEditMembers;

        const availablePeople = people.filter(
            (person) => !selectedMembers.has(Number(person.id))
        );

        if (!availablePeople.length) {
            resultsElement.innerHTML = `
                <p class="member-search-status">
                    No matching people were found.
                </p>
            `;
            return;
        }

        resultsElement.innerHTML = availablePeople
            .map((person) => {
                const fullName = getPersonName(person);

                return `
                    <button
                        type="button"
                        class="member-search-result"
                        data-mode="${escapeAttribute(mode)}"
                        data-person-id="${Number(person.id)}"
                        data-first-name="${escapeAttribute(
                            person.first_name || ''
                        )}"
                        data-last-name="${escapeAttribute(
                            person.last_name || ''
                        )}"
                        data-email="${escapeAttribute(person.email || '')}"
                        data-role="${escapeAttribute(person.role || '')}"
                    >
                        <strong>${escapeHtml(fullName)}</strong>
                        <span>${escapeHtml(person.email || '')}</span>
                        ${
                            person.role
                                ? `<small>${escapeHtml(person.role)}</small>`
                                : ''
                        }
                    </button>
                `;
            })
            .join('');
    }

    function handleMemberResultClick(event) {
        const result = event.target.closest('.member-search-result');

        if (!result) {
            return;
        }

        const mode = result.dataset.mode;
        const personId = Number(result.dataset.personId);

        if (!Number.isInteger(personId) || personId <= 0) {
            return;
        }

        const person = {
            id: personId,
            first_name: result.dataset.firstName || '',
            last_name: result.dataset.lastName || '',
            email: result.dataset.email || '',
            role: result.dataset.role || '',
        };

        const selectedMembers =
            mode === 'new'
                ? state.selectedNewMembers
                : state.selectedEditMembers;

        selectedMembers.set(personId, person);
        renderSelectedMembers(mode);

        if (mode === 'new') {
            elements.newMemberResults.innerHTML = '';
            elements.newMemberSearch.value = '';
            elements.newMemberSearch.focus();
        } else {
            elements.editMemberResults.innerHTML = '';
            elements.editMemberSearch.value = '';
            elements.editMemberSearch.focus();
        }
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

        listElement.innerHTML = Array.from(selectedMembers.values())
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
                        >
                            Remove
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