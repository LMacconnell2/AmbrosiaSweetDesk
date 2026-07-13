<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once SWEETDESK_PATH . 'src/api/controllers/email-settings-controller.php';

class SweetDesk_Email_Settings_Routes
{
    public static function register_routes(): void
    {
        $controller = new SweetDesk_Email_Settings_Controller();

        register_rest_route(
            'sweetdesk/v1',
            '/settings/email',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$controller, 'get_settings'],
                    'permission_callback' => [
                        $controller,
                        'permissions_check',
                    ],
                ],
                [
                    'methods' => 'PUT',
                    'callback' => [$controller, 'update_settings'],
                    'permission_callback' => [
                        $controller,
                        'permissions_check',
                    ],
                    'args' => self::update_args(),
                ],
            ]
        );
    }

    private static function update_args(): array
    {
        return [
            'additional_emails' => [
                'required' => false,
                'type' => 'array',
                'items' => [
                    'type' => 'string',
                    'format' => 'email',
                ],
                'validate_callback' => static function (
                    mixed $value
                ): bool|WP_Error {
                    if (!is_array($value)) {
                        return new WP_Error(
                            'sweetdesk_invalid_email_list',
                            'Additional emails must be an array.',
                            ['status' => 400]
                        );
                    }

                    if (count($value) > 5) {
                        return new WP_Error(
                            'sweetdesk_too_many_emails',
                            'A maximum of five additional email addresses is allowed.',
                            ['status' => 400]
                        );
                    }

                    foreach ($value as $email) {
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

                    return true;
                },
            ],
            'send_ticket_updates' => [
                'required' => false,
                'type' => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
            ],
            'send_team_mentions' => [
                'required' => false,
                'type' => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
            ],
        ];
    }
}