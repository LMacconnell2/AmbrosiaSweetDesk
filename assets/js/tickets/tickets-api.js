(function (window) {
    'use strict';

    const Tickets = window.SweetDeskTickets = window.SweetDeskTickets || {};

    async function apiFetch(endpoint, options = {}) {
        const response = await fetch(`${SweetDesk.apiUrl}${endpoint}`, {
            ...options,
            headers: {
                'X-WP-Nonce': SweetDesk.nonce,
                'Content-Type': 'application/json',
                ...(options.headers || {})
            }
        });

        return response;
    }

    async function parseJsonResponse(response, fallbackMessage) {
        let result = null;

        try {
            result = await response.json();
        } catch (error) {
            if (!response.ok) {
                throw new Error(fallbackMessage);
            }
        }

        if (!response.ok || result?.success === false) {
            throw new Error(result?.message || fallbackMessage);
        }

        return result;
    }

    Tickets.api = {
        apiFetch,

        async getTickets(params) {
            const response = await apiFetch(`/tickets?${params}`);
            return parseJsonResponse(response, 'Failed to load tickets.');
        },

        async getTicket(ticketId) {
            const response = await apiFetch(`/tickets/${ticketId}`);
            return parseJsonResponse(response, 'Failed to load ticket.');
        },

        async getTicketStatuses() {
            const response = await apiFetch('/settings/tickets/status');
            return parseJsonResponse(response, 'Failed to load ticket statuses.');
        },

        async getTicketFields() {
            const response = await apiFetch('/settings/tickets/fields');
            return parseJsonResponse(response, 'Failed to load custom ticket fields.');
        },

        async getPeopleLookup() {
            const params = new URLSearchParams({
                internal: 'true',
                limit: '500'
            });
            const response = await apiFetch(`/people/lookup?${params}`);
            return parseJsonResponse(response, 'Failed to load ticket assignees.');
        },

        async getClientsLookup() {
            const params = new URLSearchParams({ limit: '500' });
            const response = await apiFetch(`/clients/lookup?${params}`);
            return parseJsonResponse(response, 'Failed to load clients.');
        },

        async createTicket(payload) {
            const response = await apiFetch('/tickets', {
                method: 'POST',
                body: JSON.stringify(payload)
            });
            return parseJsonResponse(response, 'Failed to create ticket.');
        },

        async updateTicket(ticketId, payload) {
            const response = await apiFetch(`/edit-ticket/${ticketId}`, {
                method: 'PUT',
                body: JSON.stringify(payload)
            });
            return parseJsonResponse(response, 'Failed to update ticket.');
        },

        async deleteTicket(ticketId) {
            const response = await apiFetch(`/tickets/${ticketId}`, {
                method: 'DELETE'
            });
            return parseJsonResponse(response, 'Failed to delete ticket.');
        },

        async exportTickets() {
            const response = await apiFetch('/tickets/export');
            return parseJsonResponse(response, 'Failed to export tickets.');
        },

        async importTickets(payload) {
            const response = await apiFetch('/tickets/import', {
                method: 'POST',
                body: JSON.stringify(payload)
            });
            return parseJsonResponse(response, 'Failed to import tickets.');
        }
    };
})(window);
