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
 * Version:           0.0.5
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
 *  0. 0. 2  7-10-2026  Database schema updates and implementation of core API endpoints.
 *  0. 0. 3  7-13-2026  Implementation Settings API routes and DB schema updates
 *  0. 0. 4  7-13-2026  Implementation of Analytics Features and schema updates.
 *  0. 0. 5  7-13-2026  Implementation of Feedback Management Features and schema updates.
 */

if (!defined('ABSPATH')) {
    exit;
}

define('SWEETDESK_VERSION', '0.0.5');
define('SWEETDESK_DB_VERSION', '1.4.0');

define('SWEETDESK_FILE', __FILE__);
define('SWEETDESK_PATH', plugin_dir_path(__FILE__));
define('SWEETDESK_URL', plugin_dir_url(__FILE__));

require_once SWEETDESK_PATH . 'src/core/activator.php';
require_once SWEETDESK_PATH . 'src/core/deactivator.php';
require_once SWEETDESK_PATH . 'src/core/plugin.php';
require_once SWEETDESK_PATH .
    'src/database/class-database-upgrader.php';

register_activation_hook(
    SWEETDESK_FILE,
    ['SweetDesk_Activator', 'activate']
);

register_deactivation_hook(
    SWEETDESK_FILE,
    ['SweetDesk_Deactivator', 'deactivate']
);

/**
 * Run database upgrades before the rest of the plugin initializes.
 */
add_action(
    'plugins_loaded',
    ['SweetDesk_Database_Upgrader', 'maybe_upgrade'],
    5
);

/**
 * Initialize SweetDesk after database upgrades have been checked.
 */
function run_sweetdesk(): void
{
    $plugin = new SweetDesk_Plugin();
    $plugin->run();
}

add_action(
    'plugins_loaded',
    'run_sweetdesk',
    10
);

add_action(
    'wp_mail_failed',
    static function (WP_Error $error): void {
        if (
            !defined('WP_DEBUG') ||
            !WP_DEBUG
        ) {
            return;
        }

        error_log(
            'SweetDesk wp_mail failure: ' .
            $error->get_error_message()
        );
    }
);