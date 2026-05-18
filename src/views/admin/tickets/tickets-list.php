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

            <button class="sd-btn sd-btn-primary">
                <span class="dashicons dashicons-plus-alt2"></span>
                New Ticket
            </button>
        </div>
    </div>

    <!-- Search -->
    <div class="sweetdesk-search">
        <span class="dashicons dashicons-search"></span>

        <input
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
                </tr>
            </thead>

            <tbody id="sweetdesk-ticket-body"></tbody>

        </table>

        <!-- Ticket Details Modal -->
<div
    id="sweetdesk-ticket-modal"
    class="sd-modal-overlay"
>

    <div class="sd-modal">

        <!-- Modal Header -->
        <div class="sd-modal-header">

            <div>
                <h2 id="sd-modal-title">
                    Login page not working
                </h2>

                <p id="sd-modal-id">
                    Ticket #1
                </p>
            </div>

            <button
                id="sd-modal-close"
                class="sd-modal-close"
            >
                ✕
            </button>

        </div>

        <!-- Modal Body -->
        <div class="sd-modal-body">

            <div class="sd-modal-grid">

                <div class="sd-modal-card">
                    <span class="sd-label">Status</span>

                    <span class="sd-badge sd-status-open">
                        Open
                    </span>
                </div>

                <div class="sd-modal-card">
                    <span class="sd-label">Priority</span>

                    <span class="sd-badge sd-priority-urgent">
                        Urgent
                    </span>
                </div>

                <div class="sd-modal-card">
                    <span class="sd-label">Client</span>

                    <p>Acme Corp</p>
                </div>

                <div class="sd-modal-card">
                    <span class="sd-label">Assigned To</span>

                    <p>John Doe</p>
                </div>

            </div>

            <div class="sd-modal-description">

                <span class="sd-label">
                    Description
                </span>

                <p>
                    Users are unable to log into the
                    website after the latest update.
                    The login form submits but returns
                    a blank page.
                </p>

            </div>

        </div>

    </div>

</div>