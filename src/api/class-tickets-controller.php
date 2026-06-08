<?php

class SweetDesk_Tickets_Controller {

    public function register_routes() {

        register_rest_route(
            'sweetdesk/v1',
            '/tickets',
            [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_tickets'],
                    'permission_callback' => [$this, 'permissions']
                ],

                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [$this, 'create_ticket'],
                    'permission_callback' => [$this, 'permissions']
                ]
            ]
        );

        register_rest_route(
            'sweetdesk/v1',
            '/tickets/(?P<id>\d+)',
            [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_ticket'],
                    'permission_callback' => [$this, 'permissions']
                ]
            ]
        );
    }

    public function permissions() {

        return current_user_can('manage_options');
    }

    public function get_tickets() {

        global $wpdb;

        $table = $wpdb->prefix . 'sweetdesk_tickets';

        $results = $wpdb->get_results(
            "SELECT * FROM $table ORDER BY created_at DESC",
            ARRAY_A
        );

        return rest_ensure_response($results);
    }

    public function get_ticket($request) {

        global $wpdb;

        $id = (int) $request['id'];

        $table = $wpdb->prefix . 'sweetdesk_tickets';

        $ticket = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE id = %d",
                $id
            ),
            ARRAY_A
        );

        if (!$ticket) {

            return new WP_Error(
                'ticket_not_found',
                'Ticket not found',
                ['status' => 404]
            );
        }

        return rest_ensure_response($ticket);
    }

    public function create_ticket($request) {

        global $wpdb;

        $table = $wpdb->prefix . 'sweetdesk_tickets';

        $wpdb->insert(
            $table,
            [
                'title' => sanitize_text_field(
                    $request['title']
                ),

                'description' => sanitize_textarea_field(
                    $request['description']
                ),

                'status' => 'open',
                'priority' => 'normal'
            ]
        );

        return rest_ensure_response([
            'success' => true,
            'id' => $wpdb->insert_id
        ]);
    }
}