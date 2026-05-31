<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SweetDesk - Teams</title>
</head>
<body>

  <!-- Main -->
  <main class="main">
    <div class="page-header">
      <h1 class="page-title">Teams</h1>
      <button class="btn-primary" id="sd-new-team">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Team
      </button>
    </div>

    <div class="teams-grid">

      <!-- Frontend Team -->
      <div class="team-card blue">
        <div class="team-card-header">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          <div class="team-card-header-text">
            <h3>Frontend Team</h3>
            <p>Handles all UI/UX development</p>
          </div>
        </div>
        <div class="team-members-box">
          <div class="team-members-heading">
            <span>Team Members</span>
            <span class="member-count">3 members</span>
          </div>
          <ul class="member-list">
            <li>John Doe</li>
            <li>Jane Smith</li>
            <li>Alice Williams</li>
          </ul>
        </div>
        <div class="team-card-actions">
          <button onclick="openEditTeamModal('Frontend Team', 'Handles all UI/UX development')">Edit Team</button>
          <button class="email-btn">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            Email Team
          </button>
        </div>
      </div>

      <!-- Backend Team -->
      <div class="team-card green">
        <div class="team-card-header">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          <div class="team-card-header-text">
            <h3>Backend Team</h3>
            <p>API and database development</p>
          </div>
        </div>
        <div class="team-members-box">
          <div class="team-members-heading">
            <span>Team Members</span>
            <span class="member-count">2 members</span>
          </div>
          <ul class="member-list">
            <li>John Doe</li>
            <li>Alice Williams</li>
          </ul>
        </div>
        <div class="team-card-actions">
          <button onclick="openEditTeamModal('Backend Team', 'API and database development')">Edit Team</button>
          <button class="email-btn">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            Email Team
          </button>
        </div>
      </div>

      <!-- Client Success -->
      <div class="team-card pink">
        <div class="team-card-header">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          <div class="team-card-header-text">
            <h3>Client Success</h3>
            <p>Direct client communication and support</p>
          </div>
        </div>
        <div class="team-members-box">
          <div class="team-members-heading">
            <span>Team Members</span>
            <span class="member-count">1 member</span>
          </div>
          <ul class="member-list">
            <li>Jane Smith</li>
          </ul>
        </div>
        <div class="team-card-actions">
          <button onclick="openEditTeamModal('Client Success', 'Direct client communication and support')">Edit Team</button>
          <button class="email-btn">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            Email Team
          </button>
        </div>
      </div>

      <!-- DevOps -->
      <div class="team-card orange">
        <div class="team-card-header">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          <div class="team-card-header-text">
            <h3>DevOps</h3>
            <p>Infrastructure and deployment</p>
          </div>
        </div>
        <div class="team-members-box">
          <div class="team-members-heading">
            <span>Team Members</span>
            <span class="member-count">1 member</span>
          </div>
          <ul class="member-list">
            <li>Alice Williams</li>
          </ul>
        </div>
        <div class="team-card-actions">
          <button onclick="openEditTeamModal('DevOps', 'Infrastructure and deployment')">Edit Team</button>
          <button class="email-btn">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            Email Team
          </button>
        </div>
      </div>

    </div>
  </main>

  <!-- Edit Team Modal -->
  <div class="modal-overlay" id="editTeamModal">
    <div class="modal">
      <div class="modal-header">
        <h2>Edit Team</h2>
        <button class="modal-close" onclick="closeEditTeamModal()">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <div class="form-group">
        <label for="edit-team-name">Team Name</label>
        <input type="text" id="edit-team-name" />
      </div>
      <div class="form-group">
        <label for="edit-team-desc">Description</label>
        <input type="text" id="edit-team-desc" />
      </div>
      <div class="form-group">
        <label for="edit-team-members">Members</label>
        <textarea id="edit-team-members" placeholder="One name per line"></textarea>
      </div>
      <div class="modal-actions">
        <button class="btn-secondary" onclick="closeEditTeamModal()">Cancel</button>
        <button class="btn-primary">Save Changes</button>
      </div>
    </div>
  </div>

  <!-- New Team Modal -->
  <div class="modal-overlay" id="newTeamModal">
    <div class="modal">
      <div class="modal-header">
        <h2>New Team</h2>
        <button class="modal-close" id="new-team-close">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <form id="new-team-form">
        <div class="form-group">
          <label for="new-team-name">Team Name</label>
          <input type="text" id="new-team-name" placeholder="Enter team name" />
        </div>
        <div class="form-group">
          <label for="new-team-desc">Team Description</label>
          <input type="text" id="new-team-desc" placeholder="Enter team description" />
        </div>
        <div class="form-group">
          <label for="new-team-member-select">Add Team Members</label>
          <div class="input-row">
            <select id="new-team-member-select">
              <option value="">Select a person</option>
              <option value="John Doe">John Doe</option>
              <option value="Jane Smith">Jane Smith</option>
              <option value="Alice Williams">Alice Williams</option>
              <option value="Bob Johnson">Bob Johnson</option>
              <option value="Mike Chen">Mike Chen</option>
              <option value="Sarah Davis">Sarah Davis</option>
            </select>
            <button type="button" class="btn-secondary" id="add-team-member">Add</button>
          </div>
        </div>
        <div class="form-group">
          <label>Selected Members</label>
          <ul class="member-list" id="new-team-member-list"></ul>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-secondary" id="new-team-cancel">Cancel</button>
          <button type="submit" class="btn-primary">Save Team</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
