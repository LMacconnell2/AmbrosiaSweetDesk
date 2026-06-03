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

    async function loadTickets() {

        const response = await fetch('./tickets.json');

        tickets = await response.json();

        renderTickets(tickets);
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

                    <td>
                        <div class="sd-actions">
                            <button class="sd-action-btn sd-edit-btn" title="Edit ticket">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-7.5-1.5l5.5-5.5a2.121 2.121 0 013 3l-5.5 5.5m-6-6h6"/></svg>
                            </button>
                            <button class="sd-action-btn sd-delete-btn" title="Delete ticket">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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

    document
        .getElementById('sd-sort-field')
        .addEventListener('change', applyFilters);

    document
        .getElementById('sd-sort-direction')
        .addEventListener('change', applyFilters);

    setupQueryRow(document.querySelector('.sd-query-row'));

    loadTickets();
});