<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once SWEETDESK_PATH .
    'src/api/controllers/team-controller.php';

class SweetDesk_Team_Routes
{
    public static function register_routes()
    {
        $controller = new SweetDesk_Team_Controller();

        register_rest_route('sweetdesk/v1', '/teams', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$controller, 'get_teams'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$controller, 'create_team'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
        ]);

        register_rest_route('sweetdesk/v1', '/teams/people', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$controller, 'search_people'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
        ]);

        register_rest_route('sweetdesk/v1', '/teams/(?P<id>\d+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$controller, 'get_team'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$controller, 'update_team'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$controller, 'delete_team'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
        ]);

        register_rest_route('sweetdesk/v1', '/teams/(?P<id>\d+)/people', [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$controller, 'update_team_people'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
        ]);

        register_rest_route('sweetdesk/v1', '/teams/(?P<id>\d+)/people/(?P<person_id>\d+)', [
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$controller, 'remove_team_person'],
                'permission_callback' => [$controller, 'permissions_check'],
            ],
        ]);
    }
}