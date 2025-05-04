<?php
/**
 * Calendar template for the public-facing side of the site.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wp-calendar-container">
    <?php if (!is_user_logged_in() && get_option('wp_calendar_require_login', 1)) : ?>
        <div class="wp-calendar-login-notice">
            <p><?php _e('Please log in to book appointments.', 'wp-calendar'); ?></p>
            <a href="<?php echo esc_url(get_permalink(get_option('wp_calendar_login_page'))); ?>" class="button"><?php _e('Log In', 'wp-calendar'); ?></a>
            <a href="<?php echo esc_url(get_permalink(get_option('wp_calendar_register_page'))); ?>" class="button"><?php _e('Register', 'wp-calendar'); ?></a>
        </div>
    <?php else : ?>
        <div id="wp-calendar-public"></div>
        
        <div class="wp-calendar-booking-form-container">
            <h3><?php _e('Book an Appointment', 'wp-calendar'); ?></h3>
            
            <form id="wp-calendar-booking-form" method="post">
                <div class="wp-calendar-message" style="display: none;"></div>
                
                <div class="form-row">
                    <label for="appointment_date"><?php _e('Date', 'wp-calendar'); ?></label>
                    <input type="text" id="appointment_date" name="appointment_date" class="wp-calendar-datepicker" required readonly>
                </div>
                
                <div class="form-row">
                    <label for="appointment_time"><?php _e('Time', 'wp-calendar'); ?></label>
                    <select id="appointment_time" name="appointment_time" required disabled>
                        <option value=""><?php _e('Select a date first', 'wp-calendar'); ?></option>
                    </select>
                </div>
                
                <div class="form-row">
                    <label for="appointment_notes"><?php _e('Notes', 'wp-calendar'); ?></label>
                    <textarea id="appointment_notes" name="appointment_notes" rows="4"></textarea>
                </div>
                
                <div class="form-row">
                    <button type="submit" class="button button-primary"><?php _e('Book Appointment', 'wp-calendar'); ?></button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>