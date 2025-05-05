<table class="form-table">
    <tr>
        <th scope="row">
            <label for="wp_calendar_admin_email"><?php _e('Admin Email', 'wp-calendar'); ?></label>
        </th>
        <td>
            <input type="email" name="wp_calendar_admin_email" id="wp_calendar_admin_email" value="<?php echo esc_attr(get_option('wp_calendar_admin_email', get_option('admin_email'))); ?>" class="regular-text">
            <p class="description"><?php _e('Email address to receive notifications about new appointments.', 'wp-calendar'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="wp_calendar_email_notifications"><?php _e('Email Notifications', 'wp-calendar'); ?></label>
        </th>
        <td>
            <input type="checkbox" name="wp_calendar_email_notifications" id="wp_calendar_email_notifications" value="1" <?php checked(get_option('wp_calendar_email_notifications', 1), 1); ?>>
            <p class="description"><?php _e('Send email notifications for new appointments.', 'wp-calendar'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="wp_calendar_confirmation_email_subject"><?php _e('Confirmation Email Subject', 'wp-calendar'); ?></label>
        </th>
        <td>
            <input type="text" name="wp_calendar_confirmation_email_subject" id="wp_calendar_confirmation_email_subject" value="<?php echo esc_attr(get_option('wp_calendar_confirmation_email_subject', __('Your appointment has been confirmed', 'wp-calendar'))); ?>" class="regular-text">
            <p class="description"><?php _e('Subject line for appointment confirmation emails.', 'wp-calendar'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="wp_calendar_confirmation_email_body"><?php _e('Confirmation Email Body', 'wp-calendar'); ?></label>
        </th>
        <td>
            <?php
            $editor_id = 'wp_calendar_confirmation_email_body';
            $content = get_option('wp_calendar_confirmation_email_body', __('Dear {user_name},

Your appointment has been confirmed for {appointment_date} at {appointment_time}.

Thank you for booking with us.

Regards,
{site_name}', 'wp-calendar'));
            $settings = array(
                'textarea_name' => 'wp_calendar_confirmation_email_body',
                'textarea_rows' => 10,
                'media_buttons' => false
            );
            wp_editor($content, $editor_id, $settings);
            ?>
            <p class="description"><?php _e('Available placeholders: {user_name}, {appointment_date}, {appointment_time}, {site_name}', 'wp-calendar'); ?></p>
        </td>
    </tr>
</table>