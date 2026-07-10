<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once SWEETDESK_PATH .
    'src/api/controllers/people-controller.php';

class SweetDesk_People_Routes
{
    public static function register_routes()
    {
        $controller = new SweetDesk_People_Controller();

        register_rest_route('sweetdesk/v1', '/people', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$controller, 'get_people'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$controller, 'create_person'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
        ]);

        register_rest_route('sweetdesk/v1', '/people/(?P<id>\d+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$controller, 'get_person'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$controller, 'update_person'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$controller, 'delete_person'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
        ]);

        register_rest_route('sweetdesk/v1', '/people/export', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$controller, 'export_people'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
        ]);

        register_rest_route('sweetdesk/v1', '/people/import', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$controller, 'import_people'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
        ]);
    }
}