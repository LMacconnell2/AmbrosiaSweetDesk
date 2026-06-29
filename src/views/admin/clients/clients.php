<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SweetDesk - Clients</title>
</head>
<body>

  <div class="layout">
    <main class="main" id="mainContent">
      <div class="page-header">
        <h1 class="page-title">Clients</h1>
        <div class="header-actions">
          <button class="btn-outline" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Import CSV
          </button>
          <button class="btn-outline" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
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
          <input type="text" placeholder="Search clients..." />
        </div>
        <div class="filter-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
          Filters:
          <select>
            <option>All Statuses</option>
            <option>Active</option>
            <option>Inactive</option>
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
          <tbody>
            <tr>
              <td class="col-check"><input type="checkbox" /></td>
              <td class="col-name">Acme Corp</td>
              <td>Technology</td>
              <td>Bob Johnson</td>
              <td>3</td>
              <td>15</td>
              <td><span class="status-badge active">Active</span></td>
              <td>
                <div class="row-actions">
                  <button class="sd-action-btn sd-edit-btn" onclick="openEditClientSidebar('Acme Corp', 'Bob Johnson', '')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                  <button class="sd-action-btn sd-delete-btn"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
              </td>
            </tr>
            <tr>
              <td class="col-check"><input type="checkbox" /></td>
              <td class="col-name">TechStart Inc</td>
              <td>Software</td>
              <td>Mike Chen</td>
              <td>5</td>
              <td>22</td>
              <td><span class="status-badge active">Active</span></td>
              <td>
                <div class="row-actions">
                  <button class="sd-action-btn sd-edit-btn" onclick="openEditClientSidebar('TechStart Inc', 'Mike Chen', '')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                  <button class="sd-action-btn sd-delete-btn"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
              </td>
            </tr>
            <tr>
              <td class="col-check"><input type="checkbox" /></td>
              <td class="col-name">Globex Solutions</td>
              <td>Consulting</td>
              <td>Sarah Davis</td>
              <td>1</td>
              <td>8</td>
              <td><span class="status-badge active">Active</span></td>
              <td>
                <div class="row-actions">
                  <button class="sd-action-btn sd-edit-btn" onclick="openEditClientSidebar('Globex Solutions', 'Sarah Davis', '')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                  <button class="sd-action-btn sd-delete-btn"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
              </td>
            </tr>
            <tr>
              <td class="col-check"><input type="checkbox" /></td>
              <td class="col-name">Initech LLC</td>
              <td>Finance</td>
              <td>N/A</td>
              <td>0</td>
              <td>3</td>
              <td><span class="status-badge inactive">Inactive</span></td>
              <td>
                <div class="row-actions">
                  <button class="sd-action-btn sd-edit-btn" onclick="openEditClientSidebar('Initech LLC', '', '')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                  <button class="sd-action-btn sd-delete-btn"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
              </td>
            </tr>
          </tbody>
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
          <label for="client-panel-contact">Primary Contact</label>
          <select id="client-panel-contact">
            <option value="">None</option>
            <option>Bob Johnson</option>
            <option>Mike Chen</option>
            <option>Sarah Davis</option>
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
</body>
</html>
