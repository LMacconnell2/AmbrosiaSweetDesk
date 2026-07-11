<div class="layout">
    <main class="main" id="mainContent">
      <div class="page-header">
        <h1 class="page-title">Clients</h1>
        <div class="header-actions">
          <button class="btn-outline" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Import JSON
          </button>
          <button class="btn-outline" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Export JSON
          </button>
          <button id="sd-new-client" class="btn-primary" type="button">
            <span class="dashicons dashicons-plus-alt2"></span>
            New Client
          </button>
        </div>
      </div>

      <div class="toolbar">
        <div class="search-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/></svg>
          <input type="text" id="client-search" placeholder="Search clients..." />
        </div>
        <div class="filter-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
          Filters:
          <select id="client-status-filter">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th class="col-check"><input type="checkbox" /></th>
              <th>Client Name <span class="sort-icon">⇅</span></th>
              <th>Industry <span class="sort-icon">⇅</span></th>
              <th>Primary Contact <span class="sort-icon">⇅</span></th>
              <th>Active Tickets <span class="sort-icon">⇅</span></th>
              <th>Total Tickets <span class="sort-icon">⇅</span></th>
              <th>Status <span class="sort-icon">⇅</span></th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </main>

    <!-- Client sidebar (create & edit) -->
    <div class="side-panel-shell collapsed" id="clientPanelShell">
      <button class="panel-toggle" type="button" onclick="toggleClientPanel()" aria-label="Expand or collapse client panel">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
      </button>
      <aside class="side-panel" id="clientPanel">
        <div class="side-panel-inner">
          <div class="side-panel-header">
            <h2 id="client-panel-title">Add New Client</h2>
            <button type="button" class="side-panel-close" onclick="closeClientSidebar()" aria-label="Close panel">✕</button>
          </div>
        <div class="form-group">
          <label for="client-panel-name">Client Name</label>
          <input type="text" id="client-panel-name" placeholder="Company name" />
        </div>
        <div class="form-group">
          <label for="client-panel-email">Email</label>
          <input type="email" id="client-panel-email" placeholder="support@company.com" />
        </div>
        <div class="form-group">
          <label for="client-panel-phone">Phone</label>
          <input type="tel" id="client-panel-phone" placeholder="555-0000" />
        </div>
        <div class="form-group">
          <label for="client-panel-website">Website</label>
          <input type="url" id="client-panel-website" placeholder="https://company.com" />
        </div>
        <div class="form-group">
          <label for="client-panel-industry">Industry</label>
          <input type="text" id="client-panel-industry" placeholder="e.g. Technology" />
        </div>
        <div class="form-group">
          <label for="client-panel-status">Status</label>
          <select id="client-panel-status">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="form-group">
          <label for="client-panel-contact">Primary Contact</label>
          <select id="client-panel-contact">
            <option value="">None</option>
          </select>
        </div>
        <div class="form-group">
          <label for="client-panel-notes">Notes</label>
          <textarea id="client-panel-notes" placeholder="Additional notes about this client..."></textarea>
        </div>
        <button type="button" class="btn-primary" id="client-panel-submit">Add Client</button>
        </div>
      </aside>
    </div>
</div>

<div class="sd-modal-overlay" id="sd-delete-client-modal">
    <div class="sd-modal sd-delete-modal">
        <div class="sd-modal-header">
            <h2>Delete Client</h2>
            <button type="button" class="sd-modal-close" onclick="closeDeleteClientModal()">✕</button>
        </div>
        <div class="sd-modal-body">
            <p>Are you sure you want to delete <strong id="sd-delete-client-name">Client Name</strong>? This action cannot be undone.</p>
        </div>
        <div class="sd-modal-footer">
            <button type="button" class="btn-outline" onclick="closeDeleteClientModal()">Cancel</button>
            <button type="button" class="btn-danger" onclick="confirmDeleteClient()">Delete Client</button>
        </div>
    </div>
</div>
