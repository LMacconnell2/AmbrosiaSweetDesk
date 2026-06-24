document.addEventListener('DOMContentLoaded', async () => {
    let currentTicketId = null;
    let modalMode = 'create';
    const tbody = document.getElementById('sweetdesk-ticket-body');
    const queryBuilder = document.querySelector('.sweetdesk-ticket-query-builder');
    const newTicketButton =
    document.getElementById('sd-new-ticket');
        if (newTicketButton) {

            newTicketButton.addEventListener(
                'click',
                () => openTicketModal('create')
            );
        }


    let tickets = [];

    const fieldConfig = {

        status: {
            operators: ['equals', 'not equals'],
            type: 'select',
            values: [
                'open',
                'in_progress',
                'pending',
                'closed'
            ]
        },

        priority: {
            operators: ['equals', 'not equals'],
            type: 'select',
            values: [
                'urgent',
                'high',
                'normal',
                'low'
            ]
        },

        title: {
            operators: ['contains', 'equals'],
            type: 'text'
        },

        client: {
            operators: ['contains', 'equals'],
            type: 'text'
        },

        assignee: {
            operators: ['contains', 'equals'],
            type: 'text'
        },

        date_opened: {
            operators: ['before', 'after', 'on'],
            type: 'date'
        },

        latest_response: {
            operators: ['before', 'after', 'on'],
            type: 'date'
        }
    };

    let currentPage = 1;
    let totalPages = 1;

    async function apiFetch(endpoint, options = {}) {

        const response = await fetch(
            `${SweetDesk.apiUrl}${endpoint}`,
            {
                ...options,

                headers: {
                    'X-WP-Nonce': SweetDesk.nonce,
                    'Content-Type': 'application/json',
                    ...(options.headers || {})
                }
            }
        );

        return response;
    }

    async function loadTickets() {

        try {

            const params = buildApiParams();

            const response = await apiFetch(
                `/tickets?${params}`
            );

            if (!response.ok) {
                throw new Error('Failed to load tickets');
            }

            const result = await response.json();

            tickets = result.data || [];

            totalPages =
                result.pagination?.total_pages || 1;

            currentPage =
                result.pagination?.page || 1;

            updatePagination();

            renderTickets(tickets);

        } catch (error) {

            console.error(error);

            tbody.innerHTML = `
                <tr>
                    <td colspan="7">
                        Failed to load tickets.
                    </td>
                </tr>
            `;
        }
    }



    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function renderTickets(ticketData) {

        tbody.innerHTML = '';

        if (!ticketData.length) {

            tbody.innerHTML = `
                <tr>
                    <td colspan="7">
                        No tickets found.
                    </td>
                </tr>
            `;

            return;
        }

        ticketData.forEach(ticket => {

            tbody.innerHTML += `
                <tr class="sd-ticket-row">

                    <td>#${ticket.id}</td>

                    <td>
                        <a
                            class="sd-ticket-title-link"
                            href="${SweetDesk.ticketDetailBase}${ticket.id}"
                        >
                            ${escapeHtml(ticket.title)}
                        </a>
                    </td>

                    <td>
                        ${renderBadge('status', ticket.status)}
                    </td>

                    <td>
                        ${renderBadge('priority', ticket.priority)}
                    </td>

                    <td>${ticket.client_id || '—'}</td>

                    <td>${ticket.assigned_to || '—'}</td>

                    <td>
                        <div class="sd-actions">

                            <button
                                class="sd-action-btn sd-edit-btn"
                                data-id="${ticket.id}"
                            >
                                Edit
                            </button>

                            <button
                                class="sd-action-btn sd-delete-btn"
                                onclick="openDeleteTicketModal(
                                    ${ticket.id},
                                    '${ticket.title}'
                                )"
                            >
                                Delete
                            </button>

                        </div>
                    </td>

                </tr>
            `;
        });
    }

    function setupQueryRow(row) {

        const fieldSelect = row.querySelector('.sd-field');
        const operatorSelect = row.querySelector('.sd-operator');
        const valueContainer = row.querySelector('.sd-value-container');

        function buildInputs() {

            const field = fieldSelect.value;

            const config = fieldConfig[field];

            operatorSelect.innerHTML = '';

            config.operators.forEach(op => {

                operatorSelect.innerHTML += `
                    <option value="${op}">
                        ${op}
                    </option>
                `;
            });

            if (config.type === 'select') {

                valueContainer.innerHTML = `
                    <select class="sd-value"></select>
                `;

                const valueSelect = valueContainer.querySelector('.sd-value');

                valueSelect.innerHTML = `
                    <option value="">Any</option>
                `;

                config.values.forEach(value => {

                    valueSelect.innerHTML += `
                        <option value="${value}">
                            ${value.replace(/_/g, ' ')}
                        </option>
                    `;
                });

            } else if (config.type === 'date') {

                valueContainer.innerHTML = `
                    <input
                        type="date"
                        class="sd-value"
                    >
                `;

            } else {

                valueContainer.innerHTML = `
                    <input
                        type="text"
                        class="sd-value"
                    >
                `;
            }

            applyFilters();
        }

        fieldSelect.addEventListener('change', buildInputs);

        row.addEventListener('input', applyFilters);

        buildInputs();
    }

    function applyFilters() {

        currentPage = 1;

        loadTickets();
    }

    function buildApiParams() {

        const params = new URLSearchParams();

        params.append('page', currentPage);

        params.append('per_page', 25);

        const searchInput =
            document.getElementById('sd-ticket-search');

        if (searchInput.value.trim()) {

            params.append(
                'search',
                searchInput.value.trim()
            );
        }

        const rows =
            document.querySelectorAll('.sd-query-row');

        rows.forEach(row => {

            const field =
                row.querySelector('.sd-field').value;

            const value =
                row.querySelector('.sd-value')?.value;

            if (!value) {
                return;
            }

            switch (field) {

                case 'status':
                    params.append('status', value);
                    break;

                case 'priority':
                    params.append('priority', value);
                    break;

                case 'assignee':
                    params.append('assigned_to', value);
                    break;
            }
        });

        const sortField =
            document.getElementById('sd-sort-field').value;

        const sortDirection =
            document.getElementById('sd-sort-direction').value;

        const sortMap = {
            date_opened: 'created_at',
            latest_response: 'updated_at',
            priority: 'priority'
        };

        params.append(
            'sort',
            sortMap[sortField] || 'created_at'
        );

        params.append(
            'order',
            sortDirection
        );

        return params.toString();
    }

    queryBuilder.addEventListener('click', e => {

        if (e.target.classList.contains('sd-add-filter')) {

            const row =
                e.target.closest('.sd-query-row');

            const clone = row.cloneNode(true);

            queryBuilder.insertBefore(
                clone,
                document.querySelector('.sweetdesk-ticket-sort')
            );

            setupQueryRow(clone);
        }

        if (e.target.classList.contains('sd-remove-filter')) {

            const rows =
                document.querySelectorAll('.sd-query-row');

            if (rows.length > 1) {

                e.target.closest('.sd-query-row').remove();

                applyFilters();
            }
        }
    });

    function updatePagination() {

        document.getElementById(
            'sd-page-info'
        ).textContent =
            `Page ${currentPage} of ${totalPages}`;

        document.getElementById(
            'sd-prev-page'
        ).disabled =
            currentPage <= 1;

        document.getElementById(
            'sd-next-page'
        ).disabled =
            currentPage >= totalPages;
    }

    document
        .getElementById('sd-sort-field')
        .addEventListener('change', applyFilters);

    document
        .getElementById('sd-sort-direction')
        .addEventListener('change', applyFilters);

    setupQueryRow(document.querySelector('.sd-query-row'));

    loadTickets();

    // Delete Ticket Modal Functions
    let ticketToDelete = null;

    window.openDeleteTicketModal = function(ticketId, ticketTitle) {
        ticketToDelete = ticketId;
        document.getElementById('sd-delete-ticket-title').textContent = '#' + ticketId;
        document.getElementById('sd-delete-ticket-modal').classList.add('active');
    };

    window.closeDeleteTicketModal = function() {
        document.getElementById('sd-delete-ticket-modal').classList.remove('active');
        ticketToDelete = null;
    };

    window.confirmDeleteTicket = async function() {

        if (!ticketToDelete) {
            return;
        }

        try {

            const response =
                await apiFetch(
                    `/tickets/${ticketToDelete}`,
                    {
                        method: 'DELETE'
                    }
                );

            if (!response.ok) {

                throw new Error(
                    'Delete failed'
                );
            }

            closeDeleteTicketModal();

            await loadTickets();

        } catch (error) {

            console.error(error);

            alert(
                'Failed to delete ticket.'
            );
        }
    };

    // Close modal when clicking overlay
    document.getElementById('sd-delete-ticket-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteTicketModal();
        }
    });

    let searchTimeout;

    document
        .getElementById('sd-ticket-search')
        .addEventListener('input', () => {

            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(() => {

                currentPage = 1;

                loadTickets();

            }, 500);
        });

    document
        .getElementById('sd-prev-page')
        .addEventListener('click', () => {

            if (currentPage > 1) {

                currentPage--;

                loadTickets();
            }
        });

    document
        .getElementById('sd-next-page')
        .addEventListener('click', () => {

            if (currentPage < totalPages) {

                currentPage++;

                loadTickets();
            }
        });

    window.openTicketModal = openTicketModal;
    window.closeTicketModal = closeTicketModal;

    async function openTicketModal(
        mode = 'create',
        ticket = null
    ) {

        modalMode = mode;

        currentTicketId =
            ticket?.id || null;

        const modal =
            document.getElementById(
                'sd-create-ticket-modal'
            );

        modal.classList.add('active');

        const title =
            modal.querySelector('h2');

        const saveButton =
            document.getElementById(
                'sd-save-ticket'
            );

        if (mode === 'create') {

            title.textContent =
                'Create Ticket';

            saveButton.textContent =
                'Create Ticket';

            resetTicketForm();
            initTicketModalEditor(true);

            return;
        }

        title.textContent =
            'Edit Ticket';

        saveButton.textContent =
            'Save Changes';

        populateTicketForm(ticket);
        initTicketModalEditor(false);
    }

    function initTicketModalEditor(clearContent = false) {
        requestAnimationFrame(() => {
            SweetDeskEditor.init('sd-ticket-body');

            if (clearContent) {
                SweetDeskEditor.setContent('sd-ticket-body', '');
            }
        });
    }

    function populateTicketForm(ticket) {

        document.getElementById(
            'sd-ticket-title'
        ).value =
            ticket.title || '';

        document.getElementById(
            'sd-ticket-client'
        ).value =
            ticket.client_id || '';

        document.getElementById(
            'sd-ticket-assignee'
        ).value =
            ticket.assigned_to || '';

        document.getElementById(
            'sd-ticket-status'
        ).value =
            ticket.status || 'open';

        document.getElementById(
            'sd-ticket-priority'
        ).value =
            ticket.priority || 'normal';

        if (ticket.custom_fields) {

            Object.entries(
                ticket.custom_fields
            ).forEach(([key, value]) => {

                const field =
                    document.querySelector(
                        `[data-meta-key="${key}"]`
                    );

                if (field) {

                    field.value = value;
                }
            });
        }
    }

    function resetTicketForm() {

        document.getElementById(
            'sd-ticket-title'
        ).value = '';

        document.getElementById(
            'sd-ticket-client'
        ).value = '';

        document.getElementById(
            'sd-ticket-assignee'
        ).value = '';

        document.getElementById(
            'sd-ticket-status'
        ).value = 'open';

        document.getElementById(
            'sd-ticket-priority'
        ).value = 'normal';

        SweetDeskEditor.setContent(
            'sd-ticket-body',
            ''
        );
    }

    tbody.addEventListener('click', async e => {

        const editButton =
            e.target.closest('.sd-edit-btn');

        if (!editButton) {
            return;
        }

        const ticketId =
            editButton.dataset.id;

        await openEditTicketModal(ticketId);
    });

    async function openEditTicketModal(ticketId) {

        try {

            const response =
                await apiFetch(
                    `/tickets/${ticketId}`
                );

            if (!response.ok) {
                throw new Error(
                    'Failed to load ticket'
                );
            }

            const data =
                await response.json();

            modalMode = 'edit';

            currentTicketId = ticketId;

            populateTicketModal(data);

            document
                .getElementById(
                    'sd-create-ticket-modal'
                )
                .classList.add('active');

            initTicketModalEditor(false);

        } catch (error) {

            console.error(error);

            alert(
                'Failed to load ticket.'
            );
        }
    }

    function populateTicketModal(data) {

        const ticket =
            data.ticket;

        document.getElementById(
            'sd-ticket-title'
        ).value =
            ticket.title || '';

        document.getElementById(
            'sd-ticket-client'
        ).value =
            ticket.client_id || '';

        document.getElementById(
            'sd-ticket-assignee'
        ).value =
            ticket.assigned_to || '';

        document.getElementById(
            'sd-ticket-status'
        ).value =
            ticket.status || 'open';

        document.getElementById(
            'sd-ticket-priority'
        ).value =
            ticket.priority || 'normal';

        renderCustomFields(
            data.custom_fields || []
        );

        renderTicketMessages(
            data.messages || []
        );

        document.getElementById(
            'sd-save-ticket'
        ).textContent =
            'Save Changes';
    }

    function renderCustomFields(fields) {

        const container =
            document.getElementById(
                'sd-custom-fields-container'
            );

        if (!container) {
            return;
        }

        container.innerHTML = '';

        fields.forEach(field => {

            container.innerHTML += `
                <div class="sd-form-group">

                    <label>
                        ${field.meta_key}
                    </label>

                    <input
                        type="text"
                        class="sd-custom-field"
                        data-meta-id="${field.meta_id}"
                        data-meta-key="${field.meta_key}"
                        value="${field.meta_value || ''}"
                    >

                </div>
            `;
        });
    }

    function renderTicketMessages(messages) {

        const container =
            document.getElementById(
                'sd-ticket-thread'
            );

        if (!container) {
            return;
        }

        container.innerHTML = '';

        messages.forEach(message => {

            container.innerHTML += `
                <div class="sd-ticket-message">

                    <div class="sd-message-meta">

                        ${message.reply_type}
                        •
                        ${message.created_at}

                    </div>

                    <div class="sd-message-body">

                        ${message.body}

                    </div>

                </div>
            `;
        });
    }

    document
    .getElementById('sd-save-ticket')
    ?.addEventListener(
        'click',
        saveTicket
    );

    function closeTicketModal() {
        SweetDeskEditor.destroy('sd-ticket-body');

        document
            .getElementById('sd-create-ticket-modal')
            .classList.remove('active');
    }

    async function saveTicket() {

        const title = document.getElementById('sd-ticket-title').value.trim();
        const message = SweetDeskEditor.getContent('sd-ticket-body');

        if (!title) {
            alert('Title is required.');
            return;
        }

        if (modalMode === 'create' && SweetDeskEditor.isEmpty('sd-ticket-body')) {
            alert('Initial message is required.');
            return;
        }

        const customFields = {};

        document
            .querySelectorAll('.sd-custom-field')
            .forEach(field => {

                customFields[
                    field.dataset.metaKey
                ] = field.value;
            });

        const payload = {

            client_id:
                Number(
                    document.getElementById(
                        'sd-ticket-client'
                    ).value
                ),

            assigned_to:
                Number(
                    document.getElementById(
                        'sd-ticket-assignee'
                    ).value
                ) || null,

            title,

            status:
                document.getElementById(
                    'sd-ticket-status'
                ).value,

            priority:
                document.getElementById(
                    'sd-ticket-priority'
                ).value,

            message,

            custom_fields:
                customFields
        };

        const endpoint =
            modalMode === 'create'
                ? '/tickets'
                : `/edit-ticket/${currentTicketId}`;

        const method =
            modalMode === 'create'
                ? 'POST'
                : 'PUT';

        try {

            const response =
                await apiFetch(
                    endpoint,
                    {
                        method,
                        body: JSON.stringify(payload)
                    }
                );

            const result = await response.json();

            if (!response.ok || result.success === false) {
                throw new Error(
                    result.message || 'Save failed'
                );
            }

            closeTicketModal();

            await loadTickets();

        } catch (error) {

            console.error(error);

            alert(
                error.message || 'Failed to save ticket.'
            );
        }
    }

});