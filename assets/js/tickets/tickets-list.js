(function (window) {
    'use strict';

    const Tickets = window.SweetDeskTickets = window.SweetDeskTickets || {};

    async function loadTickets() {
        try {
            const result = await Tickets.api.getTickets(Tickets.filters.buildApiParams());
            Tickets.state.tickets = result.data || [];
            Tickets.state.totalPages = result.pagination?.total_pages || 1;
            Tickets.state.currentPage = result.pagination?.page || 1;
            Tickets.renderer.updatePagination();
            Tickets.renderer.renderTickets(Tickets.state.tickets);
        } catch (error) {
            console.error(error);
            Tickets.renderer.renderLoadError();
        }
    }

    function bindPagination() {
        document.getElementById('sd-prev-page')?.addEventListener('click', () => {
            if (Tickets.state.currentPage > 1) {
                Tickets.state.currentPage -= 1;
                loadTickets();
            }
        });

        document.getElementById('sd-next-page')?.addEventListener('click', () => {
            if (Tickets.state.currentPage < Tickets.state.totalPages) {
                Tickets.state.currentPage += 1;
                loadTickets();
            }
        });
    }

    async function init() {
        try {
            await Tickets.lookups.loadAll();
        } catch (error) {
            console.error('Failed to load ticket lookups:', error);
        }

        Tickets.filters.bindEvents();
        Tickets.modal.bindEvents();
        Tickets.transfer.bindEvents();
        bindPagination();
        loadTickets();
    }

    Tickets.list = {
        loadTickets,
        init
    };

    document.addEventListener('DOMContentLoaded', init);
})(window);
