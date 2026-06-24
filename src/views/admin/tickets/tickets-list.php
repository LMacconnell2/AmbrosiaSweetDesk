<div class="sweetdesk-tickets-page">

    <!-- Header -->
    <div class="sweetdesk-header">
        <h1>Tickets</h1>

        <div class="sweetdesk-header-actions">
            <button class="sd-btn sd-btn-secondary">
                <span class="dashicons dashicons-upload"></span>
                Import CSV
            </button>

            <button class="sd-btn sd-btn-secondary">
                <span class="dashicons dashicons-download"></span>
                Export CSV
            </button>

            <button id="sd-new-ticket" class="sd-btn sd-btn-primary">
                <span class="dashicons dashicons-plus-alt2"></span>
                New Ticket
            </button>
        </div>
    </div>

    <!-- Search -->
    <div class="sweetdesk-search">
        <span class="dashicons dashicons-search"></span>

        <input
            id="sd-ticket-search"
            type="text"
            placeholder="Search tickets..."
        >
    </div>

    <!-- Filters -->
    <div class="sweetdesk-filters">

        <div class="sweetdesk-filter-label">
            <span class="dashicons dashicons-filter"></span>
            <span>Filters:</span>
        </div>

        <div class="sweetdesk-ticket-query-builder">

        <div class="sd-query-row">

            <select class="sd-logic">
                <option value="AND">AND</option>
                <option value="OR">OR</option>
            </select>

            <select class="sd-field">
                <option value="status">Status</option>
                <option value="date_opened">Date Opened</option>
                <option value="latest_response">Latest Response</option>
                <option value="client">Client</option>
                <option value="assignee">Assignee</option>
                <option value="title">Search Text</option>
            </select>

            <select class="sd-operator"></select>

            <div class="sd-value-container"></div>

            <button class="sd-remove-filter">−</button>

            <button class="sd-add-filter">+</button>

        </div>
        <div class="sweetdesk-ticket-sort">
            <label>Sort By</label>
            <select id="sd-sort-field">
                <option value="date_opened">Open Date</option>
                <option value="latest_response">Latest Response</option>
                <option value="priority">Priority</option>
            </select>

            <select id="sd-sort-direction">
                <option value="desc">Descending</option>
                <option value="asc">Ascending</option>
            </select>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="sweetdesk-table-wrapper">

        <table class="sweetdesk-table">

            <thead>
                <tr>
                    <th>ID ↕</th>
                    <th>Title ↕</th>
                    <th>Status ↕</th>
                    <th>Priority ↕</th>
                    <th>Client ↕</th>
                    <th>Assignee ↕</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody id="sweetdesk-ticket-body"></tbody>

        </table>
    </div>
    <!-- Tickets Pagination -->
    <div class="sweetdesk-pagination">
        <button id="sd-prev-page" class="sd-btn sd-btn-secondary">
            Previous
        </button>

        <span id="sd-page-info">
            Page 1
        </span>

        <button id="sd-next-page" class="sd-btn sd-btn-secondary">
            Next
        </button>
    </div>

    <!-- Delete Ticket Confirmation Modal -->
    <div class="sd-modal-overlay" id="sd-delete-ticket-modal">
        <div class="sd-modal sd-delete-modal">
            <div class="sd-modal-header">
                <h2>Delete Ticket</h2>
                <button class="sd-modal-close" onclick="closeDeleteTicketModal()">
                    ✕
                </button>
            </div>
            <div class="sd-modal-body">
                <p>Are you sure you want to delete ticket <strong id="sd-delete-ticket-title">#0</strong>? This action cannot be undone.</p>
            </div>
            <div class="sd-modal-footer">
                <button type="button" class="sd-btn sd-btn-secondary" onclick="closeDeleteTicketModal()">Cancel</button>
                <button type="button" class="sd-btn sd-btn-danger" onclick="confirmDeleteTicket()">Delete Ticket</button>
            </div>
        </div>
    </div>
<div class="sd-modal-overlay" id="sd-create-ticket-modal">
<div class="sd-modal sd-ticket-modal">

    <div class="sd-modal-header">
        <h2>Create Ticket</h2>

        <button
            class="sd-modal-close"
            onclick="closeTicketModal()"
        >
            ✕
        </button>
    </div>

    <div class="sd-modal-body">

        <div class="sd-form-grid">

            <div class="sd-form-group">
                <label>Title *</label>

                <input
                    type="text"
                    id="sd-ticket-title"
                    placeholder="Enter ticket title"
                >
            </div>

            <div class="sd-form-group">
                <label>Client</label>

                <select id="sd-ticket-client">
                    <option value="">
                        No client (clients API not connected)
                    </option>
                </select>
            </div>

            <div class="sd-form-group">
                <label>Assignee</label>

                <select id="sd-ticket-assignee">
                    <option value="">
                        Unassigned
                    </option>
                </select>
            </div>

            <div class="sd-form-group">
                <label>Status</label>

                <select id="sd-ticket-status">
                    <option value="open">
                        Open
                    </option>

                    <option value="pending">
                        Pending
                    </option>

                    <option value="in_progress">
                        In Progress
                    </option>

                    <option value="closed">
                        Closed
                    </option>
                </select>
            </div>

            <div class="sd-form-group">
                <label>Priority</label>

                <select id="sd-ticket-priority">
                    <option value="low">
                        Low
                    </option>

                    <option value="normal" selected>
                        Normal
                    </option>

                    <option value="high">
                        High
                    </option>

                    <option value="urgent">
                        Urgent
                    </option>
                </select>
            </div>

        </div>

        <hr>

        <div class="sd-form-group">
            <label>Reply Type</label>

            <select id="sd-ticket-reply-type">
                <option value="public">
                    Public
                </option>

                <option value="internal">
                    Internal
                </option>
            </select>
        </div>

        <div class="sd-form-group">
            <label>Initial Message *</label>

            <div
                id="sd-ticket-body"
                class="sweetdesk-quill-editor"
                data-placeholder="Describe the issue..."
            ></div>
        </div>

        <div id="sd-custom-fields-container">

            <!-- Future custom ticket fields -->

        </div>

    </div>

    <div id="sd-ticket-thread"></div>

    <div class="sd-modal-footer">

        <button
            type="button"
            class="sd-btn sd-btn-secondary"
            onclick="closeTicketModal()"
        >
            Cancel
        </button>

        <button
            type="button"
            class="sd-btn sd-btn-primary"
            id="sd-save-ticket"
        >
            Create Ticket
        </button>

    </div>

</div>
    
</div>