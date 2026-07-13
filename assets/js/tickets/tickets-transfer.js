(function (window) {
    'use strict';

    const Tickets = window.SweetDeskTickets = window.SweetDeskTickets || {};

    async function exportTickets() {
        try {
            const data = await Tickets.api.exportTickets();
            const blob = new Blob([JSON.stringify(data, null, 2)], {
                type: 'application/json'
            });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');

            link.href = url;
            link.download = `sweetdesk-export-${Date.now()}.json`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
        } catch (error) {
            console.error(error);
            alert(error.message);
        }
    }

    async function importTickets(event) {
        const file = event.target.files?.[0];
        if (!file) {
            return;
        }

        try {
            const payload = JSON.parse(await file.text());
            const result = await Tickets.api.importTickets(payload);
            alert(result.message || 'Tickets imported successfully.');
            await Tickets.list.loadTickets();
        } catch (error) {
            console.error(error);
            alert(error.message || 'Failed to import tickets.');
        } finally {
            event.target.value = '';
        }
    }

    function bindEvents() {
        document.getElementById('sd-export-json')?.addEventListener('click', exportTickets);
        document.getElementById('sd-import-json')?.addEventListener('click', () => {
            document.getElementById('sd-import-file')?.click();
        });
        document.getElementById('sd-import-file')?.addEventListener('change', importTickets);
    }

    Tickets.transfer = {
        exportTickets,
        importTickets,
        bindEvents
    };
})(window);
