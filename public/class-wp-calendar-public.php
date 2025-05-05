<?php
/**
 * The public-facing functionality of the plugin.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for
 * the public-facing side of the site.
 */
class WP_Calendar_Public {

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
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueue_styles() {
        // Register styles
        wp_register_style('jquery-ui', WP_CALENDAR_PLUGIN_URL . 'public/css/jquery-ui.min.css', array(), $this->version);
        wp_register_style('fullcalendar', WP_CALENDAR_PLUGIN_URL . 'public/css/fullcalendar.min.css', array(), $this->version);
        wp_register_style($this->plugin_name, WP_CALENDAR_PLUGIN_URL . 'public/css/wp-calendar-public.css', array(), $this->version);
        
        // Enqueue styles for all pages where shortcodes might be used
        wp_enqueue_style('jquery-ui');
        wp_enqueue_style($this->plugin_name);
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     */
    public function enqueue_scripts() {
        // Register scripts
        wp_register_script('moment', WP_CALENDAR_PLUGIN_URL . 'public/js/moment.min.js', array('jquery'), $this->version);
        wp_register_script('fullcalendar', WP_CALENDAR_PLUGIN_URL . 'public/js/fullcalendar.min.js', array('jquery', 'moment'), $this->version);
        
        // Enqueue jQuery UI datepicker for all pages
        wp_enqueue_script('jquery-ui-datepicker');
        
        // Register and enqueue the main plugin script
        wp_register_script($this->plugin_name, WP_CALENDAR_PLUGIN_URL . 'public/js/wp-calendar-public.js', array('jquery', 'jquery-ui-datepicker'), $this->version, true);
        wp_enqueue_script($this->plugin_name);

        // Localize the script with data
        wp_localize_script($this->plugin_name, 'wp_calendar_public', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_calendar_public_nonce'),
            'is_logged_in' => is_user_logged_in() ? 1 : 0,
            'login_url' => get_permalink(get_option('wp_calendar_login_page')),
            'account_url' => get_permalink(get_option('wp_calendar_account_page')),
            'i18n' => array(
                'select_date' => __('Please select a date', 'wp-calendar'),
                'select_time' => __('Please select a time', 'wp-calendar'),
                'loading' => __('Loading...', 'wp-calendar'),
                'no_times_available' => __('No times available', 'wp-calendar'),
                'booking_success' => __('Your appointment has been booked successfully!', 'wp-calendar'),
                'booking_error' => __('An error occurred. Please try again.', 'wp-calendar'),
                'login_required' => __('Please log in to book an appointment.', 'wp-calendar'),
                'confirm_cancel' => __('Are you sure you want to cancel this appointment?', 'wp-calendar'),
                'cancel_success' => __('Your appointment has been cancelled successfully.', 'wp-calendar'),
                'cancel_error' => __('An error occurred while cancelling your appointment.', 'wp-calendar'),
                'passwords_not_match' => __('Passwords do not match.', 'wp-calendar'),
            ),
        ));
    }

    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('wp_calendar', array($this, 'calendar_shortcode'));
        add_shortcode('wp_calendar_booking', array($this, 'booking_shortcode'));
        add_shortcode('wp_calendar_account', array($this, 'account_shortcode'));
        add_shortcode('wp_calendar_login', array($this, 'login_shortcode'));
        add_shortcode('wp_calendar_register', array($this, 'register_shortcode'));
    }

    /**
     * Enqueue scripts and styles for shortcodes
     */
    public function enqueue_shortcode_assets() {
        // Enqueue jQuery UI styles
        wp_enqueue_style('jquery-ui');
        wp_enqueue_style($this->plugin_name);
        
        // Enqueue scripts
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script($this->plugin_name);
    }

    /**
     * Calendar shortcode callback
     */
    public function calendar_shortcode($atts) {
        // Enqueue necessary scripts and styles
        $this->enqueue_shortcode_assets();
        wp_enqueue_script('moment');
        wp_enqueue_script('fullcalendar');
        wp_enqueue_style('fullcalendar');
        
        // Start output buffering
        ob_start();
        
        // Include the calendar template
        include_once WP_CALENDAR_PLUGIN_DIR . 'public/partials/wp-calendar-public-calendar.php';
        
        // Return the buffered content
        return ob_get_clean();
    }

    /**
     * Booking form shortcode callback
     */
    public function booking_shortcode($atts) {
        // Enqueue necessary scripts and styles
        $this->enqueue_shortcode_assets();
        
        // Start output buffering
        ob_start();
        
        // Include the booking form template
        include_once WP_CALENDAR_PLUGIN_DIR . 'public/partials/wp-calendar-public-booking.php';
        
        // Return the buffered content
        return ob_get_clean();
    }

    /**
     * Account shortcode callback
     */
    public function account_shortcode($atts) {
        if (!is_user_logged_in()) {
            return sprintf(
                '<div class="wp-calendar-login-notice">%s <a href="%s">%s</a></div>',
                __('Please log in to view your appointments.', 'wp-calendar'),
                get_permalink(get_option('wp_calendar_login_page')),
                __('Log In', 'wp-calendar')
            );
        }

        ob_start();
        include WP_CALENDAR_PLUGIN_DIR . 'public/partials/wp-calendar-public-account.php';
        return ob_get_clean();
    }

    /**
     * Login shortcode callback
     */
    public function login_shortcode($atts) {
        if (is_user_logged_in()) {
            return sprintf(
                '<div class="wp-calendar-login-notice">%s <a href="%s">%s</a></div>',
                __('You are already logged in.', 'wp-calendar'),
                get_permalink(get_option('wp_calendar_account_page')),
                __('View Your Appointments', 'wp-calendar')
            );
        }

        ob_start();
        include WP_CALENDAR_PLUGIN_DIR . 'public/partials/wp-calendar-public-login.php';
        return ob_get_clean();
    }

    /**
     * Register shortcode callback
     */
    public function register_shortcode($atts) {
        if (is_user_logged_in()) {
            return sprintf(
                '<div class="wp-calendar-login-notice">%s <a href="%s">%s</a></div>',
                __('You are already logged in.', 'wp-calendar'),
                get_permalink(get_option('wp_calendar_account_page')),
                __('View Your Appointments', 'wp-calendar')
            );
        }

        ob_start();
        include WP_CALENDAR_PLUGIN_DIR . 'public/partials/wp-calendar-public-register.php';
        return ob_get_clean();
    }

    /**
     * Register AJAX handlers
     */
    public function register_ajax_handlers() {
        // Public AJAX handlers (available to logged in and non-logged in users)
        add_action('wp_ajax_wp_calendar_get_available_times', array($this, 'get_available_times'));
        add_action('wp_ajax_nopriv_wp_calendar_get_available_times', array($this, 'get_available_times'));
        
        add_action('wp_ajax_wp_calendar_get_public_events', array($this, 'ajax_get_public_events'));
        add_action('wp_ajax_nopriv_wp_calendar_get_public_events', array($this, 'ajax_get_public_events'));
        
        add_action('wp_ajax_wp_calendar_book_appointment', array($this, 'ajax_book_appointment'));
        add_action('wp_ajax_nopriv_wp_calendar_book_appointment', array($this, 'ajax_book_appointment'));
        
        add_action('wp_ajax_wp_calendar_cancel_appointment', array($this, 'ajax_cancel_appointment'));
        
        add_action('wp_ajax_wp_calendar_login', array($this, 'ajax_login'));
        add_action('wp_ajax_nopriv_wp_calendar_login', array($this, 'ajax_login'));
        
        add_action('wp_ajax_wp_calendar_register', array($this, 'ajax_register'));
        add_action('wp_ajax_nopriv_wp_calendar_register', array($this, 'ajax_register'));
    }

    /**
     * AJAX handler for getting public events
     */
    public function ajax_get_public_events() {
        check_ajax_referer('wp_calendar_public_nonce', 'nonce');

        $start = isset($_POST['start']) ? sanitize_text_field($_POST['start']) : null;
        $end = isset($_POST['end']) ? sanitize_text_field($_POST['end']) : null;

        $events = array();

        // Get blocked times
        $blocked_times = WP_Calendar_Appointment::get_blocked_times(array(
            'date_from' => $start,
            'date_to' => $end,
        ));

        // Process blocked times
        foreach ($blocked_times as $blocked) {
            if (!empty($blocked['blocked_date']) && empty($blocked['blocked_time'])) {
                // Entire day blocked
                $events[] = array(
                    'id' => 'blocked_' . $blocked['id'],
                    'title' => __('Not Available', 'wp-calendar'),
                    'start' => $blocked['blocked_date'],
                    'allDay' => true,
                    'rendering' => 'background',
                    'color' => '#f8d7da',
                );
            }
        }

        // If user is logged in, show their appointments
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $appointments = WP_Calendar_Appointment::get_appointments(array(
                'user_id' => $user_id,
                'date_from' => $start,
                'date_to' => $end,
            ));

            foreach ($appointments as $appointment) {
                $events[] = array(
                    'id' => 'appointment_' . $appointment['id'],
                    'title' => __('Your Appointment', 'wp-calendar'),
                    'start' => $appointment['appointment_date'] . 'T' . $appointment['appointment_time'],
                    'end' => $this->calculate_end_time($appointment['appointment_date'], $appointment['appointment_time']),
                    'status' => $appointment['status'],
                    'className' => 'wp-calendar-user-appointment status-' . $appointment['status'],
                );
            }
        }

        wp_send_json_success($events);
    }

    /**
     * AJAX handler for getting available times
     */
    public function get_available_times() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_calendar_public_nonce')) {
            wp_send_json_error(__('Security check failed.', 'wp-calendar'));
            return;
        }
        
        // Get date from request
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
        
        if (empty($date)) {
            wp_send_json_error(__('No date provided.', 'wp-calendar'));
            return;
        }
        
        try {
            // Get business hours
            $business_hours_start = get_option('wp_calendar_business_hours_start', '09:00');
            $business_hours_end = get_option('wp_calendar_business_hours_end', '17:00');
            $slot_duration = get_option('wp_calendar_time_slot_duration', 60);
            
            // Generate time slots
            $start = new DateTime('today ' . $business_hours_start);
            $end = new DateTime('today ' . $business_hours_end);
            $interval = new DateInterval('PT' . $slot_duration . 'M');
            
            $times = array();
            $current = clone $start;
            
            while ($current < $end) {
                $time_value = $current->format('H:i:s');
                $time_display = $current->format(get_option('time_format'));
                
                // Check if this time slot is available
                // Temporarily disable availability check to see if that's causing the issue
                // if (WP_Calendar_Appointment::is_time_slot_available($date, $time_value)) {
                    $times[] = array(
                        'value' => $time_value,
                        'label' => $time_display
                    );
                // }
                
                $current->add($interval);
            }
            
            wp_send_json_success($times);
        } catch (Exception $e) {
            wp_send_json_error(__('Error generating time slots: ', 'wp-calendar') . $e->getMessage());
            return;
        }
    }

    /**
     * AJAX handler for booking an appointment
     */
    public function ajax_book_appointment() {
        check_ajax_referer('wp_calendar_public_nonce', 'nonce');

        if (!is_user_logged_in() && get_option('wp_calendar_require_login', 1)) {
            wp_send_json_error(__('You must be logged in to book an appointment', 'wp-calendar'));
            return;
        }

        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
        $time = isset($_POST['time']) ? sanitize_text_field($_POST['time']) : '';
        $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';

        if (empty($date) || empty($time)) {
            wp_send_json_error(__('Please select a date and time', 'wp-calendar'));
            return;
        }

        $appointment_data = array(
            'user_id' => get_current_user_id(),
            'appointment_date' => $date,
            'appointment_time' => $time,
            'notes' => $notes,
            'status' => 'confirmed'
        );

        $result = WP_Calendar_Appointment::save_appointment($appointment_data);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }

        // Send confirmation email
        if (get_option('wp_calendar_email_notifications', 1)) {
            WP_Calendar_Notification::send_confirmation_email($result);
        }

        wp_send_json_success(__('Your appointment has been booked successfully', 'wp-calendar'));
    }

    /**
     * AJAX handler for cancelling an appointment
     */
    public function ajax_cancel_appointment() {
        check_ajax_referer('wp_calendar_public_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(__('You must be logged in to cancel an appointment', 'wp-calendar'));
            return;
        }

        $appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;

        if ($appointment_id <= 0) {
            wp_send_json_error(__('Invalid appointment ID', 'wp-calendar'));
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_calendar_appointments';

        // Prüfen, ob der Termin existiert und dem Benutzer gehört
        $appointment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d AND user_id = %d",
            $appointment_id,
            get_current_user_id()
        ));

        if (!$appointment) {
            wp_send_json_error(__('Appointment not found or you do not have permission to cancel it', 'wp-calendar'));
            return;
        }

        // Prüfen, ob der Termin bereits storniert wurde
        if ($appointment->status === 'cancelled') {
            wp_send_json_error(__('This appointment has already been cancelled', 'wp-calendar'));
            return;
        }

        // Prüfen, ob die Stornierungsfrist eingehalten wird
        $cancellation_period = get_option('wp_calendar_cancellation_period', 24);
        $appointment_datetime = strtotime($appointment->appointment_date . ' ' . $appointment->appointment_time);
        $hours_until_appointment = ($appointment_datetime - time()) / 3600;

        if ($hours_until_appointment < $cancellation_period && !current_user_can('manage_options')) {
            wp_send_json_error(sprintf(
                __('Appointments can only be cancelled %d hours before the scheduled time', 'wp-calendar'),
                $cancellation_period
            ));
            return;
        }

        // Termin stornieren
        $result = $wpdb->update(
            $table_name,
            array('status' => 'cancelled'),
            array('id' => $appointment_id),
            array('%s'),
            array('%d')
        );

        if ($result === false) {
            wp_send_json_error(__('Failed to cancel the appointment', 'wp-calendar'));
            return;
        }

        // Google Calendar Event löschen, wenn die Integration aktiviert ist
        if (get_option('wp_calendar_google_calendar_integration') === 'enabled') {
            WP_Calendar_Google::delete_event($appointment_id);
        }

        wp_send_json_success(__('Your appointment has been cancelled successfully', 'wp-calendar'));
    }

    /**
     * AJAX handler for user login
     */
    public function ajax_login() {
        check_ajax_referer('wp_calendar_public_nonce', 'nonce');

        $username = isset($_POST['username']) ? sanitize_user($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $remember = isset($_POST['remember']) ? (bool)$_POST['remember'] : false;

        if (empty($username) || empty($password)) {
            wp_send_json_error(__('Please enter both username and password', 'wp-calendar'));
        }

        $credentials = array(
            'user_login' => $username,
            'user_password' => $password,
            'remember' => $remember,
        );

        $user = wp_signon($credentials, false);

        if (is_wp_error($user)) {
            wp_send_json_error($user->get_error_message());
        } else {
            wp_send_json_success(array(
                'redirect' => get_permalink(get_option('wp_calendar_account_page')),
                'message' => __('Login successful. Redirecting...', 'wp-calendar'),
            ));
        }
    }

    /**
     * AJAX handler for user registration
     */
    public function ajax_register() {
        check_ajax_referer('wp_calendar_public_nonce', 'nonce');

        $username = isset($_POST['username']) ? sanitize_user($_POST['username']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

        if (empty($username) || empty($email) || empty($password) || empty($password_confirm)) {
            wp_send_json_error(__('Please fill in all required fields', 'wp-calendar'));
        }

        if ($password !== $password_confirm) {
            wp_send_json_error(__('Passwords do not match', 'wp-calendar'));
        }

        if (username_exists($username)) {
            wp_send_json_error(__('This username is already taken', 'wp-calendar'));
        }

        if (email_exists($email)) {
            wp_send_json_error(__('This email address is already registered', 'wp-calendar'));
        }

        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) {
            wp_send_json_error($user_id->get_error_message());
        } else {
            // Set user role
            $user = new WP_User($user_id);
            $user->set_role('subscriber');

            // Log the user in
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);

            wp_send_json_success(array(
                'redirect' => get_permalink(get_option('wp_calendar_account_page')),
                'message' => __('Registration successful. Redirecting...', 'wp-calendar'),
            ));
        }
    }

    /**
     * Calculate end time based on start time and slot duration
     */
    private function calculate_end_time($date, $time) {
        $slot_duration = get_option('wp_calendar_time_slot_duration', 60);
        $datetime = new DateTime($date . ' ' . $time);
        $datetime->add(new DateInterval('PT' . $slot_duration . 'M'));
        return $datetime->format('Y-m-d\TH:i:s');
    }
}