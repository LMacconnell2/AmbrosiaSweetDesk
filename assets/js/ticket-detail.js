/**
 * Ticket Detail Page - Message Threading
 * Handles loading, rendering, and managing messages in a hierarchical/threaded view
 */

let activeReplyTo = null;

document.addEventListener('DOMContentLoaded', async () => {
    const ticketId = getTicketIdFromPage();

    if (!ticketId) {
        showNoTicketSelected();
        return;
    }

    const loaded = await loadTicketHeader(ticketId);

    if (!loaded) {
        return;
    }

    await loadAndRenderMessages(ticketId);
    initReplyEditor();
    setupReplySubmission(ticketId);
    setupReplyContextControls();
    updateReplyContextUI();
});

function initReplyEditor() {
    SweetDeskEditor.init('reply-body', {
        onCtrlEnter() {
            document.getElementById('send-reply-btn')?.click();
        }
    });
}

async function apiFetch(endpoint, options = {}) {
    const response = await fetch(
        `${SweetDesk.apiUrl}${endpoint}`,
        {
            ...options,
            headers: {
                'X-WP-Nonce': SweetDesk.nonce,
                'Content-Type': 'application/json',
                ...(options.headers || {})
            }
        }
    );

    return response;
}

function showNoTicketSelected() {
    const emptyState = document.getElementById('ticket-detail-empty');
    const content = document.getElementById('ticket-detail-content');

    if (emptyState) {
        emptyState.hidden = false;
    }

    if (content) {
        content.hidden = true;
    }

    const title = document.getElementById('ticket-title');

    if (title) {
        title.textContent = 'Ticket Detail';
    }
}

function getTicketIdFromPage() {
    const params = new URLSearchParams(window.location.search);
    const idFromUrl = params.get('ticket_id') || params.get('id');

    if (idFromUrl) {
        return idFromUrl;
    }

    const container = document.querySelector('.sweetdesk-ticket-detail');

    if (container?.dataset.ticketId) {
        return container.dataset.ticketId;
    }

    const titleElement = document.getElementById('ticket-title');

    if (titleElement) {
        const match = titleElement.textContent.match(/#(\d+)/);

        if (match) {
            return match[1];
        }
    }

    return null;
}

async function loadTicketHeader(ticketId) {
    const content = document.getElementById('ticket-detail-content');

    try {
        const response = await apiFetch(`/tickets/${ticketId}`);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (!data.ticket) {
            throw new Error('Ticket not found');
        }

        const ticket = data.ticket;

        document.getElementById('ticket-title').textContent =
            `Ticket #${ticket.id} - ${ticket.title}`;

        document.getElementById('ticket-status').innerHTML =
            renderBadge('status', ticket.status);

        document.getElementById('ticket-priority').innerHTML =
            renderBadge('priority', ticket.priority);

        document.getElementById('ticket-assignee').textContent =
            ticket.assigned_to || 'Unassigned';

        if (content) {
            content.hidden = false;
        }

        const emptyState = document.getElementById('ticket-detail-empty');

        if (emptyState) {
            emptyState.hidden = true;
        }

        return true;
    } catch (error) {
        console.error('Error loading ticket:', error);

        if (content) {
            content.hidden = true;
        }

        const emptyState = document.getElementById('ticket-detail-empty');

        if (emptyState) {
            emptyState.innerHTML =
                `<p>Ticket #${ticketId} could not be loaded. It may not exist yet. <a href="${SweetDesk.ticketsUrl || 'admin.php?page=sweetdesk'}">Back to Tickets</a></p>`;
            emptyState.hidden = false;
        }

        return false;
    }
}

async function fetchMessages(ticketId) {
    try {
        const response = await apiFetch(`/tickets/${ticketId}/messages`);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        return data.messages || [];
    } catch (error) {
        console.error('Error fetching messages:', error);
        return [];
    }
}

async function loadAndRenderMessages(ticketId) {
    const container = document.getElementById('sweetdesk-messages-container');

    container.innerHTML =
        '<div class="sweetdesk-messages-loading"><p>Loading messages...</p></div>';

    const messages = await fetchMessages(ticketId);

    if (messages.length === 0) {
        container.innerHTML =
            '<div class="sweetdesk-messages-empty"><p>No messages yet. Be the first to reply!</p></div>';
        return;
    }

    const canSeeInternal = canUserSeeInternalMessages();
    const visibleMessages = canSeeInternal
        ? messages
        : messages.filter(m => m.visibility !== 'internal');

    const threadedMessages = buildMessageThread(visibleMessages);

    container.innerHTML = '';

    const threadContainer = document.createElement('div');
    threadContainer.className = 'sweetdesk-message-thread';

    threadedMessages.forEach(message => {
        threadContainer.appendChild(renderMessage(message, canSeeInternal));
    });

    container.appendChild(threadContainer);
    highlightActiveReplyTarget();
}

function buildMessageThread(messages) {
    const messageMap = {};

    messages.forEach(msg => {
        messageMap[msg.reply_id] = { ...msg, replies: [] };
    });

    const rootMessages = [];

    messages.forEach(msg => {
        if (!msg.reply_to_id) {
            rootMessages.push(messageMap[msg.reply_id]);
        } else if (messageMap[msg.reply_to_id]) {
            messageMap[msg.reply_to_id].replies.push(messageMap[msg.reply_id]);
        }
    });

    const sortByDate = (msgs) => {
        return msgs.sort((a, b) =>
            new Date(a.created_at) - new Date(b.created_at)
        );
    };

    const sortRecursively = (msgs) => {
        const sorted = sortByDate(msgs);

        sorted.forEach(msg => {
            if (msg.replies?.length > 0) {
                msg.replies = sortRecursively(msg.replies);
            }
        });

        return sorted;
    };

    return sortRecursively(rootMessages);
}

function isMessageEdited(message) {
    return Number(message.edited) === 1;
}

function renderMessage(message, canSeeInternal) {
    const messageDiv = document.createElement('div');
    const isInternal = message.visibility === 'internal';

    messageDiv.className =
        'sweetdesk-message' + (isInternal ? ' is-internal' : '');
    messageDiv.dataset.replyId = message.reply_id;

    const headerDiv = document.createElement('div');
    headerDiv.className = 'sweetdesk-message-header';

    const authorSpan = document.createElement('span');
    authorSpan.className = 'sweetdesk-message-author';
    authorSpan.textContent = message.person_id || 'Staff';
    headerDiv.appendChild(authorSpan);

    const dateSpan = document.createElement('span');
    dateSpan.className = 'sweetdesk-message-date';
    dateSpan.textContent = formatDate(message.created_at);
    headerDiv.appendChild(dateSpan);

    const badgeSpan = document.createElement('span');
    badgeSpan.className = `sweetdesk-visibility-badge ${message.visibility}`;
    badgeSpan.textContent = isInternal ? 'Internal' : 'Public';
    headerDiv.appendChild(badgeSpan);

    if (isMessageEdited(message)) {
        const editedSpan = document.createElement('span');
        editedSpan.className = 'sweetdesk-visibility-badge';
        editedSpan.textContent = 'Edited';
        editedSpan.style.marginLeft = '8px';
        editedSpan.style.background = '#fef3c7';
        editedSpan.style.color = '#a16207';
        headerDiv.appendChild(editedSpan);
    }

    messageDiv.appendChild(headerDiv);

    const bodyDiv = document.createElement('div');
    bodyDiv.className = 'sweetdesk-message-body';
    bodyDiv.innerHTML = message.body || '';
    messageDiv.appendChild(bodyDiv);

    if (isMessageEdited(message) && message.updated_at !== message.created_at) {
        const editInfoDiv = document.createElement('div');
        editInfoDiv.className = 'sweetdesk-message-edited';
        editInfoDiv.textContent = `Edited ${formatDate(message.updated_at)}`;
        messageDiv.appendChild(editInfoDiv);
    }

    const actionsDiv = document.createElement('div');
    actionsDiv.className = 'sweetdesk-message-actions';

    const replyBtn = document.createElement('button');
    replyBtn.type = 'button';
    replyBtn.className = 'sweetdesk-message-reply-btn';
    replyBtn.textContent = 'Reply';
    replyBtn.addEventListener('click', () => {
        startReplyTo(message);
    });
    actionsDiv.appendChild(replyBtn);
    messageDiv.appendChild(actionsDiv);

    if (message.replies?.length > 0) {
        const repliesContainer = document.createElement('div');
        repliesContainer.className = 'sweetdesk-message-replies';

        message.replies.forEach(reply => {
            repliesContainer.appendChild(renderMessage(reply, canSeeInternal));
        });

        messageDiv.appendChild(repliesContainer);
    }

    return messageDiv;
}

function truncateText(text, maxLength = 80) {
    const normalized = SweetDeskEditor.stripHtml(text);

    if (normalized.length <= maxLength) {
        return normalized;
    }

    return `${normalized.slice(0, maxLength)}...`;
}

function startReplyTo(message) {
    activeReplyTo = {
        reply_id: message.reply_id,
        preview: truncateText(message.body),
        visibility: message.visibility
    };

    updateReplyContextUI();
    highlightActiveReplyTarget();

    const replySection = document.querySelector('.sweetdesk-reply-section');

    SweetDeskEditor.focus('reply-body');
    replySection?.scrollIntoView({
        behavior: 'smooth',
        block: 'nearest'
    });
}

function clearReplyTarget() {
    activeReplyTo = null;
    updateReplyContextUI();
    highlightActiveReplyTarget();
}

function setInternalReplyLocked(locked) {
    const internalCheckbox = document.getElementById('reply-internal');
    const internalLabel = document.getElementById('reply-internal-label');

    if (!internalCheckbox || !internalLabel) {
        return;
    }

    if (locked) {
        internalCheckbox.checked = true;
        internalCheckbox.disabled = true;
        internalLabel.classList.add('is-locked');
        return;
    }

    internalCheckbox.disabled = false;
    internalCheckbox.checked = false;
    internalLabel.classList.remove('is-locked');
}

function updateReplyContextUI() {
    const context = document.getElementById('reply-context');
    const preview = document.getElementById('reply-context-preview');
    const title = document.getElementById('reply-section-title');

    if (!context || !preview || !title) {
        return;
    }

    if (!activeReplyTo) {
        context.hidden = true;
        preview.textContent = '';
        title.textContent = 'Leave a Reply';
        SweetDeskEditor.setPlaceholder(
            'reply-body',
            'Write your reply here...'
        );
        setInternalReplyLocked(false);

        return;
    }

    context.hidden = false;
    preview.textContent = activeReplyTo.preview;
    title.textContent = 'Reply to Message';
    SweetDeskEditor.setPlaceholder(
        'reply-body',
        'Write your threaded reply...'
    );
    setInternalReplyLocked(activeReplyTo.visibility === 'internal');
}

function highlightActiveReplyTarget() {
    document.querySelectorAll('.sweetdesk-message').forEach(node => {
        node.classList.remove('is-reply-target');
    });

    if (!activeReplyTo) {
        return;
    }

    const target = document.querySelector(
        `.sweetdesk-message[data-reply-id="${activeReplyTo.reply_id}"]`
    );

    target?.classList.add('is-reply-target');
}

function setupReplyContextControls() {
    document
        .getElementById('cancel-reply-btn')
        ?.addEventListener('click', clearReplyTarget);
}

function canUserSeeInternalMessages() {
    return Boolean(SweetDesk.canSeeInternal);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) {
        return 'just now';
    }

    if (diffMins < 60) {
        return `${diffMins}m ago`;
    }

    if (diffHours < 24) {
        return `${diffHours}h ago`;
    }

    if (diffDays < 7) {
        return `${diffDays}d ago`;
    }

    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showSuccessNotice(message) {
    const notices = document.getElementById('ticket-detail-notices');

    if (!notices) {
        return;
    }

    notices.innerHTML = '';

    const successDiv = document.createElement('div');
    successDiv.className = 'notice notice-success';
    successDiv.innerHTML = `<p>${escapeHtml(message)}</p>`;

    notices.appendChild(successDiv);

    setTimeout(() => successDiv.remove(), 3000);
}

function setupReplySubmission(ticketId) {
    const sendBtn = document.getElementById('send-reply-btn');
    const internalCheckbox = document.getElementById('reply-internal');

    if (!sendBtn) {
        return;
    }

    sendBtn.addEventListener('click', async () => {
        const body = SweetDeskEditor.getContent('reply-body');

        if (SweetDeskEditor.isEmpty('reply-body')) {
            alert('Please enter a reply message');
            return;
        }

        const isInternal =
            activeReplyTo?.visibility === 'internal' ||
            internalCheckbox?.checked ||
            false;
        const visibility = isInternal ? 'internal' : 'public';

        sendBtn.disabled = true;
        const originalText = sendBtn.textContent;
        sendBtn.textContent = 'Sending...';

        try {
            const response = await apiFetch(
                `/tickets/${ticketId}/messages`,
                {
                    method: 'POST',
                    body: JSON.stringify({
                        body,
                        visibility,
                        reply_type: visibility,
                        reply_to_id: activeReplyTo?.reply_id || null
                    })
                }
            );

            const result = await response.json();

            if (!response.ok || result.success === false) {
                throw new Error(result.message || 'Failed to post reply');
            }

            SweetDeskEditor.setContent('reply-body', '');

            clearReplyTarget();
            await loadAndRenderMessages(ticketId);

            showSuccessNotice('Reply posted successfully!');
        } catch (error) {
            console.error('Error posting reply:', error);
            alert(error.message || 'Failed to post reply. Please try again.');
        } finally {
            sendBtn.disabled = false;
            sendBtn.textContent = originalText;
        }
    });
}
