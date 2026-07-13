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

    async function loadStatuses(forceRefresh = false) {
        const lookups = Tickets.state.lookups;

        if (!forceRefresh && lookups.statuses.length) {
            return lookups.statuses;
        }

        if (!forceRefresh && lookups.statusesPromise) {
            return lookups.statusesPromise;
        }

        lookups.statusesPromise = Tickets.api.getTicketStatuses()
            .then(result => {
                lookups.statuses = (result.data || [])
                    .map(normalizeStatus)
                    .filter(status => status.is_active && status.slug)
                    .sort((a, b) => a.sort_order - b.sort_order || a.name.localeCompare(b.name));

                return lookups.statuses;
            })
            .finally(() => {
                lookups.statusesPromise = null;
            });

        return lookups.statusesPromise;
    }

    async function loadCustomFields(forceRefresh = false) {
        const lookups = Tickets.state.lookups;

        if (!forceRefresh && lookups.customFields.length) {
            return lookups.customFields;
        }

        if (!forceRefresh && lookups.customFieldsPromise) {
            return lookups.customFieldsPromise;
        }

        lookups.customFieldsPromise = Tickets.api.getTicketFields()
            .then(result => {
                lookups.customFields = (result.data || [])
                    .map(normalizeCustomField)
                    .filter(field => field.is_active && field.field_key)
                    .sort((a, b) => a.sort_order - b.sort_order || a.name.localeCompare(b.name));

                return lookups.customFields;
            })
            .finally(() => {
                lookups.customFieldsPromise = null;
            });

        return lookups.customFieldsPromise;
    }

    async function loadAll(forceRefresh = false) {
        const [statuses, customFields] = await Promise.all([
            loadStatuses(forceRefresh),
            loadCustomFields(forceRefresh)
        ]);

        return { statuses, customFields };
    }

    Tickets.lookups = {
        loadStatuses,
        loadCustomFields,
        loadAll
    };
})(window);
