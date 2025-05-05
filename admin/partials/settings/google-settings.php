<table class="form-table">
    <tr>
        <th scope="row">
            <label for="wp_calendar_google_calendar_integration"><?php _e('Google Calendar Integration', 'wp-calendar'); ?></label>
        </th>
        <td>
            <select name="wp_calendar_google_calendar_integration" id="wp_calendar_google_calendar_integration">
                <option value="disabled" <?php selected(get_option('wp_calendar_google_calendar_integration', 'disabled'), 'disabled'); ?>><?php _e('Disabled', 'wp-calendar'); ?></option>
                <option value="enabled" <?php selected(get_option('wp_calendar_google_calendar_integration', 'disabled'), 'enabled'); ?>><?php _e('Enabled', 'wp-calendar'); ?></option>
            </select>
            <p class="description"><?php _e('Enable or disable Google Calendar integration.', 'wp-calendar'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="wp_calendar_google_client_id"><?php _e('Google Client ID', 'wp-calendar'); ?></label>
        </th>
        <td>
            <input type="text" name="wp_calendar_google_client_id" id="wp_calendar_google_client_id" value="<?php echo esc_attr(get_option('wp_calendar_google_client_id', '')); ?>" class="regular-text">
            <p class="description"><?php _e('Your Google API Client ID.', 'wp-calendar'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="wp_calendar_google_client_secret"><?php _e('Google Client Secret', 'wp-calendar'); ?></label>
        </th>
        <td>
            <input type="password" name="wp_calendar_google_client_secret" id="wp_calendar_google_client_secret" value="<?php echo esc_attr(get_option('wp_calendar_google_client_secret', '')); ?>" class="regular-text">
            <p class="description"><?php _e('Your Google API Client Secret.', 'wp-calendar'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="wp_calendar_google_calendar_id"><?php _e('Google Calendar ID', 'wp-calendar'); ?></label>
        </th>
        <td>
            <input type="text" name="wp_calendar_google_calendar_id" id="wp_calendar_google_calendar_id" value="<?php echo esc_attr(get_option('wp_calendar_google_calendar_id', '')); ?>" class="regular-text">
            <p class="description"><?php _e('The ID of the Google Calendar to use.', 'wp-calendar'); ?></p>
        </td>
    </tr>
</table>