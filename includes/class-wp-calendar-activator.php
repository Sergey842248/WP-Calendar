<?php
/**
 * Fired during plugin activation.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class WP_Calendar_Activator {

    /**
     * Create the database tables and set default options.
     */
    public static function activate() {
        // Create database tables
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-calendar-db.php';
        WP_Calendar_DB::create_tables();

        // Set default options if they don't exist
        if (false === get_option('wp_calendar_business_hours_start')) {
            add_option('wp_calendar_business_hours_start', '09:00');
        }

        if (false === get_option('wp_calendar_business_hours_end')) {
            add_option('wp_calendar_business_hours_end', '17:00');
        }

        if (false === get_option('wp_calendar_time_slot_duration')) {
            add_option('wp_calendar_time_slot_duration', 60); // 60 minutes
        }

        if (false === get_option('wp_calendar_cancellation_period')) {
            add_option('wp_calendar_cancellation_period', 24); // 24 hours
        }

        if (false === get_option('wp_calendar_admin_email')) {
            add_option('wp_calendar_admin_email', get_option('admin_email'));
        }

        if (false === get_option('wp_calendar_notification_emails')) {
            add_option('wp_calendar_notification_emails', 1); // Enable by default
        }

        if (false === get_option('wp_calendar_google_calendar_integration')) {
            add_option('wp_calendar_google_calendar_integration', 0); // Disabled by default
        }

        // Flush rewrite rules
        flush_rewrite_rules();
    }
}