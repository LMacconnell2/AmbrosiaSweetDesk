<?php

if (!defined('ABSPATH')) {
    exit;
}

function sweetdesk_create_ticket_fields_table_sql(): string
{
    global $wpdb;

    $table = $wpdb->prefix . 'sweetdesk_ticket_fields';
    $charset_collate = $wpdb->get_charset_collate();

    return "
        CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(150) NOT NULL,
            field_key varchar(150) NOT NULL,
            field_type varchar(50) NOT NULL DEFAULT 'text',
            is_required tinyint(1) NOT NULL DEFAULT 0,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            sort_order int(10) unsigned NOT NULL DEFAULT 0,
            options longtext NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY uq_sweetdesk_ticket_field_key (field_key),
            KEY idx_sweetdesk_ticket_field_active (is_active),
            KEY idx_sweetdesk_ticket_field_sort (sort_order)
        ) {$charset_collate};
    ";
}