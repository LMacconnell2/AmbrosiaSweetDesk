<?php
/**
 * Plugin Name: SweetDesk
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'admin/AdminMenu.php';

function sweetdesk_init() {
    new SweetDesk\Admin\AdminMenu();
}
add_action('plugins_loaded', 'sweetdesk_init');