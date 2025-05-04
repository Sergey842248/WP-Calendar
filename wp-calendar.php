<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://example.com
 * @since             1.0.0
 * @package           WP_Calendar
 *
 * @wordpress-plugin
 * Plugin Name:       WP Calendar
 * Plugin URI:        https://example.com/wp-calendar
 * Description:       A simple appointment booking calendar for WordPress.
 * Version:           1.0.0
 * Author:            Your Name
 * Author URI:        https://example.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-calendar
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Currently plugin version.
 */
define('WP_CALENDAR_VERSION', '1.0.0');

/**
 * Plugin directory path.
 */
define('WP_CALENDAR_PLUGIN_DIR', plugin_dir_path(__FILE__));

/**
 * Plugin directory URL.
 */
define('WP_CALENDAR_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_wp_calendar() {
    require_once WP_CALENDAR_PLUGIN_DIR . 'includes/class-wp-calendar-activator.php';
    WP_Calendar_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wp_calendar() {
    require_once WP_CALENDAR_PLUGIN_DIR . 'includes/class-wp-calendar-deactivator.php';
    WP_Calendar_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wp_calendar');
register_deactivation_hook(__FILE__, 'deactivate_wp_calendar');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require WP_CALENDAR_PLUGIN_DIR . 'includes/class-wp-calendar.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_wp_calendar() {
    $plugin = new WP_Calendar();
    $plugin->run();
}
run_wp_calendar();