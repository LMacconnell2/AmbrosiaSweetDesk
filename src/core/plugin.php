<?php

require_once SWEETDESK_PATH . 'src/views/admin/class-admin-menu.php';

use SweetDesk\Admin\AdminMenu;

class SweetDesk_Plugin {

    protected $loader;

    public function __construct() {

        $this->load_dependencies();

        $this->define_admin_hooks();

        // $this->define_public_hooks();
    }

    private function load_dependencies() {

        require_once SWEETDESK_PATH . 'src/core/loader.php';

        // require_once SWEETDESK_PATH . 'public/class-public.php';

        require_once SWEETDESK_PATH . 'src/includes/class-api.php';

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

    // private function define_public_hooks() {

    //     $public = new SweetDesk_Public();

    //     $this->loader->add_action(
    //         'wp_enqueue_scripts',
    //         $public,
    //         'enqueue_assets'
    //     );
    // }

    public function run() {

        $this->loader->run();
    }

    private function define_api_hooks() {
        SweetDesk_API::init();
    }
}