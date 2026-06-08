<?php

class SweetDesk_Ticket_Model {

    public static function all() {

        global $wpdb;

        $table = $wpdb->prefix .
            'sweetdesk_tickets';

        return $wpdb->get_results(
            "SELECT * FROM $table",
            ARRAY_A
        );
    }
}