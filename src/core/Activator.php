<?php

class SweetDesk_Activator {
    require_once SWEETDESK_PATH .
    'database/class-installer.php';

    public static function activate() {

        error_log(
            'Ambrosia SweetDesk plugin activated.'
        );

        SweetDesk_Installer::install();

        flush_rewrite_rules();
    }
}