<?php
/**
 * Database operations for the plugin.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database operations for the plugin.
 */
class WP_Calendar_DB {

    /**
     * The appointments table name.
     *
     * @var string
     */
    private static $appointments_table;

    /**
     * The blocked times table name.
     *
     * @var string
     */
    private static $blocked_times_table;

    /**
     * Initialize the class and set its properties.
     */
    public static function init() {
        global $wpdb;
        self::$appointments_table = $wpdb->prefix . 'wp_calendar_appointments';
        self::$blocked_times_table = $wpdb->prefix . 'wp_calendar_blocked_times';
    }

    /**
     * Create the database tables.
     */
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Initialize table names
        self::init();

        // Create appointments table
        $sql = "CREATE TABLE " . self::$appointments_table . " (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            appointment_date date NOT NULL,
            appointment_time time NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            notes text NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Create blocked times table
        $sql .= "CREATE TABLE " . self::$blocked_times_table . " (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            blocked_date date NULL,
            blocked_time time NULL,
            is_recurring tinyint(1) NOT NULL DEFAULT 0,
            day_of_week tinyint(1) NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Get appointments.
     *
     * @param array $args Query arguments.
     * @return array Array of appointments.
     */
    public static function get_appointments($args = array()) {
        global $wpdb;
        self::init();

        $defaults = array(
            'id' => null,
            'user_id' => null,
            'status' => null,
            'start_date' => null,
            'end_date' => null,
            'orderby' => 'appointment_date, appointment_time',
            'order' => 'ASC',
            'limit' => null,
            'offset' => 0,
        );

        $args = wp_parse_args($args, $defaults);
        $where = array('1=1');
        $prepare = array();

        if (!is_null($args['id'])) {
            $where[] = 'id = %d';
            $prepare[] = $args['id'];
        }

        if (!is_null($args['user_id'])) {
            $where[] = 'user_id = %d';
            $prepare[] = $args['user_id'];
        }

        if (!is_null($args['status'])) {
            $where[] = 'status = %s';
            $prepare[] = $args['status'];
        }

        if (!is_null($args['start_date'])) {
            $where[] = 'appointment_date >= %s';
            $prepare[] = $args['start_date'];
        }

        if (!is_null($args['end_date'])) {
            $where[] = 'appointment_date <= %s';
            $prepare[] = $args['end_date'];
        }

        $sql = "SELECT * FROM " . self::$appointments_table . " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY " . esc_sql($args['orderby']) . " " . esc_sql($args['order']);

        if (!is_null($args['limit'])) {
            $sql .= " LIMIT %d, %d";
            $prepare[] = $args['offset'];
            $prepare[] = $args['limit'];
        }

        if (!empty($prepare)) {
            $sql = $wpdb->prepare($sql, $prepare);
        }

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Get a single appointment.
     *
     * @param int $id Appointment ID.
     * @return array|null Appointment data or null if not found.
     */
    public static function get_appointment($id) {
        $appointments = self::get_appointments(array('id' => $id));
        return !empty($appointments) ? $appointments[0] : null;
    }

    /**
     * Save an appointment.
     *
     * @param array $data Appointment data.
     * @return int|false The appointment ID on success, false on failure.
     */
    public static function save_appointment($data) {
        global $wpdb;
        self::init();

        $id = isset($data['id']) ? absint($data['id']) : 0;
        $user_id = isset($data['user_id']) ? absint($data['user_id']) : 0;
        $appointment_date = isset($data['appointment_date']) ? sanitize_text_field($data['appointment_date']) : '';
        $appointment_time = isset($data['appointment_time']) ? sanitize_text_field($data['appointment_time']) : '';
        $status = isset($data['status']) ? sanitize_text_field($data['status']) : 'pending';
        $notes = isset($data['notes']) ? sanitize_textarea_field($data['notes']) : '';

        // Validate required fields
        if (empty($user_id) || empty($appointment_date) || empty($appointment_time)) {
            return false;
        }

        $appointment_data = array(
            'user_id' => $user_id,
            'appointment_date' => $appointment_date,
            'appointment_time' => $appointment_time,
            'status' => $status,
            'notes' => $notes,
        );

        // Update existing appointment
        if ($id > 0) {
            $result = $wpdb->update(
                self::$appointments_table,
                $appointment_data,
                array('id' => $id)
            );

            return $result !== false ? $id : false;
        }

        // Insert new appointment
        $result = $wpdb->insert(
            self::$appointments_table,
            $appointment_data
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Delete an appointment.
     *
     * @param int $id Appointment ID.
     * @return bool True on success, false on failure.
     */
    public static function delete_appointment($id) {
        global $wpdb;
        self::init();

        return $wpdb->delete(
            self::$appointments_table,
            array('id' => $id),
            array('%d')
        );
    }

    /**
     * Get blocked times.
     *
     * @param array $args Query arguments.
     * @return array Array of blocked times.
     */
    public static function get_blocked_times($args = array()) {
        global $wpdb;
        self::init();

        $defaults = array(
            'id' => null,
            'date_from' => null,
            'date_to' => null,
            'is_recurring' => null,
            'day_of_week' => null,
            'orderby' => 'blocked_date, blocked_time',
            'order' => 'ASC',
            'limit' => null,
            'offset' => 0,
        );

        $args = wp_parse_args($args, $defaults);
        $where = array('1=1');
        $prepare = array();

        if (!is_null($args['id'])) {
            $where[] = 'id = %d';
            $prepare[] = $args['id'];
        }

        if (!is_null($args['is_recurring'])) {
            $where[] = 'is_recurring = %d';
            $prepare[] = $args['is_recurring'] ? 1 : 0;
        }

        if (!is_null($args['day_of_week'])) {
            $where[] = 'day_of_week = %d';
            $prepare[] = $args['day_of_week'];
        }

        // Handle date range for non-recurring blocks
        if (!is_null($args['date_from']) && !is_null($args['date_to'])) {
            $where[] = '(
                (is_recurring = 0 AND blocked_date BETWEEN %s AND %s)
                OR
                (is_recurring = 1)
            )';
            $prepare[] = $args['date_from'];
            $prepare[] = $args['date_to'];
        } elseif (!is_null($args['date_from'])) {
            $where[] = '(
                (is_recurring = 0 AND blocked_date >= %s)
                OR
                (is_recurring = 1)
            )';
            $prepare[] = $args['date_from'];
        } elseif (!is_null($args['date_to'])) {
            $where[] = '(
                (is_recurring = 0 AND blocked_date <= %s)
                OR
                (is_recurring = 1)
            )';
            $prepare[] = $args['date_to'];
        }

        $sql = "SELECT * FROM " . self::$blocked_times_table . " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY " . esc_sql($args['orderby']) . " " . esc_sql($args['order']);

        if (!is_null($args['limit'])) {
            $sql .= " LIMIT %d, %d";
            $prepare[] = $args['offset'];
            $prepare[] = $args['limit'];
        }

        if (!empty($prepare)) {
            $sql = $wpdb->prepare($sql, $prepare);
        }

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Get a single blocked time.
     *
     * @param int $id Blocked time ID.
     * @return array|null Blocked time data or null if not found.
     */
    public static function get_blocked_time($id) {
        $blocked_times = self::get_blocked_times(array('id' => $id));
        return !empty($blocked_times) ? $blocked_times[0] : null;
    }

    /**
     * Save a blocked time.
     *
     * @param array $data Blocked time data.
     * @return int|false The blocked time ID on success, false on failure.
     */
    public static function save_blocked_time($data) {
        global $wpdb;
        self::init();

        $id = isset($data['id']) ? absint($data['id']) : 0;
        $blocked_date = isset($data['blocked_date']) ? sanitize_text_field($data['blocked_date']) : null;
        $blocked_time = isset($data['blocked_time']) ? sanitize_text_field($data['blocked_time']) : null;
        $is_recurring = isset($data['is_recurring']) ? (bool) $data['is_recurring'] : false;
        $day_of_week = isset($data['day_of_week']) ? absint($data['day_of_week']) : null;

        // Validate required fields
        if ($is_recurring && is_null($day_of_week)) {
            return false;
        }

        if (!$is_recurring && is_null($blocked_date)) {
            return false;
        }

        $blocked_time_data = array(
            'blocked_date' => $blocked_date,
            'blocked_time' => $blocked_time,
            'is_recurring' => $is_recurring ? 1 : 0,
            'day_of_week' => $day_of_week,
        );

        // Update existing blocked time
        if ($id > 0) {
            $result = $wpdb->update(
                self::$blocked_times_table,
                $blocked_time_data,
                array('id' => $id)
            );

            return $result !== false ? $id : false;
        }

        // Insert new blocked time
        $result = $wpdb->insert(
            self::$blocked_times_table,
            $blocked_time_data
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Delete a blocked time.
     *
     * @param int $id Blocked time ID.
     * @return bool True on success, false on failure.
     */
    public static function delete_blocked_time($id) {
        global $wpdb;
        self::init();

        return $wpdb->delete(
            self::$blocked_times_table,
            array('id' => $id),
            array('%d')
        );
    }

    /**
     * Get available time slots for a specific date.
     *
     * @param string $date Date in Y-m-d format.
     * @return array Array of available time slots.
     */
    public static function get_available_time_slots($date) {
        // Get business hours
        $start_hour = get_option('wp_calendar_business_hours_start', '09:00');
        $end_hour = get_option('wp_calendar_business_hours_end', '17:00');
        $slot_duration = get_option('wp_calendar_time_slot_duration', 60); // in minutes

        // Generate all possible time slots
        $start_time = strtotime($date . ' ' . $start_hour);
        $end_time = strtotime($date . ' ' . $end_hour);
        $all_slots = array();

        for ($time = $start_time; $time < $end_time; $time += $slot_duration * 60) {
            $slot_time = date('H:i:s', $time);
            $all_slots[] = array(
                'value' => $slot_time,
                'label' => date('g:i A', $time),
                'available' => true
            );
        }

        // Get day of week (0 = Sunday, 6 = Saturday)
        $day_of_week = date('w', strtotime($date));

        // Get blocked times for this date
        $blocked_times = self::get_blocked_times(array(
            'date_from' => $date,
            'date_to' => $date,
        ));

        // Get existing appointments for this date
        $appointments = self::get_appointments(array(
            'start_date' => $date,
            'end_date' => $date,
            'status' => array('pending', 'confirmed')
        ));

        // Mark blocked slots as unavailable
        foreach ($blocked_times as $blocked) {
            // Check if this is a recurring block for this day of week
            if ($blocked['is_recurring'] && $blocked['day_of_week'] == $day_of_week) {
                if (empty($blocked['blocked_time'])) {
                    // Entire day is blocked
                    foreach ($all_slots as &$slot) {
                        $slot['available'] = false;
                    }
                    break;
                } else {
                    // Specific time is blocked
                    foreach ($all_slots as &$slot) {
                        if ($slot['value'] == $blocked['blocked_time']) {
                            $slot['available'] = false;
                        }
                    }
                }
            }
            // Check if this is a non-recurring block for this specific date
            elseif (!$blocked['is_recurring'] && $blocked['blocked_date'] == $date) {
                if (empty($blocked['blocked_time'])) {
                    // Entire day is blocked
                    foreach ($all_slots as &$slot) {
                        $slot['available'] = false;
                    }
                    break;
                } else {
                    // Specific time is blocked
                    foreach ($all_slots as &$slot) {
                        if ($slot['value'] == $blocked['blocked_time']) {
                            $slot['available'] = false;
                        }
                    }
                }
            }
        }

        // Mark booked slots as unavailable
        foreach ($appointments as $appointment) {
            foreach ($all_slots as &$slot) {
                if ($slot['value'] == $appointment['appointment_time']) {
                    $slot['available'] = false;
                }
            }
        }

        // Filter out unavailable slots
        $available_slots = array();
        foreach ($all_slots as $slot) {
            if ($slot['available']) {
                $available_slots[] = array(
                    'value' => $slot['value'],
                    'label' => $slot['label']
                );
            }
        }

        return $available_slots;
    }

    /**
     * Check if a specific time slot is available.
     *
     * @param string $date Date in Y-m-d format.
     * @param string $time Time in H:i:s format.
     * @return bool True if available, false if not.
     */
    public static function is_time_slot_available($date, $time) {
        $available_slots = self::get_available_time_slots($date);
        
        foreach ($available_slots as $slot) {
            if ($slot['value'] == $time) {
                return true;
            }
        }
        
        return false;
    }
}

// Initialize the DB class
WP_Calendar_DB::init();