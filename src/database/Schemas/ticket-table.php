<?php

function sweetdesk_create_ticket_table_sql() {

    global $wpdb;

    $table = $wpdb->prefix . 'sweetdesk_tickets';

    $charset_collate = $wpdb->get_charset_collate();

    return "
    CREATE TABLE {$table} (

        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

        client_id BIGINT UNSIGNED NULL,
        assigned_to BIGINT UNSIGNED NULL,
        created_by BIGINT UNSIGNED NULL,

        title VARCHAR(255) NOT NULL,

        description LONGTEXT NULL,

        status VARCHAR(50) NOT NULL DEFAULT 'open',

        priority VARCHAR(50) NOT NULL DEFAULT 'normal',

        source VARCHAR(50) NULL,

        due_date DATETIME NULL,

        created_at DATETIME NOT NULL
            DEFAULT CURRENT_TIMESTAMP,

        updated_at DATETIME NOT NULL
            DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP,

        KEY idx_client_id (client_id),

        KEY idx_assigned_to (assigned_to),

        KEY idx_created_by (created_by),

        KEY idx_status (status),

        KEY idx_priority (priority),

        KEY idx_created_at (created_at)

    ) {$charset_collate};
    ";
}