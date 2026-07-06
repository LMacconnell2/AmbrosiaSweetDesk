<?php

function sweetdesk_create_team_table_sql() {

    global $wpdb;

    $table = $wpdb->prefix . 'sweetdesk_teams';

    $charset_collate = $wpdb->get_charset_collate();

    return "
    CREATE TABLE {$table} (

        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

        name VARCHAR(255) NOT NULL,

        description TEXT NULL,

        color VARCHAR(20) NULL,

        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

        updated_at DATETIME NOT NULL
            DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP,

        KEY idx_name (name)

    ) {$charset_collate};
    ";
}