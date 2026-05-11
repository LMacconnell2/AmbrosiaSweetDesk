<?php
namespace SweetDesk\Admin;

class AdminMenu {

    public function __construct() {
        add_action('admin_menu', [$this, 'registerMenu']);
    }

    public function registerMenu() {

        // Main Menu
        add_menu_page(
            'SweetDesk',
            'SweetDesk',
            'manage_options',
            'sweetdesk',
            [$this, 'ticketsPage'],
            'dashicons-tickets',
            6
        );

        // Tickets
        add_submenu_page(
            'sweetdesk',
            'Tickets',
            'Tickets',
            'manage_options',
            'sweetdesk',
            [$this, 'ticketsPage']
        );

        // Analytics
        add_submenu_page(
            'sweetdesk',
            'Analytics',
            'Analytics',
            'manage_options',
            'sweetdesk-analytics',
            [$this, 'analyticsPage']
        );

        // Clients
        add_submenu_page(
            'sweetdesk',
            'Clients',
            'Clients',
            'manage_options',
            'sweetdesk-clients',
            [$this, 'clientsPage']
        );

        // People
        add_submenu_page(
            'sweetdesk',
            'People',
            'People',
            'manage_options',
            'sweetdesk-people',
            [$this, 'peoplePage']
        );

        // Teams
        add_submenu_page(
            'sweetdesk',
            'Teams',
            'Teams',
            'manage_options',
            'sweetdesk-teams',
            [$this, 'teamsPage']
        );
    }

    public function ticketsPage() {
        include plugin_dir_path(__FILE__) . 'tickets/tickets-list.php';
    }

    public function analyticsPage() {
        include plugin_dir_path(__FILE__) . 'analytics/analytics.php';
    }

    public function clientsPage() {
        include plugin_dir_path(__FILE__) . 'clients/clients.php';
    }

    public function peoplePage() {
        include plugin_dir_path(__FILE__) . 'people/people.php';
    }

    public function teamsPage() {
        include plugin_dir_path(__FILE__) . 'teams/teams.php';
    }
}