/**
 * Ticket Detail Page - Message Threading
 * Handles loading, rendering, and managing messages in a hierarchical/threaded view
 */

document.addEventListener('DOMContentLoaded', async () => {
    // Get ticket ID from URL or data attribute
    const ticketId = getTicketIdFromPage();
    
    if (!ticketId) {
        console.error('Ticket ID not found');
        return;
    }

    // Load and render messages
    await loadAndRenderMessages(ticketId);

    // Setup reply submission
    setupReplySubmission(ticketId);
});

/**
 * Extract ticket ID from the current page
 */
function getTicketIdFromPage() {
    // Try to get from URL parameter
    const params = new URLSearchParams(window.location.search);
    const idFromUrl = params.get('ticket_id') || params.get('id');
    
    if (idFromUrl) {
        return idFromUrl;
    }

    // Try to get from page element
    const titleElement = document.getElementById('ticket-title');
    if (titleElement) {
        const match = titleElement.textContent.match(/#(\d+)/);
        if (match) {
            return match[1];
        }
    }

    // Try to get from data attribute
    const container = document.querySelector('.sweetdesk-ticket-detail');
    if (container && container.dataset.ticketId) {
        return container.dataset.ticketId;
    }

    return null;
}

/**
 * Fetch messages from the API endpoint
 */
async function fetchMessages(ticketId) {
    try {
        const response = await fetch(
            `/wp-json/sweetdesk/v1/tickets/${ticketId}/messages`
        );

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

/**
 * Load and render messages with hierarchical structure
 */
async function loadAndRenderMessages(ticketId) {
    const container = document.getElementById('sweetdesk-messages-container');
    
    // Show loading state
    container.innerHTML = '<div class="sweetdesk-messages-loading"><p>Loading messages...</p></div>';

    // Fetch messages from API
    const messages = await fetchMessages(ticketId);

    if (messages.length === 0) {
        container.innerHTML = '<div class="sweetdesk-messages-empty"><p>No messages yet. Be the first to reply!</p></div>';
        return;
    }

    // Check if user can see internal messages (is admin/editor)
    const canSeeInternal = await canUserSeeInternalMessages();

    // Filter messages if user cannot see internal ones
    const visibleMessages = canSeeInternal 
        ? messages 
        : messages.filter(m => m.visibility !== 'internal');

    // Build hierarchical structure
    const threadedMessages = buildMessageThread(visibleMessages);

    // Render the messages
    container.innerHTML = '';
    const threadContainer = document.createElement('div');
    threadContainer.className = 'sweetdesk-message-thread';
    
    threadedMessages.forEach(message => {
        threadContainer.appendChild(renderMessage(message, canSeeInternal));
    });

    container.appendChild(threadContainer);
}

/**
 * Build hierarchical message structure based on reply_to_id
 */
function buildMessageThread(messages) {
    // Create a map of messages by ID for quick lookup
    const messageMap = {};
    messages.forEach(msg => {
        messageMap[msg.reply_id] = { ...msg, replies: [] };
    });

    // Build the hierarchy
    const rootMessages = [];
    messages.forEach(msg => {
        if (!msg.reply_to_id) {
            // Root message (no parent)
            rootMessages.push(messageMap[msg.reply_id]);
        } else if (messageMap[msg.reply_to_id]) {
            // Add as a reply to parent
            messageMap[msg.reply_to_id].replies.push(messageMap[msg.reply_id]);
        }
    });

    // Sort by creation date (oldest first)
    const sortByDate = (msgs) => {
        return msgs.sort((a, b) => 
            new Date(a.created_at) - new Date(b.created_at)
        );
    };

    // Recursively sort all messages and their replies
    const sortRecursively = (msgs) => {
        const sorted = sortByDate(msgs);
        sorted.forEach(msg => {
            if (msg.replies && msg.replies.length > 0) {
                msg.replies = sortRecursively(msg.replies);
            }
        });
        return sorted;
    };

    return sortRecursively(rootMessages);
}

/**
 * Render a single message and its replies recursively
 */
function renderMessage(message, canSeeInternal) {
    const messageDiv = document.createElement('div');
    const isInternal = message.visibility === 'internal';
    
    messageDiv.className = 'sweetdesk-message' + (isInternal ? ' is-internal' : '');
    
    // Build message header with author and date
    const headerDiv = document.createElement('div');
    headerDiv.className = 'sweetdesk-message-header';

    const authorSpan = document.createElement('span');
    authorSpan.className = 'sweetdesk-message-author';
    authorSpan.textContent = message.person_id || 'Unknown';
    headerDiv.appendChild(authorSpan);

    const dateSpan = document.createElement('span');
    dateSpan.className = 'sweetdesk-message-date';
    dateSpan.textContent = formatDate(message.created_at);
    headerDiv.appendChild(dateSpan);

    // Add visibility badge
    const badgeSpan = document.createElement('span');
    badgeSpan.className = `sweetdesk-visibility-badge ${message.visibility}`;
    badgeSpan.textContent = isInternal ? 'Internal' : 'External';
    headerDiv.appendChild(badgeSpan);

    // Add edited indicator if applicable
    if (message.edited) {
        const editedSpan = document.createElement('span');
        editedSpan.className = 'sweetdesk-visibility-badge';
        editedSpan.textContent = 'Edited';
        editedSpan.style.marginLeft = '8px';
        editedSpan.style.background = '#fef3c7';
        editedSpan.style.color = '#a16207';
        headerDiv.appendChild(editedSpan);
    }

    messageDiv.appendChild(headerDiv);

    // Add message body
    const bodyDiv = document.createElement('div');
    bodyDiv.className = 'sweetdesk-message-body';
    bodyDiv.innerHTML = escapeHtml(message.body).replace(/\n/g, '<br>');
    messageDiv.appendChild(bodyDiv);

    // Add edited timestamp if message was edited
    if (message.edited && message.updated_at !== message.created_at) {
        const editInfoDiv = document.createElement('div');
        editInfoDiv.className = 'sweetdesk-message-edited';
        editInfoDiv.textContent = `Edited ${formatDate(message.updated_at)}`;
        messageDiv.appendChild(editInfoDiv);
    }

    // Render replies if there are any
    if (message.replies && message.replies.length > 0) {
        const repliesContainer = document.createElement('div');
        repliesContainer.className = 'sweetdesk-message-replies';

        message.replies.forEach(reply => {
            repliesContainer.appendChild(renderMessage(reply, canSeeInternal));
        });

        messageDiv.appendChild(repliesContainer);
    }

    return messageDiv;
}

/**
 * Check if current user can see internal messages
 * This checks user capabilities via WordPress nonce or user role
 */
async function canUserSeeInternalMessages() {
    // Check for WordPress user role stored in page
    const userRole = document.body.dataset.userRole;
    
    // Admins and editors can see internal messages
    return userRole === 'administrator' || userRole === 'editor';
}

/**
 * Format date string to readable format
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) {
        return 'just now';
    } else if (diffMins < 60) {
        return `${diffMins}m ago`;
    } else if (diffHours < 24) {
        return `${diffHours}h ago`;
    } else if (diffDays < 7) {
        return `${diffDays}d ago`;
    } else {
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Setup reply submission handler
 */
function setupReplySubmission(ticketId) {
    const sendBtn = document.getElementById('send-reply-btn');
    const bodyInput = document.getElementById('reply-body');
    const internalCheckbox = document.getElementById('reply-internal');

    if (!sendBtn || !bodyInput) return;

    sendBtn.addEventListener('click', async () => {
        const body = bodyInput.value.trim();
        
        if (!body) {
            alert('Please enter a reply message');
            return;
        }

        const isInternal = internalCheckbox?.checked || false;

        // Disable button while submitting
        sendBtn.disabled = true;
        const originalText = sendBtn.textContent;
        sendBtn.textContent = 'Sending...';

        try {
            const response = await fetch(
                `/wp-json/sweetdesk/v1/tickets/${ticketId}/messages`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        body: body,
                        visibility: isInternal ? 'internal' : 'external',
                        reply_to_id: null
                    })
                }
            );

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Clear the form
            bodyInput.value = '';
            if (internalCheckbox) internalCheckbox.checked = false;

            // Reload messages
            await loadAndRenderMessages(ticketId);

            // Show success message
            const successDiv = document.createElement('div');
            successDiv.className = 'notice notice-success';
            successDiv.innerHTML = '<p>Reply posted successfully!</p>';
            document.querySelector('.sweetdesk-ticket-detail').insertBefore(
                successDiv,
                document.querySelector('.sweetdesk-messages-section')
            );

            // Remove success message after 3 seconds
            setTimeout(() => successDiv.remove(), 3000);
        } catch (error) {
            console.error('Error posting reply:', error);
            alert('Failed to post reply. Please try again.');
        } finally {
            sendBtn.disabled = false;
            sendBtn.textContent = originalText;
        }
    });

    // Allow Ctrl+Enter to submit
    bodyInput.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            sendBtn.click();
        }
    });
}
