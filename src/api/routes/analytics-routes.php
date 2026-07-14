<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once SWEETDESK_PATH . 'src/api/controllers/analytics-controller.php';

class SweetDesk_Analytics_Routes
{
    public static function register_routes(): void
    {
        $controller = new SweetDesk_Analytics_Controller();

        register_rest_route(
            'sweetdesk/v1',
            '/analytics/summary',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$controller, 'get_summary'],
                'permission_callback' => [$controller, 'permissions_check'],
                'args' => self::get_summary_args(),
            ]
        );

        register_rest_route(
            'sweetdesk/v1',
            '/analytics/oldest-unresolved',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$controller, 'get_oldest_unresolved'],
                'permission_callback' => [$controller, 'permissions_check'],
                'args' => self::get_oldest_unresolved_args(),
            ]
        );

        register_rest_route(
            'sweetdesk/v1',
            '/analytics/recent-messages',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$controller, 'get_recent_messages'],
                'permission_callback' => [$controller, 'permissions_check'],
                'args' => self::get_recent_messages_args(),
            ]
        );

        register_rest_route(
            'sweetdesk/v1',
            '/analytics/export',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$controller, 'export'],
                'permission_callback' => [$controller, 'permissions_check'],
                'args' => self::get_export_args(),
            ]
        );
    }

    private static function get_summary_args(): array
    {
        return [
            'scope' => [
                'required' => true,
                'type' => 'string',
                'enum' => ['company', 'user'],
                'sanitize_callback' => 'sanitize_key',
            ],
            'person_id' => [
                'required' => false,
                'type' => 'integer',
                'minimum' => 1,
                'sanitize_callback' => 'absint',
            ],
            'date_start' => [
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'date_end' => [
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'team_id' => [
                'required' => false,
                'type' => 'integer',
                'minimum' => 1,
                'sanitize_callback' => 'absint',
            ],
        ];
    }

    private static function get_oldest_unresolved_args(): array
    {
        return [
            'scope' => [
                'required' => true,
                'type' => 'string',
                'enum' => ['company', 'user'],
                'sanitize_callback' => 'sanitize_key',
            ],
            'person_id' => [
                'required' => false,
                'type' => 'integer',
                'minimum' => 1,
                'sanitize_callback' => 'absint',
            ],
            'limit' => [
                'required' => false,
                'type' => 'integer',
                'default' => 3,
                'minimum' => 1,
                'maximum' => 100,
                'sanitize_callback' => 'absint',
            ],
        ];
    }

    private static function get_recent_messages_args(): array
    {
        return [
            'person_id' => [
                'required' => false,
                'type' => 'integer',
                'minimum' => 1,
                'sanitize_callback' => 'absint',
            ],
            'source' => [
                'required' => false,
                'type' => 'string',
                'default' => 'all',
                'enum' => ['all', 'customer', 'staff'],
                'sanitize_callback' => 'sanitize_key',
            ],
            'limit' => [
                'required' => false,
                'type' => 'integer',
                'default' => 5,
                'minimum' => 1,
                'maximum' => 100,
                'sanitize_callback' => 'absint',
            ],
        ];
    }

    private static function get_export_args(): array
    {
        return [
            'scope' => [
                'required' => true,
                'type' => 'string',
                'enum' => ['company', 'user'],
                'sanitize_callback' => 'sanitize_key',
            ],
            'person_id' => [
                'required' => false,
                'type' => 'integer',
                'minimum' => 1,
                'sanitize_callback' => 'absint',
            ],
            'date_start' => [
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'date_end' => [
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'format' => [
                'required' => false,
                'type' => 'string',
                'default' => 'json',
                'enum' => ['csv', 'json'],
                'sanitize_callback' => 'sanitize_key',
            ],
        ];
    }
}