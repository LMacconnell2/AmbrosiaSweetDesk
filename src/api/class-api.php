<?php

class SweetDesk_API {

    public static function init() {

        require_once SWEETDESK_PATH .
            'src/api/routes/ticket-routes.php';

        require_once SWEETDESK_PATH .
            'src/api/routes/client-routes.php';

        require_once SWEETDESK_PATH .
            'src/api/routes/people-routes.php';

        require_once SWEETDESK_PATH .
            'src/api/routes/team-routes.php';

        require_once SWEETDESK_PATH .
            'src/api/routes/settings-status-routes.php';

        require_once SWEETDESK_PATH .
            'src/api/routes/settings-fields-routes.php';

        require_once SWEETDESK_PATH .
            'src/api/routes/settings-email-routes.php';

        add_action(
            'rest_api_init',
            [ __CLASS__, 'register_routes' ]
        );
    }

    public static function register_routes() {

        (new SweetDesk_Ticket_Routes())
            ->register_routes();

        (new SweetDesk_Client_Routes())
            ->register_routes();

        (new SweetDesk_People_Routes())
            ->register_routes();

        (new SweetDesk_Team_Routes())
            ->register_routes();

        (new SweetDesk_Settings_Status_Routes())
            ->register_routes();

        (new SweetDesk_Settings_Fields_Routes())
            ->register_routes();

        (new SweetDesk_Email_Settings_Routes())
            ->register_routes();
    }
}