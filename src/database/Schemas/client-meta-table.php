<?php

function sweetdesk_create_client_meta_table_sql() {

    global $wpdb;

    $table = $wpdb->prefix . 'sweetdesk_client_meta';

    $charset_collate = $wpdb->get_charset_collate();

    return "
    CREATE TABLE {$table} ( (

    meta_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    client_id BIGINT UNSIGNED NOT NULL,

    meta_key VARCHAR(191) NOT NULL,
    meta_value LONGTEXT NULL,

    KEY idx_client_id (client_id),
    KEY idx_meta_key (meta_key),

    UNIQUE KEY unique_client_meta (
        client_id,
        meta_key
    )

) {$charset_collate};
    ";
}