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
}