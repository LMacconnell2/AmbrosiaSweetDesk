<?php

function sweetdesk_create_attachments_table_sql() {

    global $wpdb;

    $table = $wpdb->prefix . 'sweetdesk_attachments';

    $charset_collate = $wpdb->get_charset_collate();

    return "
        CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            ticket_id bigint(20) unsigned NOT NULL,
            uploaded_by bigint(20) unsigned NOT NULL,
            file_name varchar(255) NOT NULL,
            file_url text NOT NULL,
            mime_type varchar(100) NOT NULL,
            file_size bigint(20) unsigned NOT NULL DEFAULT 0,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY idx_sweetdesk_attachment_ticket (ticket_id),
            KEY idx_sweetdesk_attachment_uploader (uploaded_by),
            KEY idx_sweetdesk_attachment_created (created_at)
        ) {$charset_collate};
    ";
}