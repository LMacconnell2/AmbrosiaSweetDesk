document.addEventListener('DOMContentLoaded', () => {

    const modal = document.getElementById(
        'sweetdesk-ticket-modal'
    );

    const closeBtn = document.getElementById(
        'sd-modal-close'
    );

    const tbody = document.getElementById('sweetdesk-ticket-body');

    /*
    |--------------------------------------------------------------------------
    | Open Modal
    |--------------------------------------------------------------------------
    */

    tbody.addEventListener('click', (e) => {
        const row = e.target.closest('.sd-ticket-row');

        if (!row) {
            return;
        }

        modal.classList.add('active');
    });

    /*
    |--------------------------------------------------------------------------
    | Close Modal Button
    |--------------------------------------------------------------------------
    */

    closeBtn.addEventListener('click', () => {
        modal.classList.remove('active');
    });

    /*
    |--------------------------------------------------------------------------
    | Click Outside Modal
    |--------------------------------------------------------------------------
    */

    modal.addEventListener('click', (e) => {

        if (e.target === modal) {
            modal.classList.remove('active');
        }

    });

});

document.addEventListener('DOMContentLoaded', async () => {

    const tbody = document.getElementById('sweetdesk-ticket-body');
    const queryBuilder = document.querySelector('.sweetdesk-ticket-query-builder');
    const newTicketButton = document.getElementById('sd-new-ticket');
    const newTicketModal = document.getElementById('sweetdesk-new-ticket-modal');
    const newTicketClose = document.getElementById('sd-new-ticket-close');
    const newTicketForm = document.getElementById('sd-new-ticket-form');
    const newTicketCancel = document.getElementById('sd-new-ticket-cancel');

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

    async function loadTickets() {

        const response = await fetch('./tickets.json');

        tickets = await response.json();

        renderTickets(tickets);
    }

    function openNewTicketModal() {
        newTicketModal.classList.add('active');
    }

    function closeNewTicketModal() {
        newTicketModal.classList.remove('active');
        newTicketForm.reset();
    }

    function addNewTicket(event) {
        event.preventDefault();

        const title = document.getElementById('sd-ticket-title').value.trim();
        const status = document.getElementById('sd-ticket-status').value;
        const priority = document.getElementById('sd-ticket-priority').value;
        const client = document.getElementById('sd-ticket-client').value.trim();
        const assignee = document.getElementById('sd-ticket-assignee').value.trim();
        const description = document.getElementById('sd-ticket-description').value.trim();

        if (!title) {
            return;
        }

        const id = tickets.length
            ? Math.max(...tickets.map(ticket => ticket.id)) + 1
            : 1;

        const today = new Date().toISOString().split('T')[0];

        tickets.push({
            id,
            title,
            status,
            priority,
            client,
            assignee,
            description,
            date_opened: today,
            latest_response: today
        });

        applyFilters();
        closeNewTicketModal();
    }

    function renderTickets(ticketData) {

        tbody.innerHTML = '';

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

                    <td>${ticket.client || '—'}</td>

                    <td>${ticket.assignee}</td>

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

        const rows = document.querySelectorAll('.sd-query-row');

        let filtered = [...tickets];

        rows.forEach((row, index) => {

            const logic = row.querySelector('.sd-logic').value;

            const field = row.querySelector('.sd-field').value;

            const operator = row.querySelector('.sd-operator').value;

            const value = row.querySelector('.sd-value').value;

            const results = tickets.filter(ticket => {

                const ticketValue = (ticket[field] || '')
                    .toString()
                    .toLowerCase();

                const compareValue = value.toLowerCase();

                switch (operator) {

                    case 'equals':
                        return ticketValue === compareValue;

                    case 'not equals':
                        return ticketValue !== compareValue;

                    case 'contains':
                        return ticketValue.includes(compareValue);

                    case 'before':
                        return ticketValue < compareValue;

                    case 'after':
                        return ticketValue > compareValue;

                    case 'on':
                        return ticketValue === compareValue;

                    default:
                        return true;
                }
            });

            if (index === 0) {

                filtered = results;

            } else {

                if (logic === 'AND') {

                    filtered = filtered.filter(ticket =>
                        results.includes(ticket)
                    );

                } else {

                    filtered = [
                        ...new Set([
                            ...filtered,
                            ...results
                        ])
                    ];
                }
            }
        });

        applySorting(filtered);
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

    newTicketButton.addEventListener('click', openNewTicketModal);
    newTicketClose.addEventListener('click', closeNewTicketModal);
    newTicketCancel.addEventListener('click', closeNewTicketModal);
    newTicketModal.addEventListener('click', (e) => {
        if (e.target === newTicketModal) {
            closeNewTicketModal();
        }
    });

    newTicketForm.addEventListener('submit', addNewTicket);

    document
        .getElementById('sd-sort-field')
        .addEventListener('change', applyFilters);

    document
        .getElementById('sd-sort-direction')
        .addEventListener('change', applyFilters);

    setupQueryRow(document.querySelector('.sd-query-row'));

    loadTickets();
});