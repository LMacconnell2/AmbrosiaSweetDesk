<?php
/**
 * Plugin Name: SweetDesk
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'src/views/admin/AdminMenu.php';

function sweetdesk_init() {
    new SweetDesk\Admin\AdminMenu();
}
add_action('plugins_loaded', 'sweetdesk_init');