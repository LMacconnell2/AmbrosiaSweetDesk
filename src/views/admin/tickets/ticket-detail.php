<?php
$ticket_id = isset($_GET['ticket_id']) ? absint($_GET['ticket_id']) : 0;
?>
<div
    class="wrap sweetdesk-ticket-detail"
    <?php if ($ticket_id) : ?>
        data-ticket-id="<?php echo esc_attr($ticket_id); ?>"
    <?php endif; ?>
>
    <a href="<?php echo esc_url(admin_url('admin.php?page=sweetdesk')); ?>" class="sd-back-link">
        ← Back to Tickets
    </a>

    <div class="page-header sweetdesk-ticket-detail-header">
        <div>
            <h1 class="page-title" id="ticket-title">Ticket</h1>
            <div class="ticket-metas">
                <p>Status: <span id="ticket-status">—</span></p>
                <p>Priority: <span id="ticket-priority">—</span></p>
                <p>Assigned: <span id="ticket-assignee">—</span></p>
            </div>
        </div>
    </div>

    <div id="ticket-detail-empty" class="sweetdesk-messages-empty" hidden>
        <p>No ticket selected. Open a ticket from the <a href="<?php echo esc_url(admin_url('admin.php?page=sweetdesk')); ?>">Tickets</a> list.</p>
    </div>

    <div id="ticket-detail-content">
        <div id="ticket-detail-notices"></div>

        <div class="section-card sweetdesk-messages-section">
            <div class="section-card-header">
                <h2>Messages &amp; Activity</h2>
            </div>
            <div class="section-card-body sweetdesk-messages-container-wrap">
                <div id="sweetdesk-messages-container" class="sweetdesk-messages-container">
                    <div class="sweetdesk-messages-loading">
                        <p>Loading messages...</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-card sweetdesk-reply-section">
            <div class="section-card-header section-card-header--magenta">
                <h3 id="reply-section-title">Leave a Reply</h3>
            </div>
            <div class="section-card-body">
                <div id="reply-context" class="sweetdesk-reply-context" hidden>
                    <div class="sweetdesk-reply-context-text">
                        Replying to:
                        <span id="reply-context-preview"></span>
                    </div>
                    <button type="button" id="cancel-reply-btn" class="btn-outline sweetdesk-reply-cancel">
                        Cancel
                    </button>
                </div>
                <div
                    id="reply-body"
                    class="sweetdesk-quill-editor"
                    data-placeholder="Write your reply here..."
                ></div>
                <div class="sweetdesk-reply-options">
                    <label>
                        <input type="checkbox" id="reply-internal">
                        Internal only (not visible to customer)
                    </label>
                </div>
                <button id="send-reply-btn" class="btn-primary" type="button">Send Reply</button>
            </div>
        </div>
    </div>
</div>
