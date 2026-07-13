<?php

if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="sweetdesk-ticket-form sweetdesk-tickets-page">
    <div class="page-header">
        <h1 class="page-title">Submit a Ticket</h1>
        <div class="header-actions">
            <?php if ($is_logged_in) : ?>
                <span class="sweetdesk-ticket-form__user">
                    Submitting as: <?php echo esc_html($current_user_display); ?>
                </span>
                <a class="btn-outline" href="<?php echo esc_url($logout_url); ?>">
                    Log Out
                </a>
            <?php else : ?>
                <a class="btn-primary" href="<?php echo esc_url($login_url); ?>">
                    Log In
                </a>
            <?php endif; ?>
        </div>
    </div>

    <p class="sweetdesk-ticket-form__subtitle">
        Tell us about your issue and our team will get back to you.
    </p>

    <?php if (!$is_logged_in) : ?>
        <div class="sweetdesk-ticket-form__notice sweetdesk-ticket-form__notice--warning">
            <p>You must be logged in to submit a ticket.</p>
            <a class="btn-primary" href="<?php echo esc_url($login_url); ?>">Log In</a>
        </div>
    <?php else : ?>
        <div class="sd-ticket-form-panel">
            <div
                class="sweetdesk-ticket-form__notice sweetdesk-ticket-form__notice--success"
                id="sd-frontend-ticket-success"
                hidden
            >
                <p id="sd-frontend-ticket-success-text"></p>
            </div>

            <div
                class="sweetdesk-ticket-form__notice sweetdesk-ticket-form__notice--error"
                id="sd-frontend-ticket-error"
                hidden
            >
                <p id="sd-frontend-ticket-error-text"></p>
            </div>

            <form id="sd-frontend-ticket-form" class="sweetdesk-ticket-form__body" novalidate>
                <div class="sd-form-group">
                    <label for="sd-frontend-ticket-title">Title *</label>
                    <input
                        type="text"
                        id="sd-frontend-ticket-title"
                        name="title"
                        placeholder="Brief summary of your issue"
                        required
                    >
                </div>

                <div class="sd-form-group">
                    <label for="sd-frontend-ticket-body">Message *</label>
                    <div
                        id="sd-frontend-ticket-body"
                        class="sweetdesk-quill-editor"
                        data-placeholder="Describe your issue in detail..."
                    ></div>
                </div>

                <div class="sweetdesk-ticket-form__actions">
                    <button type="submit" class="btn-primary" id="sd-frontend-ticket-submit">
                        Submit Ticket
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>
