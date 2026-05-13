<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SweetDesk - Settings</title>
  <link rel="stylesheet" href="settings.css" />
</head>
<body>

  <main class="main">
    <div class="page-header">
      <h1 class="page-title">Settings</h1>
    </div>

    <div class="settings-layout">

    <!-- Left nav -->
    <aside class="settings-nav">
      <nav>
        <button class="nav-item active" data-tab="profile" onclick="switchTab('profile')">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          Profile
        </button>
        <button class="nav-item" data-tab="notifications" onclick="switchTab('notifications')">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
          Notifications
        </button>
        <button class="nav-item" data-tab="ticket-config" onclick="switchTab('ticket-config')">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
          Ticket Configuration
        </button>
      </nav>
    </aside>

    <!-- Right content -->
    <div class="settings-content">

      <!-- Profile Tab -->
      <div class="tab-panel active" id="tab-profile">
        <h2 class="tab-title">Profile Settings</h2>
        <div class="form-group">
          <label for="profile-name">Full Name</label>
          <input type="text" id="profile-name" placeholder="John Doe" />
        </div>
        <div class="form-group">
          <label for="profile-email">Email Address</label>
          <input type="email" id="profile-email" placeholder="john@ambrosia.dev" />
        </div>
        <div class="form-group">
          <label for="profile-role">Role</label>
          <input type="text" id="profile-role" placeholder="Developer" />
        </div>
        <div class="form-actions">
          <button class="btn-primary">Save Changes</button>
        </div>
      </div>

      <!-- Notifications Tab -->
      <div class="tab-panel" id="tab-notifications">
        <h2 class="tab-title">Notification Settings</h2>

        <div class="setting-row" id="row-email-notifications">
          <div class="setting-row-text">
            <span class="setting-label">Email Notifications</span>
            <span class="setting-desc">Receive email updates for ticket assignments</span>
          </div>
          <input type="checkbox" class="toggle-checkbox" id="toggle-email" checked onchange="toggleEmailConfig(this.checked)" />
        </div>

        <!-- Email Integration (revealed when Email Notifications is checked) -->
        <div class="email-config" id="emailConfig">
          <div class="email-config-inner">
            <div class="form-group">
              <label for="email-provider">Email Provider</label>
              <select id="email-provider">
                <option>Gmail</option>
                <option>Outlook</option>
                <option>Other</option>
              </select>
            </div>
            <div class="form-group">
              <label for="connected-email">Connected Email</label>
              <input type="email" id="connected-email" placeholder="Not connected" />
            </div>
            <button class="btn-primary btn-connect">Connect Email</button>
          </div>
        </div>

        <div class="setting-row">
          <div class="setting-row-text">
            <span class="setting-label">Ticket Updates</span>
            <span class="setting-desc">Get notified when tickets you're assigned to are updated</span>
          </div>
          <input type="checkbox" class="toggle-checkbox" id="toggle-tickets" checked />
        </div>

        <div class="setting-row">
          <div class="setting-row-text">
            <span class="setting-label">Team Mentions</span>
            <span class="setting-desc">Notifications when someone mentions you or your team</span>
          </div>
          <input type="checkbox" class="toggle-checkbox" id="toggle-mentions" checked />
        </div>
      </div>

      <!-- Ticket Configuration Tab -->
      <div class="tab-panel" id="tab-ticket-config">
        <h2 class="tab-title">Ticket Configuration</h2>

        <div class="config-section">
          <h3 class="config-section-title">Custom Status Options</h3>
          <ul class="config-list" id="statusList">
            <li class="config-item"><span>Open</span><button class="btn-remove" onclick="removeItem(this)">Remove</button></li>
            <li class="config-item"><span>Pending</span><button class="btn-remove" onclick="removeItem(this)">Remove</button></li>
            <li class="config-item"><span>In Progress</span><button class="btn-remove" onclick="removeItem(this)">Remove</button></li>
            <li class="config-item"><span>Waiting on Customer</span><button class="btn-remove" onclick="removeItem(this)">Remove</button></li>
            <li class="config-item"><span>Resolved</span><button class="btn-remove" onclick="removeItem(this)">Remove</button></li>
            <li class="config-item"><span>Closed</span><button class="btn-remove" onclick="removeItem(this)">Remove</button></li>
          </ul>
          <div class="add-row" id="addStatusRow" style="display:none;">
            <input type="text" id="newStatusInput" placeholder="Status name..." />
            <button class="btn-add-confirm" onclick="confirmAddStatus()">Add</button>
            <button class="btn-add-cancel" onclick="cancelAddStatus()">Cancel</button>
          </div>
          <button class="btn-add-link" id="addStatusBtn" onclick="showAddStatus()">+ Add Status</button>
        </div>

        <div class="config-section">
          <h3 class="config-section-title">Custom Fields</h3>
          <ul class="config-list" id="fieldList">
            <li class="config-item"><span>Estimated Hours</span><button class="btn-edit-link" onclick="editField(this)">Edit</button></li>
          </ul>
          <div class="add-row" id="addFieldRow" style="display:none;">
            <input type="text" id="newFieldInput" placeholder="Field name..." />
            <button class="btn-add-confirm" onclick="confirmAddField()">Add</button>
            <button class="btn-add-cancel" onclick="cancelAddField()">Cancel</button>
          </div>
          <button class="btn-add-link" id="addFieldBtn" onclick="showAddField()">+ Add Custom Field</button>
        </div>
      </div>

    </div>
  </div>

  </main>

  <script src="settings.js"></script>
</body>
</html>
