<div class="layout">
    <main class="main" id="mainContent">
      <div class="page-header">
        <h1 class="page-title">People</h1>
        <div class="header-actions">
          <button class="btn-outline" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Import CSV
          </button>
          <button class="btn-outline" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
          </button>
          <button id="sd-new-person" class="btn-primary" type="button">
            <span class="dashicons dashicons-plus-alt2"></span>
            New Person
          </button>
        </div>
      </div>

      <!-- Ambrosia Personnel -->
      <div class="section">
        <div class="section-header">
          <div class="section-accent-bar blue"></div>
          <h2 class="section-title">Ambrosia Personnel</h2>
        </div>
        <div class="toolbar">
          <div class="search-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/></svg>
            <input type="text" placeholder="Search Ambrosia personnel..." />
          </div>
          <div class="filter-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
            Filters:
            <select>
              <option>All Roles</option>
              <option>Developer</option>
              <option>Designer</option>
            </select>
          </div>
        </div>
        <table>
          <thead>
            <tr>
              <th class="col-check"><input type="checkbox" /></th>
              <th>Name <span class="sort-icon">⇅</span></th>
              <th>Role <span class="sort-icon">⇅</span></th>
              <th>Email <span class="sort-icon">⇅</span></th>
              <th>Phone <span class="sort-icon">⇅</span></th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="col-check"><input type="checkbox" /></td>
              <td class="col-name">John Doe</td>
              <td>Developer</td>
              <td><a href="mailto:john@example.com" class="email-link">john@example.com</a></td>
              <td>555-0101</td>
              <td>
                <div class="row-actions">
                  <button class="sd-action-btn sd-edit-btn" onclick="openEditPersonSidebar('John Doe','Developer','john@example.com','555-0101','','')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                  <button class="sd-action-btn sd-delete-btn"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
              </td>
            </tr>
            <tr>
              <td class="col-check"><input type="checkbox" /></td>
              <td class="col-name">Jane Smith</td>
              <td>Designer</td>
              <td><a href="mailto:jane@example.com" class="email-link">jane@example.com</a></td>
              <td>555-0102</td>
              <td>
                <div class="row-actions">
                  <button class="sd-action-btn sd-edit-btn" onclick="openEditPersonSidebar('Jane Smith','Designer','jane@example.com','555-0102','','')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                  <button class="sd-action-btn sd-delete-btn"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
              </td>
            </tr>
            <tr>
              <td class="col-check"><input type="checkbox" /></td>
              <td class="col-name">Alice Williams</td>
              <td>Developer</td>
              <td><a href="mailto:alice@example.com" class="email-link">alice@example.com</a></td>
              <td>555-0104</td>
              <td>
                <div class="row-actions">
                  <button class="sd-action-btn sd-edit-btn" onclick="openEditPersonSidebar('Alice Williams','Developer','alice@example.com','555-0104','','')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                  <button class="sd-action-btn sd-delete-btn"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Client Contacts -->
      <div class="section">
        <div class="section-header">
          <div class="section-accent-bar purple"></div>
          <h2 class="section-title">Client Contacts</h2>
        </div>
        <div class="toolbar">
          <div class="search-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/></svg>
            <input type="text" placeholder="Search client contacts..." />
          </div>
          <div class="filter-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
            Filters:
            <select>
              <option>All Companies</option>
              <option>Acme Corp</option>
              <option>TechStart Inc</option>
              <option>Globex Solutions</option>
            </select>
          </div>
        </div>
        <table>
          <thead>
            <tr>
              <th class="col-check"><input type="checkbox" /></th>
              <th>Name <span class="sort-icon">⇅</span></th>
              <th>Role <span class="sort-icon">⇅</span></th>
              <th>Company <span class="sort-icon">⇅</span></th>
              <th>Email <span class="sort-icon">⇅</span></th>
              <th>Phone <span class="sort-icon">⇅</span></th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="col-check"><input type="checkbox" /></td>
              <td class="col-name">Bob Johnson</td>
              <td>Manager</td>
              <td>Acme Corp</td>
              <td><a href="mailto:bob@example.com" class="email-link">bob@example.com</a></td>
              <td>555-0103</td>
              <td>
                <div class="row-actions">
                  <button class="sd-action-btn sd-edit-btn" onclick="openEditPersonSidebar('Bob Johnson','Manager','bob@example.com','555-0103','Acme Corp','')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                  <button class="sd-action-btn sd-delete-btn"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
              </td>
            </tr>
            <tr>
              <td class="col-check"><input type="checkbox" /></td>
              <td class="col-name">Mike Chen</td>
              <td>CTO</td>
              <td>TechStart Inc</td>
              <td><a href="mailto:mike@techstart.com" class="email-link">mike@techstart.com</a></td>
              <td>555-0105</td>
              <td>
                <div class="row-actions">
                  <button class="sd-action-btn sd-edit-btn" onclick="openEditPersonSidebar('Mike Chen','CTO','mike@techstart.com','555-0105','TechStart Inc','')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                  <button class="sd-action-btn sd-delete-btn"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
              </td>
            </tr>
            <tr>
              <td class="col-check"><input type="checkbox" /></td>
              <td class="col-name">Sarah Davis</td>
              <td>Product Owner</td>
              <td>Globex Solutions</td>
              <td><a href="mailto:sarah@globex.com" class="email-link">sarah@globex.com</a></td>
              <td>555-0106</td>
              <td>
                <div class="row-actions">
                  <button class="sd-action-btn sd-edit-btn" onclick="openEditPersonSidebar('Sarah Davis','Product Owner','sarah@globex.com','555-0106','Globex Solutions','')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                  <button class="sd-action-btn sd-delete-btn"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
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
        <div class="form-group">
          <label>Type</label>
          <div class="type-toggle">
            <button type="button" class="active" id="typeInternal" onclick="setType('internal')">Internal</button>
            <button type="button" id="typeClient" onclick="setType('client')">Client</button>
          </div>
        </div>
        <div class="form-group">
          <label for="person-panel-name">Name</label>
          <input type="text" id="person-panel-name" placeholder="Full name" />
        </div>
        <div class="form-group">
          <label for="person-panel-role">Role</label>
          <input type="text" id="person-panel-role" placeholder="Job title or role" />
        </div>
        <div class="form-group">
          <label for="person-panel-email">Email</label>
          <input type="email" id="person-panel-email" placeholder="email@example.com" />
        </div>
        <div class="form-group" id="companyField">
          <label for="person-panel-company">Company</label>
          <input type="text" id="person-panel-company" placeholder="Company name" />
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
