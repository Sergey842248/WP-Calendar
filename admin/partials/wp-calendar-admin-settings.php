<?php
/**
 * Admin settings page
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap wp-calendar-admin">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <h2 class="nav-tab-wrapper">
        <a href="?page=wp-calendar-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php _e('General', 'wp-calendar'); ?></a>
        <a href="?page=wp-calendar-settings&tab=google" class="nav-tab <?php echo $active_tab == 'google' ? 'nav-tab-active' : ''; ?>"><?php _e('Google Calendar', 'wp-calendar'); ?></a>
        <a href="?page=wp-calendar-settings&tab=pages" class="nav-tab <?php echo $active_tab == 'pages' ? 'nav-tab-active' : ''; ?>"><?php _e('Pages', 'wp-calendar'); ?></a>
        <a href="?page=wp-calendar-settings&tab=notifications" class="nav-tab <?php echo $active_tab == 'notifications' ? 'nav-tab-active' : ''; ?>"><?php _e('Notifications', 'wp-calendar'); ?></a>
    </h2>
    
    <form method="post" action="options.php">
        <?php if ($active_tab == 'general') : ?>
            <?php settings_fields('wp_calendar_general'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Time Slot Duration (minutes)', 'wp-calendar'); ?></th>
                    <td>
                        <select name="wp_calendar_time_slot_duration">
                            <?php
                            $duration = get_option('wp_calendar_time_slot_duration', 60);
                            $options = array(15, 30, 45, 60, 90, 120);
                            foreach ($options as $option) {
                                echo '<option value="' . esc_attr($option) . '" ' . selected($duration, $option, false) . '>' . esc_html($option) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Business Hours Start', 'wp-calendar'); ?></th>
                    <td>
                        <select name="wp_calendar_business_hours_start">
                            <?php
                            $start = get_option('wp_calendar_business_hours_start', '09:00');
                            for ($hour = 0; $hour < 24; $hour++) {
                                $time = sprintf('%02d:00', $hour);
                                echo '<option value="' . esc_attr($time) . '" ' . selected($start, $time, false) . '>' . esc_html(date_i18n(get_option('time_format'), strtotime($time))) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Business Hours End', 'wp-calendar'); ?></th>
                    <td>
                        <select name="wp_calendar_business_hours_end">
                            <?php
                            $end = get_option('wp_calendar_business_hours_end', '17:00');
                            for ($hour = 0; $hour < 24; $hour++) {
                                $time = sprintf('%02d:00', $hour);
                                echo '<option value="' . esc_attr($time) . '" ' . selected($end, $time, false) . '>' . esc_html(date_i18n(get_option('time_format'), strtotime($time))) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Minimum Cancellation Period (hours)', 'wp-calendar'); ?></th>
                    <td>
                        <input type="number" name="wp_calendar_cancellation_period" value="<?php echo esc_attr(get_option('wp_calendar_cancellation_period', 24)); ?>" min="0" step="1">
                        <p class="description"><?php _e('Minimum number of hours before an appointment that a user can cancel or reschedule. Set to 0 to allow cancellation at any time.', 'wp-calendar'); ?></p>
                    </td>
                </tr>
            </table>
        <?php elseif ($active_tab == 'google') : ?>
            <?php settings_fields('wp_calendar_google'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Enable Google Calendar Integration', 'wp-calendar'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wp_calendar_google_calendar_integration" value="1" <?php checked(get_option('wp_calendar_google_calendar_integration'), 1); ?>>
                            <?php _e('Sync appointments with Google Calendar', 'wp-calendar'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Google Client ID', 'wp-calendar'); ?></th>
                    <td>
                        <input type="text" name="wp_calendar_google_client_id" value="<?php echo esc_attr(get_option('wp_calendar_google_client_id')); ?>" class="regular-text">
                        <p class="description"><?php _e('Enter your Google API Client ID.', 'wp-calendar'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Google Client Secret', 'wp-calendar'); ?></th>
                    <td>
                        <input type="password" name="wp_calendar_google_client_secret" value="<?php echo esc_attr(get_option('wp_calendar_google_client_secret')); ?>" class="regular-text">
                        <p class="description"><?php _e('Enter your Google API Client Secret.', 'wp-calendar'); ?></p>
                    </td>
                </tr>
                <?php if (WP_Calendar_Google::is_connected()) : ?>
                    <tr>
                        <th scope="row"><?php _e('Google Calendar', 'wp-calendar'); ?></th>
                        <td>
                            <select name="wp_calendar_google_calendar_id">
                                <option value="primary"><?php _e('Primary Calendar', 'wp-calendar'); ?></option>
                                <?php
                                $calendars = WP_Calendar_Google::list_calendars();
                                $selected_calendar = get_option('wp_calendar_google_calendar_id', 'primary');
                                foreach ($calendars as $id => $name) {
                                    if ($id === 'primary') continue; // Skip primary as it's already in the list
                                    echo '<option value="' . esc_attr($id) . '" ' . selected($selected_calendar, $id, false) . '>' . esc_html($name) . '</option>';
                                }
                                ?>
                            </select>
                            <p class="description"><?php _e('Select which Google Calendar to sync with.', 'wp-calendar'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Connection Status', 'wp-calendar'); ?></th>
                        <td>
                            <p><span class="dashicons dashicons-yes" style="color: green;"></span> <?php _e('Connected to Google Calendar', 'wp-calendar'); ?></p>
                            <p><a href="?page=wp-calendar-settings&tab=google&disconnect=1" class="button"><?php _e('Disconnect', 'wp-calendar'); ?></a></p>
                        </td>
                    </tr>
                <?php else : ?>
                    <tr>
                        <th scope="row"><?php _e('Connection Status', 'wp-calendar'); ?></th>
                        <td>
                            <p><span class="dashicons dashicons-no" style="color: red;"></span> <?php _e('Not connected to Google Calendar', 'wp-calendar'); ?></p>
                            <?php if (get_option('wp_calendar_google_client_id') && get_option('wp_calendar_google_client_secret')) : ?>
                                <p><a href="<?php echo esc_url(WP_Calendar_Google::get_auth_url()); ?>" class="button button-primary"><?php _e('Connect to Google Calendar', 'wp-calendar'); ?></a></p>
                            <?php else : ?>
                                <p class="description"><?php _e('Enter your Google API credentials above and save settings to connect.', 'wp-calendar'); ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
        <?php elseif ($active_tab == 'pages') : ?>
            <?php settings_fields('wp_calendar_pages'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Calendar Page', 'wp-calendar'); ?></th>
                    <td>
                        <?php
                        wp_dropdown_pages(array(
                            'name' => 'wp_calendar_calendar_page',
                            'selected' => get_option('wp_calendar_calendar_page'),
                            'show_option_none' => __('Select a page', 'wp-calendar'),
                        ));
                        ?>
                        <p class="description"><?php _e('Select the page where you want to display the booking calendar. Add the shortcode [wp_calendar] to this page.', 'wp-calendar'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Account Page', 'wp-calendar'); ?></th>
                    <td>
                        <?php
                        wp_dropdown_pages(array(
                            'name' => 'wp_calendar_account_page',
                            'selected' => get_option('wp_calendar_account_page'),
                            'show_option_none' => __('Select a page', 'wp-calendar'),
                        ));
                        ?>
                        <p class="description"><?php _e('Select the page where users can manage their appointments. Add the shortcode [wp_calendar_account] to this page.', 'wp-calendar'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Login Page', 'wp-calendar'); ?></th>
                    <td>
                        <?php
                        wp_dropdown_pages(array(
                            'name' => 'wp_calendar_login_page',
                            'selected' => get_option('wp_calendar_login_page'),
                            'show_option_none' => __('Select a page', 'wp-calendar'),
                        ));
                        ?>
                        <p class="description"><?php _e('Select the page where users can log in. Add the shortcode [wp_calendar_login] to this page.', 'wp-calendar'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Registration Page', 'wp-calendar'); ?></th>
                    <td>
                        <?php
                        wp_dropdown_pages(array(
                            'name' => 'wp_calendar_register_page',
                            'selected' => get_option('wp_calendar_register_page'),
                            'show_option_none' => __('Select a page', 'wp-calendar'),
                        ));
                        ?>
                        <p class="description"><?php _e('Select the page where users can register. Add the shortcode [wp_calendar_register] to this page.', 'wp-calendar'); ?></p>
                    </td>
                </tr>
            </table>
        <?php elseif ($active_tab == 'notifications') : ?>
            <?php settings_fields('wp_calendar_notifications'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Admin Email Notifications', 'wp-calendar'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wp_calendar_admin_notification_new" value="1" <?php checked(get_option('wp_calendar_admin_notification_new'), 1); ?>>
                            <?php _e('New appointment', 'wp-calendar'); ?>
                        </label><br>
                        <label>
                            <input type="checkbox" name="wp_calendar_admin_notification_cancelled" value="1" <?php checked(get_option('wp_calendar_admin_notification_cancelled'), 1); ?>>
                            <?php _e('Cancelled appointment', 'wp-calendar'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Admin Email', 'wp-calendar'); ?></th>
                    <td>
                        <input type="email" name="wp_calendar_admin_email" value="<?php echo esc_attr(get_option('wp_calendar_admin_email', get_option('admin_email'))); ?>" class="regular-text">
                        <p class="description"><?php _e('Email address to receive admin notifications. Leave blank to use the default admin email.', 'wp-calendar'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('User Email Notifications', 'wp-calendar'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wp_calendar_user_notification_new" value="1" <?php checked(get_option('wp_calendar_user_notification_new'), 1); ?>>
                            <?php _e('New appointment', 'wp-calendar'); ?>
                        </label><br>
                        <label>
                            <input type="checkbox" name="wp_calendar_user_notification_cancelled" value="1" <?php checked(get_option('wp_calendar_user_notification_cancelled'), 1); ?>>
                            <?php _e('Cancelled appointment', 'wp-calendar'); ?>
                        </label><br>
                        <label>
                            <input type="checkbox" name="wp_calendar_user_notification_reminder" value="1" <?php checked(get_option('wp_calendar_user_notification_reminder'), 1); ?>>
                            <?php _e('Appointment reminder', 'wp-calendar'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Reminder Time (hours)', 'wp-calendar'); ?></th>
                    <td>
                        <input type="number" name="wp_calendar_reminder_time" value="<?php echo esc_attr(get_option('wp_calendar_reminder_time', 24)); ?>" min="1" step="1">
                        <p class="description"><?php _e('Number of hours before an appointment to send a reminder email.', 'wp-calendar'); ?></p>
                    </td>
                </tr>
            </table>
        <?php endif; ?>
        
        <?php submit_button(); ?>
    </form>
</div>