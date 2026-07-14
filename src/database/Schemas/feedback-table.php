<?php

if (!defined('ABSPATH')) {
    exit;
}

function sweetdesk_create_feedback_table_sql(): string
{
    global $wpdb;

    $table = $wpdb->prefix . 'sweetdesk_ticket_feedback';
    $charset_collate = $wpdb->get_charset_collate();

    return "
        CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            ticket_id BIGINT UNSIGNED NOT NULL,
            person_id BIGINT UNSIGNED NULL,
            score TINYINT UNSIGNED NOT NULL,
            comment TEXT NULL,
            submitted_at DATETIME NOT NULL,
            updated_at DATETIME NULL,

            PRIMARY KEY (id),
            UNIQUE KEY uq_ticket_feedback_ticket (ticket_id),
            KEY idx_feedback_score (score),
            KEY idx_feedback_submitted_at (submitted_at),
            KEY idx_feedback_ticket_submitted (ticket_id, submitted_at),
            KEY idx_feedback_person (person_id)
        ) {$charset_collate};
    ";
}