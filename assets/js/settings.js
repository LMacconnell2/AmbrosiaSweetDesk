const SweetDeskSettingsPage = {
    maxAdditionalEmails: 5,

    endpoints: {
        email: 'email',
        statuses: 'tickets/status',
        fields: 'tickets/fields',
    },

    state: {
        emailSettings: null,
        statuses: [],
        fields: [],
    },

    async init() {
        this.initTabs();
        this.initToggles();
        this.bindNotificationEvents();
        this.bindStatusEvents();
        this.bindFieldEvents();

        await Promise.all([
            this.loadEmailSettings(),
            this.loadStatuses(),
            this.loadFields(),
        ]);
    },

    /**
     * REST helper
     */
    async request(path, options = {}) {
        const baseUrl = sweetdeskSettings.restUrl.replace(/\/+$/, '');
        const normalizedPath = path.replace(/^\/+/, '');

        const response = await fetch(`${baseUrl}/${normalizedPath}`, {
            method: options.method || 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': sweetdeskSettings.nonce,
                ...(options.headers || {}),
            },
            body:
                options.body !== undefined
                    ? JSON.stringify(options.body)
                    : undefined,
        });

        let payload = null;

        try {
            payload = await response.json();
        } catch {
            payload = null;
        }

        if (!response.ok) {
            const message =
                payload?.message ||
                `Request failed with status ${response.status}.`;

            throw new Error(message);
        }

        return payload;
    },

    /**
     * Tabs
     */
    initTabs() {
        document.querySelectorAll('.nav-item').forEach(button => {
            button.addEventListener('click', () => {
                this.switchTab(button.dataset.tab);
            });
        });
    },

    switchTab(tab) {
        if (tab === 'profile') {
            window.location.href = `${window.location.origin}/wp-admin/profile.php`;
            return;
        }

        document.querySelectorAll('.nav-item').forEach(button => {
            button.classList.toggle(
                'active',
                button.dataset.tab === tab
            );
        });

        document.querySelectorAll('.tab-panel').forEach(panel => {
            panel.classList.toggle(
                'active',
                panel.id === `tab-${tab}`
            );
        });
    },

    /**
     * Toggle controls
     */
    initToggles() {
        document.querySelectorAll('.toggle').forEach(toggle => {
            toggle.addEventListener('click', () => {
                this.setToggle(
                    toggle,
                    toggle.getAttribute('aria-checked') !== 'true'
                );
            });
        });
    },

    setToggle(toggleOrId, checked) {
        const toggle =
            typeof toggleOrId === 'string'
                ? document.getElementById(toggleOrId)
                : toggleOrId;

        if (!toggle) {
            return;
        }

        toggle.setAttribute(
            'aria-checked',
            checked ? 'true' : 'false'
        );
    },

    getToggleValue(id) {
        return (
            document
                .getElementById(id)
                ?.getAttribute('aria-checked') === 'true'
        );
    },

    /**
     * Messages
     */
    showMessage(elementId, message, type = 'success') {
        const element = document.getElementById(elementId);

        if (!element) {
            return;
        }

        element.textContent = message;
        element.className = `settings-message ${type}`;
        element.hidden = false;
    },

    clearMessage(elementId) {
        const element = document.getElementById(elementId);

        if (!element) {
            return;
        }

        element.textContent = '';
        element.hidden = true;
    },

    /**
     * Email settings
     */
    bindNotificationEvents() {
        document
            .getElementById('addEmailBtn')
            ?.addEventListener('click', () => {
                this.addEmailInput();
            });

        document
            .getElementById('saveNotificationSettings')
            ?.addEventListener('click', () => {
                this.saveEmailSettings();
            });
    },

    async loadEmailSettings() {
        this.clearMessage('notificationMessage');

        try {
            const response = await this.request(
                this.endpoints.email
            );

            this.state.emailSettings = response.data;

            document.getElementById('wordpress-email').value =
                response.data.wordpress_email || '';

            this.setToggle(
                'toggle-tickets',
                Boolean(response.data.send_ticket_updates)
            );

            this.setToggle(
                'toggle-mentions',
                Boolean(response.data.send_team_mentions)
            );

            this.renderAdditionalEmails(
                response.data.additional_emails || []
            );
        } catch (error) {
            this.showMessage(
                'notificationMessage',
                error.message,
                'error'
            );
        }
    },

    renderAdditionalEmails(emails) {
        const container = document.getElementById(
            'additionalEmailList'
        );

        container.replaceChildren();

        emails.forEach(email => {
            this.addEmailInput(email);
        });

        this.updateAddEmailButton();
    },

    addEmailInput(value = '') {
        const container = document.getElementById(
            'additionalEmailList'
        );

        const currentRows = container.querySelectorAll(
            '.additional-email-row'
        );

        if (
            currentRows.length >=
            this.maxAdditionalEmails
        ) {
            this.showMessage(
                'notificationMessage',
                `You may add no more than ${this.maxAdditionalEmails} additional email addresses.`,
                'error'
            );

            return;
        }

        const row = document.createElement('div');
        row.className = 'additional-email-row';

        const input = document.createElement('input');
        input.type = 'email';
        input.className = 'additional-email-input';
        input.placeholder = 'name@example.com';
        input.value = value;
        input.autocomplete = 'email';

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'btn-remove';
        removeButton.textContent = 'Remove';

        removeButton.addEventListener('click', () => {
            row.remove();
            this.updateAddEmailButton();
        });

        row.append(input, removeButton);
        container.appendChild(row);

        input.focus();
        this.updateAddEmailButton();
    },

    updateAddEmailButton() {
        const button = document.getElementById('addEmailBtn');
        const count = document.querySelectorAll(
            '.additional-email-row'
        ).length;

        if (button) {
            button.disabled =
                count >= this.maxAdditionalEmails;
        }
    },

    getAdditionalEmails() {
        return Array.from(
            document.querySelectorAll(
                '.additional-email-input'
            )
        )
            .map(input => input.value.trim().toLowerCase())
            .filter(Boolean);
    },

    validateAdditionalEmails(emails) {
        const wordpressEmail = document
            .getElementById('wordpress-email')
            .value.trim()
            .toLowerCase();

        if (emails.length > this.maxAdditionalEmails) {
            throw new Error(
                `You may add no more than ${this.maxAdditionalEmails} additional email addresses.`
            );
        }

        const uniqueEmails = new Set();

        emails.forEach(email => {
            const input = document.createElement('input');
            input.type = 'email';
            input.value = email;

            if (!input.checkValidity()) {
                throw new Error(
                    `"${email}" is not a valid email address.`
                );
            }

            if (email === wordpressEmail) {
                throw new Error(
                    'Your WordPress account email does not need to be added as an additional address.'
                );
            }

            if (uniqueEmails.has(email)) {
                throw new Error(
                    `"${email}" was entered more than once.`
                );
            }

            uniqueEmails.add(email);
        });

        return Array.from(uniqueEmails);
    },

    async saveEmailSettings() {
        this.clearMessage('notificationMessage');

        const button = document.getElementById(
            'saveNotificationSettings'
        );

        button.disabled = true;

        try {
            const emails = this.validateAdditionalEmails(
                this.getAdditionalEmails()
            );

            const response = await this.request(
                this.endpoints.email,
                {
                    method: 'PUT',
                    body: {
                        additional_emails: emails,
                        send_ticket_updates:
                            this.getToggleValue(
                                'toggle-tickets'
                            ),
                        send_team_mentions:
                            this.getToggleValue(
                                'toggle-mentions'
                            ),
                    },
                }
            );

            this.state.emailSettings = response.data;

            this.renderAdditionalEmails(
                response.data.additional_emails || []
            );

            this.showMessage(
                'notificationMessage',
                response.message ||
                    'Email settings updated successfully.'
            );
        } catch (error) {
            this.showMessage(
                'notificationMessage',
                error.message,
                'error'
            );
        } finally {
            button.disabled = false;
        }
    },

    /**
     * Statuses
     */
    bindStatusEvents() {
        document
            .getElementById('addStatusBtn')
            ?.addEventListener('click', () => {
                this.showAddStatus();
            });

        document
            .getElementById('confirmAddStatusBtn')
            ?.addEventListener('click', () => {
                this.createStatus();
            });

        document
            .getElementById('cancelAddStatusBtn')
            ?.addEventListener('click', () => {
                this.cancelAddStatus();
            });

        document
            .getElementById('newStatusInput')
            ?.addEventListener('keydown', event => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    this.createStatus();
                }
            });
    },

    async loadStatuses() {
        this.clearMessage('ticketConfigMessage');

        try {
            const response = await this.request(
                `${this.endpoints.statuses}?include_inactive=true`
            );

            this.state.statuses = response.data || [];
            this.renderStatuses();
        } catch (error) {
            this.showMessage(
                'ticketConfigMessage',
                error.message,
                'error'
            );
        }
    },

    renderStatuses() {
        const list = document.getElementById('statusList');
        list.replaceChildren();

        if (this.state.statuses.length === 0) {
            list.appendChild(
                this.createEmptyListItem(
                    'No ticket statuses have been configured.'
                )
            );

            return;
        }

        this.state.statuses.forEach(status => {
            list.appendChild(this.createStatusItem(status));
        });
    },

    createStatusItem(status) {
        const item = document.createElement('li');
        item.className = 'config-item';
        item.dataset.id = status.id;

        if (!status.is_active) {
            item.classList.add('inactive');
        }

        const content = document.createElement('div');
        content.className = 'config-item-content';

        const name = document.createElement('span');
        name.className = 'config-item-name';
        name.textContent = status.name;

        const metadata = document.createElement('span');
        metadata.className = 'config-item-meta';
        metadata.textContent =
            `${status.slug} · Sort ${status.sort_order}` +
            (status.is_active ? '' : ' · Inactive');

        content.append(name, metadata);

        const actions = document.createElement('div');
        actions.className = 'config-item-actions';

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.className = 'btn-edit-link';
        editButton.textContent = 'Edit';
        editButton.addEventListener('click', () => {
            this.editStatus(item, status);
        });

        const activeButton = document.createElement('button');
        activeButton.type = 'button';
        activeButton.className = status.is_active
            ? 'btn-remove'
            : 'btn-add-link';

        activeButton.textContent = status.is_active
            ? 'Deactivate'
            : 'Reactivate';

        activeButton.addEventListener('click', () => {
            this.setStatusActive(
                status.id,
                !status.is_active
            );
        });

        actions.append(editButton, activeButton);
        item.append(content, actions);

        return item;
    },

    showAddStatus() {
        document.getElementById('addStatusRow').hidden = false;
        document.getElementById('addStatusBtn').hidden = true;
        document.getElementById('newStatusInput').focus();
    },

    cancelAddStatus() {
        document.getElementById('addStatusRow').hidden = true;
        document.getElementById('addStatusBtn').hidden = false;
        document.getElementById('newStatusInput').value = '';
        document.getElementById('newStatusSortOrder').value = '0';
    },

    async createStatus() {
        const name = document
            .getElementById('newStatusInput')
            .value.trim();

        const sortOrder = Number(
            document.getElementById(
                'newStatusSortOrder'
            ).value || 0
        );

        if (!name) {
            this.showMessage(
                'ticketConfigMessage',
                'Enter a status name.',
                'error'
            );

            return;
        }

        try {
            await this.request(this.endpoints.statuses, {
                method: 'POST',
                body: {
                    name,
                    is_active: true,
                    sort_order: Math.max(0, sortOrder),
                },
            });

            this.cancelAddStatus();
            await this.loadStatuses();

            this.showMessage(
                'ticketConfigMessage',
                'Ticket status created successfully.'
            );
        } catch (error) {
            this.showMessage(
                'ticketConfigMessage',
                error.message,
                'error'
            );
        }
    },

    editStatus(item, status) {
        item.replaceChildren();

        const nameInput = document.createElement('input');
        nameInput.type = 'text';
        nameInput.value = status.name;
        nameInput.maxLength = 100;
        nameInput.className = 'inline-edit-input';

        const sortInput = document.createElement('input');
        sortInput.type = 'number';
        sortInput.min = '0';
        sortInput.value = String(status.sort_order);
        sortInput.className = 'inline-edit-number';

        const saveButton = document.createElement('button');
        saveButton.type = 'button';
        saveButton.className = 'btn-add-confirm';
        saveButton.textContent = 'Save';

        const cancelButton = document.createElement('button');
        cancelButton.type = 'button';
        cancelButton.className = 'btn-add-cancel';
        cancelButton.textContent = 'Cancel';

        saveButton.addEventListener('click', async () => {
            const name = nameInput.value.trim();

            if (!name) {
                this.showMessage(
                    'ticketConfigMessage',
                    'Status names cannot be empty.',
                    'error'
                );

                return;
            }

            try {
                await this.request(
                    `${this.endpoints.statuses}/${status.id}`,
                    {
                        method: 'PUT',
                        body: {
                            name,
                            sort_order: Math.max(
                                0,
                                Number(sortInput.value || 0)
                            ),
                        },
                    }
                );

                await this.loadStatuses();

                this.showMessage(
                    'ticketConfigMessage',
                    'Ticket status updated successfully.'
                );
            } catch (error) {
                this.showMessage(
                    'ticketConfigMessage',
                    error.message,
                    'error'
                );
            }
        });

        cancelButton.addEventListener('click', () => {
            this.renderStatuses();
        });

        item.append(
            nameInput,
            sortInput,
            saveButton,
            cancelButton
        );

        nameInput.focus();
    },

    async setStatusActive(id, isActive) {
        try {
            await this.request(
                `${this.endpoints.statuses}/${id}`,
                {
                    method: 'PUT',
                    body: {
                        is_active: isActive,
                    },
                }
            );

            await this.loadStatuses();

            this.showMessage(
                'ticketConfigMessage',
                isActive
                    ? 'Ticket status reactivated.'
                    : 'Ticket status deactivated.'
            );
        } catch (error) {
            this.showMessage(
                'ticketConfigMessage',
                error.message,
                'error'
            );
        }
    },

    /**
     * Fields
     */
    bindFieldEvents() {
        document
            .getElementById('addFieldBtn')
            ?.addEventListener('click', () => {
                this.showAddField();
            });

        document
            .getElementById('confirmAddFieldBtn')
            ?.addEventListener('click', () => {
                this.createField();
            });

        document
            .getElementById('cancelAddFieldBtn')
            ?.addEventListener('click', () => {
                this.cancelAddField();
            });

        document
            .getElementById('newFieldType')
            ?.addEventListener('change', event => {
                document.getElementById(
                    'newFieldOptionsGroup'
                ).hidden = event.target.value !== 'select';
            });

        document
            .getElementById('newFieldInput')
            ?.addEventListener('keydown', event => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    this.createField();
                }
            });
    },

    async loadFields() {
        try {
            const response = await this.request(
                `${this.endpoints.fields}?include_inactive=true`
            );

            this.state.fields = response.data || [];
            this.renderFields();
        } catch (error) {
            this.showMessage(
                'ticketConfigMessage',
                error.message,
                'error'
            );
        }
    },

    renderFields() {
        const list = document.getElementById('fieldList');
        list.replaceChildren();

        if (this.state.fields.length === 0) {
            list.appendChild(
                this.createEmptyListItem(
                    'No custom ticket fields have been configured.'
                )
            );

            return;
        }

        this.state.fields.forEach(field => {
            list.appendChild(this.createFieldItem(field));
        });
    },

    createFieldItem(field) {
        const item = document.createElement('li');
        item.className = 'config-item';
        item.dataset.id = field.id;

        if (!field.is_active) {
            item.classList.add('inactive');
        }

        const content = document.createElement('div');
        content.className = 'config-item-content';

        const name = document.createElement('span');
        name.className = 'config-item-name';
        name.textContent = field.name;

        const metadata = document.createElement('span');
        metadata.className = 'config-item-meta';

        const details = [
            this.formatFieldType(field.field_type),
            field.field_key,
            `Sort ${field.sort_order}`,
        ];

        if (field.is_required) {
            details.push('Required');
        }

        if (!field.is_active) {
            details.push('Inactive');
        }

        metadata.textContent = details.join(' · ');

        content.append(name, metadata);

        const actions = document.createElement('div');
        actions.className = 'config-item-actions';

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.className = 'btn-edit-link';
        editButton.textContent = 'Edit';
        editButton.addEventListener('click', () => {
            this.editField(item, field);
        });

        const activeButton = document.createElement('button');
        activeButton.type = 'button';
        activeButton.className = field.is_active
            ? 'btn-remove'
            : 'btn-add-link';

        activeButton.textContent = field.is_active
            ? 'Deactivate'
            : 'Reactivate';

        activeButton.addEventListener('click', () => {
            this.setFieldActive(
                field.id,
                !field.is_active
            );
        });

        actions.append(editButton, activeButton);
        item.append(content, actions);

        return item;
    },

    showAddField() {
        document.getElementById('addFieldRow').hidden = false;
        document.getElementById('addFieldBtn').hidden = true;
        document.getElementById('newFieldInput').focus();
    },

    cancelAddField() {
        document.getElementById('addFieldRow').hidden = true;
        document.getElementById('addFieldBtn').hidden = false;

        document.getElementById('newFieldInput').value = '';
        document.getElementById('newFieldType').value = 'text';
        document.getElementById('newFieldRequired').checked = false;
        document.getElementById('newFieldSortOrder').value = '0';
        document.getElementById('newFieldOptions').value = '';
        document.getElementById('newFieldOptionsGroup').hidden = true;
    },

    parseOptions(value) {
        return Array.from(
            new Set(
                value
                    .split(',')
                    .map(option => option.trim())
                    .filter(Boolean)
            )
        );
    },

    async createField() {
        const name = document
            .getElementById('newFieldInput')
            .value.trim();

        const fieldType =
            document.getElementById('newFieldType').value;

        const isRequired =
            document.getElementById(
                'newFieldRequired'
            ).checked;

        const sortOrder = Math.max(
            0,
            Number(
                document.getElementById(
                    'newFieldSortOrder'
                ).value || 0
            )
        );

        const options = this.parseOptions(
            document.getElementById('newFieldOptions').value
        );

        if (!name) {
            this.showMessage(
                'ticketConfigMessage',
                'Enter a custom field name.',
                'error'
            );

            return;
        }

        if (fieldType === 'select' && options.length === 0) {
            this.showMessage(
                'ticketConfigMessage',
                'Select fields require at least one option.',
                'error'
            );

            return;
        }

        const body = {
            name,
            field_type: fieldType,
            is_required: isRequired,
            is_active: true,
            sort_order: sortOrder,
        };

        if (fieldType === 'select') {
            body.options = options;
        }

        try {
            await this.request(this.endpoints.fields, {
                method: 'POST',
                body,
            });

            this.cancelAddField();
            await this.loadFields();

            this.showMessage(
                'ticketConfigMessage',
                'Custom ticket field created successfully.'
            );
        } catch (error) {
            this.showMessage(
                'ticketConfigMessage',
                error.message,
                'error'
            );
        }
    },

    editField(item, field) {
        item.replaceChildren();

        const nameInput = document.createElement('input');
        nameInput.type = 'text';
        nameInput.value = field.name;
        nameInput.maxLength = 150;
        nameInput.className = 'inline-edit-input';

        const typeSelect = document.createElement('select');
        typeSelect.className = 'inline-edit-select';

        [
            ['text', 'Text'],
            ['textarea', 'Long Text'],
            ['number', 'Number'],
            ['email', 'Email'],
            ['url', 'URL'],
            ['date', 'Date'],
            ['datetime', 'Date and Time'],
            ['checkbox', 'Checkbox'],
            ['select', 'Select'],
        ].forEach(([value, label]) => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            option.selected = field.field_type === value;
            typeSelect.appendChild(option);
        });

        const requiredLabel = document.createElement('label');
        requiredLabel.className = 'inline-checkbox';

        const requiredInput = document.createElement('input');
        requiredInput.type = 'checkbox';
        requiredInput.checked = field.is_required;

        requiredLabel.append(
            requiredInput,
            document.createTextNode(' Required')
        );

        const sortInput = document.createElement('input');
        sortInput.type = 'number';
        sortInput.min = '0';
        sortInput.value = String(field.sort_order);
        sortInput.className = 'inline-edit-number';

        const optionsInput = document.createElement('input');
        optionsInput.type = 'text';
        optionsInput.className = 'inline-edit-input';
        optionsInput.placeholder = 'Options separated by commas';
        optionsInput.value = (field.options || []).join(', ');
        optionsInput.hidden = field.field_type !== 'select';

        typeSelect.addEventListener('change', () => {
            optionsInput.hidden = typeSelect.value !== 'select';
        });

        const saveButton = document.createElement('button');
        saveButton.type = 'button';
        saveButton.className = 'btn-add-confirm';
        saveButton.textContent = 'Save';

        const cancelButton = document.createElement('button');
        cancelButton.type = 'button';
        cancelButton.className = 'btn-add-cancel';
        cancelButton.textContent = 'Cancel';

        saveButton.addEventListener('click', async () => {
            const name = nameInput.value.trim();
            const fieldType = typeSelect.value;
            const options = this.parseOptions(
                optionsInput.value
            );

            if (!name) {
                this.showMessage(
                    'ticketConfigMessage',
                    'Custom field names cannot be empty.',
                    'error'
                );

                return;
            }

            if (
                fieldType === 'select' &&
                options.length === 0
            ) {
                this.showMessage(
                    'ticketConfigMessage',
                    'Select fields require at least one option.',
                    'error'
                );

                return;
            }

            const body = {
                name,
                field_type: fieldType,
                is_required: requiredInput.checked,
                sort_order: Math.max(
                    0,
                    Number(sortInput.value || 0)
                ),
                options:
                    fieldType === 'select'
                        ? options
                        : [],
            };

            try {
                await this.request(
                    `${this.endpoints.fields}/${field.id}`,
                    {
                        method: 'PUT',
                        body,
                    }
                );

                await this.loadFields();

                this.showMessage(
                    'ticketConfigMessage',
                    'Custom ticket field updated successfully.'
                );
            } catch (error) {
                this.showMessage(
                    'ticketConfigMessage',
                    error.message,
                    'error'
                );
            }
        });

        cancelButton.addEventListener('click', () => {
            this.renderFields();
        });

        item.append(
            nameInput,
            typeSelect,
            requiredLabel,
            sortInput,
            optionsInput,
            saveButton,
            cancelButton
        );

        nameInput.focus();
    },

    async setFieldActive(id, isActive) {
        try {
            await this.request(
                `${this.endpoints.fields}/${id}`,
                {
                    method: 'PUT',
                    body: {
                        is_active: isActive,
                    },
                }
            );

            await this.loadFields();

            this.showMessage(
                'ticketConfigMessage',
                isActive
                    ? 'Custom ticket field reactivated.'
                    : 'Custom ticket field deactivated.'
            );
        } catch (error) {
            this.showMessage(
                'ticketConfigMessage',
                error.message,
                'error'
            );
        }
    },

    /**
     * Shared rendering helpers
     */
    createEmptyListItem(message) {
        const item = document.createElement('li');
        item.className = 'config-list-empty';
        item.textContent = message;

        return item;
    },

    formatFieldType(type) {
        const labels = {
            text: 'Text',
            textarea: 'Long Text',
            number: 'Number',
            email: 'Email',
            url: 'URL',
            date: 'Date',
            datetime: 'Date and Time',
            checkbox: 'Checkbox',
            select: 'Select',
        };

        return labels[type] || type;
    },
};

document.addEventListener('DOMContentLoaded', () => {
    SweetDeskSettingsPage.init();
});