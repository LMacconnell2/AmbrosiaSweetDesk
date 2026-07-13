<?php

if (!defined('ABSPATH')) {
    exit;
}

class SweetDesk_Email_Settings_Service
{
    private const ADDITIONAL_EMAILS_META_KEY =
        'sweetdesk_additional_notification_emails';

    private const TICKET_UPDATES_META_KEY =
        'sweetdesk_notify_ticket_updates';

    private const TEAM_MENTIONS_META_KEY =
        'sweetdesk_notify_team_mentions';

    private const MAX_ADDITIONAL_EMAILS = 5;

    public function get_settings(int $user_id): array|WP_Error
    {
        $user = get_userdata($user_id);

        if (!$user) {
            return new WP_Error(
                'sweetdesk_user_not_found',
                'The current WordPress user could not be found.',
                ['status' => 404]
            );
        }

        $additional_emails = get_user_meta(
            $user_id,
            self::ADDITIONAL_EMAILS_META_KEY,
            true
        );

        if (!is_array($additional_emails)) {
            $additional_emails = [];
        }

        $ticket_updates = get_user_meta(
            $user_id,
            self::TICKET_UPDATES_META_KEY,
            true
        );

        $team_mentions = get_user_meta(
            $user_id,
            self::TEAM_MENTIONS_META_KEY,
            true
        );

        return [
            'wordpress_email' => sanitize_email(
                $user->user_email
            ),
            'additional_emails' => $this->sanitize_email_list(
                $additional_emails,
                $user->user_email
            ),
            /*
             * Default both preferences to true when the user has
             * never explicitly saved a setting.
             */
            'send_ticket_updates' => $ticket_updates === ''
                ? true
                : (bool) $ticket_updates,
            'send_team_mentions' => $team_mentions === ''
                ? true
                : (bool) $team_mentions,
        ];
    }

    public function update_settings(
        int $user_id,
        array $data
    ): array|WP_Error {
        $user = get_userdata($user_id);

        if (!$user) {
            return new WP_Error(
                'sweetdesk_user_not_found',
                'The current WordPress user could not be found.',
                ['status' => 404]
            );
        }

        if ($data === []) {
            return new WP_Error(
                'sweetdesk_no_email_settings',
                'No email settings were provided.',
                ['status' => 400]
            );
        }

        if (array_key_exists('additional_emails', $data)) {
            if (!is_array($data['additional_emails'])) {
                return new WP_Error(
                    'sweetdesk_invalid_email_list',
                    'Additional emails must be an array.',
                    ['status' => 400]
                );
            }

            if (
                count($data['additional_emails']) >
                self::MAX_ADDITIONAL_EMAILS
            ) {
                return new WP_Error(
                    'sweetdesk_too_many_emails',
                    sprintf(
                        'A maximum of %d additional email addresses is allowed.',
                        self::MAX_ADDITIONAL_EMAILS
                    ),
                    ['status' => 400]
                );
            }

            $additional_emails = $this->sanitize_email_list(
                $data['additional_emails'],
                $user->user_email
            );

            /*
             * Check whether invalid addresses were removed during
             * sanitization. This prevents silently saving only part
             * of an invalid request.
             */
            foreach ($data['additional_emails'] as $email) {
                if (!is_string($email) || !is_email($email)) {
                    return new WP_Error(
                        'sweetdesk_invalid_email',
                        sprintf(
                            'The email address "%s" is invalid.',
                            sanitize_text_field((string) $email)
                        ),
                        ['status' => 400]
                    );
                }
            }

            update_user_meta(
                $user_id,
                self::ADDITIONAL_EMAILS_META_KEY,
                $additional_emails
            );
        }

        if (array_key_exists('send_ticket_updates', $data)) {
            update_user_meta(
                $user_id,
                self::TICKET_UPDATES_META_KEY,
                rest_sanitize_boolean(
                    $data['send_ticket_updates']
                ) ? 1 : 0
            );
        }

        if (array_key_exists('send_team_mentions', $data)) {
            update_user_meta(
                $user_id,
                self::TEAM_MENTIONS_META_KEY,
                rest_sanitize_boolean(
                    $data['send_team_mentions']
                ) ? 1 : 0
            );
        }

        return $this->get_settings($user_id);
    }

    public function get_notification_recipients(
        int $user_id
    ): array {
        $settings = $this->get_settings($user_id);

        if (is_wp_error($settings)) {
            return [];
        }

        $recipients = array_merge(
            [$settings['wordpress_email']],
            $settings['additional_emails']
        );

        return $this->sanitize_email_list($recipients);
    }

    public function should_receive_ticket_updates(
        int $user_id
    ): bool {
        $settings = $this->get_settings($user_id);

        if (is_wp_error($settings)) {
            return false;
        }

        return $settings['send_ticket_updates'];
    }

    public function should_receive_team_mentions(
        int $user_id
    ): bool {
        $settings = $this->get_settings($user_id);

        if (is_wp_error($settings)) {
            return false;
        }

        return $settings['send_team_mentions'];
    }

    private function sanitize_email_list(
        array $emails,
        ?string $excluded_email = null
    ): array {
        $excluded_email = $excluded_email
            ? strtolower(sanitize_email($excluded_email))
            : null;

        $sanitized = [];

        foreach ($emails as $email) {
            if (!is_string($email)) {
                continue;
            }

            $email = strtolower(sanitize_email($email));

            if ($email === '' || !is_email($email)) {
                continue;
            }

            /*
             * Do not store the WordPress account email in the
             * additional address list.
             */
            if (
                $excluded_email !== null &&
                $email === $excluded_email
            ) {
                continue;
            }

            $sanitized[] = $email;
        }

        return array_values(array_unique($sanitized));
    }
}