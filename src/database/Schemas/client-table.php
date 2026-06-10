<?php

function sweetdesk_create_client_table_sql() {

    global $wpdb;

    $table = $wpdb->prefix . 'sweetdesk_clients';

    $charset_collate = $wpdb->get_charset_collate();

    return "
    CREATE TABLE {$table} (

        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

        name VARCHAR(255) NOT NULL,

        email VARCHAR(255) NULL,
        phone VARCHAR(50) NULL,

        website VARCHAR(255) NULL,

        notes LONGTEXT NULL,

        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

        updated_at DATETIME NOT NULL
            DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP,

        KEY idx_name (name),
        KEY idx_email (email)

    ) {$charset_collate};
    ";
}