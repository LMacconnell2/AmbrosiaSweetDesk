  <main class="main">
    <div class="page-header">
      <h1 class="page-title">Settings</h1>
    </div>

    <div class="settings-layout">

    <!-- Left nav -->
    <aside class="settings-nav">
      <nav>
        <button class="nav-item active" data-tab="notifications">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
          Notifications
        </button>
        <button type="button" class="nav-item" data-tab="ticket-config">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
          Ticket Configuration
        </button>
        <button type="button" class="nav-item" data-tab="integrations">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
          Integrations
        </button>
        <button type="button" class="nav-item" data-tab="licenses">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-id-card-icon lucide-id-card"><path d="M16 10h2"/><path d="M16 14h2"/><path d="M6.17 15a3 3 0 0 1 5.66 0"/><circle cx="9" cy="11" r="2"/><rect x="2" y="5" width="20" height="14" rx="2"/></svg>
          Licenses
        </button>
        <button type="button" class="nav-item" data-tab="profile">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          Profile
        </button>
      </nav>
    </aside>

    <!-- Right content -->
    <div class="settings-content">

      <!-- Notifications Tab -->
      <div class="tab-panel active" id="tab-notifications">
        <div class="tab-heading-row">
          <div>
            <h2 class="tab-title">Notification Settings</h2>
            <p class="tab-description">
              Manage where SweetDesk sends your email notifications.
            </p>
          </div>

          <button
            type="button"
            class="btn-primary"
            id="saveNotificationSettings"
          >
            Save Changes
          </button>
        </div>

        <div
          class="settings-message"
          id="notificationMessage"
          role="status"
          aria-live="polite"
          hidden
        ></div>

        <div class="email-config open" id="emailConfig">
          <div class="email-config-inner">
            <div class="form-group">
              <label for="wordpress-email">WordPress Account Email</label>

              <input
                type="email"
                id="wordpress-email"
                disabled
                aria-describedby="wordpress-email-description"
              />

              <span
                class="field-description"
                id="wordpress-email-description"
              >
                This address is managed through your WordPress profile.
              </span>
            </div>

            <div class="form-group">
              <div class="field-heading-row">
                <div>
                  <label>Additional Notification Emails</label>

                  <span class="field-description">
                    SweetDesk will send notifications to these addresses in
                    addition to your WordPress account email.
                  </span>
                </div>

                <button
                  type="button"
                  class="btn-add-link"
                  id="addEmailBtn"
                >
                  + Add Email
                </button>
              </div>

              <div
                class="additional-email-list"
                id="additionalEmailList"
              ></div>

              <span class="field-description">
                You may add up to five additional email addresses.
              </span>
            </div>
          </div>
        </div>

        <div class="setting-row">
          <div class="setting-row-text">
            <span class="setting-label" id="label-toggle-tickets">
              Ticket Updates
            </span>

            <span class="setting-desc">
              Receive notifications for ticket changes, assignments, and replies.
            </span>
          </div>

          <button
            type="button"
            class="toggle"
            id="toggle-tickets"
            role="switch"
            aria-checked="true"
            aria-labelledby="label-toggle-tickets"
          >
            <span class="toggle-thumb"></span>
          </button>
        </div>

        <div class="setting-row">
          <div class="setting-row-text">
            <span class="setting-label" id="label-toggle-mentions">
              Team Mentions
            </span>

            <span class="setting-desc">
              Receive a notification when you or one of your teams is mentioned.
            </span>
          </div>

          <button
            type="button"
            class="toggle"
            id="toggle-mentions"
            role="switch"
            aria-checked="true"
            aria-labelledby="label-toggle-mentions"
          >
            <span class="toggle-thumb"></span>
          </button>
        </div>
      </div>

      <!-- Ticket Configuration Tab -->
      <div class="tab-panel" id="tab-ticket-config">
        <h2 class="tab-title">Ticket Configuration</h2>

        <div
          class="settings-message"
          id="ticketConfigMessage"
          role="status"
          aria-live="polite"
          hidden
        ></div>

        <div class="config-section">
          <div class="config-section-header">
            <div>
              <h3 class="config-section-title">Custom Status Options</h3>

              <p class="config-section-description">
                Configure the statuses available when creating or editing tickets.
              </p>
            </div>

            <button
              type="button"
              class="btn-add-link"
              id="addStatusBtn"
            >
              + Add Status
            </button>
          </div>

          <ul class="config-list" id="statusList"></ul>

          <div class="add-row" id="addStatusRow" hidden>
            <input
              type="text"
              id="newStatusInput"
              maxlength="100"
              placeholder="Status name..."
            />

            <input
              type="number"
              id="newStatusSortOrder"
              min="0"
              value="0"
              placeholder="Sort order"
            />

            <button
              type="button"
              class="btn-add-confirm"
              id="confirmAddStatusBtn"
            >
              Add
            </button>

            <button
              type="button"
              class="btn-add-cancel"
              id="cancelAddStatusBtn"
            >
              Cancel
            </button>
          </div>
        </div>

        <div class="config-section">
          <div class="config-section-header">
            <div>
              <h3 class="config-section-title">Custom Fields</h3>

              <p class="config-section-description">
                Add custom data fields to SweetDesk tickets.
              </p>
            </div>

            <button
              type="button"
              class="btn-add-link"
              id="addFieldBtn"
            >
              + Add Custom Field
            </button>
          </div>

          <ul class="config-list" id="fieldList"></ul>

          <div class="add-row add-field-row" id="addFieldRow" hidden>
            <input
              type="text"
              id="newFieldInput"
              maxlength="150"
              placeholder="Field name..."
            />

            <select id="newFieldType">
              <option value="text">Text</option>
              <option value="textarea">Long Text</option>
              <option value="number">Number</option>
              <option value="email">Email</option>
              <option value="url">URL</option>
              <option value="date">Date</option>
              <option value="datetime">Date and Time</option>
              <option value="checkbox">Checkbox</option>
              <option value="select">Select</option>
            </select>

            <label class="inline-checkbox">
              <input type="checkbox" id="newFieldRequired" />
              Required
            </label>

            <input
              type="number"
              id="newFieldSortOrder"
              min="0"
              value="0"
              placeholder="Sort order"
            />

            <div id="newFieldOptionsGroup" hidden>
              <input
                type="text"
                id="newFieldOptions"
                placeholder="Options separated by commas"
              />
            </div>

            <button
              type="button"
              class="btn-add-confirm"
              id="confirmAddFieldBtn"
            >
              Add
            </button>

            <button
              type="button"
              class="btn-add-cancel"
              id="cancelAddFieldBtn"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>

      <!-- Integrations Tab -->
      <div class="tab-panel" id="tab-integrations">
        <h2 class="tab-title">Integrations</h2>
        <p class="coming-soon">Coming soon.</p>
      </div>

      <!-- Licenses Tab -->
      <div class="tab-panel" id="tab-licenses">
        <h2 class="tab-title">Licenses</h2>
        <p class="coming-soon">Coming soon.</p>
      </div>

      <!-- Profile Tab -->
      <div class="tab-panel" id="tab-profile">
        <p class="coming-soon">Redirecting to WordPress profile page...</p>
      </div>

    </div>
  </div>

  </main>