<?php

if (!defined('ABSPATH')) {
    exit;
}

class SweetDesk_Client_Routes
{
    public static function register_routes()
    {
        $controller = new SweetDesk_Client_Controller();

        register_rest_route('sweetdesk/v1', '/clients', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$controller, 'get_clients'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$controller, 'create_client'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
        ]);

        register_rest_route('sweetdesk/v1', '/clients/export', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$controller, 'export_clients'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
        ]);

        register_rest_route('sweetdesk/v1', '/clients/import', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$controller, 'import_clients'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
        ]);

        register_rest_route('sweetdesk/v1', '/clients/(?P<id>\d+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$controller, 'get_client'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$controller, 'update_client'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$controller, 'delete_client'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
        ]);
    }
}

