<?php

require_once SWEETDESK_PATH . 'src/views/admin/class-admin-menu.php';
require_once SWEETDESK_PATH . 'src/api/class-api.php';

use SweetDesk\Admin\AdminMenu;

class SweetDesk_Plugin {

    protected $loader;

    public function __construct() {

        $this->load_dependencies();

        $this->define_admin_hooks();

        $this->define_api_hooks();

        $this->define_public_hooks();
    }

    private function load_dependencies() {

        require_once SWEETDESK_PATH . 'src/core/loader.php';

        require_once SWEETDESK_PATH . 'src/core/class-portal-access.php';

        require_once SWEETDESK_PATH . 'src/api/class-api.php';

        $this->loader = new SweetDesk_Loader();
    }

    private function define_admin_hooks() {

        $admin = new AdminMenu();

        $this->loader->add_action(
            'admin_menu',
            $admin,
            'register_menu'
        );

        $this->loader->add_action(
            'admin_enqueue_scripts',
            $admin,
            'enqueue_admin_assets'
        );
    }

    private function define_public_hooks() {

        require_once SWEETDESK_PATH .
            'src/views/frontend/class-shortcodes.php';

        $shortcodes = new SweetDesk_Frontend_Shortcodes();

        $this->loader->add_action(
            'init',
            $shortcodes,
            'register_shortcodes'
        );

        $this->loader->add_action(
            'wp_enqueue_scripts',
            $shortcodes,
            'enqueue_assets'
        );

        add_filter(
            'template_include',
            [$shortcodes, 'use_portal_template']
        );

        $portal = new SweetDesk_Portal_Access();

        $this->loader->add_action(
            'template_redirect',
            $portal,
            'restrict_frontend_access',
            1
        );

        $this->loader->add_action(
            'admin_init',
            $portal,
            'restrict_admin_access',
            1
        );

        $this->loader->add_action(
            'admin_init',
            $portal,
            'ensure_portal_page',
            20
        );

        $this->loader->add_action(
            'init',
            $portal,
            'ensure_portal_page',
            20
        );

        $this->loader->add_action(
            'save_post',
            $portal,
            'track_portal_page'
        );

        $this->loader->add_action(
            'admin_notices',
            $portal,
            'maybe_show_portal_notice'
        );

        add_filter(
            'login_redirect',
            [$portal, 'redirect_after_login'],
            10,
            3
        );

        add_filter(
            'show_admin_bar',
            [$portal, 'hide_admin_bar']
        );
    }

    public function run() {

        $this->loader->run();
    }

    private function define_api_hooks() {
        SweetDesk_API::init();
    }
}
