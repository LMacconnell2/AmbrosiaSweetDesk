(function (window) {
    'use strict';

    const Tickets = window.SweetDeskTickets = window.SweetDeskTickets || {};

    const state = {
        tickets: [],
        currentPage: 1,
        totalPages: 1,
        perPage: 25,
        modalMode: 'create',
        currentTicketId: null,
        ticketToDelete: null
    };

    Tickets.state = state;
})(window);
