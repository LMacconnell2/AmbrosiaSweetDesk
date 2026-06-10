<?php

class SweetDesk_API {

    public static function init() {

        require_once SWEETDESK_PATH .
            'src/api/routes/ticket-routes.php';

        // require_once SWEETDESK_PATH .
        //     'src/api/routes/client-routes.php';

        add_action(
            'rest_api_init',
            [ __CLASS__, 'register_routes' ]
        );
    }

    public static function register_routes() {

        (new SweetDesk_Ticket_Routes())
            ->register_routes();

        // (new SweetDesk_Client_Routes())
        //     ->register_routes();
    }
}