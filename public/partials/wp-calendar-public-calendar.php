<?php
/**
 * Public calendar view
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wp-calendar-container">
    <div class="wp-calendar-main">
        <div id="wp-calendar-public-calendar"></div>
    </div>
    
    <div class="wp-calendar-sidebar">
        <div class="wp-calendar-booking-form">
            <h3><?php _e('Book an Appointment', 'wp-calendar'); ?></h3>
            
            <?php if (!is_user_logged_in()) : ?>
                <div class="wp-calendar-login-notice">
                    <p><?php _e('You must be logged in to book an appointment.', 'wp-calendar'); ?></p>
                    <p>
                        <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="button"><?php _e('Log In', 'wp-calendar'); ?></a>
                        <?php if (get_option('users_can_register')) : ?>
                            <a href="<?php echo esc_url(wp_registration_url()); ?>" class="button"><?php _e('Register', 'wp-calendar'); ?></a>
                        <?php endif; ?>
                    </p>
                </div>
            <?php else : ?>
                <form id="wp-calendar-booking-form">
                    <div class="form-field">
                        <label for="booking-date"><?php _e('Date', 'wp-calendar'); ?></label>
                        <input type="text" id="booking-date" name="date" class="datepicker" required>
                    </div>
                    
                    <div class="form-field">
                        <label for="booking-time"><?php _e('Time', 'wp-calendar'); ?></label>
                        <select id="booking-time" name="time" required>
                            <option value=""><?php _e('Select a date first', 'wp-calendar'); ?></option>
                        </select>
                    </div>
                    
                    <div class="form-field">
                        <label for="booking-notes"><?php _e('Notes', 'wp-calendar'); ?></label>
                        <textarea id="booking-notes" name="notes" rows="4"></textarea>
                    </div>
                    
                    <div class="form-field">
                        <button type="submit" class="button button-primary"><?php _e('Book Appointment', 'wp-calendar'); ?></button>
                    </div>
                </form>
                
                <div id="wp-calendar-booking-message" style="display:none;"></div>
            <?php endif; ?>
        </div>
    </div>
</div>