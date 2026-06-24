<?php
namespace SweetDesk\Admin;

class AdminMenu {

    public function __construct() {
        // add_action('admin_menu', [$this, 'registerMenu']);
        // add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
    }

    public function register_menu() {

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

        // Settings
        add_submenu_page(
            'sweetdesk',
            'Settings',
            'Settings',
            'manage_options',
            'sweetdesk-settings',
            [$this, 'settingsPage']
        );

        // Ticket detail — hidden from menu, reachable via ticket list links
        add_submenu_page(
            null,
            'Ticket Detail',
            'Ticket Detail',
            'manage_options',
            'sweetdesk-ticket-detail',
            [$this, 'ticketDetailPage']
        );
    }

    public function enqueue_admin_assets($hook) {

        /*
         * Base admin styles
         * Loaded on ALL SweetDesk pages
         */
        $sweetdeskHooks = [
            'toplevel_page_sweetdesk',
            'sweetdesk_page_sweetdesk-analytics',
            'sweetdesk_page_sweetdesk-clients',
            'sweetdesk_page_sweetdesk-people',
            'sweetdesk_page_sweetdesk-teams',
            'sweetdesk_page_sweetdesk-settings',
            'admin_page_sweetdesk-ticket-detail'
        ];

        if (in_array($hook, $sweetdeskHooks)) {

            wp_enqueue_style(
                'sweetdesk-admin',
                plugin_dir_url(__FILE__) . '../../../assets/css/admin.css',
                [],
                SWEETDESK_VERSION
            );
        }

        if ($hook === 'toplevel_page_sweetdesk') {
            wp_enqueue_style(
                'sweetdesk-badge-helpers',
                plugin_dir_url(__FILE__) . '../../../assets/css/badge-helpers.css',
                [],
                SWEETDESK_VERSION
            );

            wp_enqueue_style(
                'sweetdesk-tickets',
                plugin_dir_url(__FILE__) . '../../../assets/css/tickets-list.css',
                ['sweetdesk-badge-helpers'],
                SWEETDESK_VERSION
            );

            wp_enqueue_script(
                'sweetdesk-badge-helpers',
                plugin_dir_url(__FILE__) . '../../../assets/js/badge-helpers.js',
                [],
                SWEETDESK_VERSION,
                true
            );

            wp_enqueue_script(
                'sweetdesk-tickets',
                plugin_dir_url(__FILE__) . '../../../assets/js/tickets.js',
                ['sweetdesk-badge-helpers'],
                SWEETDESK_VERSION,
                true
            );

            wp_localize_script(
                'sweetdesk-tickets',
                'SweetDesk',
                [
                    'apiUrl' => rest_url('sweetdesk/v1'),
                    'nonce' => wp_create_nonce('wp_rest'),
                    'ticketDetailBase' => admin_url(
                        'admin.php?page=sweetdesk-ticket-detail&ticket_id='
                    )
                ]
            );
        }

        /*
         * Clients page styles
         */
        if ($hook === 'sweetdesk_page_sweetdesk-clients') {

            wp_enqueue_style(
                'sweetdesk-clients',
                plugin_dir_url(__FILE__) . '../../../assets/css/clients.css',
                [],
                SWEETDESK_VERSION
            );

            wp_enqueue_script(
                'sweetdesk-clients',
                plugin_dir_url(__FILE__) . '../../../assets/js/clients.js',
                [],
                SWEETDESK_VERSION,
                true
            );
        }

        /*
         * People page styles
         */
        if ($hook === 'sweetdesk_page_sweetdesk-people') {

            wp_enqueue_style(
                'sweetdesk-people',
                plugin_dir_url(__FILE__) . '../../../assets/css/people.css',
                [],
                SWEETDESK_VERSION
            );

            wp_enqueue_script(
                'sweetdesk-people',
                plugin_dir_url(__FILE__) . '../../../assets/js/people.js',
                [],
                SWEETDESK_VERSION,
                true
            );
        }

        /*
         * Teams page styles
         */
        if ($hook === 'sweetdesk_page_sweetdesk-teams') {

            wp_enqueue_style(
                'sweetdesk-teams',
                plugin_dir_url(__FILE__) . '../../../assets/css/teams.css',
                [],
                SWEETDESK_VERSION
            );

            wp_enqueue_script(
                'sweetdesk-teams',
                plugin_dir_url(__FILE__) . '../../../assets/js/teams.js',
                [],
                SWEETDESK_VERSION,
                true
            );
        }
        if ($hook === 'sweetdesk_page_sweetdesk-analytics') {

            wp_enqueue_style(
                'sweetdesk-analytics',
                plugin_dir_url(__FILE__) . '../../../assets/css/analytics.css',
                [],
                SWEETDESK_VERSION
            );

            wp_enqueue_script(
                'sweetdesk-analytics',
                plugin_dir_url(__FILE__) . '../../../assets/js/analytics.js',
                [],
                SWEETDESK_VERSION,
                true
            );
        }

        /*
         * Settings page styles
         */
        if ($hook === 'sweetdesk_page_sweetdesk-settings') {

            wp_enqueue_style(
                'sweetdesk-settings',
                plugin_dir_url(__FILE__) . '../../../assets/css/settings.css',
                [],
                SWEETDESK_VERSION
            );

            wp_enqueue_script(
                'sweetdesk-analytics',
                plugin_dir_url(__FILE__) . '../../../assets/js/settings.js',
                [],
                SWEETDESK_VERSION,
                true
            );
        }

        /*
         * Ticket Detail page styles
         */
        if ($hook === 'admin_page_sweetdesk-ticket-detail') {

            wp_enqueue_style(
                'sweetdesk-badge-helpers',
                plugin_dir_url(__FILE__) . '../../../assets/css/badge-helpers.css',
                [],
                SWEETDESK_VERSION
            );

            wp_enqueue_style(
                'sweetdesk-ticket-detail',
                plugin_dir_url(__FILE__) . '../../../assets/css/ticket-detail.css',
                ['sweetdesk-badge-helpers'],
                SWEETDESK_VERSION
            );

            wp_enqueue_script(
                'sweetdesk-badge-helpers',
                plugin_dir_url(__FILE__) . '../../../assets/js/badge-helpers.js',
                [],
                SWEETDESK_VERSION,
                true
            );

            wp_enqueue_script(
                'sweetdesk-ticket-detail',
                plugin_dir_url(__FILE__) . '../../../assets/js/ticket-detail.js',
                ['sweetdesk-badge-helpers'],
                SWEETDESK_VERSION,
                true
            );

            wp_localize_script(
                'sweetdesk-ticket-detail',
                'SweetDesk',
                [
                    'apiUrl' => rest_url('sweetdesk/v1'),
                    'nonce' => wp_create_nonce('wp_rest'),
                    'canSeeInternal' => current_user_can('manage_options'),
                    'ticketsUrl' => admin_url('admin.php?page=sweetdesk')
                ]
            );
        }
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

    public function settingsPage() {
        include plugin_dir_path(__FILE__) . 'settings/settings.php';
    }

    public function ticketDetailPage() {
        include plugin_dir_path(__FILE__) . 'tickets/ticket-detail.php';
    }
}