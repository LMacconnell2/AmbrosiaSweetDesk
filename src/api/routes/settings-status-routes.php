<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once SWEETDESK_PATH .
    'src/api/controllers/settings-status-controller.php';

class SweetDesk_Settings_Status_Routes
{
    public static function register_routes(): void
    {
        $controller = new SweetDesk_Settings_Status_Controller();

        register_rest_route(
            'sweetdesk/v1',
            '/settings/tickets/status',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$controller, 'get_statuses'],
                    'permission_callback' => [
                        $controller,
                        'view_permissions_check',
                    ],
                    'args' => [
                        'include_inactive' => [
                            'required' => false,
                            'type' => 'boolean',
                            'default' => false,
                            'sanitize_callback' => 'rest_sanitize_boolean',
                        ],
                    ],
                ],
                [
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => [$controller, 'create_status'],
                    'permission_callback' => [
                        $controller,
                        'manage_permissions_check',
                    ],
                    'args' => self::create_status_args(),
                ],
            ]
        );

        register_rest_route(
            'sweetdesk/v1',
            '/settings/tickets/status/(?P<id>\d+)',
            [
                [
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => [$controller, 'update_status'],
                    'permission_callback' => [
                        $controller,
                        'manage_permissions_check',
                    ],
                    'args' => array_merge(
                        self::id_args(),
                        self::update_status_args()
                    ),
                ],
                [
                    'methods' => WP_REST_Server::DELETABLE,
                    'callback' => [$controller, 'delete_status'],
                    'permission_callback' => [
                        $controller,
                        'delete_permissions_check',
                    ],
                    'args' => self::id_args(),
                ],
            ]
        );
    }

    private static function id_args(): array
    {
        return [
            'id' => [
                'required' => true,
                'type' => 'integer',
                'minimum' => 1,
                'sanitize_callback' => 'absint',
            ],
        ];
    }

    private static function create_status_args(): array
    {
        return [
            'name' => [
                'required' => true,
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 100,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'slug' => [
                'required' => false,
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 100,
                'sanitize_callback' => 'sanitize_title',
            ],
            'is_active' => [
                'required' => false,
                'type' => 'boolean',
                'default' => true,
                'sanitize_callback' => 'rest_sanitize_boolean',
            ],
            'sort_order' => [
                'required' => false,
                'type' => 'integer',
                'minimum' => 0,
                'default' => 0,
                'sanitize_callback' => 'absint',
            ],
        ];
    }

    private static function update_status_args(): array
    {
        return [
            'name' => [
                'required' => false,
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 100,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'slug' => [
                'required' => false,
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 100,
                'sanitize_callback' => 'sanitize_title',
            ],
            'is_active' => [
                'required' => false,
                'type' => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
            ],
            'sort_order' => [
                'required' => false,
                'type' => 'integer',
                'minimum' => 0,
                'sanitize_callback' => 'absint',
            ],
        ];
    }
}