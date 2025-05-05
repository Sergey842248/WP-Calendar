<table class="form-table">
    <tr>
        <th scope="row">
            <label for="wp_calendar_business_hours_start"><?php _e('Business Hours Start', 'wp-calendar'); ?></label>
        </th>
        <td>
            <input type="time" name="wp_calendar_business_hours_start" id="wp_calendar_business_hours_start" value="<?php echo esc_attr(get_option('wp_calendar_business_hours_start', '09:00')); ?>" class="regular-text">
            <p class="description"><?php _e('Start time of your business hours.', 'wp-calendar'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="wp_calendar_business_hours_end"><?php _e('Business Hours End', 'wp-calendar'); ?></label>
        </th>
        <td>
            <input type="time" name="wp_calendar_business_hours_end" id="wp_calendar_business_hours_end" value="<?php echo esc_attr(get_option('wp_calendar_business_hours_end', '17:00')); ?>" class="regular-text">
            <p class="description"><?php _e('End time of your business hours.', 'wp-calendar'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="wp_calendar_time_slot_duration"><?php _e('Time Slot Duration', 'wp-calendar'); ?></label>
        </th>
        <td>
            <input type="number" name="wp_calendar_time_slot_duration" id="wp_calendar_time_slot_duration" value="<?php echo esc_attr(get_option('wp_calendar_time_slot_duration', '60')); ?>" class="small-text" min="15" step="15">
            <p class="description"><?php _e('Duration of each appointment slot in minutes.', 'wp-calendar'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="wp_calendar_require_login"><?php _e('Require Login', 'wp-calendar'); ?></label>
        </th>
        <td>
            <input type="checkbox" name="wp_calendar_require_login" id="wp_calendar_require_login" value="1" <?php checked(get_option('wp_calendar_require_login', 1), 1); ?>>
            <p class="description"><?php _e('Require users to be logged in to book appointments.', 'wp-calendar'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="wp_calendar_cancellation_period"><?php _e('Cancellation Period', 'wp-calendar'); ?></label>
        </th>
        <td>
            <input type="number" name="wp_calendar_cancellation_period" id="wp_calendar_cancellation_period" value="<?php echo esc_attr(get_option('wp_calendar_cancellation_period', '24')); ?>" class="small-text" min="0">
            <p class="description"><?php _e('Hours before appointment when cancellation is no longer allowed.', 'wp-calendar'); ?></p>
        </td>
    </tr>
</table>