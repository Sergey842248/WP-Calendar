/**
 * Send appointment confirmation email
 */
public static function send_confirmation_email($appointment_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_calendar_appointments';
    
    // Get appointment details
    $appointment = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $appointment_id
    ), ARRAY_A);
    
    if (!$appointment) {
        return false;
    }
    
    // Get user details
    $user = get_userdata($appointment['user_id']);
    if (!$user) {
        return false;
    }
    
    // Get email settings
    $admin_email = get_option('wp_calendar_admin_email', get_option('admin_email'));
    $subject = get_option('wp_calendar_confirmation_email_subject', __('Your appointment has been confirmed', 'wp-calendar'));
    $body = get_option('wp_calendar_confirmation_email_body', __('Dear {user_name},

Your appointment has been confirmed for {appointment_date} at {appointment_time}.

Thank you for booking with us.

Regards,
{site_name}', 'wp-calendar'));
    
    // Replace placeholders
    $date_format = get_option('date_format');
    $time_format = get_option('time_format');
    
    $appointment_date = date_i18n($date_format, strtotime($appointment['appointment_date']));
    $appointment_time = date_i18n($time_format, strtotime($appointment['appointment_time']));
    
    $replacements = array(
        '{user_name}' => $user->display_name,
        '{appointment_date}' => $appointment_date,
        '{appointment_time}' => $appointment_time,
        '{site_name}' => get_bloginfo('name')
    );
    
    $body = str_replace(array_keys($replacements), array_values($replacements), $body);
    
    // Set headers
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . get_bloginfo('name') . ' <' . $admin_email . '>'
    );
    
    // Send email to user
    $user_mail_sent = wp_mail($user->user_email, $subject, $body, $headers);
    
    // Send notification to admin
    $admin_subject = __('New Appointment Booking', 'wp-calendar');
    $admin_body = sprintf(
        __('A new appointment has been booked by %s for %s at %s.', 'wp-calendar'),
        $user->display_name,
        $appointment_date,
        $appointment_time
    );
    
    $admin_mail_sent = wp_mail($admin_email, $admin_subject, $admin_body, $headers);
    
    return $user_mail_sent && $admin_mail_sent;
}