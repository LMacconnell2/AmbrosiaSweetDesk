(function (window) {
    'use strict';

    const Tickets = window.SweetDeskTickets = window.SweetDeskTickets || {};

    const ICON_EDIT = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>';
    const ICON_DELETE = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function renderTickets(ticketData) {
        const tbody = document.getElementById('sweetdesk-ticket-body');

        if (!tbody) {
            return;
        }

        if (!ticketData.length) {
            tbody.innerHTML = '<tr><td colspan="7">No tickets found.</td></tr>';
            return;
        }

        tbody.innerHTML = ticketData.map(ticket => `
            <tr class="sd-ticket-row">
                <td>#${ticket.id}</td>
                <td>
                    <a class="sd-ticket-title-link" href="${SweetDesk.ticketDetailBase}${ticket.id}">
                        ${escapeHtml(ticket.title)}
                    </a>
                </td>
                <td>${renderBadge('status', ticket.status)}</td>
                <td>${renderBadge('priority', ticket.priority)}</td>
                <td>${ticket.client_id || '—'}</td>
                <td>${ticket.assigned_to || '—'}</td>
                <td>
                    <div class="sd-actions">
                        <button type="button" class="sd-action-btn sd-edit-btn" data-id="${ticket.id}" aria-label="Edit ticket">
                            ${ICON_EDIT}
                        </button>
                        <button type="button" class="sd-action-btn sd-delete-btn" data-id="${ticket.id}" aria-label="Delete ticket">
                            ${ICON_DELETE}
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function renderLoadError() {
        const tbody = document.getElementById('sweetdesk-ticket-body');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="7">Failed to load tickets.</td></tr>';
        }
    }

    function updatePagination() {
        const state = Tickets.state;
        const pageInfo = document.getElementById('sd-page-info');
        const previous = document.getElementById('sd-prev-page');
        const next = document.getElementById('sd-next-page');

        if (pageInfo) {
            pageInfo.textContent = `Page ${state.currentPage} of ${state.totalPages}`;
        }
        if (previous) {
            previous.disabled = state.currentPage <= 1;
        }
        if (next) {
            next.disabled = state.currentPage >= state.totalPages;
        }
    }

    Tickets.renderer = {
        escapeHtml,
        renderTickets,
        renderLoadError,
        updatePagination
    };
})(window);
