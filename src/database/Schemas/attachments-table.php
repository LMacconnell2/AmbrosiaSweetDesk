<?php

function sweetdesk_create_attachments_table_sql() {

    global $wpdb;

    $table = $wpdb->prefix . 'sweetdesk_attachments';

    $charset_collate = $wpdb->get_charset_collate();

    return "
    CREATE TABLE {$table} (

        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

        ticket_id BIGINT UNSIGNED NOT NULL,

        uploaded_by BIGINT UNSIGNED NULL,

        file_name VARCHAR(255) NOT NULL,
        file_url VARCHAR(500) NOT NULL,

        mime_type VARCHAR(100) NULL,

        file_size BIGINT UNSIGNED NULL,

        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

        KEY idx_ticket_id (ticket_id),
        KEY idx_uploaded_by (uploaded_by)

    ) {$charset_collate};
    ";
}