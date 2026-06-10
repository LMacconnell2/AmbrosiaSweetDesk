<?php

function sweetdesk_create_ticket_messages_table_sql() {

    global $wpdb;

    $table = $wpdb->prefix . 'sweetdesk_ticket_messages';

    $charset_collate = $wpdb->get_charset_collate();

    return "
    CREATE TABLE {$table} (

        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

        ticket_id BIGINT UNSIGNED NOT NULL,

        person_id BIGINT UNSIGNED NULL,

        reply_type VARCHAR(50) NOT NULL
            DEFAULT 'comment',

        body LONGTEXT NOT NULL,

        visibility VARCHAR(50) NOT NULL
            DEFAULT 'internal',

        edited TINYINT(1) NOT NULL
            DEFAULT 0,

        reply_to_id BIGINT UNSIGNED NULL,

        created_at DATETIME NOT NULL
            DEFAULT CURRENT_TIMESTAMP,

        updated_at DATETIME NOT NULL
            DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP,

        KEY idx_ticket_id (ticket_id),

        KEY idx_person_id (person_id),

        KEY idx_reply_to_id (reply_to_id),

        KEY idx_created_at (created_at)

    ) {$charset_collate};
    ";
}