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
    }
}