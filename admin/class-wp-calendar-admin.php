<?php
/**
 * The admin-specific functionality of the plugin.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 */
class WP_Calendar_Admin {

    /**
     * The ID of this plugin.
     *
     * @var string
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @var string
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
        wp_enqueue_style('fullcalendar-css', 'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css');
        wp_enqueue_style($this->plugin_name, WP_CALENDAR_PLUGIN_URL . 'admin/css/wp-calendar-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     */
    /**
     * Enqueue scripts for admin
     */
    public function enqueue_scripts($hook) {
        // Nur auf Plugin-Seiten laden
        if (strpos($hook, 'wp-calendar') === false) {
            return;
        }
    
        // jQuery UI und FullCalendar laden
        wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
        wp_enqueue_script('jquery-ui-datepicker');
        
        // FullCalendar
        wp_enqueue_style('fullcalendar', plugin_dir_url(__FILE__) . 'css/fullcalendar.min.css');
        wp_enqueue_script('moment', plugin_dir_url(__FILE__) . 'js/moment.min.js', array(), '2.29.1', true);
        wp_enqueue_script('fullcalendar', plugin_dir_url(__FILE__) . 'js/fullcalendar.min.js', array('jquery', 'moment'), '3.10.2', true);
        
        // Admin-spezifische Styles und Scripts
        wp_enqueue_style('wp-calendar-admin', plugin_dir_url(__FILE__) . 'css/wp-calendar-admin.css');
        wp_enqueue_script('wp-calendar-admin', plugin_dir_url(__FILE__) . 'js/wp-calendar-admin.js', array('jquery', 'fullcalendar'), $this->version, true);
        
        // Lokalisierungsdaten für JavaScript
        wp_localize_script('wp-calendar-admin', 'wp_calendar_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_calendar_admin_nonce'),
            'i18n' => array(
                'confirm_delete' => __('Are you sure you want to delete this item?', 'wp-calendar'),
                'loading' => __('Loading...', 'wp-calendar'),
                'error' => __('An error occurred.', 'wp-calendar'),
                'save' => __('Save', 'wp-calendar'),
                'cancel' => __('Cancel', 'wp-calendar'),
            )
        ));
    }

    /**
     * Add admin menu items.
     */
    public function add_admin_menu() {
        add_menu_page(
            __('WP Calendar', 'wp-calendar'),
            __('WP Calendar', 'wp-calendar'),
            'manage_options',
            'wp-calendar',
            array($this, 'display_calendar_page'),
            'dashicons-calendar-alt',
            30
        );

        add_submenu_page(
            'wp-calendar',
            __('Calendar', 'wp-calendar'),
            __('Calendar', 'wp-calendar'),
            'manage_options',
            'wp-calendar',
            array($this, 'display_calendar_page')
        );

        add_submenu_page(
            'wp-calendar',
            __('Appointments', 'wp-calendar'),
            __('Appointments', 'wp-calendar'),
            'manage_options',
            'wp-calendar-appointments',
            array($this, 'display_appointments_page')
        );

        add_submenu_page(
            'wp-calendar',
            __('Blocked Times', 'wp-calendar'),
            __('Blocked Times', 'wp-calendar'),
            'manage_options',
            'wp-calendar-blocked-times',
            array($this, 'display_blocked_times_page')
        );

        add_submenu_page(
            'wp-calendar',
            __('Settings', 'wp-calendar'),
            __('Settings', 'wp-calendar'),
            'manage_options',
            'wp-calendar-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Register plugin settings.
     */
    /**
     * Register the settings for the plugin
     */
    public function register_settings() {
        // Register all settings fields here
        register_setting('wp_calendar_general_settings', 'wp_calendar_business_hours_start');
        register_setting('wp_calendar_general_settings', 'wp_calendar_business_hours_end');
        register_setting('wp_calendar_general_settings', 'wp_calendar_time_slot_duration');
        register_setting('wp_calendar_general_settings', 'wp_calendar_require_login');
        register_setting('wp_calendar_general_settings', 'wp_calendar_cancellation_period');
        
        // Register page settings
        register_setting('wp_calendar_page_settings', 'wp_calendar_booking_page');
        register_setting('wp_calendar_page_settings', 'wp_calendar_account_page');
        register_setting('wp_calendar_page_settings', 'wp_calendar_login_page');
        register_setting('wp_calendar_page_settings', 'wp_calendar_register_page');
        
        // Register email settings
        register_setting('wp_calendar_email_settings', 'wp_calendar_admin_email');
        register_setting('wp_calendar_email_settings', 'wp_calendar_email_notifications');
        register_setting('wp_calendar_email_settings', 'wp_calendar_confirmation_email_subject');
        register_setting('wp_calendar_email_settings', 'wp_calendar_confirmation_email_body');
        
        // Register Google Calendar settings
        register_setting('wp_calendar_google_settings', 'wp_calendar_google_calendar_integration');
        register_setting('wp_calendar_google_settings', 'wp_calendar_google_client_id');
        register_setting('wp_calendar_google_settings', 'wp_calendar_google_client_secret');
        register_setting('wp_calendar_google_settings', 'wp_calendar_google_calendar_id');
    }

    /**
     * Display the calendar page.
     */
    public function display_calendar_page() {
        include_once WP_CALENDAR_PLUGIN_DIR . 'admin/partials/wp-calendar-admin-calendar.php';
    }

    /**
     * Display the appointments page.
     */
    public function display_appointments_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($action === 'edit' && $id > 0) {
            include_once WP_CALENDAR_PLUGIN_DIR . 'admin/partials/wp-calendar-admin-appointment-edit.php';
        } else {
            include_once WP_CALENDAR_PLUGIN_DIR . 'admin/partials/wp-calendar-admin-appointments.php';
        }
    }

    /**
     * Display the blocked times page.
     */
    public function display_blocked_times_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($action === 'edit' && $id > 0) {
            include_once WP_CALENDAR_PLUGIN_DIR . 'admin/partials/wp-calendar-admin-blocked-time-edit.php';
        } else {
            include_once WP_CALENDAR_PLUGIN_DIR . 'admin/partials/wp-calendar-admin-blocked-times.php';
        }
    }

    /**
     * Display the settings page.
     */
    public function display_settings_page() {
        include_once WP_CALENDAR_PLUGIN_DIR . 'admin/partials/wp-calendar-admin-settings.php';
    }

    /**
     * AJAX handler for getting appointments.
     */
    public function ajax_get_appointments() {
        check_ajax_referer('wp_calendar_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'wp-calendar'));
        }

        $start = isset($_POST['start']) ? sanitize_text_field($_POST['start']) : '';
        $end = isset($_POST['end']) ? sanitize_text_field($_POST['end']) : '';

        $args = array();

        if (!empty($start)) {
            $args['start_date'] = $start;
        }

        if (!empty($end)) {
            $args['end_date'] = $end;
        }

        $appointments = WP_Calendar_DB::get_appointments($args);
        $events = array();

        foreach ($appointments as $appointment) {
            $user = get_userdata($appointment['user_id']);
            $username = $user ? $user->display_name : __('Unknown User', 'wp-calendar');

            $events[] = array(
                'id' => $appointment['id'],
                'title' => sprintf(__('Appointment: %s', 'wp-calendar'), $username),
                'start' => $appointment['appointment_date'] . 'T' . $appointment['appointment_time'],
                'end' => date('Y-m-d\TH:i:s', strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time']) + (get_option('wp_calendar_time_slot_duration', 60) * 60)),
                'className' => 'status-' . $appointment['status'],
                'status' => $appointment['status'],
                'notes' => $appointment['notes'],
                'user_id' => $appointment['user_id'],
                'username' => $username,
            );
        }

        wp_send_json_success($events);
    }

    /**
     * AJAX handler for getting a single appointment.
     */
    public function ajax_get_appointment() {
        check_ajax_referer('wp_calendar_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'wp-calendar'));
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if ($id <= 0) {
            wp_send_json_error(__('Invalid appointment ID.', 'wp-calendar'));
        }

        $appointment = WP_Calendar_DB::get_appointment($id);

        if (!$appointment) {
            wp_send_json_error(__('Appointment not found.', 'wp-calendar'));
        }

        $user = get_userdata($appointment['user_id']);
        $appointment['username'] = $user ? $user->display_name : __('Unknown User', 'wp-calendar');
        $appointment['user_email'] = $user ? $user->user_email : '';

        wp_send_json_success($appointment);
    }

    /**
     * AJAX handler for saving an appointment.
     */
    public function ajax_save_appointment() {
        check_ajax_referer('wp_calendar_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'wp-calendar'));
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
        $time = isset($_POST['time']) ? sanitize_text_field($_POST['time']) : '';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'pending';
        $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';

        if (empty($date) || empty($time) || empty($user_id)) {
            wp_send_json_error(__('Please fill in all required fields.', 'wp-calendar'));
        }

        $data = array(
            'id' => $id,
            'user_id' => $user_id,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'status' => $status,
            'notes' => $notes,
        );

        // Check if we're updating an existing appointment
        $old_status = '';
        if ($id > 0) {
            $existing = WP_Calendar_DB::get_appointment($id);
            if ($existing) {
                $old_status = $existing['status'];
            }
        }

        $result = WP_Calendar_DB::save_appointment($data);

        if ($result) {
            // If this is a new appointment or the status has changed, send notifications
            if ($id === 0) {
                WP_Calendar_Notifications::send_booking_notification($result);
                WP_Calendar_Google::add_appointment($result);
            } elseif ($old_status !== $status) {
                WP_Calendar_Notifications::send_status_update_notification($id, $old_status, $status);
                WP_Calendar_Google::update_appointment($id);
            }

            wp_send_json_success(__('Appointment saved successfully.', 'wp-calendar'));
        } else {
            wp_send_json_error(__('Failed to save appointment.', 'wp-calendar'));
        }
    }

    /**
     * AJAX handler for deleting an appointment.
     */
    public function ajax_delete_appointment() {
        check_ajax_referer('wp_calendar_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'wp-calendar'));
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if ($id <= 0) {
            wp_send_json_error(__('Invalid appointment ID.', 'wp-calendar'));
        }

        // Get the appointment before deleting it
        $appointment = WP_Calendar_DB::get_appointment($id);

        if (!$appointment) {
            wp_send_json_error(__('Appointment not found.', 'wp-calendar'));
        }

        // Delete from Google Calendar
        WP_Calendar_Google::delete_appointment($id);

        $result = WP_Calendar_DB::delete_appointment($id);

        if ($result) {
            wp_send_json_success(__('Appointment deleted successfully.', 'wp-calendar'));
        } else {
            wp_send_json_error(__('Failed to delete appointment.', 'wp-calendar'));
        }
    }

    /**
     * AJAX handler for getting a blocked time.
     */
    public function ajax_get_blocked_time() {
        check_ajax_referer('wp_calendar_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'wp-calendar'));
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if ($id <= 0) {
            wp_send_json_error(__('Invalid blocked time ID.', 'wp-calendar'));
        }

        $blocked_time = WP_Calendar_DB::get_blocked_time($id);

        if (!$blocked_time) {
            wp_send_json_error(__('Blocked time not found.', 'wp-calendar'));
        }

        wp_send_json_success($blocked_time);
    }

    /**
     * AJAX handler for saving a blocked time.
     */
    public function ajax_save_blocked_time() {
        check_ajax_referer('wp_calendar_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'wp-calendar'));
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
        $time = isset($_POST['time']) ? sanitize_text_field($_POST['time']) : null;
        $is_recurring = isset($_POST['is_recurring']) ? (bool) $_POST['is_recurring'] : false;
        $day_of_week = isset($_POST['day_of_week']) ? intval($_POST['day_of_week']) : null;

        $data = array(
            'id' => $id,
            'blocked_date' => $is_recurring ? null : $date,
            'blocked_time' => $time,
            'is_recurring' => $is_recurring ? 1 : 0,
            'day_of_week' => $is_recurring ? $day_of_week : null,
        );

        $result = WP_Calendar_DB::save_blocked_time($data);

        if ($result) {
            wp_send_json_success(__('Blocked time saved successfully.', 'wp-calendar'));
        } else {
            wp_send_json_error(__('Failed to save blocked time.', 'wp-calendar'));
        }
    }

    /**
     * AJAX handler for deleting a blocked time.
     */
    public function ajax_delete_blocked_time() {
        check_ajax_referer('wp_calendar_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'wp-calendar'));
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if ($id <= 0) {
            wp_send_json_error(__('Invalid blocked time ID.', 'wp-calendar'));
        }

        $result = WP_Calendar_DB::delete_blocked_time($id);

        if ($result) {
            wp_send_json_success(__('Blocked time deleted successfully.', 'wp-calendar'));
        } else {
            wp_send_json_error(__('Failed to delete blocked time.', 'wp-calendar'));
        }
    }
}