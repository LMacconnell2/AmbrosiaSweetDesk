(function (window) {
    'use strict';

    const Tickets = window.SweetDeskTickets = window.SweetDeskTickets || {};

    function normalizeBoolean(value) {
        return value === true || value === 1 || value === '1' || value === 'true';
    }

    function normalizeOptions(options) {
        if (Array.isArray(options)) {
            return options;
        }

        if (typeof options !== 'string' || !options.trim()) {
            return [];
        }

        try {
            const parsed = JSON.parse(options);
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return options.split(',').map(option => option.trim()).filter(Boolean);
        }
    }

    function normalizeStatus(status) {
        return {
            ...status,
            id: Number(status.id),
            name: status.name || status.slug || '',
            slug: status.slug || '',
            is_active: normalizeBoolean(status.is_active),
            sort_order: Number(status.sort_order || 0)
        };
    }

    function normalizeCustomField(field) {
        return {
            ...field,
            id: Number(field.id),
            name: field.name || field.field_key || '',
            field_key: field.field_key || field.meta_key || '',
            field_type: field.field_type || 'text',
            is_required: normalizeBoolean(field.is_required),
            is_active: normalizeBoolean(field.is_active),
            sort_order: Number(field.sort_order || 0),
            options: normalizeOptions(field.options)
        };
    }

    function normalizePerson(person) {
        const firstName = person.first_name || '';
        const lastName = person.last_name || '';

        return {
            id: Number(person.id),
            first_name: firstName,
            last_name: lastName,
            display_name: person.display_name || `${firstName} ${lastName}`.trim()
        };
    }

    function normalizeClient(client) {
        return {
            id: Number(client.id),
            name: client.name || ''
        };
    }

    function loadCached({ forceRefresh, valuesKey, promiseKey, request, normalize, filter, sort }) {
        const lookups = Tickets.state.lookups;

        if (!forceRefresh && lookups[valuesKey].length) {
            return Promise.resolve(lookups[valuesKey]);
        }

        if (!forceRefresh && lookups[promiseKey]) {
            return lookups[promiseKey];
        }

        lookups[promiseKey] = request()
            .then(result => {
                let values = (result.data || []).map(normalize);

                if (filter) {
                    values = values.filter(filter);
                }

                if (sort) {
                    values.sort(sort);
                }

                lookups[valuesKey] = values;
                return values;
            })
            .finally(() => {
                lookups[promiseKey] = null;
            });

        return lookups[promiseKey];
    }

    function loadStatuses(forceRefresh = false) {
        return loadCached({
            forceRefresh,
            valuesKey: 'statuses',
            promiseKey: 'statusesPromise',
            request: Tickets.api.getTicketStatuses,
            normalize: normalizeStatus,
            filter: status => status.is_active && status.slug,
            sort: (a, b) => a.sort_order - b.sort_order || a.name.localeCompare(b.name)
        });
    }

    function loadCustomFields(forceRefresh = false) {
        return loadCached({
            forceRefresh,
            valuesKey: 'customFields',
            promiseKey: 'customFieldsPromise',
            request: Tickets.api.getTicketFields,
            normalize: normalizeCustomField,
            filter: field => field.is_active && field.field_key,
            sort: (a, b) => a.sort_order - b.sort_order || a.name.localeCompare(b.name)
        });
    }

    function loadPeople(forceRefresh = false) {
        return loadCached({
            forceRefresh,
            valuesKey: 'people',
            promiseKey: 'peoplePromise',
            request: Tickets.api.getPeopleLookup,
            normalize: normalizePerson,
            filter: person => person.id > 0 && person.display_name,
            sort: (a, b) => a.display_name.localeCompare(b.display_name)
        });
    }

    function loadClients(forceRefresh = false) {
        return loadCached({
            forceRefresh,
            valuesKey: 'clients',
            promiseKey: 'clientsPromise',
            request: Tickets.api.getClientsLookup,
            normalize: normalizeClient,
            filter: client => client.id > 0 && client.name,
            sort: (a, b) => a.name.localeCompare(b.name)
        });
    }

    async function loadAll(forceRefresh = false) {
        const [statuses, customFields, people, clients] = await Promise.all([
            loadStatuses(forceRefresh),
            loadCustomFields(forceRefresh),
            loadPeople(forceRefresh),
            loadClients(forceRefresh)
        ]);

        return { statuses, customFields, people, clients };
    }

    Tickets.lookups = {
        loadStatuses,
        loadCustomFields,
        loadPeople,
        loadClients,
        loadAll
    };
})(window);
