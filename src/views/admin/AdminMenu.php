<?php
namespace SweetDesk\Admin;

class AdminMenu {
    public function __construct() {
        add_action('admin_menu', [$this, 'registerMenu']);
    }

    public function registerMenu() {
        add_menu_page(
            'SweetDesk',
            'SweetDesk',
            'manage_options',
            'sweetdesk',
            [$this, 'ticketsPage'],
            'dashicons-tickets',
            6
        );

        add_submenu_page(
            'sweetdesk',
            'Tickets',
            'Tickets',
            'manage_options',
            'sweetdesk',
            [$this, 'ticketsPage']
        );

        add_submenu_page(
            'sweetdesk',
            'Analytics',
            'Analytics',
            'manage_options',
            'sweetdesk-analytics',
            [$this, 'analyticsPage']
        );
    }

    public function ticketsPage() {
        include plugin_dir_path(__FILE__) . '/tickets/tickets-list.php';
    }

    public function analyticsPage() {
        include plugin_dir_path(__FILE__) . '/tickets/analytics.php';
    }
}