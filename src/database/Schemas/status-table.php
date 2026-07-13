<?php

if (!defined('ABSPATH')) {
    exit;
}

function sweetdesk_create_statuses_table_sql(): string
{
    global $wpdb;

    $table = $wpdb->prefix . 'sweetdesk_statuses';
    $charset_collate = $wpdb->get_charset_collate();

    return "
        CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            slug varchar(100) NOT NULL,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            sort_order int(10) unsigned NOT NULL DEFAULT 0,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY uq_sweetdesk_status_slug (slug),
            KEY idx_sweetdesk_status_active (is_active),
            KEY idx_sweetdesk_status_sort (sort_order)
        ) {$charset_collate};
    ";
}