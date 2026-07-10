<?php

if (!defined('ABSPATH')) {
    exit;
}

class SweetDesk_Database_Upgrader
{
    public static function maybe_upgrade(): void
    {
        $installed_version = get_option(
            'sweetdesk_db_version',
            '0.0.0'
        );

        if (
            version_compare(
                $installed_version,
                SWEETDESK_DB_VERSION,
                '>='
            )
        ) {
            return;
        }

        SweetDesk_Activator::load_dependencies();
        SweetDesk_Activator::update_schema();

        update_option(
            'sweetdesk_db_version',
            SWEETDESK_DB_VERSION,
            false
        );
    }
}