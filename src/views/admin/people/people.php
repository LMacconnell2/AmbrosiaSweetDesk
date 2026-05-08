<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SweetDesk – People</title>
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

      --section-personnel-border: #93c5fd;
      --section-contacts-border:  #c4b5fd;

      --btn-outline-border: #d1d5db;
      --btn-outline-text:   #374151;

      --panel-width: 300px;
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
    .sidebar-nav { list-style: none; padding: 10px 0; flex: 1; }
    .sidebar-nav li a {
      display: flex;
      align-items: center;
      gap: 11px;
      padding: 9px 20px;
      text-decoration: none;
      font-size: 0.875rem;
      font-weight: 500;
      color: var(--sidebar-text);
      transition: background 0.15s, color 0.15s;
    }
    .sidebar-nav li a:hover { background: var(--sidebar-hover-bg); color: #e5e7eb; }
    .sidebar-nav li.active a { background: var(--sidebar-active-bg); color: var(--sidebar-active-text); }
    .sidebar-nav li a svg { width: 16px; height: 16px; flex-shrink: 0; opacity: 0.85; }

    /* ── Layout ── */
    .layout {
      margin-left: 215px;
      flex: 1;
      display: flex;
      min-height: 100vh;
      position: relative;
    }

    .main {
      flex: 1;
      padding: 36px 36px 48px;
      min-width: 0;
      transition: margin-right 0.3s ease;
    }
    .main.panel-open { margin-right: var(--panel-width); }

    /* ── Page header ── */
    .page-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 28px;
    }
    .page-title { font-size: 1.6rem; font-weight: 700; }
    .header-actions { display: flex; gap: 10px; }

    .btn-outline {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      background: var(--surface);
      color: var(--btn-outline-text);
      border: 1.5px solid var(--btn-outline-border);
      border-radius: 7px;
      padding: 8px 14px;
      font-family: inherit;
      font-size: 0.84rem;
      font-weight: 500;
      cursor: pointer;
      transition: background 0.15s;
    }
    .btn-outline:hover { background: #f9fafb; }
    .btn-outline svg { width: 14px; height: 14px; }

    /* ── Section blocks ── */
    .section {
      background: var(--surface);
      border-radius: 10px;
      border: 1px solid var(--border);
      margin-bottom: 24px;
      overflow: hidden;
    }

    .section-header {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 16px 20px 14px;
      border-bottom: 1px solid var(--border);
    }

    .section-accent-bar {
      width: 4px;
      height: 22px;
      border-radius: 2px;
      flex-shrink: 0;
    }
    .section-accent-bar.blue   { background: var(--section-personnel-border); }
    .section-accent-bar.purple { background: var(--section-contacts-border); }

    .section-title {
      font-size: 1rem;
      font-weight: 700;
      color: var(--text-primary);
    }

    /* ── Toolbar ── */
    .toolbar {
      display: flex;
      gap: 12px;
      padding: 14px 16px 12px;
      align-items: center;
      border-bottom: 1px solid var(--border);
    }
    .search-wrap {
      position: relative;
      flex: 1;
    }
    .search-wrap svg {
      position: absolute;
      left: 11px;
      top: 50%;
      transform: translateY(-50%);
      width: 15px; height: 15px;
      color: var(--text-muted);
      pointer-events: none;
    }
    .search-wrap input {
      width: 100%;
      padding: 8px 12px 8px 34px;
      border: 1.5px solid var(--border);
      border-radius: 7px;
      font-family: inherit;
      font-size: 0.875rem;
      color: var(--text-primary);
      background: var(--page-bg);
      outline: none;
      transition: border-color 0.15s;
    }
    .search-wrap input:focus { border-color: var(--accent); background: #fff; }

    .filter-wrap {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.84rem;
      color: var(--text-secondary);
    }
    .filter-wrap svg { width: 14px; height: 14px; }
    .filter-wrap select {
      font-family: inherit;
      font-size: 0.84rem;
      border: 1.5px solid var(--border);
      border-radius: 7px;
      padding: 6px 10px;
      background: var(--page-bg);
      color: var(--text-primary);
      outline: none;
      cursor: pointer;
    }

    /* ── Table ── */
    table {
      width: 100%;
      border-collapse: collapse;
    }
    thead tr { border-bottom: 1.5px solid var(--border); }
    th {
      padding: 10px 16px;
      font-size: 0.78rem;
      font-weight: 600;
      color: var(--text-secondary);
      text-align: left;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      white-space: nowrap;
    }
    th.col-check, td.col-check { width: 36px; padding-left: 16px; padding-right: 4px; }
    th .sort-icon { display: inline; margin-left: 3px; opacity: 0.5; font-size: 0.7rem; }

    tbody tr { border-bottom: 1px solid var(--border); transition: background 0.1s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #f9fafb; }

    td {
      padding: 12px 16px;
      font-size: 0.875rem;
      color: var(--text-primary);
    }
    td.col-name { font-weight: 600; }

    a.email-link {
      color: var(--accent);
      text-decoration: none;
      font-size: 0.875rem;
    }
    a.email-link:hover { text-decoration: underline; }

    .row-actions { display: flex; gap: 8px; align-items: center; }
    .row-actions button {
      background: none;
      border: none;
      cursor: pointer;
      padding: 3px;
      border-radius: 4px;
      display: flex;
      align-items: center;
      transition: background 0.1s;
    }
    .row-actions button:hover { background: #f3f4f6; }
    .row-actions button svg { width: 15px; height: 15px; }
    .btn-edit svg   { color: var(--accent); }
    .btn-delete svg { color: #ef4444; }

    /* ── Side panel ── */
    .side-panel {
      width: var(--panel-width);
      background: var(--surface);
      border-left: 1px solid var(--border);
      position: fixed;
      top: 0; right: 0; bottom: 0;
      display: flex;
      flex-direction: column;
      transition: transform 0.3s ease;
      z-index: 10;
    }
    .side-panel.collapsed { transform: translateX(var(--panel-width)); }

    .panel-toggle {
      position: absolute;
      left: -28px;
      top: 50%;
      transform: translateY(-50%);
      width: 28px;
      height: 52px;
      background: var(--surface);
      border: 1px solid var(--border);
      border-right: none;
      border-radius: 8px 0 0 8px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: -2px 0 6px rgba(0,0,0,0.05);
    }
    .panel-toggle svg {
      width: 16px; height: 16px;
      color: var(--text-secondary);
      transition: transform 0.3s ease;
    }
    .side-panel.collapsed .panel-toggle svg { transform: rotate(180deg); }

    .side-panel-inner {
      padding: 24px 20px;
      flex: 1;
      overflow-y: auto;
    }

    .side-panel-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 18px;
    }
    .side-panel-header h2 { font-size: 1rem; font-weight: 700; }

    /* Type toggle */
    .type-toggle {
      display: flex;
      border: 1.5px solid var(--border);
      border-radius: 7px;
      overflow: hidden;
      margin-bottom: 16px;
    }
    .type-toggle button {
      flex: 1;
      padding: 7px 0;
      font-family: inherit;
      font-size: 0.84rem;
      font-weight: 600;
      border: none;
      cursor: pointer;
      background: none;
      color: var(--text-secondary);
      transition: background 0.15s, color 0.15s;
    }
    .type-toggle button.active {
      background: var(--accent);
      color: #fff;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 5px;
      margin-bottom: 13px;
    }
    .form-group label { font-size: 0.78rem; font-weight: 600; color: var(--text-secondary); }
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
    .form-group select:focus { border-color: var(--accent); }
    .form-group textarea { resize: vertical; min-height: 70px; }

    .btn-primary {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 7px;
      background: var(--accent);
      color: #fff;
      border: none;
      border-radius: 7px;
      padding: 10px 16px;
      width: 100%;
      font-family: inherit;
      font-size: 0.875rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.15s;
      margin-top: 4px;
    }
    .btn-primary:hover { background: var(--accent-hover); }

    /* ── Modals ── */
    .modal-overlay {
      display: none;
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
      width: 460px;
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
    .modal-header h2 { font-size: 1.05rem; font-weight: 700; }
    .modal-close {
      background: none; border: none; cursor: pointer;
      color: var(--text-muted); padding: 4px; border-radius: 4px;
      display: flex; align-items: center;
    }
    .modal-close:hover { color: var(--text-primary); }
    .modal-close svg { width: 18px; height: 18px; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
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
    .modal .btn-primary { width: auto; margin-top: 0; }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-logo">SweetDesk</div>
    <ul class="sidebar-nav">
      <li><a href="">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        Dashboard
      </a></li>
      <li><a href="">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Tickets
      </a></li>
      <li class="active"><a href="">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        People
      </a></li>
      <li><a href="">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        Clients
      </a></li>
      <li><a href="">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Teams
      </a></li>
      <li><a href="">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        Reports
      </a></li>
      <li><a href="">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
        Settings
      </a></li>
    </ul>
  </aside>

  <div class="layout">
    <main class="main panel-open" id="mainContent">
      <div class="page-header">
        <h1 class="page-title">People</h1>
        <div class="header-actions">
          <button class="btn-outline">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Import CSV
          </button>
          <button class="btn-outline">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
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
                  <button class="btn-edit" onclick="openEditPersonModal('John Doe','Developer','john@example.com','555-0101','','')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                  <button class="btn-delete"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
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
                  <button class="btn-edit" onclick="openEditPersonModal('Jane Smith','Designer','jane@example.com','555-0102','','')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                  <button class="btn-delete"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
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
                  <button class="btn-edit" onclick="openEditPersonModal('Alice Williams','Developer','alice@example.com','555-0104','','')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                  <button class="btn-delete"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
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
                  <button class="btn-edit" onclick="openEditPersonModal('Bob Johnson','Manager','bob@example.com','555-0103','Acme Corp','')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                  <button class="btn-delete"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
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
                  <button class="btn-edit" onclick="openEditPersonModal('Mike Chen','CTO','mike@techstart.com','555-0105','TechStart Inc','')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                  <button class="btn-delete"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
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
                  <button class="btn-edit" onclick="openEditPersonModal('Sarah Davis','Product Owner','sarah@globex.com','555-0106','Globex Solutions','')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                  <button class="btn-delete"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>

    <!-- Add New Person Panel -->
    <aside class="side-panel" id="personPanel">
      <button class="panel-toggle" id="panelToggle" onclick="togglePanel()">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
      </button>
      <div class="side-panel-inner">
        <div class="side-panel-header">
          <h2>Add New Person</h2>
        </div>
        <div class="form-group">
          <label>Type</label>
          <div class="type-toggle">
            <button class="active" id="typeInternal" onclick="setType('internal')">Internal</button>
            <button id="typeClient" onclick="setType('client')">Client</button>
          </div>
        </div>
        <div class="form-group">
          <label for="new-person-name">Name</label>
          <input type="text" id="new-person-name" placeholder="Full name" />
        </div>
        <div class="form-group">
          <label for="new-person-email">Email</label>
          <input type="email" id="new-person-email" placeholder="email@example.com" />
        </div>
        <div class="form-group" id="companyField">
          <label for="new-person-company">Company</label>
          <input type="text" id="new-person-company" placeholder="Company name" />
        </div>
        <div class="form-group">
          <label for="new-person-phone">Phone Number</label>
          <input type="tel" id="new-person-phone" placeholder="555-0000" />
        </div>
        <div class="form-group">
          <label for="new-person-notes">Notes</label>
          <textarea id="new-person-notes" placeholder="Additional notes..."></textarea>
        </div>
        <button class="btn-primary">Add Person</button>
      </div>
    </aside>
  </div>

  <!-- Edit Person Modal -->
  <div class="modal-overlay" id="editPersonModal">
    <div class="modal">
      <div class="modal-header">
        <h2>Edit Person</h2>
        <button class="modal-close" onclick="closeEditPersonModal()">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <div class="form-group">
        <label for="edit-person-name">Name</label>
        <input type="text" id="edit-person-name" />
      </div>
      <div class="form-group">
        <label for="edit-person-role">Role</label>
        <input type="text" id="edit-person-role" />
      </div>
      <div class="form-group">
        <label for="edit-person-email">Email</label>
        <input type="email" id="edit-person-email" />
      </div>
      <div class="form-group">
        <label for="edit-person-phone">Phone</label>
        <input type="tel" id="edit-person-phone" />
      </div>
      <div class="form-group">
        <label for="edit-person-company">Company</label>
        <input type="text" id="edit-person-company" placeholder="Leave blank for internal staff" />
      </div>
      <div class="form-group">
        <label for="edit-person-notes">Notes</label>
        <textarea id="edit-person-notes"></textarea>
      </div>
      <div class="modal-actions">
        <button class="btn-secondary" onclick="closeEditPersonModal()">Cancel</button>
        <button class="btn-primary">Save Changes</button>
      </div>
    </div>
  </div>

  <script>
    function togglePanel() {
      const panel = document.getElementById('personPanel');
      const main  = document.getElementById('mainContent');
      panel.classList.toggle('collapsed');
      main.classList.toggle('panel-open');
    }

    function setType(type) {
      document.getElementById('typeInternal').classList.toggle('active', type === 'internal');
      document.getElementById('typeClient').classList.toggle('active', type === 'client');
    }

    function openEditPersonModal(name, role, email, phone, company, notes) {
      document.getElementById('edit-person-name').value    = name;
      document.getElementById('edit-person-role').value    = role;
      document.getElementById('edit-person-email').value   = email;
      document.getElementById('edit-person-phone').value   = phone;
      document.getElementById('edit-person-company').value = company;
      document.getElementById('edit-person-notes').value   = notes;
      document.getElementById('editPersonModal').style.display = 'flex';
    }
    function closeEditPersonModal() {
      document.getElementById('editPersonModal').style.display = 'none';
    }
    document.getElementById('editPersonModal').addEventListener('click', function(e) {
      if (e.target === this) closeEditPersonModal();
    });
  </script>

</body>
</html>
