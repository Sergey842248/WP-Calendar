<?php
/**
 * Define the internationalization functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 */
class WP_Calendar_i18n {

    /**
     * Load the plugin text domain for translation.
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'wp-calendar',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}