<?php

if (!defined('ABSPATH')) {
    exit;
}

class SweetDesk_Activator
{
    public static function activate(): void
    {
        self::load_dependencies();
        self::update_schema();

        update_option(
            'sweetdesk_db_version',
            SWEETDESK_DB_VERSION,
            false
        );
    }

    public static function load_dependencies(): void
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $schema_path = SWEETDESK_PATH . 'src/database/Schemas/';

        require_once $schema_path . 'activity-table.php';
        require_once $schema_path . 'attachment-table.php';
        require_once $schema_path . 'client-table.php';
        require_once $schema_path . 'client-meta-table.php';
        require_once $schema_path . 'people-table.php';
        require_once $schema_path . 'people-meta-table.php';
        require_once $schema_path . 'team-table.php';
        require_once $schema_path . 'team-meta-table.php';
        require_once $schema_path . 'people-teams-table.php';
        require_once $schema_path . 'ticket-table.php';
        require_once $schema_path . 'ticket-meta-table.php';
        require_once $schema_path . 'ticket-messages-table.php';
        require_once $schema_path . 'status-table.php';
        require_once $schema_path . 'ticket-fields-table.php';
        require_once $schema_path . 'feedback-table.php';
    }

    public static function update_schema(): void
    {
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

        dbDelta(sweetdesk_create_statuses_table_sql());
        dbDelta(sweetdesk_create_ticket_fields_table_sql());
        dbDelta(sweetdesk_create_feedback_table_sql());
    }

    private static function add_settings_capabilities(): void
    {
        $administrator = get_role('administrator');

        if (!$administrator) {
            return;
        }

        $administrator->add_cap('sweetdesk_manage_settings');
        $administrator->add_cap('sweetdesk_delete_settings');
    }
}