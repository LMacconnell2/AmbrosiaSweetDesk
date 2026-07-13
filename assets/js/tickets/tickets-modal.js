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

    function resetForm() {
        setValue('sd-ticket-title', '');
        setValue('sd-ticket-client', '');
        setValue('sd-ticket-assignee', '');
        setValue('sd-ticket-status', 'open');
        setValue('sd-ticket-priority', 'normal');
        document.getElementById('sd-custom-fields-container').innerHTML = '';
    }

    function renderCustomFields(fields) {
        const container = document.getElementById('sd-custom-fields-container');
        if (!container) {
            return;
        }

        container.innerHTML = fields.map(field => `
            <div class="sd-form-group">
                <label>${Tickets.renderer.escapeHtml(field.meta_key)}</label>
                <input
                    type="text"
                    class="sd-custom-field"
                    data-meta-id="${field.meta_id}"
                    data-meta-key="${Tickets.renderer.escapeHtml(field.meta_key)}"
                    value="${Tickets.renderer.escapeHtml(field.meta_value || '')}"
                >
            </div>
        `).join('');
    }

    function populateForm(data) {
        const ticket = data.ticket;
        setValue('sd-ticket-title', ticket.title);
        setValue('sd-ticket-client', ticket.client_id);
        setValue('sd-ticket-assignee', ticket.assigned_to);
        setValue('sd-ticket-status', ticket.status || 'open');
        setValue('sd-ticket-priority', ticket.priority || 'normal');
        renderCustomFields(data.custom_fields || []);
    }

    function openCreateModal() {
        Tickets.state.modalMode = 'create';
        Tickets.state.currentTicketId = null;
        resetForm();
        document.getElementById('sd-ticket-modal-title').textContent = 'Create Ticket';
        document.getElementById('sd-save-ticket').textContent = 'Create Ticket';
        setInitialMessageSectionVisible(true);
        document.getElementById('sd-create-ticket-modal').classList.add('active');
        initEditor(true);
    }

    async function openEditModal(ticketId) {
        try {
            const data = await Tickets.api.getTicket(ticketId);
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
            customFields[field.dataset.metaKey] = field.value;
        });
        return customFields;
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
        bindEvents
    };

    window.openTicketModal = mode => mode === 'edit' ? null : openCreateModal();
    window.closeTicketModal = closeModal;
    window.openDeleteTicketModal = openDeleteModal;
    window.closeDeleteTicketModal = closeDeleteModal;
    window.confirmDeleteTicket = confirmDelete;
})(window);
