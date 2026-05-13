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

        <select>
            <option>All Statuses</option>
            <option>Open</option>
            <option>In Progress</option>
            <option>Pending</option>
            <option>Closed</option>
            <option>Resolved</option>
        </select>

        <select>
            <option>All Priorities</option>
            <option>Urgent</option>
            <option>High</option>
            <option>Normal</option>
            <option>Low</option>
        </select>
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

            <tbody>

                <tr class="sd-ticket-row">
                    <td>#1</td>
                    <td>Login page not working</td>
                    <td>
                        <span class="sd-badge sd-status-open">
                            Open
                        </span>
                    </td>
                    <td>
                        <span class="sd-badge sd-priority-urgent">
                            Urgent
                        </span>
                    </td>
                    <td>
                        <span class="sd-pill">
                            Acme Corp
                        </span>
                    </td>
                    <td>John Doe</td>
                </tr>

                <tr class="sd-ticket-row">
                    <td>#2</td>
                    <td>Add export feature</td>
                    <td>
                        <span class="sd-badge sd-status-progress">
                            In Progress
                        </span>
                    </td>
                    <td>
                        <span class="sd-badge sd-priority-normal">
                            Normal
                        </span>
                    </td>
                    <td>
                        <span class="sd-pill">
                            TechStart Inc
                        </span>
                    </td>
                    <td>Jane Smith</td>
                </tr>

                <tr class="sd-ticket-row">
                    <td>#3</td>
                    <td>Update documentation</td>
                    <td>
                        <span class="sd-badge sd-status-pending">
                            Pending
                        </span>
                    </td>
                    <td>
                        <span class="sd-badge sd-priority-low">
                            Low
                        </span>
                    </td>
                    <td class="sd-muted">No client</td>
                    <td>Frontend Team</td>
                </tr>

                <tr class="sd-ticket-row">
                    <td>#4</td>
                    <td>Fix mobile layout</td>
                    <td>
                        <span class="sd-badge sd-status-closed">
                            Closed
                        </span>
                    </td>
                    <td>
                        <span class="sd-badge sd-priority-high">
                            High
                        </span>
                    </td>
                    <td>
                        <span class="sd-pill">
                            Acme Corp
                        </span>
                    </td>
                    <td>John Doe</td>
                </tr>

                <tr class="sd-ticket-row">
                    <td>#5</td>
                    <td>Customer needs clarification</td>
                    <td>
                        <span class="sd-badge sd-status-customer">
                            Waiting on Customer
                        </span>
                    </td>
                    <td>
                        <span class="sd-badge sd-priority-normal">
                            Normal
                        </span>
                    </td>
                    <td>
                        <span class="sd-pill">
                            Globex Solutions
                        </span>
                    </td>
                    <td>Jane Smith</td>
                </tr>

                <tr class="sd-ticket-row">
                    <td>#6</td>
                    <td>Security patch applied</td>
                    <td>
                        <span class="sd-badge sd-status-resolved">
                            Resolved
                        </span>
                    </td>
                    <td>
                        <span class="sd-badge sd-priority-urgent">
                            Urgent
                        </span>
                    </td>
                    <td>
                        <span class="sd-pill">
                            TechStart Inc
                        </span>
                    </td>
                    <td>Backend Team</td>
                </tr>

            </tbody>

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