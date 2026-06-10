<?php

function sweetdesk_create_activity_table_sql() {

    global $wpdb;

    $table = $wpdb->prefix . 'sweetdesk_ticket_activity';

    $charset_collate = $wpdb->get_charset_collate();

    return "
    CREATE TABLE {$table} (

        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

        ticket_id BIGINT UNSIGNED NOT NULL,

        person_id BIGINT UNSIGNED NULL,

        activity_type VARCHAR(100) NOT NULL,

        activity_message TEXT NULL,

        old_value LONGTEXT NULL,
        new_value LONGTEXT NULL,

        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

        KEY idx_ticket_id (ticket_id),
        KEY idx_person_id (person_id),
        KEY idx_activity_type (activity_type)

    ) {$charset_collate};
    ";
}
