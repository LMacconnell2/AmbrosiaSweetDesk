<?php

require_once SWEETDESK_PATH .
    'src/api/services/ticket-service.php';

class SweetDesk_Ticket_Controller {

    public static function get_tickets(
        WP_REST_Request $request
    ) {

        $service =
            new SweetDesk_Ticket_Service();

        return rest_ensure_response(
            $service->search_tickets(
                $request->get_params()
            )
        );
    }
    public static function create_ticket(
        WP_REST_Request $request
    ) {

        $service =
            new SweetDesk_Ticket_Service();

        return rest_ensure_response(
            $service->create_ticket(
                $request->get_json_params()
            )
        );
    }

    public static function update_ticket(
        WP_REST_Request $request
    ) {

        $service =
            new SweetDesk_Ticket_Service();

        return rest_ensure_response(
            $service->update_ticket(
                absint(
                    $request['id']
                ),
                $request->get_json_params()
            )
        );
    }

    public static function update_assignee(
        WP_REST_Request $request
    ) {

        $service =
            new SweetDesk_Ticket_Service();

        return rest_ensure_response(
            $service->update_assignee(
                absint($request['id']),
                $request->get_json_params()
            )
        );
    }

    public static function update_status(
        WP_REST_Request $request
    ) {

        $service =
            new SweetDesk_Ticket_Service();

        return rest_ensure_response(
            $service->update_status(
                absint($request['id']),
                $request->get_json_params()
            )
        );
    }

    public static function delete_ticket(
        WP_REST_Request $request
    ) {

        $service =
            new SweetDesk_Ticket_Service();

        return rest_ensure_response(
            $service->delete_ticket(
                absint($request['id'])
            )
        );
    }

    public static function get_ticket(
        WP_REST_Request $request
    ) {

        $service =
            new SweetDesk_Ticket_Service();

        return rest_ensure_response(
            $service->get_ticket(
                absint(
                    $request['id']
                )
            )
        );
    }

    public static function export_tickets(
        WP_REST_Request $request
    ) {

        $service =
            new SweetDesk_Ticket_Service();

        return rest_ensure_response(
            $service->export_tickets(
                $request->get_params()
            )
        );
    }

    public static function import_tickets(
        WP_REST_Request $request
    ) {

        $service =
            new SweetDesk_Ticket_Service();

        return rest_ensure_response(
            $service->import_tickets(
                $request->get_json_params()
            )
        );
    }

    public static function create_message(
        WP_REST_Request $request
    ) {

        $service =
            new SweetDesk_Ticket_Service();

        return rest_ensure_response(
            $service->create_message(
                absint($request['id']),
                $request->get_json_params()
            )
        );
    }

    public static function get_messages(
        WP_REST_Request $request
    ) {

        $service =
            new SweetDesk_Ticket_Service();

        return rest_ensure_response(
            $service->get_messages(
                absint(
                    $request['id']
                )
            )
        );
    }

    public static function update_message(
        WP_REST_Request $request
    ) {

        $service =
            new SweetDesk_Ticket_Service();

        return rest_ensure_response(
            $service->update_message(
                absint($request['id']),
                $request->get_json_params()
            )
        );
    }

    public static function delete_message(
        WP_REST_Request $request
    ) {

        $service =
            new SweetDesk_Ticket_Service();

        return rest_ensure_response(
            $service->delete_message(
                absint(
                    $request['id']
                )
            )
        );
    }
}