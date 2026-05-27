<?php
/**
 *
 *
 * @link              https://ambrosia.digital
 * @since             1.0.0
 *
 * Plugin Name:       Ambrosia SweetDesk
 * Plugin URI:        https://ambrosia.digital/ambrosia-sweetdesk
 * Description:       This plugin provides a comprehensive help desk solution for WordPress, allowing you to manage customer support tickets, teams, and analytics all from your WordPress dashboard.
 * Version:           0.0.1
 * Author:            Logan MacConnell, Matthew C, Art Smith
 * Author URI:        https://ambrosia.digital
 * License:           
 * License URI:       
 * Text Domain:       ambrosia-sweetdesk
 */

/**
 * Version Notes
 *
 *  Version    Date    Description
 * --------- --------  ----------------------------------------------------------------------------------------------------
 *  0. 0. 1  5-20-2026  Refactor for better code ogranization and maintanability.
 */

if (!defined('ABSPATH')) exit;

define( 'SWEETDESK_VERSION', '0.0.1' );
define( 'SWEETDESK_PATH', plugin_dir_path( __FILE__ ) );
define( 'SWEETDESK_URL', plugin_dir_url( __FILE__ ) );

require_once SWEETDESK_PATH . 'src/core/activator.php';
require_once SWEETDESK_PATH . 'src/core/deactivator.php';
require_once SWEETDESK_PATH . 'src/core/plugin.php';

register_activation_hook(
    __FILE__,
    [ 'SweetDesk_Activator', 'activate' ]
);

register_deactivation_hook(
    __FILE__,
    [ 'SweetDesk_Deactivator', 'deactivate' ]
);

// function sweetdesk_init() {
//     new SweetDesk\Admin\AdminMenu();
// }
// add_action('plugins_loaded', 'sweetdesk_init');

function run_sweetdesk() {
    $plugin = new SweetDesk_Plugin();
    $plugin->run();
}

run_sweetdesk();