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

    function optionHtml(value, label, selectedValue) {
        const selected = String(value) === String(selectedValue) ? ' selected' : '';
        return `<option value="${Tickets.renderer.escapeHtml(String(value))}"${selected}>${Tickets.renderer.escapeHtml(label)}</option>`;
    }

    function renderStatusOptions(selectedValue = '') {
        const select = document.getElementById('sd-ticket-status');
        if (!select) {
            return;
        }

        const statuses = Tickets.state.lookups.statuses;
        let html = statuses.map(status => optionHtml(status.slug, status.name, selectedValue)).join('');

        if (selectedValue && !statuses.some(status => status.slug === selectedValue)) {
            html += optionHtml(selectedValue, selectedValue.replace(/[_-]/g, ' '), selectedValue);
        }

        select.innerHTML = html || '<option value="">No statuses available</option>';
    }

    function renderClientOptions(selectedValue = '') {
        const select = document.getElementById('sd-ticket-client');
        if (!select) {
            return;
        }

        const clients = Tickets.state.lookups.clients;
        let html = '<option value="">No client</option>';
        html += clients.map(client => optionHtml(client.id, client.name, selectedValue)).join('');

        if (selectedValue && !clients.some(client => String(client.id) === String(selectedValue))) {
            html += optionHtml(selectedValue, `Client #${selectedValue}`, selectedValue);
        }

        select.innerHTML = html;
    }

    function renderAssigneeOptions(selectedValue = '') {
        const select = document.getElementById('sd-ticket-assignee');
        if (!select) {
            return;
        }

        const people = Tickets.state.lookups.people;
        let html = '<option value="">Unassigned</option>';
        html += people.map(person => optionHtml(person.id, person.display_name, selectedValue)).join('');

        if (selectedValue && !people.some(person => String(person.id) === String(selectedValue))) {
            html += optionHtml(selectedValue, `Person #${selectedValue}`, selectedValue);
        }

        select.innerHTML = html;
    }

    function savedFieldValues(fields = []) {
        return fields.reduce((values, field) => {
            const key = field.meta_key || field.field_key;
            if (key) {
                values[key] = field.meta_value ?? field.value ?? '';
            }
            return values;
        }, {});
    }

    function renderCustomFieldInput(field, value) {
        const escapedKey = Tickets.renderer.escapeHtml(field.field_key);
        const required = field.is_required ? ' required' : '';
        const requiredMark = field.is_required ? ' *' : '';
        const escapedValue = Tickets.renderer.escapeHtml(String(value ?? ''));
        let input;

        switch (field.field_type) {
            case 'textarea':
                input = `<textarea class="sd-custom-field" data-meta-key="${escapedKey}"${required}>${escapedValue}</textarea>`;
                break;
            case 'select':
                input = `<select class="sd-custom-field" data-meta-key="${escapedKey}"${required}>
                    <option value="">Select an option</option>
                    ${field.options.map(option => optionHtml(option, option, value)).join('')}
                </select>`;
                break;
            case 'checkbox':
                input = `<input type="checkbox" class="sd-custom-field" data-meta-key="${escapedKey}" value="1"${String(value) === '1' || value === true ? ' checked' : ''}${required}>`;
                break;
            case 'number':
            case 'date':
            case 'email':
            case 'url':
                input = `<input type="${field.field_type}" class="sd-custom-field" data-meta-key="${escapedKey}" value="${escapedValue}"${required}>`;
                break;
            default:
                input = `<input type="text" class="sd-custom-field" data-meta-key="${escapedKey}" value="${escapedValue}"${required}>`;
        }

        return `<div class="sd-form-group"><label>${Tickets.renderer.escapeHtml(field.name)}${requiredMark}</label>${input}</div>`;
    }

    function renderCustomFields(existingFields = []) {
        const container = document.getElementById('sd-custom-fields-container');
        if (!container) {
            return;
        }

        const values = savedFieldValues(existingFields);
        container.innerHTML = Tickets.state.lookups.customFields
            .map(field => renderCustomFieldInput(field, values[field.field_key]))
            .join('');
    }

    function resetForm() {
        setValue('sd-ticket-title', '');
        renderClientOptions('');
        renderAssigneeOptions('');
        const defaultStatus = Tickets.state.lookups.statuses[0]?.slug || 'open';
        renderStatusOptions(defaultStatus);
        setValue('sd-ticket-priority', 'normal');
        setValue('sd-ticket-reply-type', 'public');
        renderCustomFields();
    }

    function populateForm(data) {
        const ticket = data.ticket || {};
        setValue('sd-ticket-title', ticket.title);
        renderClientOptions(ticket.client_id || '');
        renderAssigneeOptions(ticket.assigned_to || '');
        renderStatusOptions(ticket.status || Tickets.state.lookups.statuses[0]?.slug || 'open');
        setValue('sd-ticket-priority', ticket.priority || 'normal');
        renderCustomFields(data.custom_fields || []);
    }

    async function ensureLookups() {
        await Tickets.lookups.loadAll();
    }

    async function openCreateModal() {
        try {
            await ensureLookups();
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
            alert(error.message || 'Failed to load ticket form options.');
        }
    }

    async function openEditModal(ticketId) {
        try {
            const [data] = await Promise.all([
                Tickets.api.getTicket(ticketId),
                ensureLookups()
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
            alert(error.message || 'Failed to load ticket.');
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
            const empty = field.type === 'checkbox' ? !field.checked : !field.value.trim();
            if (empty) {
                field.focus();
                alert('Please complete all required custom fields.');
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

        if (state.modalMode === 'create' && SweetDeskEditor.isEmpty('sd-ticket-body')) {
            alert('Initial message is required.');
            return;
        }

        if (!validateCustomFields()) {
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
        openCreateModal,
        openEditModal,
        closeModal,
        openDeleteModal,
        closeDeleteModal,
        confirmDelete,
        saveTicket,
        bindEvents,
        renderClientOptions,
        renderAssigneeOptions,
        renderStatusOptions,
        renderCustomFields
    };

    window.openTicketModal = mode => mode === 'edit' ? null : openCreateModal();
    window.closeTicketModal = closeModal;
    window.openDeleteTicketModal = openDeleteModal;
    window.closeDeleteTicketModal = closeDeleteModal;
    window.confirmDeleteTicket = confirmDelete;
})(window);
