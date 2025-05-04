<?php
/**
 * Email notifications for the plugin.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Email notifications for the plugin.
 */
class WP_Calendar_Notifications {

    /**
     * Send a notification email when an appointment is booked.
     *
     * @param int $appointment_id The appointment ID.
     * @return bool Whether the email was sent successfully.
     */
    public static function send_booking_notification($appointment_id) {
        if (!get_option('wp_calendar_notification_emails', 1)) {
            return false;
        }
        
        $appointment = WP_Calendar_DB::get_appointment($appointment_id);
        
        if (!$appointment) {
            return false;
        }
        
        $user = get_userdata($appointment['user_id']);
        
        if (!$user) {
            return false;
        }
        
        $admin_email = get_option('wp_calendar_admin_email', get_option('admin_email'));
        $site_name = get_bloginfo('name');
        
        // Admin notification
        $admin_subject = sprintf(__('[%s] New Appointment Booking', 'wp-calendar'), $site_name);
        
        $admin_message = sprintf(
            __('A new appointment has been booked by %s.', 'wp-calendar'),
            $user->display_name
        ) . "\n\n";
        
        $admin_message .= __('Appointment Details:', 'wp-calendar') . "\n";
        $admin_message .= sprintf(__('Date: %s', 'wp-calendar'), date_i18n(get_option('date_format'), strtotime($appointment['appointment_date']))) . "\n";
        $admin_message .= sprintf(__('Time: %s', 'wp-calendar'), date_i18n(get_option('time_format'), strtotime($appointment['appointment_time']))) . "\n";
        $admin_message .= sprintf(__('Status: %s', 'wp-calendar'), ucfirst($appointment['status'])) . "\n";
        
        if (!empty($appointment['notes'])) {
            $admin_message .= sprintf(__('Notes: %s', 'wp-calendar'), $appointment['notes']) . "\n";
        }
        
        $admin_message .= "\n" . sprintf(
            __('You can manage this appointment in the admin area: %s', 'wp-calendar'),
            admin_url('admin.php?page=wp-calendar-appointments&action=edit&id=' . $appointment_id)
        );
        
        $admin_headers = array('Content-Type: text/plain; charset=UTF-8');
        
        wp_mail($admin_email, $admin_subject, $admin_message, $admin_headers);
        
        // User notification
        $user_subject = sprintf(__('[%s] Your Appointment Confirmation', 'wp-calendar'), $site_name);
        
        $user_message = sprintf(
            __('Hello %s,', 'wp-calendar'),
            $user->display_name
        ) . "\n\n";
        
        $user_message .= __('Your appointment has been booked successfully.', 'wp-calendar') . "\n\n";
        
        $user_message .= __('Appointment Details:', 'wp-calendar') . "\n";
        $user_message .= sprintf(__('Date: %s', 'wp-calendar'), date_i18n(get_option('date_format'), strtotime($appointment['appointment_date']))) . "\n";
        $user_message .= sprintf(__('Time: %s', 'wp-calendar'), date_i18n(get_option('time_format'), strtotime($appointment['appointment_time']))) . "\n";
        $user_message .= sprintf(__('Status: %s', 'wp-calendar'), ucfirst($appointment['status'])) . "\n";
        
        if (!empty($appointment['notes'])) {
            $user_message .= sprintf(__('Notes: %s', 'wp-calendar'), $appointment['notes']) . "\n";
        }
        
        $user_message .= "\n" . sprintf(
            __('You can view and manage your appointments here: %s', 'wp-calendar'),
            get_permalink(get_option('wp_calendar_account_page'))
        );
        
        $user_message .= "\n\n" . sprintf(
            __('Thank you for booking with %s.', 'wp-calendar'),
            $site_name
        );
        
        $user_headers = array('Content-Type: text/plain; charset=UTF-8');
        
        return wp_mail($user->user_email, $user_subject, $user_message, $user_headers);
    }
    
    /**
     * Send a notification email when an appointment status is updated.
     *
     * @param int $appointment_id The appointment ID.
     * @param string $old_status The old status.
     * @param string $new_status The new status.
     * @return bool Whether the email was sent successfully.
     */
    public static function send_status_update_notification($appointment_id, $old_status, $new_status) {
        if (!get_option('wp_calendar_notification_emails', 1)) {
            return false;
        }
        
        $appointment = WP_Calendar_DB::get_appointment($appointment_id);
        
        if (!$appointment) {
            return false;
        }
        
        $user = get_userdata($appointment['user_id']);
        
        if (!$user) {
            return false;
        }
        
        $site_name = get_bloginfo('name');
        
        // User notification
        $user_subject = sprintf(__('[%s] Your Appointment Status Update', 'wp-calendar'), $site_name);
        
        $user_message = sprintf(
            __('Hello %s,', 'wp-calendar'),
            $user->display_name
        ) . "\n\n";
        
        $user_message .= sprintf(
            __('The status of your appointment has been updated from "%s" to "%s".', 'wp-calendar'),
            ucfirst($old_status),
            ucfirst($new_status)
        ) . "\n\n";
        
        $user_message .= __('Appointment Details:', 'wp-calendar') . "\n";
        $user_message .= sprintf(__('Date: %s', 'wp-calendar'), date_i18n(get_option('date_format'), strtotime($appointment['appointment_date']))) . "\n";
        $user_message .= sprintf(__('Time: %s', 'wp-calendar'), date_i18n(get_option('time_format'), strtotime($appointment['appointment_time']))) . "\n";
        
        if (!empty($appointment['notes'])) {
            $user_message .= sprintf(__('Notes: %s', 'wp-calendar'), $appointment['notes']) . "\n";
        }
        
        $user_message .= "\n" . sprintf(
            __('You can view and manage your appointments here: %s', 'wp-calendar'),
            get_permalink(get_option('wp_calendar_account_page'))
        );
        
        $user_message .= "\n\n" . sprintf(
            __('Thank you for booking with %s.', 'wp-calendar'),
            $site_name
        );
        
        $user_headers = array('Content-Type: text/plain; charset=UTF-8');
        
        return wp_mail($user->user_email, $user_subject, $user_message, $user_headers);
    }
    
    /**
     * Send a notification email when an appointment is cancelled.
     *
     * @param int $appointment_id The appointment ID.
     * @return bool Whether the email was sent successfully.
     */
    public static function send_cancellation_notification($appointment_id) {
        if (!get_option('wp_calendar_notification_emails', 1)) {
            return false;
        }
        
        $appointment = WP_Calendar_DB::get_appointment($appointment_id);
        
        if (!$appointment) {
            return false;
        }
        
        $user = get_userdata($appointment['user_id']);
        
        if (!$user) {
            return false;
        }
        
        $admin_email = get_option('wp_calendar_admin_email', get_option('admin_email'));
        $site_name = get_bloginfo('name');
        
        // Admin notification
        $admin_subject = sprintf(__('[%s] Appointment Cancelled', 'wp-calendar'), $site_name);
        
        $admin_message = sprintf(
            __('An appointment has been cancelled by %s.', 'wp-calendar'),
            $user->display_name
        ) . "\n\n";
        
        $admin_message .= __('Appointment Details:', 'wp-calendar') . "\n";
        $admin_message .= sprintf(__('Date: %s', 'wp-calendar'), date_i18n(get_option('date_format'), strtotime($appointment['appointment_date']))) . "\n";
        $admin_message .= sprintf(__('Time: %s', 'wp-calendar'), date_i18n(get_option('time_format'), strtotime($appointment['appointment_time']))) . "\n";
        
        if (!empty($appointment['notes'])) {
            $admin_message .= sprintf(__('Notes: %s', 'wp-calendar'), $appointment['notes']) . "\n";
        }
        
        $admin_headers = array('Content-Type: text/plain; charset=UTF-8');
        
        wp_mail($admin_email, $admin_subject, $admin_message, $admin_headers);
        
        // User notification
        $user_subject = sprintf(__('[%s] Your Appointment Cancellation Confirmation', 'wp-calendar'), $site_name);
        
        $user_message = sprintf(
            __('Hello %s,', 'wp-calendar'),
            $user->display_name
        ) . "\n\n";
        
        $user_message .= __('Your appointment has been cancelled successfully.', 'wp-calendar') . "\n\n";
        
        $user_message .= __('Appointment Details:', 'wp-calendar') . "\n";
        $user_message .= sprintf(__('Date: %s', 'wp-calendar'), date_i18n(get_option('date_format'), strtotime($appointment['appointment_date']))) . "\n";
        $user_message .= sprintf(__('Time: %s', 'wp-calendar'), date_i18n(get_option('time_format'), strtotime($appointment['appointment_time']))) . "\n";
        
        if (!empty($appointment['notes'])) {
            $user_message .= sprintf(__('Notes: %s', 'wp-calendar'), $appointment['notes']) . "\n";
        }
        
        $user_message .= "\n" . sprintf(
            __('You can book a new appointment here: %s', 'wp-calendar'),
            get_permalink(get_option('wp_calendar_calendar_page'))
        );
        
        $user_headers = array('Content-Type: text/plain; charset=UTF-8');
        
        return wp_mail($user->user_email, $user_subject, $user_message, $user_headers);
    }
}