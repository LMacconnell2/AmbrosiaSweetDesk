<?php

class SweetDesk_API {

    public static function init() {

        require_once SWEETDESK_PATH .
            'api/class-tickets-controller.php';

        require_once SWEETDESK_PATH .
            'api/class-clients-controller.php';

        add_action(
            'rest_api_init',
            [ __CLASS__, 'register_routes' ]
        );
    }

    public static function register_routes() {

        $tickets = new SweetDesk_Tickets_Controller();
        $tickets->register_routes();

        $clients = new SweetDesk_Clients_Controller();
        $clients->register_routes();
    }
}