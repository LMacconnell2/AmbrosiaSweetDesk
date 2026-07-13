<?php

if (!defined('ABSPATH')) {
    exit;
}

class SweetDesk_Notification_Service
{
    private SweetDesk_Email_Settings_Service $settings_service;

    public function __construct()
    {
        $this->settings_service =
            new SweetDesk_Email_Settings_Service();
    }

    /**
     * Send an email when a ticket changes.
     *
     * This includes:
     * - ticket creation;
     * - status changes;
     * - priority changes;
     * - assignment changes;
     * - new replies;
     * - edited replies.
     */
    public function send_ticket_update(
        int $user_id,
        array $ticket,
        string $update_message
    ): bool {
        if (
            !$this->settings_service
                ->should_receive_ticket_updates($user_id)
        ) {
            return false;
        }

        $recipients = $this->settings_service
            ->get_notification_recipients($user_id);

        if ($recipients === []) {
            return false;
        }

        $ticket_id = absint($ticket['id'] ?? 0);

        $ticket_title = sanitize_text_field(
            $ticket['title'] ?? 'Untitled Ticket'
        );

        $subject = sprintf(
            '[SweetDesk] Ticket #%d updated: %s',
            $ticket_id,
            $ticket_title
        );

        $message = $this->build_ticket_update_message(
            $ticket,
            $update_message
        );

        return $this->send(
            $recipients,
            $subject,
            $message
        );
    }

    public function send_team_mention(
        int $user_id,
        array $ticket,
        string $mention_message
    ): bool {
        if (
            !$this->settings_service
                ->should_receive_team_mentions($user_id)
        ) {
            return false;
        }

        $recipients = $this->settings_service
            ->get_notification_recipients($user_id);

        if ($recipients === []) {
            return false;
        }

        $ticket_id = absint($ticket['id'] ?? 0);

        $ticket_title = sanitize_text_field(
            $ticket['title'] ?? 'Untitled Ticket'
        );

        $subject = sprintf(
            '[SweetDesk] Your team was mentioned on ticket #%d',
            $ticket_id
        );

        $message = sprintf(
            '<p>Your team was mentioned on a SweetDesk ticket.</p>
            <p><strong>Ticket:</strong> #%1$d — %2$s</p>
            <p>%3$s</p>',
            $ticket_id,
            esc_html($ticket_title),
            nl2br(esc_html($mention_message))
        );

        return $this->send(
            $recipients,
            $subject,
            $message
        );
    }

    private function send(
        array $recipients,
        string $subject,
        string $message
    ): bool {
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
        ];

        /*
         * wp_mail() uses the site's configured WordPress mail
         * transport. SMTP, Mailgun, SES, SendGrid, and similar
         * plugins can intercept this normally.
         */
        $sent = wp_mail(
            $recipients,
            $subject,
            $message,
            $headers
        );

        if (!$sent) {
            $this->log_failure(
                $recipients,
                $subject
            );
        }

        return $sent;
    }

    private function build_ticket_update_message(
        array $ticket,
        string $update_message
    ): string {
        $ticket_id = absint($ticket['id'] ?? 0);

        $ticket_title = sanitize_text_field(
            $ticket['title'] ?? 'Untitled Ticket'
        );

        return sprintf(
            '<p>A SweetDesk ticket you follow has been updated.</p>
            <p><strong>Ticket:</strong> #%1$d — %2$s</p>
            <p>%3$s</p>',
            $ticket_id,
            esc_html($ticket_title),
            nl2br(esc_html($update_message))
        );
    }

    private function log_failure(
        array $recipients,
        string $subject
    ): void {
        if (
            !defined('WP_DEBUG') ||
            !WP_DEBUG
        ) {
            return;
        }

        error_log(
            sprintf(
                'SweetDesk email failed. Recipients: %s; Subject: %s',
                implode(', ', $recipients),
                $subject
            )
        );
    }
}