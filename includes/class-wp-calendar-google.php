<?php
/**
 * Google Calendar integration for the plugin.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Google Calendar integration for the plugin.
 */
class WP_Calendar_Google {

    /**
     * Check if Google Calendar integration is enabled.
     *
     * @return bool Whether Google Calendar integration is enabled.
     */
    public static function is_enabled() {
        return (bool) get_option('wp_calendar_google_calendar_integration', 0);
    }

    /**
     * Get the Google Calendar ID.
     *
     * @return string The Google Calendar ID.
     */
    public static function get_calendar_id() {
        return get_option('wp_calendar_google_calendar_id', '');
    }

    /**
     * Get the Google API key.
     *
     * @return string The Google API key.
     */
    public static function get_api_key() {
        return get_option('wp_calendar_google_api_key', '');
    }

    /**
     * Add an appointment to Google Calendar.
     *
     * @param int $appointment_id The appointment ID.
     * @return bool|string True on success, error message on failure.
     */
    public static function add_appointment($appointment_id) {
        if (!self::is_enabled()) {
            return false;
        }

        $api_key = self::get_api_key();
        $calendar_id = self::get_calendar_id();

        if (empty($api_key) || empty($calendar_id)) {
            return __('Google Calendar API key or Calendar ID is missing.', 'wp-calendar');
        }

        $appointment = WP_Calendar_DB::get_appointment($appointment_id);

        if (!$appointment) {
            return __('Appointment not found.', 'wp-calendar');
        }

        $user = get_userdata($appointment['user_id']);

        if (!$user) {
            return __('User not found.', 'wp-calendar');
        }

        // Build the event data
        $start_datetime = $appointment['appointment_date'] . 'T' . $appointment['appointment_time'];
        $end_datetime = date('Y-m-d\TH:i:s', strtotime($start_datetime) + (get_option('wp_calendar_time_slot_duration', 60) * 60));

        $event = array(
            'summary' => sprintf(__('Appointment with %s', 'wp-calendar'), $user->display_name),
            'description' => !empty($appointment['notes']) ? $appointment['notes'] : '',
            'start' => array(
                'dateTime' => $start_datetime,
                'timeZone' => get_option('timezone_string', 'UTC'),
            ),
            'end' => array(
                'dateTime' => $end_datetime,
                'timeZone' => get_option('timezone_string', 'UTC'),
            ),
            'attendees' => array(
                array('email' => $user->user_email),
            ),
            'reminders' => array(
                'useDefault' => true,
            ),
        );

        // This is a placeholder for the actual Google Calendar API integration
        // In a real implementation, you would use the Google API Client Library for PHP
        // to make the API request to create the event

        // For now, we'll just return true to simulate success
        return true;
    }

    /**
     * Update an appointment in Google Calendar.
     *
     * @param int $appointment_id The appointment ID.
     * @return bool|string True on success, error message on failure.
     */
    public static function update_appointment($appointment_id) {
        if (!self::is_enabled()) {
            return false;
        }

        // Similar to add_appointment, but would update an existing event
        // This is a placeholder for the actual implementation

        return true;
    }

    /**
     * Delete an appointment from Google Calendar.
     *
     * @param int $appointment_id The appointment ID.
     * @return bool|string True on success, error message on failure.
     */
    public static function delete_appointment($appointment_id) {
        if (!self::is_enabled()) {
            return false;
        }

        // This would delete the event from Google Calendar
        // This is a placeholder for the actual implementation

        return true;
    }

    /**
     * Get events from Google Calendar.
     *
     * @param string $start_date Start date in Y-m-d format.
     * @param string $end_date End date in Y-m-d format.
     * @return array|string Array of events on success, error message on failure.
     */
    public static function get_events($start_date, $end_date) {
        if (!self::is_enabled()) {
            return array();
        }

        $api_key = self::get_api_key();
        $calendar_id = self::get_calendar_id();

        if (empty($api_key) || empty($calendar_id)) {
            return __('Google Calendar API key or Calendar ID is missing.', 'wp-calendar');
        }

        // This would fetch events from Google Calendar
        // This is a placeholder for the actual implementation

        return array();
    }
}