<?php

function sweetdesk_create_people_teams_table_sql() {

    global $wpdb;

    $table = $wpdb->prefix . 'sweetdesk_people_teams';

    $charset_collate = $wpdb->get_charset_collate();

    return "
    CREATE TABLE {$table} (

        person_id BIGINT UNSIGNED NOT NULL,

        team_id BIGINT UNSIGNED NOT NULL,

        assigned_at DATETIME NOT NULL
            DEFAULT CURRENT_TIMESTAMP,

        PRIMARY KEY (
            person_id,
            team_id
        ),

        KEY idx_team_id (team_id)

    ) {$charset_collate};
    ";
}
