<?php
namespace SweetDesk\Admin;

class AdminMenu {

    public function __construct() {
        // add_action('admin_menu', [$this, 'registerMenu']);
        // add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
    }

    private function enqueue_message_editor(
        $script_handle,
        $script_file,
        $script_deps = []
    ) {
        $plugin_url = plugin_dir_url(__FILE__) . '../../../';

        wp_enqueue_style(
            'quill-snow',
            $plugin_url . 'assets/vendor/quill/quill.snow.css',
            [],
            '1.3.7'
        );

        wp_enqueue_style(
            'sweetdesk-quill-editor',
            $plugin_url . 'assets/css/sweetdesk-editor.css',
            ['quill-snow', 'sweetdesk-theme'],
            SWEETDESK_VERSION
        );

        wp_enqueue_script(
            'quill',
            $plugin_url . 'assets/vendor/quill/quill.min.js',
            [],
            '1.3.7',
            true
        );

        wp_enqueue_script(
            'sweetdesk-editor',
            $plugin_url . 'assets/js/sweetdesk-editor.js',
            ['quill'],
            SWEETDESK_VERSION,
            true
        );

        wp_enqueue_script(
            $script_handle,
            $plugin_url . 'assets/js/' . $script_file,
            array_merge(['sweetdesk-editor'], $script_deps),
            SWEETDESK_VERSION,
            true
        );
    }

    public function register_menu() {

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

        add_submenu_page(
            'sweetdesk',
            'Clients',
            'Clients',
            'manage_options',
            'sweetdesk-clients',
            [$this, 'clientsPage']
        );

        add_submenu_page(
            'sweetdesk',
            'People',
            'People',
            'manage_options',
            'sweetdesk-people',
            [$this, 'peoplePage']
        );

        add_submenu_page(
            'sweetdesk',
            'Teams',
            'Teams',
            'manage_options',
            'sweetdesk-teams',
            [$this, 'teamsPage']
        );

        add_submenu_page(
            'sweetdesk',
            'Settings',
            'Settings',
            'manage_options',
            'sweetdesk-settings',
            [$this, 'settingsPage']
        );

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
                'sweetdesk-theme',
                plugin_dir_url(__FILE__) . '../../../assets/css/ambrosia-theme.css',
                [],
                SWEETDESK_VERSION
            );

            wp_enqueue_style(
                'sweetdesk-components',
                plugin_dir_url(__FILE__) . '../../../assets/css/ambrosia-components.css',
                ['sweetdesk-theme'],
                SWEETDESK_VERSION
            );

            wp_enqueue_style(
                'sweetdesk-admin',
                plugin_dir_url(__FILE__) . '../../../assets/css/admin.css',
                ['sweetdesk-components'],
                SWEETDESK_VERSION
            );
        }

        if ($hook === 'toplevel_page_sweetdesk') {

            $plugin_url = plugin_dir_url(__FILE__) . '../../../';

            wp_enqueue_style(
                'sweetdesk-badge-helpers',
                $plugin_url . 'assets/css/badge-helpers.css',
                ['sweetdesk-components'],
                SWEETDESK_VERSION
            );

            wp_enqueue_style(
                'sweetdesk-tickets',
                $plugin_url . 'assets/css/tickets-list.css',
                ['sweetdesk-badge-helpers'],
                SWEETDESK_VERSION
            );

            wp_enqueue_script(
                'sweetdesk-badge-helpers',
                $plugin_url . 'assets/js/badge-helpers.js',
                [],
                SWEETDESK_VERSION,
                true
            );

            $this->enqueue_ticket_scripts();

            wp_localize_script(
                'sweetdesk-tickets-state',
                'SweetDesk',
                [
                    'apiUrl' => esc_url_raw(
                        rest_url('sweetdesk/v1')
                    ),
                    'nonce' => wp_create_nonce('wp_rest'),
                    'ticketDetailBase' => admin_url(
                        'admin.php?page=sweetdesk-ticket-detail&ticket_id='
                    ),
                ]
            );
        }

        if ($hook === 'sweetdesk_page_sweetdesk-clients') {

            wp_enqueue_style(
                'sweetdesk-clients',
                plugin_dir_url(__FILE__) . '../../../assets/css/clients.css',
                ['sweetdesk-components'],
                SWEETDESK_VERSION
            );

            wp_enqueue_script(
                'sweetdesk-clients',
                plugin_dir_url(__FILE__) . '../../../assets/js/clients.js',
                [],
                SWEETDESK_VERSION,
                true
            );

            wp_localize_script('sweetdesk-clients', 'sweetdeskClients', [
                'apiUrl' => esc_url_raw(rest_url('sweetdesk/v1')),
                'nonce'  => wp_create_nonce('wp_rest'),
            ]);
        }

        if ($hook === 'sweetdesk_page_sweetdesk-people') {

            wp_enqueue_style(
                'sweetdesk-people',
                plugin_dir_url(__FILE__) . '../../../assets/css/people.css',
                ['sweetdesk-components'],
                SWEETDESK_VERSION
            );

            wp_enqueue_script(
                'sweetdesk-team-color-helpers',
                plugin_dir_url(__FILE__) . '../../../assets/js/team-color-helpers.js',
                [],
                SWEETDESK_VERSION,
                true
            );

            wp_enqueue_script(
                'sweetdesk-people',
                plugin_dir_url(__FILE__) . '../../../assets/js/people.js',
                ['sweetdesk-team-color-helpers'],
                SWEETDESK_VERSION,
                true
            );

            wp_localize_script('sweetdesk-people', 'sweetdeskPeople', [
                'apiUrl' => esc_url_raw(rest_url('sweetdesk/v1')),
                'nonce'  => wp_create_nonce('wp_rest'),
            ]);
        }

        if ($hook === 'sweetdesk_page_sweetdesk-teams') {

            wp_enqueue_style(
                'sweetdesk-teams',
                plugin_dir_url(__FILE__) . '../../../assets/css/teams.css',
                ['sweetdesk-components'],
                SWEETDESK_VERSION
            );

            wp_enqueue_script(
                'sweetdesk-team-color-helpers',
                plugin_dir_url(__FILE__) . '../../../assets/js/team-color-helpers.js',
                [],
                SWEETDESK_VERSION,
                true
            );

            wp_enqueue_script(
                'sweetdesk-teams',
                plugin_dir_url(__FILE__) . '../../../assets/js/teams.js',
                ['sweetdesk-team-color-helpers'],
                SWEETDESK_VERSION,
                true
            );

            wp_localize_script(
                'sweetdesk-teams',
                'sweetdeskTeams',
                [
                    'apiBase' => esc_url_raw(rest_url('sweetdesk/v1/teams')),
                    'peopleApiUrl' => esc_url_raw(rest_url('sweetdesk/v1')),
                    'nonce'   => wp_create_nonce('wp_rest'),
                ]
            );
        }

        if ($hook === 'sweetdesk_page_sweetdesk-analytics') {

            wp_enqueue_style(
                'sweetdesk-analytics',
                plugin_dir_url(__FILE__) . '../../../assets/css/analytics.css',
                ['sweetdesk-components'],
                SWEETDESK_VERSION
            );

            wp_enqueue_script(
                'sweetdesk-analytics',
                plugin_dir_url(__FILE__) . '../../../assets/js/analytics.js',
                [],
                SWEETDESK_VERSION,
                true
            );

            wp_localize_script(
                'sweetdesk-analytics',
                'sweetdeskAnalytics',
                [
                    'restRoot' => esc_url_raw(rest_url()),
                    'nonce' => wp_create_nonce('wp_rest'),
                    'ticketPageUrl' => admin_url(
                        'admin.php?page=sweetdesk-tickets'
                    ),
                    'oldestTicketLimit' => 3,
                ]
            );
        }

        if ($hook === 'sweetdesk_page_sweetdesk-settings') {

            wp_enqueue_style(
                'sweetdesk-settings',
                plugin_dir_url(__FILE__) . '../../../assets/css/settings.css',
                ['sweetdesk-components'],
                SWEETDESK_VERSION
            );

            wp_enqueue_script(
                'sweetdesk-settings',
                plugin_dir_url(__FILE__) . '../../../assets/js/settings.js',
                [],
                SWEETDESK_VERSION,
                true
            );

            wp_localize_script(
                'sweetdesk-settings',
                'sweetdeskSettings',
                [
                    'restUrl' => esc_url_raw(
                        rest_url('sweetdesk/v1/settings/')
                    ),
                    'nonce' => wp_create_nonce('wp_rest'),
                ]
            );
        }

        if ($hook === 'admin_page_sweetdesk-ticket-detail') {

            wp_enqueue_style(
                'sweetdesk-badge-helpers',
                plugin_dir_url(__FILE__) . '../../../assets/css/badge-helpers.css',
                ['sweetdesk-components'],
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

            $this->enqueue_message_editor(
                'sweetdesk-ticket-detail',
                'ticket-detail.js',
                ['sweetdesk-badge-helpers']
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

    private function enqueue_ticket_scripts(): void
    {
        $plugin_url = plugin_dir_url(__FILE__) . '../../../';

        /*
        * Load Quill and the shared SweetDesk editor.
        */
        wp_enqueue_style(
            'quill-snow',
            $plugin_url . 'assets/vendor/quill/quill.snow.css',
            [],
            '1.3.7'
        );

        wp_enqueue_style(
            'sweetdesk-quill-editor',
            $plugin_url . 'assets/css/sweetdesk-editor.css',
            [
                'quill-snow',
                'sweetdesk-theme',
            ],
            SWEETDESK_VERSION
        );

        wp_enqueue_script(
            'quill',
            $plugin_url . 'assets/vendor/quill/quill.min.js',
            [],
            '1.3.7',
            true
        );

        wp_enqueue_script(
            'sweetdesk-editor',
            $plugin_url . 'assets/js/sweetdesk-editor.js',
            ['quill'],
            SWEETDESK_VERSION,
            true
        );

        /*
        * Shared ticket state.
        *
        * This is loaded first because all other ticket modules use
        * window.SweetDeskTickets.
        */
        wp_enqueue_script(
            'sweetdesk-tickets-state',
            $plugin_url . 'assets/js/tickets/tickets-state.js',
            [
                'sweetdesk-editor',
                'sweetdesk-badge-helpers',
            ],
            SWEETDESK_VERSION,
            true
        );

        /*
        * Ticket REST API functions.
        */
        wp_enqueue_script(
            'sweetdesk-tickets-api',
            $plugin_url . 'assets/js/tickets/tickets-api.js',
            ['sweetdesk-tickets-state'],
            SWEETDESK_VERSION,
            true
        );

        /*
        * Ticket Lookup API functions.
        */
        wp_enqueue_script(
            'sweetdesk-tickets-lookup',
            $plugin_url . 'assets/js/tickets/tickets-lookups.js',
            ['sweetdesk-tickets-state'],
            SWEETDESK_VERSION,
            true
        );

        /*
        * Ticket table and pagination rendering.
        */
        wp_enqueue_script(
            'sweetdesk-tickets-renderer',
            $plugin_url . 'assets/js/tickets/tickets-renderer.js',
            [
                'sweetdesk-tickets-state',
                'sweetdesk-badge-helpers',
            ],
            SWEETDESK_VERSION,
            true
        );

        /*
        * Search, filtering, sorting, and query-builder behavior.
        */
        wp_enqueue_script(
            'sweetdesk-tickets-filters',
            $plugin_url . 'assets/js/tickets/tickets-filters.js',
            [
                'sweetdesk-tickets-state',
                'sweetdesk-tickets-api',
            ],
            SWEETDESK_VERSION,
            true
        );

        /*
        * Create, edit, and delete modal behavior.
        */
        wp_enqueue_script(
            'sweetdesk-tickets-modal',
            $plugin_url . 'assets/js/tickets/tickets-modal.js',
            [
                'sweetdesk-tickets-state',
                'sweetdesk-tickets-api',
                'sweetdesk-editor',
            ],
            SWEETDESK_VERSION,
            true
        );

        /*
        * Ticket import and export behavior.
        */
        wp_enqueue_script(
            'sweetdesk-tickets-transfer',
            $plugin_url . 'assets/js/tickets/tickets-transfer.js',
            [
                'sweetdesk-tickets-state',
                'sweetdesk-tickets-api',
            ],
            SWEETDESK_VERSION,
            true
        );

        /*
        * Main page coordinator.
        *
        * This must load last because it initializes all other modules.
        */
        wp_enqueue_script(
            'sweetdesk-tickets-list',
            $plugin_url . 'assets/js/tickets/tickets-list.js',
            [
                'sweetdesk-tickets-state',
                'sweetdesk-tickets-api',
                'sweetdesk-tickets-renderer',
                'sweetdesk-tickets-filters',
                'sweetdesk-tickets-modal',
                'sweetdesk-tickets-transfer',
            ],
            SWEETDESK_VERSION,
            true
        );
    }
}
