<?php

require_once SWEETDESK_PATH .
    'src/api/controllers/ticket-controller.php';

class SweetDesk_Ticket_Routes {

    public function register_routes() {

        register_rest_route(
            'sweetdesk/v1',
            '/tickets',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [
                    SweetDesk_Ticket_Controller::class,
                    'get_tickets'
                ],
                'permission_callback' => function () {
                    return current_user_can('read');
                }
            ]
        );

        register_rest_route(
            'sweetdesk/v1',
            '/tickets',
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [
                    SweetDesk_Ticket_Controller::class,
                    'create_ticket'
                ],
                'permission_callback' => function () {
                    return current_user_can('read');
                }
            ]
        );

        register_rest_route(
            'sweetdesk/v1',
            '/edit-ticket/(?P<id>\d+)',
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [
                    SweetDesk_Ticket_Controller::class,
                    'update_ticket'
                ],
                'permission_callback' => function () {
                    return current_user_can('read');
                }
            ]
        );

        register_rest_route(
            'sweetdesk/v1',
            '/tickets/(?P<id>\d+)/assignee',
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [
                    SweetDesk_Ticket_Controller::class,
                    'update_assignee'
                ],
                'permission_callback' => function () {
                    return current_user_can('read');
                }
            ]
        );

        register_rest_route(
            'sweetdesk/v1',
            '/tickets/(?P<id>\d+)/status',
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [
                    SweetDesk_Ticket_Controller::class,
                    'update_status'
                ],
                'permission_callback' => function () {
                    return current_user_can('read');
                }
            ]
        );

        register_rest_route(
            'sweetdesk/v1',
            '/tickets/(?P<id>\d+)',
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [
                    SweetDesk_Ticket_Controller::class,
                    'delete_ticket'
                ],
                'permission_callback' => function () {
                    return current_user_can('delete_posts');
                }
            ]
        );

        register_rest_route(
            'sweetdesk/v1',
            '/tickets/(?P<id>\d+)',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [
                    SweetDesk_Ticket_Controller::class,
                    'get_ticket'
                ],
                'permission_callback' => function () {
                    return current_user_can('read');
                }
            ]
        );

        register_rest_route(
            'sweetdesk/v1',
            '/tickets/export',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [
                    SweetDesk_Ticket_Controller::class,
                    'export_tickets'
                ],
                'permission_callback' => function () {
                    return current_user_can(
                        'manage_options'
                    );
                }
            ]
        );

        register_rest_route(
            'sweetdesk/v1',
            '/tickets/import',
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [
                    SweetDesk_Ticket_Controller::class,
                    'import_tickets'
                ],
                'permission_callback' => function () {
                    return current_user_can(
                        'manage_options'
                    );
                }
            ]
        );

        register_rest_route(
            'sweetdesk/v1',
            '/tickets/(?P<id>\d+)/messages',
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [
                    SweetDesk_Ticket_Controller::class,
                    'create_message'
                ],
                'permission_callback' => function () {
                    return current_user_can('read');
                }
            ]
        );

        register_rest_route(
            'sweetdesk/v1',
            '/tickets/(?P<id>\d+)/messages',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [
                    SweetDesk_Ticket_Controller::class,
                    'get_messages'
                ],
                'permission_callback' => function () {
                    return current_user_can('read');
                }
            ]
        );

        register_rest_route(
            'sweetdesk/v1',
            '/messages/(?P<id>\d+)',
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [
                    SweetDesk_Ticket_Controller::class,
                    'update_message'
                ],
                'permission_callback' => function () {
                    return current_user_can('read');
                }
            ]
        );

        register_rest_route(
            'sweetdesk/v1',
            '/messages/(?P<id>\d+)',
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [
                    SweetDesk_Ticket_Controller::class,
                    'delete_message'
                ],
                'permission_callback' => function () {
                    return current_user_can('read');
                }
            ]
        );
    }
}