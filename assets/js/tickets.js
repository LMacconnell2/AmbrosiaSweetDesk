document.addEventListener('DOMContentLoaded', async () => {

    const tbody = document.getElementById('sweetdesk-ticket-body');
    const queryBuilder = document.querySelector('.sweetdesk-ticket-query-builder');
    const newTicketButton = document.getElementById('sd-new-ticket');

    let tickets = [];

    const fieldConfig = {

        status: {
            operators: ['equals', 'not equals'],
            type: 'select',
            values: [
                'Open',
                'In Progress',
                'Pending',
                'Closed'
            ]
        },

        priority: {
            operators: ['equals', 'not equals'],
            type: 'select',
            values: [
                'Urgent',
                'High',
                'Normal',
                'Low'
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

    async function loadTickets() {

        try {

            const params = buildApiParams();

            const response = await fetch(
                `${window.location.origin}/wp-json/sweetdesk/v1/tickets?${params}`
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

                    <td>${ticket.title}</td>

                    <td>
                        <span class="sd-badge">
                            ${ticket.status}
                        </span>
                    </td>

                    <td>
                        <span class="sd-badge">
                            ${ticket.priority}
                        </span>
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

                config.values.forEach(value => {

                    valueSelect.innerHTML += `
                        <option value="${value}">
                            ${value}
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

    function applySorting(data) {

        const field =
            document.getElementById('sd-sort-field').value;

        const direction =
            document.getElementById('sd-sort-direction').value;

        data.sort((a, b) => {

            let valA = a[field];
            let valB = b[field];

            if (field === 'priority') {

                const priorityMap = {
                    Urgent: 4,
                    High: 3,
                    Normal: 2,
                    Low: 1
                };

                valA = priorityMap[valA];
                valB = priorityMap[valB];
            }

            if (valA < valB)
                return direction === 'asc' ? -1 : 1;

            if (valA > valB)
                return direction === 'asc' ? 1 : -1;

            return 0;
        });

        renderTickets(data);
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

    window.confirmDeleteTicket = function() {
        if (ticketToDelete) {
            // TODO: Implement actual deletion logic here
            console.log('Deleting ticket:', ticketToDelete);
            tickets = tickets.filter(t => t.id !== ticketToDelete);
            applyFilters();
        }
        closeDeleteTicketModal();
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
});