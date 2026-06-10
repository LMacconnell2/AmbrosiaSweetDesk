<?php

class SweetDesk_Activator {

    public static function activate() {

        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        require_once SWEETDESK_PATH .
            'database/Schemas/activity-table.php';

        require_once SWEETDESK_PATH .
            'database/Schemas/attachment-table.php';

        require_once SWEETDESK_PATH . 
                'database/Schemas/client-table.php';

        require_once SWEETDESK_PATH . 
                'database/Schemas/client-meta-table.php';

        require_once SWEETDESK_PATH . 
                'database/Schemas/people-table.php';

        require_once SWEETDESK_PATH . 
                'database/Schemas/people-meta-table.php';

        require_once SWEETDESK_PATH . 
                'database/Schemas/team-table.php';

        require_once SWEETDESK_PATH . 
                'database/Schemas/team-meta-table.php';

        require_once SWEETDESK_PATH . 
                'database/Schemas/people-teams-table.php';

        require_once SWEETDESK_PATH . 
                'database/Schemas/ticket-table.php';
                
        require_once SWEETDESK_PATH . 
                'database/Schemas/ticket-meta-table.php';

        require_once SWEETDESK_PATH . 
                'database/Schemas/ticket-messages-table.php';


        dbDelta(sweetdesk_create_client_table_sql());
        dbDelta(sweetdesk_create_client_meta_table_sql());

        dbDelta(sweetdesk_create_people_table_sql());
        dbDelta(sweetdesk_create_people_meta_table_sql());

        dbDelta(sweetdesk_create_team_table_sql());
        dbDelta(sweetdesk_create_team_meta_table_sql());

        dbDelta(sweetdesk_create_people_teams_table_sql());

        dbDelta(sweetdesk_create_ticket_table_sql());

        dbDelta(sweetdesk_create_ticket_meta_table_sql());
        dbDelta(sweetdesk_create_ticket_messages_table_sql());

        dbDelta(sweetdesk_create_attachments_table_sql());
        dbDelta(sweetdesk_create_activity_table_sql());


        update_option(
            'sweetdesk_db_version',
            SWEETDESK_VERSION
        );
    }
}