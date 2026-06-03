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