(function (window) {
    'use strict';

    const Tickets = window.SweetDeskTickets = window.SweetDeskTickets || {};

    function valueOf(id) {
        return document.getElementById(id)?.value ?? '';
    }

    function setValue(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.value = value ?? '';
        }
    }

    function escapeAttribute(value) {
        return Tickets.renderer.escapeHtml(String(value ?? ''));
    }

    function setInitialMessageSectionVisible(visible) {
        const section = document.getElementById('sd-ticket-initial-message-section');
        if (section) {
            section.hidden = !visible;
        }
    }

    function initEditor(clearContent = false) {
        requestAnimationFrame(() => {
            SweetDeskEditor.init('sd-ticket-body');
            if (clearContent) {
                SweetDeskEditor.setContent('sd-ticket-body', '');
            }
        });
    }

    function renderStatusOptions(selectedStatus = '') {
        const statusSelect = document.getElementById('sd-ticket-status');
        if (!statusSelect) {
            return;
        }

        const statuses = Tickets.state.lookups.statuses;
        statusSelect.innerHTML = statuses.map(status => `
            <option value="${escapeAttribute(status.slug)}">
                ${Tickets.renderer.escapeHtml(status.name)}
            </option>
        `).join('');

        const desiredStatus = selectedStatus || statuses[0]?.slug || '';
        statusSelect.value = desiredStatus;

        if (desiredStatus && statusSelect.value !== desiredStatus) {
            statusSelect.insertAdjacentHTML('beforeend', `
                <option value="${escapeAttribute(desiredStatus)}">
                    ${Tickets.renderer.escapeHtml(desiredStatus.replace(/[_-]/g, ' '))}
                </option>
            `);
            statusSelect.value = desiredStatus;
        }
    }

    function customFieldValueMap(savedFields = []) {
        if (!Array.isArray(savedFields)) {
            return savedFields || {};
        }

        return savedFields.reduce((values, field) => {
            const key = field.meta_key || field.field_key;
            if (key) {
                values[key] = field.meta_value ?? field.value ?? '';
            }
            return values;
        }, {});
    }

    function renderFieldControl(field, savedValue) {
        const key = escapeAttribute(field.field_key);
        const required = field.is_required ? ' required' : '';
        const requiredLabel = field.is_required ? ' *' : '';
        const value = savedValue ?? '';
        let control = '';

        switch (field.field_type) {
            case 'textarea':
                control = `
                    <textarea
                        class="sd-custom-field"
                        data-meta-key="${key}"
                        ${required}
                    >${Tickets.renderer.escapeHtml(value)}</textarea>
                `;
                break;

            case 'select':
                control = `
                    <select
                        class="sd-custom-field"
                        data-meta-key="${key}"
                        ${required}
                    >
                        <option value="">Select an option</option>
                        ${field.options.map(option => `
                            <option
                                value="${escapeAttribute(option)}"
                                ${String(option) === String(value) ? 'selected' : ''}
                            >
                                ${Tickets.renderer.escapeHtml(option)}
                            </option>
                        `).join('')}
                    </select>
                `;
                break;

            case 'checkbox':
                control = `
                    <label class="sd-checkbox-label">
                        <input
                            type="checkbox"
                            class="sd-custom-field"
                            data-meta-key="${key}"
                            value="1"
                            ${['1', 'true', 'yes', 'on'].includes(String(value).toLowerCase()) ? 'checked' : ''}
                            ${required}
                        >
                        Yes
                    </label>
                `;
                break;

            default: {
                const supportedTypes = ['text', 'number', 'date', 'email', 'url'];
                const inputType = supportedTypes.includes(field.field_type)
                    ? field.field_type
                    : 'text';

                control = `
                    <input
                        type="${inputType}"
                        class="sd-custom-field"
                        data-meta-key="${key}"
                        value="${escapeAttribute(value)}"
                        ${required}
                    >
                `;
            }
        }

        return `
            <div class="sd-form-group">
                <label>${Tickets.renderer.escapeHtml(field.name)}${requiredLabel}</label>
                ${control}
            </div>
        `;
    }

    function renderCustomFields(savedFields = []) {
        const container = document.getElementById('sd-custom-fields-container');
        if (!container) {
            return;
        }

        const values = customFieldValueMap(savedFields);
        const definitions = Tickets.state.lookups.customFields;

        container.innerHTML = definitions
            .map(field => renderFieldControl(field, values[field.field_key]))
            .join('');
    }

    function resetForm() {
        setValue('sd-ticket-title', '');
        setValue('sd-ticket-client', '');
        setValue('sd-ticket-assignee', '');
        setValue('sd-ticket-priority', 'normal');
        setValue('sd-ticket-reply-type', 'public');
        renderStatusOptions();
        renderCustomFields();
    }

    function populateForm(data) {
        const ticket = data.ticket;
        setValue('sd-ticket-title', ticket.title);
        setValue('sd-ticket-client', ticket.client_id);
        setValue('sd-ticket-assignee', ticket.assigned_to);
        setValue('sd-ticket-priority', ticket.priority || 'normal');
        renderStatusOptions(ticket.status);
        renderCustomFields(data.custom_fields || []);
    }

    async function openCreateModal() {
        try {
            await Tickets.lookups.loadAll();
            Tickets.state.modalMode = 'create';
            Tickets.state.currentTicketId = null;
            resetForm();
            document.getElementById('sd-ticket-modal-title').textContent = 'Create Ticket';
            document.getElementById('sd-save-ticket').textContent = 'Create Ticket';
            setInitialMessageSectionVisible(true);
            document.getElementById('sd-create-ticket-modal').classList.add('active');
            initEditor(true);
        } catch (error) {
            console.error(error);
            alert(error.message || 'Failed to load ticket form settings.');
        }
    }

    async function openEditModal(ticketId) {
        try {
            const [, data] = await Promise.all([
                Tickets.lookups.loadAll(),
                Tickets.api.getTicket(ticketId)
            ]);

            Tickets.state.modalMode = 'edit';
            Tickets.state.currentTicketId = Number(ticketId);
            populateForm(data);
            document.getElementById('sd-ticket-modal-title').textContent = 'Edit Ticket';
            document.getElementById('sd-save-ticket').textContent = 'Save Changes';
            SweetDeskEditor.destroy('sd-ticket-body');
            setInitialMessageSectionVisible(false);
            document.getElementById('sd-create-ticket-modal').classList.add('active');
        } catch (error) {
            console.error(error);
            alert(error.message);
        }
    }

    function closeModal() {
        SweetDeskEditor.destroy('sd-ticket-body');
        setInitialMessageSectionVisible(true);
        document.getElementById('sd-create-ticket-modal')?.classList.remove('active');
    }

    function collectCustomFields() {
        const customFields = {};

        document.querySelectorAll('.sd-custom-field').forEach(field => {
            customFields[field.dataset.metaKey] = field.type === 'checkbox'
                ? (field.checked ? '1' : '0')
                : field.value;
        });

        return customFields;
    }

    function validateCustomFields() {
        for (const field of document.querySelectorAll('.sd-custom-field[required]')) {
            const isInvalid = field.type === 'checkbox'
                ? !field.checked
                : !field.value.trim();

            if (isInvalid) {
                field.reportValidity();
                field.focus();
                return false;
            }
        }

        return true;
    }

    async function saveTicket() {
        const state = Tickets.state;
        const title = valueOf('sd-ticket-title').trim();

        if (!title) {
            alert('Title is required.');
            return;
        }

        if (!validateCustomFields()) {
            return;
        }

        if (state.modalMode === 'create' && SweetDeskEditor.isEmpty('sd-ticket-body')) {
            alert('Initial message is required.');
            return;
        }

        const clientValue = valueOf('sd-ticket-client');
        const assigneeValue = valueOf('sd-ticket-assignee');
        const payload = {
            client_id: clientValue ? Number(clientValue) : null,
            assigned_to: assigneeValue ? Number(assigneeValue) : null,
            title,
            status: valueOf('sd-ticket-status'),
            priority: valueOf('sd-ticket-priority'),
            custom_fields: collectCustomFields()
        };

        if (state.modalMode === 'create') {
            payload.message = SweetDeskEditor.getContent('sd-ticket-body');
            payload.reply_type = valueOf('sd-ticket-reply-type') || 'public';
            payload.visibility = payload.reply_type;
        }

        try {
            if (state.modalMode === 'create') {
                await Tickets.api.createTicket(payload);
            } else {
                await Tickets.api.updateTicket(state.currentTicketId, payload);
            }
            closeModal();
            await Tickets.list.loadTickets();
        } catch (error) {
            console.error(error);
            alert(error.message);
        }
    }

    function openDeleteModal(ticketId, ticketTitle) {
        Tickets.state.ticketToDelete = ticketId;
        document.getElementById('sd-delete-ticket-title').textContent = `#${ticketId}: ${ticketTitle}`;
        document.getElementById('sd-delete-ticket-modal').classList.add('active');
    }

    function closeDeleteModal() {
        Tickets.state.ticketToDelete = null;
        document.getElementById('sd-delete-ticket-modal')?.classList.remove('active');
    }

    async function confirmDelete() {
        const ticketId = Tickets.state.ticketToDelete;
        if (!ticketId) {
            return;
        }

        try {
            await Tickets.api.deleteTicket(ticketId);
            closeDeleteModal();
            await Tickets.list.loadTickets();
        } catch (error) {
            console.error(error);
            alert(error.message);
        }
    }

    function bindEvents() {
        document.getElementById('sd-new-ticket')?.addEventListener('click', openCreateModal);
        document.getElementById('sd-save-ticket')?.addEventListener('click', saveTicket);

        document.getElementById('sweetdesk-ticket-body')?.addEventListener('click', event => {
            const deleteButton = event.target.closest('.sd-delete-btn');
            const editButton = event.target.closest('.sd-edit-btn');

            if (deleteButton) {
                const row = deleteButton.closest('tr');
                const title = row?.querySelector('.sd-ticket-title-link')?.textContent?.trim() || '';
                openDeleteModal(Number(deleteButton.dataset.id), title);
                return;
            }

            if (editButton) {
                openEditModal(editButton.dataset.id);
            }
        });

        document.getElementById('sd-delete-ticket-modal')?.addEventListener('click', event => {
            if (event.target === event.currentTarget) {
                closeDeleteModal();
            }
        });
    }

    Tickets.modal = {
        renderStatusOptions,
        renderCustomFields,
        openCreateModal,
        openEditModal,
        closeModal,
        openDeleteModal,
        closeDeleteModal,
        confirmDelete,
        saveTicket,
        bindEvents
    };

    window.openTicketModal = mode => mode === 'edit' ? null : openCreateModal();
    window.closeTicketModal = closeModal;
    window.openDeleteTicketModal = openDeleteModal;
    window.closeDeleteTicketModal = closeDeleteModal;
    window.confirmDeleteTicket = confirmDelete;
})(window);
