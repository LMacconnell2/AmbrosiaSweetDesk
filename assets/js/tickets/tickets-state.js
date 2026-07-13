(function (window) {
    'use strict';

    const Tickets = window.SweetDeskTickets = window.SweetDeskTickets || {};

    Tickets.state = {
        tickets: [],
        currentPage: 1,
        totalPages: 1,
        perPage: 25,
        modalMode: 'create',
        currentTicketId: null,
        ticketToDelete: null,
        lookups: {
            statuses: [],
            customFields: [],
            people: [],
            clients: [],
            statusesPromise: null,
            customFieldsPromise: null,
            peoplePromise: null,
            clientsPromise: null
        }
    };
})(window);
