<?php
/**
 * Admin settings page template.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Initialize active tab
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';

?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <h2 class="nav-tab-wrapper">
        <a href="?page=wp-calendar-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php _e('General', 'wp-calendar'); ?></a>
        <a href="?page=wp-calendar-settings&tab=business_hours" class="nav-tab <?php echo $active_tab == 'business_hours' ? 'nav-tab-active' : ''; ?>"><?php _e('Business Hours', 'wp-calendar'); ?></a>
        <a href="?page=wp-calendar-settings&tab=notifications" class="nav-tab <?php echo $active_tab == 'notifications' ? 'nav-tab-active' : ''; ?>"><?php _e('Notifications', 'wp-calendar'); ?></a>
        <a href="?page=wp-calendar-settings&tab=google_calendar" class="nav-tab <?php echo $active_tab == 'google_calendar' ? 'nav-tab-active' : ''; ?>"><?php _e('Google Calendar', 'wp-calendar'); ?></a>
    </h2>
    
    <form method="post" action="options.php">
        <?php
        if ($active_tab == 'general') {
            settings_fields('wp_calendar_settings');
            do_settings_sections('wp-calendar-settings');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Time Slot Duration (minutes)', 'wp-calendar'); ?></th>
                    <td>
                        <input type="number" name="wp_calendar_time_slot_duration" value="<?php echo esc_attr(get_option('wp_calendar_time_slot_duration', 60)); ?>" min="15" max="120" step="15" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Cancellation Period (hours)', 'wp-calendar'); ?></th>
                    <td>
                        <input type="number" name="wp_calendar_cancellation_period" value="<?php echo esc_attr(get_option('wp_calendar_cancellation_period', 24)); ?>" min="1" max="72" />
                        <p class="description"><?php _e('Minimum hours before an appointment that a user can cancel', 'wp-calendar'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Login Page', 'wp-calendar'); ?></th>
                    <td>
                        <?php
                        wp_dropdown_pages(array(
                            'name' => 'wp_calendar_login_page',
                            'show_option_none' => __('Select a page', 'wp-calendar'),
                            'option_none_value' => '0',
                            'selected' => get_option('wp_calendar_login_page'),
                        ));
                        ?>
                        <p class="description"><?php _e('Page containing the [wp_calendar_login] shortcode', 'wp-calendar'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Register Page', 'wp-calendar'); ?></th>
                    <td>
                        <?php
                        wp_dropdown_pages(array(
                            'name' => 'wp_calendar_register_page',
                            'show_option_none' => __('Select a page', 'wp-calendar'),
                            'option_none_value' => '0',
                            'selected' => get_option('wp_calendar_register_page'),
                        ));
                        ?>
                        <p class="description"><?php _e('Page containing the [wp_calendar_register] shortcode', 'wp-calendar'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Account Page', 'wp-calendar'); ?></th>
                    <td>
                        <?php
                        wp_dropdown_pages(array(
                            'name' => 'wp_calendar_account_page',
                            'show_option_none' => __('Select a page', 'wp-calendar'),
                            'option_none_value' => '0',
                            'selected' => get_option('wp_calendar_account_page'),
                        ));
                        ?>
                        <p class="description"><?php _e('Page containing the [wp_calendar_account] shortcode', 'wp-calendar'); ?></p>
                    </td>
                </tr>
            </table>
        <?php
        } elseif ($active_tab == 'business_hours') {
            settings_fields('wp_calendar_settings');
            do_settings_sections('wp-calendar-settings');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Business Hours Start', 'wp-calendar'); ?></th>
                    <td>
                        <input type="time" name="wp_calendar_business_hours_start" value="<?php echo esc_attr(get_option('wp_calendar_business_hours_start', '09:00')); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Business Hours End', 'wp-calendar'); ?></th>
                    <td>
                        <input type="time" name="wp_calendar_business_hours_end" value="<?php echo esc_attr(get_option('wp_calendar_business_hours_end', '17:00')); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Working Days', 'wp-calendar'); ?></th>
                    <td>
                        <?php
                        $working_days = get_option('wp_calendar_working_days', array(1, 2, 3, 4, 5));
                        $days = array(
                            0 => __('Sunday', 'wp-calendar'),
                            1 => __('Monday', 'wp-calendar'),
                            2 => __('Tuesday', 'wp-calendar'),
                            3 => __('Wednesday', 'wp-calendar'),
                            4 => __('Thursday', 'wp-calendar'),
                            5 => __('Friday', 'wp-calendar'),
                            6 => __('Saturday', 'wp-calendar'),
                        );
                        foreach ($days as $value => $label) {
                            echo '<label><input type="checkbox" name="wp_calendar_working_days[]" value="' . esc_attr($value) . '" ' . (in_array($value, $working_days) ? 'checked' : '') . ' /> ' . esc_html($label) . '</label><br>';
                        }
                        ?>
                    </td>
                </tr>
            </table>
        <?php
        } elseif ($active_tab == 'notifications') {
            settings_fields('wp_calendar_settings');
            do_settings_sections('wp-calendar-settings');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Enable Email Notifications', 'wp-calendar'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wp_calendar_notification_emails" value="1" <?php checked(get_option('wp_calendar_notification_emails', 1), 1); ?> />
                            <?php _e('Send email notifications for bookings and cancellations', 'wp-calendar'); ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Admin Email', 'wp-calendar'); ?></th>
                    <td>
                        <input type="email" name="wp_calendar_admin_email" value="<?php echo esc_attr(get_option('wp_calendar_admin_email', get_option('admin_email'))); ?>" class="regular-text" />
                        <p class="description"><?php _e('Email address to receive admin notifications', 'wp-calendar'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Booking Confirmation Subject', 'wp-calendar'); ?></th>
                    <td>
                        <input type="text" name="wp_calendar_booking_subject" value="<?php echo esc_attr(get_option('wp_calendar_booking_subject', __('Your appointment has been booked', 'wp-calendar'))); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Booking Confirmation Message', 'wp-calendar'); ?></th>
                    <td>
                        <textarea name="wp_calendar_booking_message" rows="5" cols="50" class="large-text"><?php echo esc_textarea(get_option('wp_calendar_booking_message', __("Dear {user_name},\n\nYour appointment has been booked for {appointment_date} at {appointment_time}.\n\nThank you for using our service.", 'wp-calendar'))); ?></textarea>
                        <p class="description"><?php _e('Available placeholders: {user_name}, {appointment_date}, {appointment_time}', 'wp-calendar'); ?></p>
                    </td>
                </tr>
            </table>
        <?php
        } elseif ($active_tab == 'google_calendar') {
            settings_fields('wp_calendar_settings');
            do_settings_sections('wp-calendar-settings');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Enable Google Calendar Integration', 'wp-calendar'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wp_calendar_google_calendar_integration" value="1" <?php checked(get_option('wp_calendar_google_calendar_integration', 0), 1); ?> />
                            <?php _e('Sync appointments with Google Calendar', 'wp-calendar'); ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Google Calendar ID', 'wp-calendar'); ?></th>
                    <td>
                        <input type="text" name="wp_calendar_google_calendar_id" value="<?php echo esc_attr(get_option('wp_calendar_google_calendar_id')); ?>" class="regular-text" />
                        <p class="description"><?php _e('The ID of the Google Calendar to sync with', 'wp-calendar'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Google API Key', 'wp-calendar'); ?></th>
                    <td>
                        <input type="text" name="wp_calendar_google_api_key" value="<?php echo esc_attr(get_option('wp_calendar_google_api_key')); ?>" class="regular-text" />
                        <p class="description"><?php _e('Your Google API Key', 'wp-calendar'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Google Client ID', 'wp-calendar'); ?></th>
                    <td>
                        <input type="text" name="wp_calendar_google_client_id" value="<?php echo esc_attr(get_option('wp_calendar_google_client_id')); ?>" class="regular-text" />
                        <p class="description"><?php _e('Your Google Client ID', 'wp-calendar'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Google Client Secret', 'wp-calendar'); ?></th>
                    <td>
                        <input type="password" name="wp_calendar_google_client_secret" value="<?php echo esc_attr(get_option('wp_calendar_google_client_secret')); ?>" class="regular-text" />
                        <p class="description"><?php _e('Your Google Client Secret', 'wp-calendar'); ?></p>
                    </td>
                </tr>
            </table>
        <?php
        }
        submit_button();
        ?>
    </form>
</div>
