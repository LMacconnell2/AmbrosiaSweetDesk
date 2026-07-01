// ── Constants ──────────────────────────────────────────────────────────────

// Role of the current user: 'editor' or 'admin'
// In production this would be set via wp_localize_script from PHP
const SD_CURRENT_ROLE = 'admin'; // 'admin' | 'editor'

// Current user's ID (used as default for admin's specific-user dropdown)
const SD_CURRENT_USER_ID = 'self';

// ── Static sample messages ─────────────────────────────────────────────────
// Each message has: author, source ('employee'|'customer'), ticketName,
// ticketUrl, body, timestamp (ms since epoch)

const SD_SAMPLE_MESSAGES = [
    {
        author: 'Bob Johnson',
        source: 'customer',
        ticketName: 'Login page throws 500 error on submit',
        ticketUrl: '#',
        body: 'Just wanted to say the fix worked perfectly — we haven\'t seen the error since Tuesday.',
        timestamp: Date.now() - 1000 * 60 * 30,
    },
    {
        author: 'Jane Smith',
        source: 'employee',
        ticketName: 'PDF export produces blank file',
        ticketUrl: '#',
        body: 'I\'ve left a note on the ticket — the issue seems to be related to the wkhtmltopdf version on staging.',
        timestamp: Date.now() - 1000 * 60 * 60 * 2,
    },
    {
        author: 'Mike Chen',
        source: 'customer',
        ticketName: 'Dashboard widgets not loading for Safari users',
        ticketUrl: '#',
        body: 'Still seeing the issue on Safari 17.4. Let me know if you need a screen recording.',
        timestamp: Date.now() - 1000 * 60 * 60 * 5,
    },
    {
        author: 'Alice Williams',
        source: 'employee',
        ticketName: 'Newly-created users can\'t access settings',
        ticketUrl: '#',
        body: 'I traced it back to the role assignment hook — adding my findings to the ticket now.',
        timestamp: Date.now() - 1000 * 60 * 60 * 9,
    },
    {
        author: 'Sarah Davis',
        source: 'customer',
        ticketName: 'Billing page crash on checkout',
        ticketUrl: '#',
        body: 'We processed a test order this morning and it went through without issues. Thanks for the quick turnaround.',
        timestamp: Date.now() - 1000 * 60 * 60 * 14,
    },
    {
        author: 'John Doe',
        source: 'employee',
        ticketName: 'PDF export produces blank file',
        ticketUrl: '#',
        body: 'Confirmed on my end as well. Flagging for the next sprint.',
        timestamp: Date.now() - 1000 * 60 * 60 * 20,
    },
    {
        author: 'Bob Johnson',
        source: 'customer',
        ticketName: 'Billing page crash on checkout',
        ticketUrl: '#',
        body: 'Appreciate the update, but our finance team is still reporting the occasional 502.',
        timestamp: Date.now() - 1000 * 60 * 60 * 26,
    },
    {
        author: 'Jane Smith',
        source: 'employee',
        ticketName: 'Dashboard widgets not loading for Safari users',
        ticketUrl: '#',
        body: 'Pushed a polyfill fix to staging — could you check again when you get a chance?',
        timestamp: Date.now() - 1000 * 60 * 60 * 32,
    },
    {
        author: 'Mike Chen',
        source: 'customer',
        ticketName: 'Login page throws 500 error on submit',
        ticketUrl: '#',
        body: 'All good here. Closing from our side.',
        timestamp: Date.now() - 1000 * 60 * 60 * 40,
    },
    {
        author: 'Alice Williams',
        source: 'employee',
        ticketName: 'Newly-created users can\'t access settings',
        ticketUrl: '#',
        body: 'Deployed the fix. Marking as resolved pending customer confirmation.',
        timestamp: Date.now() - 1000 * 60 * 60 * 50,
    },
];

// ── Helpers ────────────────────────────────────────────────────────────────

function sdFormatRelativeTime(timestamp) {
    const diff = Date.now() - timestamp;
    const minutes = Math.floor(diff / 60000);
    const hours   = Math.floor(diff / 3600000);
    const days    = Math.floor(diff / 86400000);
    if (minutes < 60)  return minutes + 'm ago';
    if (hours   < 24)  return hours   + 'h ago';
    return days + 'd ago';
}

// ── Date range defaults ────────────────────────────────────────────────────

function sdInitDateRange() {
    const today    = new Date();
    const oneWeek  = new Date(today);
    oneWeek.setDate(today.getDate() - 7);

    const fmt = d => d.toISOString().split('T')[0];

    document.getElementById('sdDateStart').value = fmt(oneWeek);
    document.getElementById('sdDateEnd').value   = fmt(today);
}

// ── Admin view mode ────────────────────────────────────────────────────────

function setViewMode(mode) {
    // Toggle button states
    document.getElementById('btnCompanyWide').classList.toggle('active', mode === 'company');
    document.getElementById('btnSpecificUser').classList.toggle('active', mode === 'user');

    // Show/hide user selector
    const selector = document.getElementById('sdUserSelector');
    selector.style.display = mode === 'user' ? 'flex' : 'none';

    // Show/hide the appropriate sub-views in each section
    document.querySelectorAll('.sd-view--editor').forEach(el => {
        el.style.display = mode === 'user' ? '' : 'none';
    });
    document.querySelectorAll('.sd-view--company').forEach(el => {
        el.style.display = mode === 'company' ? '' : 'none';
    });

    // Recent Messages only shows in user/specific-user mode
    const recentMessages = document.getElementById('sdRecentMessages');
    if (recentMessages) {
        recentMessages.style.display = mode === 'company' ? 'none' : '';
    }
}

// ── Recent Messages ────────────────────────────────────────────────────────

function renderMessages() {
    const count    = parseInt(document.getElementById('sdMessageCount').value, 10);
    const filter   = document.getElementById('sdMessageFilter').value;
    const list     = document.getElementById('sdMessageList');

    // Filter by source
    let messages = SD_SAMPLE_MESSAGES;
    if (filter === 'employee') messages = messages.filter(m => m.source === 'employee');
    if (filter === 'customer') messages = messages.filter(m => m.source === 'customer');

    // Already sorted by recency (most recent first in array)
    messages = messages.slice(0, count);

    if (messages.length === 0) {
        list.innerHTML = '<p class="sd-empty-state">No messages to display.</p>';
        return;
    }

    list.innerHTML = messages.map(m => `
        <a href="${m.ticketUrl}" class="sd-message-item">
            <div class="sd-message-meta">
                <span>
                    <span class="sd-message-author">${m.author}</span>
                    <span class="sd-message-source-badge sd-message-source-badge--${m.source}">
                        ${m.source === 'employee' ? 'Employee' : 'Customer'}
                    </span>
                </span>
                <span>${sdFormatRelativeTime(m.timestamp)}</span>
            </div>
            <span class="sd-message-ticket">${m.ticketName}</span>
            <span class="sd-message-body">${m.body}</span>
        </a>
    `).join('');
}

// ── Role-based page setup ──────────────────────────────────────────────────

function sdInit() {
    sdInitDateRange();

    if (SD_CURRENT_ROLE === 'editor') {
        // Hide admin-only controls
        const toggle   = document.getElementById('sdViewToggle');
        const selector = document.getElementById('sdUserSelector');
        if (toggle)   toggle.style.display   = 'none';
        if (selector) selector.style.display = 'none';

        // Make sure editor views are visible, company views hidden
        document.querySelectorAll('.sd-view--editor').forEach(el => el.style.display = '');
        document.querySelectorAll('.sd-view--company').forEach(el => el.style.display = 'none');
    } else {
        // Admin: default to company-wide
        setViewMode('company');
    }

    renderMessages();
}

document.addEventListener('DOMContentLoaded', sdInit);