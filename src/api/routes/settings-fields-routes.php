<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once SWEETDESK_PATH .
    'src/api/controllers/settings-fields-controller.php';

class SweetDesk_Settings_Fields_Routes
{
    public static function register_routes(): void
    {
        $controller = new SweetDesk_Settings_Fields_Controller();

        register_rest_route(
            'sweetdesk/v1',
            '/settings/tickets/fields',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$controller, 'get_fields'],
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
                    'callback' => [$controller, 'create_field'],
                    'permission_callback' => [
                        $controller,
                        'manage_permissions_check',
                    ],
                    'args' => self::create_field_args(),
                ],
            ]
        );

        register_rest_route(
            'sweetdesk/v1',
            '/settings/tickets/fields/(?P<id>\d+)',
            [
                [
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => [$controller, 'update_field'],
                    'permission_callback' => [
                        $controller,
                        'manage_permissions_check',
                    ],
                    'args' => array_merge(
                        self::id_args(),
                        self::update_field_args()
                    ),
                ],
                [
                    'methods' => WP_REST_Server::DELETABLE,
                    'callback' => [$controller, 'delete_field'],
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

    private static function create_field_args(): array
    {
        return [
            'name' => [
                'required' => true,
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 150,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'field_key' => [
                'required' => false,
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 150,
                'sanitize_callback' => 'sanitize_key',
            ],
            'field_type' => [
                'required' => false,
                'type' => 'string',
                'default' => 'text',
                'enum' => self::allowed_field_types(),
                'sanitize_callback' => 'sanitize_key',
            ],
            'is_required' => [
                'required' => false,
                'type' => 'boolean',
                'default' => false,
                'sanitize_callback' => 'rest_sanitize_boolean',
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
            'options' => [
                'required' => false,
                'type' => 'array',
                'default' => [],
                'items' => [
                    'type' => 'string',
                ],
            ],
        ];
    }

    private static function update_field_args(): array
    {
        return [
            'name' => [
                'required' => false,
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 150,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'field_key' => [
                'required' => false,
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 150,
                'sanitize_callback' => 'sanitize_key',
            ],
            'field_type' => [
                'required' => false,
                'type' => 'string',
                'enum' => self::allowed_field_types(),
                'sanitize_callback' => 'sanitize_key',
            ],
            'is_required' => [
                'required' => false,
                'type' => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
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
            'options' => [
                'required' => false,
                'type' => 'array',
                'items' => [
                    'type' => 'string',
                ],
            ],
        ];
    }

    private static function allowed_field_types(): array
    {
        return [
            'text',
            'textarea',
            'number',
            'email',
            'url',
            'date',
            'datetime',
            'checkbox',
            'select',
        ];
    }
}