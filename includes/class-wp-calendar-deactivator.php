<?php
/**
 * Fired during plugin deactivation.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 */
class WP_Calendar_Deactivator {

    /**
     * Clean up when the plugin is deactivated.
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}