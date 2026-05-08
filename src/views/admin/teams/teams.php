<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SweetDesk – Teams</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap');

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --sidebar-bg:   #111827;
      --sidebar-text: #9ca3af;
      --sidebar-active-bg: #2563eb;
      --sidebar-active-text: #ffffff;
      --sidebar-hover-bg: #1f2937;

      --page-bg:      #f3f4f6;
      --surface:      #ffffff;
      --border:       #e5e7eb;

      --text-primary:   #111827;
      --text-secondary: #6b7280;
      --text-muted:     #9ca3af;

      --accent:       #2563eb;
      --accent-hover: #1d4ed8;

      --card-blue-bg:   #eff6ff;
      --card-blue-border: #bfdbfe;
      --card-green-bg:  #f0fdf4;
      --card-green-border: #bbf7d0;
      --card-pink-bg:   #fdf4ff;
      --card-pink-border: #f0abfc;
      --card-orange-bg: #fff7ed;
      --card-orange-border: #fed7aa;

      --btn-outline-border: #d1d5db;
      --btn-outline-text:   #374151;
    }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--page-bg);
      color: var(--text-primary);
      display: flex;
      min-height: 100vh;
    }

    /* ── Sidebar ── */
    .sidebar {
      width: 215px;
      min-height: 100vh;
      background: var(--sidebar-bg);
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
      position: fixed;
      top: 0; left: 0; bottom: 0;
    }

    .sidebar-logo {
      padding: 22px 20px 18px;
      font-size: 1.05rem;
      font-weight: 700;
      color: #ffffff;
      letter-spacing: 0.01em;
      border-bottom: 1px solid #1f2937;
    }

    .sidebar-nav {
      list-style: none;
      padding: 10px 0;
      flex: 1;
    }

    .sidebar-nav li a {
      display: flex;
      align-items: center;
      gap: 11px;
      padding: 9px 20px;
      text-decoration: none;
      font-size: 0.875rem;
      font-weight: 500;
      color: var(--sidebar-text);
      border-radius: 0;
      transition: background 0.15s, color 0.15s;
    }

    .sidebar-nav li a:hover {
      background: var(--sidebar-hover-bg);
      color: #e5e7eb;
    }

    .sidebar-nav li.active a {
      background: var(--sidebar-active-bg);
      color: var(--sidebar-active-text);
    }

    .sidebar-nav li a svg {
      width: 16px; height: 16px;
      flex-shrink: 0;
      opacity: 0.85;
    }

    /* ── Main content ── */
    .main {
      margin-left: 215px;
      flex: 1;
      padding: 36px 36px 48px;
      min-height: 100vh;
    }

    .page-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 28px;
    }

    .page-title {
      font-size: 1.6rem;
      font-weight: 700;
      color: var(--text-primary);
    }

    .btn-primary {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      background: var(--accent);
      color: #fff;
      border: none;
      border-radius: 7px;
      padding: 9px 16px;
      font-family: inherit;
      font-size: 0.875rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.15s;
    }
    .btn-primary:hover { background: var(--accent-hover); }
    .btn-primary svg { width: 15px; height: 15px; }

    /* ── Team cards grid ── */
    .teams-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
    }

    .team-card {
      border-radius: 12px;
      border: 1.5px solid var(--border);
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }

    .team-card.blue   { background: var(--card-blue-bg);   border-color: var(--card-blue-border); }
    .team-card.green  { background: var(--card-green-bg);  border-color: var(--card-green-border); }
    .team-card.pink   { background: var(--card-pink-bg);   border-color: var(--card-pink-border); }
    .team-card.orange { background: var(--card-orange-bg); border-color: var(--card-orange-border); }

    .team-card-header {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      padding: 18px 18px 14px;
    }

    .team-card-header svg {
      width: 22px; height: 22px;
      color: var(--text-secondary);
      flex-shrink: 0;
      margin-top: 2px;
    }

    .team-card-header-text h3 {
      font-size: 0.975rem;
      font-weight: 700;
      color: var(--text-primary);
    }

    .team-card-header-text p {
      font-size: 0.8rem;
      color: var(--text-secondary);
      margin-top: 2px;
    }

    /* Members inner box */
    .team-members-box {
      margin: 0 14px 14px;
      background: var(--surface);
      border-radius: 8px;
      border: 1px solid var(--border);
      padding: 12px 14px;
    }

    .team-members-heading {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }

    .team-members-heading span:first-child {
      font-size: 0.78rem;
      font-weight: 600;
      color: var(--text-secondary);
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .team-members-heading .member-count {
      font-size: 0.78rem;
      color: var(--text-muted);
    }

    .member-list {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .member-list li {
      font-size: 0.85rem;
      color: var(--text-primary);
      font-weight: 500;
    }

    /* Card action buttons */
    .team-card-actions {
      display: grid;
      grid-template-columns: 1fr 1fr;
      border-top: 1px solid var(--border);
      margin-top: auto;
    }

    .team-card-actions button {
      background: var(--surface);
      border: none;
      padding: 11px 0;
      font-family: inherit;
      font-size: 0.84rem;
      font-weight: 500;
      cursor: pointer;
      transition: background 0.13s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      color: var(--text-primary);
    }

    .team-card-actions button:first-child {
      border-right: 1px solid var(--border);
    }

    .team-card-actions button:hover { background: #f9fafb; }

    .team-card-actions button.email-btn {
      color: var(--accent);
    }

    .team-card-actions button svg {
      width: 14px; height: 14px;
    }

    /* ── Edit Team Modal ── */
    .modal-overlay {
      display: none; /* JS will toggle to flex */
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.45);
      z-index: 100;
      align-items: center;
      justify-content: center;
    }

    .modal {
      background: var(--surface);
      border-radius: 12px;
      width: 480px;
      max-width: 95vw;
      padding: 28px 28px 24px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.18);
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .modal-header h2 {
      font-size: 1.05rem;
      font-weight: 700;
    }

    .modal-close {
      background: none;
      border: none;
      cursor: pointer;
      color: var(--text-muted);
      padding: 4px;
      border-radius: 4px;
      display: flex;
      align-items: center;
    }
    .modal-close:hover { color: var(--text-primary); }
    .modal-close svg { width: 18px; height: 18px; }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 5px;
      margin-bottom: 14px;
    }

    .form-group label {
      font-size: 0.8rem;
      font-weight: 600;
      color: var(--text-secondary);
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
      font-family: inherit;
      font-size: 0.875rem;
      border: 1.5px solid var(--border);
      border-radius: 7px;
      padding: 8px 11px;
      color: var(--text-primary);
      background: #fff;
      outline: none;
      transition: border-color 0.15s;
    }
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
      border-color: var(--accent);
    }
    .form-group textarea { resize: vertical; min-height: 70px; }

    .modal-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 20px;
    }

    .btn-secondary {
      background: none;
      border: 1.5px solid var(--btn-outline-border);
      color: var(--btn-outline-text);
      border-radius: 7px;
      padding: 8px 16px;
      font-family: inherit;
      font-size: 0.875rem;
      font-weight: 500;
      cursor: pointer;
    }
    .btn-secondary:hover { background: #f9fafb; }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-logo">SweetDesk</div>
    <ul class="sidebar-nav">
      <li>
        <a href="">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
          Dashboard
        </a>
      </li>
      <li>
        <a href="">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          Tickets
        </a>
      </li>
      <li>
        <a href="">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          People
        </a>
      </li>
      <li>
        <a href="">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
          Clients
        </a>
      </li>
      <li class="active">
        <a href="">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          Teams
        </a>
      </li>
      <li>
        <a href="">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
          Reports
        </a>
      </li>
      <li>
        <a href="">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
          Settings
        </a>
      </li>
    </ul>
  </aside>

  <!-- Main -->
  <main class="main">
    <div class="page-header">
      <h1 class="page-title">Teams</h1>
      <button class="btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Create Team
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

  <script>
    function openEditTeamModal(name, desc) {
      document.getElementById('edit-team-name').value = name;
      document.getElementById('edit-team-desc').value = desc;
      document.getElementById('editTeamModal').style.display = 'flex';
    }
    function closeEditTeamModal() {
      document.getElementById('editTeamModal').style.display = 'none';
    }
    // Close on overlay click
    document.getElementById('editTeamModal').addEventListener('click', function(e) {
      if (e.target === this) closeEditTeamModal();
    });
  </script>

</body>
</html>
