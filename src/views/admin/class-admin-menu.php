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
            'sweetdesk_page_sweetdesk-settings'
        ];

        if (in_array($hook, $sweetdeskHooks)) {

            wp_enqueue_style(
                'sweetdesk-theme',
                plugin_dir_url(__FILE__) . '../../../assets/css/ambrosia-theme.css',
                [],
                SWEETDESK_VERSION
            );

            wp_enqueue_style(
                'sweetdesk-admin',
                plugin_dir_url(__FILE__) . '../../../assets/css/admin.css',
                ['sweetdesk-theme'],
                SWEETDESK_VERSION
            );
        }

        if ($hook === 'toplevel_page_sweetdesk') {
            wp_enqueue_style(
                'sweetdesk-tickets',
                plugin_dir_url(__FILE__) . '../../../assets/css/tickets.css',
                ['sweetdesk-theme'],
                SWEETDESK_VERSION
            );

            wp_enqueue_script(
                'sweetdesk-tickets',
                plugin_dir_url(__FILE__) . '../../../assets/js/tickets.js',
                [],
                SWEETDESK_VERSION,
                true
            );

            wp_localize_script(
                'sweetdesk-tickets',
                'SweetDesk',
                [
                    'apiUrl' => rest_url('sweetdesk/v1'),
                    'nonce' => wp_create_nonce('wp_rest')
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
                ['sweetdesk-theme'],
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
                ['sweetdesk-theme'],
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
                ['sweetdesk-theme'],
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
                ['sweetdesk-theme'],
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
                ['sweetdesk-theme'],
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
}