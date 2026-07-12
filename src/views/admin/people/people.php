<div class="layout">
    <main class="main" id="mainContent">
      <div class="page-header">
        <h1 class="page-title">People</h1>
        <div class="header-actions">
          <button class="btn-outline" type="button" id="sd-import-people">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Import CSV
          </button>
          <button class="btn-outline" type="button" id="sd-export-people">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Export CSV
          </button>
          <button id="sd-new-person" class="btn-primary" type="button">
            <span class="dashicons dashicons-plus-alt2"></span>
            New Person
          </button>
        </div>
      </div>

      <div class="section section-people">
        <div class="toolbar">
          <div class="search-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/></svg>
            <input type="text" id="people-search" placeholder="Search people..." />
          </div>
          <div class="filter-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
            Filters:
            <select id="people-role-filter">
              <option value="">All Roles</option>
            </select>
            <select id="people-company-filter">
              <option value="">All Companies</option>
            </select>
            <div class="people-team-filter" id="people-team-filter">
              <button
                type="button"
                class="people-team-filter-trigger"
                id="people-team-filter-trigger"
                aria-haspopup="listbox"
                aria-expanded="false"
                aria-controls="people-team-filter-menu"
              >
                <span class="people-team-filter-label" id="people-team-filter-label">All Teams</span>
              </button>
              <div class="people-team-filter-menu" id="people-team-filter-menu" hidden>
                <ul
                  class="people-team-filter-list"
                  id="people-team-filter-list"
                  role="listbox"
                  aria-multiselectable="true"
                ></ul>
              </div>
            </div>
          </div>
        </div>
        <table>
          <thead>
            <tr>
              <th class="col-check"><input type="checkbox" class="select-all-check" id="people-select-all" aria-label="Select all people" /></th>
              <th>Name <span class="sort-icon">⇅</span></th>
              <th>Role <span class="sort-icon">⇅</span></th>
              <th>Company <span class="sort-icon">⇅</span></th>
              <th>Teams <span class="sort-icon">⇅</span></th>
              <th>Email <span class="sort-icon">⇅</span></th>
              <th>Phone</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="people-table-body"></tbody>
        </table>
        <div class="table-bulk-actions sd-form-hidden" id="people-bulk-actions">
          <button type="button" class="btn-danger" id="people-bulk-delete">Delete Selected</button>
        </div>
        <div class="pagination-bar">
          <button id="sd-people-prev-page" class="btn-outline" type="button">Previous</button>
          <span id="sd-people-page-info">Page 1</span>
          <button id="sd-people-next-page" class="btn-outline" type="button">Next</button>
        </div>
      </div>
    </main>

    <!-- Person sidebar (create & edit) -->
    <div class="side-panel-shell collapsed" id="personPanelShell">
      <button class="panel-toggle" type="button" onclick="togglePersonPanel()" aria-label="Expand or collapse person panel">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
      </button>
      <aside class="side-panel" id="personPanel">
        <div class="side-panel-inner">
          <div class="side-panel-header">
            <h2 id="person-panel-title">Add New Person</h2>
            <button type="button" class="side-panel-close" onclick="closePersonSidebar()" aria-label="Close panel">✕</button>
          </div>
        <div class="form-group" id="typeToggleField">
          <label>Type</label>
          <div class="type-toggle">
            <button type="button" id="typeInternal">Internal</button>
            <button type="button" class="active" id="typeClient">Client</button>
          </div>
        </div>
        <div class="form-group">
          <label for="person-panel-name">Name</label>
          <input type="text" id="person-panel-name" placeholder="Full name" />
        </div>
        <div class="form-group" id="roleField">
          <label for="person-panel-role">Role</label>
          <input type="text" id="person-panel-role" placeholder="Job title or role" />
        </div>
        <div class="form-group">
          <label for="person-panel-email">Email</label>
          <input type="email" id="person-panel-email" placeholder="email@example.com" />
        </div>
        <div class="form-group" id="companyField">
          <label for="person-panel-client">Company</label>
          <select id="person-panel-client">
            <option value="">None</option>
          </select>
        </div>
        <div class="form-group" id="teamsField">
          <label for="person-panel-teams-add">Teams</label>
          <div class="person-team-picker">
            <select id="person-panel-teams-add">
              <option value="">Add a team...</option>
            </select>
            <div id="person-panel-teams-selected" class="person-team-picker-box" aria-live="polite"></div>
          </div>
        </div>
        <div class="form-group">
          <label for="person-panel-phone">Phone Number</label>
          <input type="tel" id="person-panel-phone" placeholder="555-0000" />
        </div>
        <div class="form-group">
          <label for="person-panel-notes">Notes</label>
          <textarea id="person-panel-notes" placeholder="Additional notes..."></textarea>
        </div>
        <button type="button" class="btn-primary" id="person-panel-submit">Add Person</button>
        </div>
      </aside>
    </div>
</div>

<div class="sd-modal-overlay" id="sd-delete-person-modal">
    <div class="sd-modal sd-delete-modal">
        <div class="sd-modal-header">
            <h2>Delete Person</h2>
            <button type="button" class="sd-modal-close" onclick="closeDeletePersonModal()">✕</button>
        </div>
        <div class="sd-modal-body">
            <p>Are you sure you want to delete <strong id="sd-delete-person-name">Person Name</strong>? This action cannot be undone.</p>
        </div>
        <div class="sd-modal-footer">
            <button type="button" class="btn-outline" onclick="closeDeletePersonModal()">Cancel</button>
            <button type="button" class="btn-danger" onclick="confirmDeletePerson()">Delete Person</button>
        </div>
    </div>
</div>
