<?php

if (!defined('ABSPATH')) exit;

function sweetdesk_register_client_routes($controller) {
    register_rest_route('sweetdesk/v1', '/clients', [
        [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$controller, 'get_clients'],
            'permission_callback' => function () {
                return current_user_can('read');
            },
        ],
        [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$controller, 'create_client'],
            'permission_callback' => function () {
                return current_user_can('read');
            },
        ],
    ]);

    register_rest_route('sweetdesk/v1', '/clients/export', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => [$controller, 'export_clients'],
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ]);

    register_rest_route('sweetdesk/v1', '/clients/import', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => [$controller, 'import_clients'],
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ]);

    register_rest_route('sweetdesk/v1', '/clients/(?P<id>\d+)', [
        [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$controller, 'get_client'],
            'permission_callback' => function () {
                return current_user_can('read');
            },
        ],
        [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => [$controller, 'update_client'],
            'permission_callback' => function () {
                return current_user_can('read');
            },
        ],
        [
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => [$controller, 'delete_client'],
            'permission_callback' => function () {
                return current_user_can('read');
            },
        ],
    ]);
}