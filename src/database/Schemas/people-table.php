<?php

function sweetdesk_create_people_table_sql() {

    global $wpdb;

    $table = $wpdb->prefix . 'sweetdesk_people';

    $charset_collate = $wpdb->get_charset_collate();

    return "
    CREATE TABLE {$table} (

        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

        wp_user_id BIGINT UNSIGNED NULL,

        first_name VARCHAR(100) NULL,
        last_name VARCHAR(100) NULL,

        email VARCHAR(255) NULL,

        role VARCHAR(50) NULL,

        avatar_url VARCHAR(500) NULL,

        is_active TINYINT(1) NOT NULL DEFAULT 1,

        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

        updated_at DATETIME NOT NULL
            DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP,

        KEY idx_wp_user_id (wp_user_id),
        KEY idx_email (email),
        KEY idx_role (role)

    ) {$charset_collate};
    ";
}
