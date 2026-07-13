(function (window) {
    'use strict';

    const Tickets = window.SweetDeskTickets = window.SweetDeskTickets || {};

    const fieldConfig = {
        status: {
            operators: ['equals', 'not equals'],
            type: 'select',
            values: []
        },
        priority: {
            operators: ['equals', 'not equals'],
            type: 'select',
            values: ['urgent', 'high', 'normal', 'low']
        },
        title: { operators: ['contains', 'equals'], type: 'text' },
        client: { operators: ['contains', 'equals'], type: 'text' },
        assignee: { operators: ['contains', 'equals'], type: 'text' },
        date_opened: { operators: ['before', 'after', 'on'], type: 'date' },
        latest_response: { operators: ['before', 'after', 'on'], type: 'date' }
    };

    function buildApiParams() {
        const state = Tickets.state;
        const params = new URLSearchParams({
            page: String(state.currentPage),
            per_page: String(state.perPage)
        });
        const searchInput = document.getElementById('sd-ticket-search');
        const search = searchInput?.value.trim();

        if (search) {
            params.set('search', search);
        }

        document.querySelectorAll('.sd-query-row').forEach(row => {
            const field = row.querySelector('.sd-field')?.value;
            const value = row.querySelector('.sd-value')?.value;

            if (!field || !value) {
                return;
            }

            const paramMap = {
                status: 'status',
                priority: 'priority',
                assignee: 'assigned_to'
            };

            if (paramMap[field]) {
                params.append(paramMap[field], value);
            }
        });

        const sortMap = {
            date_opened: 'created_at',
            latest_response: 'updated_at',
            priority: 'priority'
        };
        const sortField = document.getElementById('sd-sort-field')?.value;
        const sortDirection = document.getElementById('sd-sort-direction')?.value || 'desc';

        params.set('sort', sortMap[sortField] || 'created_at');
        params.set('order', sortDirection);

        return params.toString();
    }

    function applyFilters() {
        Tickets.state.currentPage = 1;
        Tickets.list.loadTickets();
    }

    function setupQueryRow(row) {
        if (!row) {
            return;
        }

        const fieldSelect = row.querySelector('.sd-field');
        const operatorSelect = row.querySelector('.sd-operator');
        const valueContainer = row.querySelector('.sd-value-container');

        function buildInputs() {
            const config = fieldConfig[fieldSelect.value];

            if (!config) {
                return;
            }

            operatorSelect.innerHTML = config.operators
                .map(operator => `<option value="${operator}">${operator}</option>`)
                .join('');

            if (config.type === 'select') {
                const values = fieldSelect.value === 'status'
                    ? Tickets.state.lookups.statuses.map(status => ({ value: status.slug, label: status.name }))
                    : config.values.map(value => ({ value, label: value.replace(/_/g, ' ') }));

                valueContainer.innerHTML = `
                    <select class="sd-value">
                        <option value="">Any</option>
                        ${values.map(item => `
                            <option value="${Tickets.renderer.escapeHtml(item.value)}">${Tickets.renderer.escapeHtml(item.label)}</option>
                        `).join('')}
                    </select>
                `;
            } else if (config.type === 'date') {
                valueContainer.innerHTML = '<input type="date" class="sd-value">';
            } else {
                valueContainer.innerHTML = '<input type="text" class="sd-value">';
            }
        }

        fieldSelect.addEventListener('change', () => {
            buildInputs();
            applyFilters();
        });
        row.addEventListener('input', applyFilters);
        row.addEventListener('change', applyFilters);
        buildInputs();
    }

    function bindEvents() {
        const queryBuilder = document.querySelector('.sweetdesk-ticket-query-builder');
        let searchTimeout;

        queryBuilder?.addEventListener('click', event => {
            const addButton = event.target.closest('.sd-add-filter');
            const removeButton = event.target.closest('.sd-remove-filter');

            if (addButton) {
                const row = addButton.closest('.sd-query-row');
                const clone = row.cloneNode(true);
                queryBuilder.querySelector('.sd-query-rows')?.appendChild(clone);
                setupQueryRow(clone);
            }

            if (removeButton) {
                const rows = document.querySelectorAll('.sd-query-row');
                if (rows.length > 1) {
                    removeButton.closest('.sd-query-row')?.remove();
                    applyFilters();
                }
            }
        });

        document.getElementById('sd-ticket-search')?.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(applyFilters, 500);
        });
        document.getElementById('sd-sort-field')?.addEventListener('change', applyFilters);
        document.getElementById('sd-sort-direction')?.addEventListener('change', applyFilters);

        setupQueryRow(document.querySelector('.sd-query-row'));
    }

    Tickets.filters = {
        fieldConfig,
        buildApiParams,
        applyFilters,
        setupQueryRow,
        bindEvents
    };
})(window);
