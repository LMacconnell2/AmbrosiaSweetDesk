<div class="sweetdesk-tickets-page">

    <div class="page-header">
        <h1 class="page-title">Tickets</h1>
        <div class="header-actions">
            <button id="sd-import-json" class="btn-outline" type="button">
                <span class="dashicons dashicons-upload"></span>
                Import JSON
            </button>
            <button id="sd-export-json" class="btn-outline" type="button">
                <span class="dashicons dashicons-download"></span>
                Export JSON
            </button>
            <button id="sd-new-ticket" class="btn-primary" type="button">
                <span class="dashicons dashicons-plus-alt2"></span>
                New Ticket
            </button>
        </div>
    </div>

    <div class="search-wrap">
        <span class="dashicons dashicons-search"></span>
        <input
            id="sd-ticket-search"
            type="text"
            placeholder="Search tickets..."
        >
    </div>

    <div class="filters-panel">
        <div class="filters-panel-label">
            <span class="dashicons dashicons-filter"></span>
            <span>Filters</span>
        </div>

        <div class="sweetdesk-ticket-query-builder">
            <div class="sd-query-rows">
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
                    <button type="button" class="sd-remove-filter" aria-label="Remove filter">−</button>
                    <button type="button" class="sd-add-filter" aria-label="Add filter">+</button>
                </div>
            </div>

            <div class="sweetdesk-ticket-sort">
                <label for="sd-sort-field">Sort by</label>
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
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID <span class="sort-icon">↕</span></th>
                    <th>Title <span class="sort-icon">↕</span></th>
                    <th>Status <span class="sort-icon">↕</span></th>
                    <th>Priority <span class="sort-icon">↕</span></th>
                    <th>Client <span class="sort-icon">↕</span></th>
                    <th>Assignee <span class="sort-icon">↕</span></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="sweetdesk-ticket-body"></tbody>
        </table>
    </div>

    <div class="pagination-bar">
        <button id="sd-prev-page" class="btn-outline" type="button">Previous</button>
        <span id="sd-page-info">Page 1</span>
        <button id="sd-next-page" class="btn-outline" type="button">Next</button>
    </div>

</div>

<div class="sd-modal-overlay" id="sd-delete-ticket-modal">
    <div class="sd-modal sd-delete-modal">
        <div class="sd-modal-header">
            <h2>Delete Ticket</h2>
            <button type="button" class="sd-modal-close" onclick="closeDeleteTicketModal()">✕</button>
        </div>
        <div class="sd-modal-body">
            <p>Are you sure you want to delete ticket <strong id="sd-delete-ticket-title">#0</strong>? This action cannot be undone.</p>
        </div>
        <div class="sd-modal-footer">
            <button type="button" class="btn-outline" onclick="closeDeleteTicketModal()">Cancel</button>
            <button type="button" class="btn-danger" onclick="confirmDeleteTicket()">Delete Ticket</button>
        </div>
    </div>
</div>

<div class="sd-modal-overlay" id="sd-create-ticket-modal">
    <div class="sd-modal sd-ticket-modal">
        <div class="sd-modal-header">
            <h2 id="sd-ticket-modal-title">Create Ticket</h2>
            <button type="button" class="sd-modal-close" onclick="closeTicketModal()">✕</button>
        </div>
        <div class="sd-modal-body">
            <div class="sd-form-grid">
                <div class="sd-form-group">
                    <label for="sd-ticket-title">Title *</label>
                    <input type="text" id="sd-ticket-title" placeholder="Enter ticket title">
                </div>
                <div class="sd-form-group">
                    <label for="sd-ticket-client">Client</label>
                    <select id="sd-ticket-client">
                        <option value="">No client (clients API not connected)</option>
                    </select>
                </div>
                <div class="sd-form-group">
                    <label for="sd-ticket-assignee">Assignee</label>
                    <select id="sd-ticket-assignee">
                        <option value="">Unassigned</option>
                    </select>
                </div>
                <div class="sd-form-group">
                    <label for="sd-ticket-status">Status</label>
                    <select id="sd-ticket-status">
                        <option value="open">Open</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div class="sd-form-group">
                    <label for="sd-ticket-priority">Priority</label>
                    <select id="sd-ticket-priority">
                        <option value="low">Low</option>
                        <option value="normal" selected>Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>

            <div id="sd-ticket-initial-message-section">
                <hr class="sd-form-divider">

                <div class="sd-form-group">
                    <label for="sd-ticket-reply-type">Reply Type</label>
                    <select id="sd-ticket-reply-type">
                        <option value="public">Public</option>
                        <option value="internal">Internal</option>
                    </select>
                </div>

                <div class="sd-form-group">
                    <label for="sd-ticket-body">Initial Message *</label>
                    <div
                        id="sd-ticket-body"
                        class="sweetdesk-quill-editor"
                        data-placeholder="Describe the issue..."
                    ></div>
                </div>
            </div>

            <div id="sd-custom-fields-container"></div>
        </div>
        <div class="sd-modal-footer">
            <button type="button" class="btn-outline" onclick="closeTicketModal()">Cancel</button>
            <button type="button" class="btn-primary" id="sd-save-ticket">Create Ticket</button>
        </div>
        <input type="file" id="sd-import-file" accept=".json" hidden>
    </div>
</div>
