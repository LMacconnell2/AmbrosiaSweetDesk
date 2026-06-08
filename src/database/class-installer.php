<?php

class SweetDesk_Installer {

    public static function install() {

        global $wpdb;

        require_once ABSPATH .
            'wp-admin/includes/upgrade.php';

        $charset_collate =
            $wpdb->get_charset_collate();

        $tickets =
            $wpdb->prefix . 'sweetdesk_tickets';

        $sql = "CREATE TABLE $tickets (

            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

            title VARCHAR(255) NOT NULL,

            description LONGTEXT NULL,

            status VARCHAR(50) DEFAULT 'open',

            created_at DATETIME DEFAULT CURRENT_TIMESTAMP

        ) $charset_collate;";

        dbDelta($sql);
    }
}