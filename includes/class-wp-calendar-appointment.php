<?php
/**
 * Handles appointment operations
 */
class WP_Calendar_Appointment {

    /**
     * Get all appointments
     */
    public static function get_appointments($args = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_calendar_appointments';
        
        $defaults = array(
            'user_id' => 0,
            'date_from' => null,
            'date_to' => null,
            'status' => 'confirmed',
            'limit' => 0,
            'offset' => 0,
        );
        
        $args = wp_parse_args($args, $defaults);
        $where = array('1=1');
        $prepare_values = array();
        
        if ($args['user_id'] > 0) {
            $where[] = 'user_id = %d';
            $prepare_values[] = $args['user_id'];
        }
        
        if ($args['date_from']) {
            $where[] = 'appointment_date >= %s';
            $prepare_values[] = $args['date_from'];
        }
        
        if ($args['date_to']) {
            $where[] = 'appointment_date <= %s';
            $prepare_values[] = $args['date_to'];
        }
        
        if ($args['status']) {
            $where[] = 'status = %s';
            $prepare_values[] = $args['status'];
        }
        
        $where_clause = implode(' AND ', $where);
        $limit_clause = '';
        
        if ($args['limit'] > 0) {
            $limit_clause = 'LIMIT %d';
            $prepare_values[] = $args['limit'];
            
            if ($args['offset'] > 0) {
                $limit_clause .= ' OFFSET %d';
                $prepare_values[] = $args['offset'];
            }
        }
        
        $query = "SELECT * FROM $table_name WHERE $where_clause ORDER BY appointment_date ASC, appointment_time ASC $limit_clause";
        
        if (!empty($prepare_values)) {
            $query = $wpdb->prepare($query, $prepare_values);
        }
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        return $results;
    }
    
    /**
     * Check if a time slot is available
     */
    public static function is_time_slot_available($date, $time) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_calendar_appointments';
        $blocked_table = $wpdb->prefix . 'wp_calendar_blocked_times';
        
        // Check if the date is blocked
        $blocked_date = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM $blocked_table
            WHERE blocked_date = %s AND blocked_time IS NULL
        ", $date));
        
        if ($blocked_date > 0) {
            return false;
        }
        
        // Check if the specific time slot is blocked
        $blocked_time = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM $blocked_table
            WHERE blocked_date = %s AND blocked_time = %s
        ", $date, $time));
        
        if ($blocked_time > 0) {
            return false;
        }
        
        // Check if there's already an appointment at this time
        $existing_appointment = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM $table_name
            WHERE appointment_date = %s AND appointment_time = %s AND status != 'cancelled'
        ", $date, $time));
        
        return $existing_appointment == 0;
    }
    
    /**
     * Create or update an appointment
     */
    public static function save_appointment($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_calendar_appointments';
        
        $defaults = array(
            'id' => 0,
            'user_id' => get_current_user_id(),
            'appointment_date' => '',
            'appointment_time' => '',
            'status' => 'confirmed',
            'notes' => '',
            'google_event_id' => '',
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Validate data
        if (empty($data['appointment_date']) || empty($data['appointment_time'])) {
            return new WP_Error('missing_data', __('Date and time are required', 'wp-calendar'));
        }
        
        // Check if user can modify this appointment
        if ($data['id'] > 0) {
            $appointment = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $data['id']
            ));
            
            if (!$appointment) {
                return new WP_Error('not_found', __('Appointment not found', 'wp-calendar'));
            }
            
            // Check if user owns this appointment or is admin
            if (!current_user_can('manage_options') && $appointment->user_id != get_current_user_id()) {
                return new WP_Error('permission_denied', __('You do not have permission to modify this appointment', 'wp-calendar'));
            }
            
            // Check cancellation period if changing date/time
            if ($appointment->appointment_date != $data['appointment_date'] || $appointment->appointment_time != $data['appointment_time']) {
                $cancellation_period = get_option('wp_calendar_cancellation_period', 24);
                $appointment_datetime = strtotime($appointment->appointment_date . ' ' . $appointment->appointment_time);
                $hours_until_appointment = ($appointment_datetime - time()) / 3600;
                
                if ($hours_until_appointment < $cancellation_period && !current_user_can('manage_options')) {
                    return new WP_Error('too_late', sprintf(
                        __('Appointments can only be modified %d hours before the scheduled time', 'wp-calendar'),
                        $cancellation_period
                    ));
                }
            }
        }
        
        // Check if the time slot is available (only for new appointments or when changing date/time)
        if ($data['id'] == 0 || ($appointment && ($appointment->appointment_date != $data['appointment_date'] || $appointment->appointment_time != $data['appointment_time']))) {
            if (!self::is_time_slot_available($data['appointment_date'], $data['appointment_time'])) {
                return new WP_Error('slot_unavailable', __('This time slot is not available', 'wp-calendar'));
            }
        }
        
        // Prepare data for database
        $db_data = array(
            'user_id' => $data['user_id'],
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'],
            'status' => $data['status'],
            'notes' => $data['notes'],
            'google_event_id' => $data['google_event_id'],
        );
        
        $db_format = array('%d', '%s', '%s', '%s', '%s', '%s');
        
        // Insert or update
        if ($data['id'] > 0) {
            $wpdb->update(
                $table_name,
                $db_data,
                array('id' => $data['id']),
                $db_format,
                array('%d')
            );
            $appointment_id = $data['id'];
        } else {
            $wpdb->insert(
                $table_name,
                $db_data,
                $db_format
            );
            $appointment_id = $wpdb->insert_id;
        }
        
        // Sync with Google Calendar if enabled
        if (get_option('wp_calendar_google_calendar_integration') === 'enabled') {
            WP_Calendar_Google::sync_appointment($appointment_id);
        }
        
        return $appointment_id;
    }
    
    /**
     * Delete an appointment
     */
    public static function delete_appointment($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_calendar_appointments';
        
        // Get the appointment
        $appointment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $id
        ));
        
        if (!$appointment) {
            return new WP_Error('not_found', __('Appointment not found', 'wp-calendar'));
        }
        
        // Check if user owns this appointment or is admin
        if (!current_user_can('manage_options') && $appointment->user_id != get_current_user_id()) {
            return new WP_Error('permission_denied', __('You do not have permission to delete this appointment', 'wp-calendar'));
        }
        
        // Check cancellation period
        $cancellation_period = get_option('wp_calendar_cancellation_period', 24);
        $appointment_datetime = strtotime($appointment->appointment_date . ' ' . $appointment->appointment_time);
        $hours_until_appointment = ($appointment_datetime - time()) / 3600;
        
        if ($hours_until_appointment < $cancellation_period && !current_user_can('manage_options')) {
            return new WP_Error('too_late', sprintf(
                __('Appointments can only be cancelled %d hours before the scheduled time', 'wp-calendar'),
                $cancellation_period
            ));
        }
        
        // Instead of deleting, update the status to 'cancelled'
        $result = $wpdb->update(
            $table_name,
            array('status' => 'cancelled'),
            array('id' => $id),
            array('%s'),
            array('%d')
        );
        
        // Delete from Google Calendar if integrated
        if (!empty($appointment->google_event_id) && get_option('wp_calendar_google_calendar_integration') === 'enabled') {
            WP_Calendar_Google::delete_event($appointment->google_event_id);
        }
        
        return $result ? true : false;
    }
    
    /**
     * Get blocked times
     */
    public static function get_blocked_times($args = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_calendar_blocked_times';
        
        $defaults = array(
            'date_from' => null,
            'date_to' => null,
        );
        
        $args = wp_parse_args($args, $defaults);
        $where = array('1=1');
        $prepare_values = array();
        
        if ($args['date_from']) {
            $where[] = '(blocked_date >= %s OR blocked_date IS NULL)';
            $prepare_values[] = $args['date_from'];
        }
        
        if ($args['date_to']) {
            $where[] = '(blocked_date <= %s OR blocked_date IS NULL)';
            $prepare_values[] = $args['date_to'];
        }
        
        $where_clause = implode(' AND ', $where);
        
        $query = "SELECT * FROM $table_name WHERE $where_clause ORDER BY blocked_date ASC, blocked_time ASC";
        
        if (!empty($prepare_values)) {
            $query = $wpdb->prepare($query, $prepare_values);
        }
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        return $results;
    }
    
    /**
     * Save blocked time
     */
    public static function save_blocked_time($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_calendar_blocked_times';
        
        $defaults = array(
            'id' => 0,
            'blocked_date' => null,
            'blocked_time' => null,
            'is_recurring' => 0,
            'day_of_week' => null,
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Validate data
        if (empty($data['blocked_date']) && empty($data['day_of_week'])) {
            return new WP_Error('missing_data', __('Either date or day of week is required', 'wp-calendar'));
        }
        
        // If recurring, set day of week
        if ($data['is_recurring'] && !empty($data['blocked_date'])) {
            $data['day_of_week'] = date('w', strtotime($data['blocked_date']));
        }
        
        // Prepare data for database
        $db_data = array(
            'blocked_date' => $data['blocked_date'],
            'blocked_time' => $data['blocked_time'],
            'is_recurring' => $data['is_recurring'] ? 1 : 0,
            'day_of_week' => $data['day_of_week'],
        );
        
        $db_format = array('%s', '%s', '%d', '%d');
        
        // Insert or update
        if ($data['id'] > 0) {
            $wpdb->update(
                $table_name,
                $db_data,
                array('id' => $data['id']),
                $db_format,
                array('%d')
            );
            return $data['id'];
        } else {
            $wpdb->insert(
                $table_name,
                $db_data,
                $db_format
            );
            return $wpdb->insert_id;
        }
    }
    
    /**
     * Delete blocked time
     */
    public static function delete_blocked_time($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_calendar_blocked_times';
        
        return $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );
    }
}