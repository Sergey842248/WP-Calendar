<table class="form-table">
    <tr>
        <th scope="row">
            <label for="wp_calendar_booking_page"><?php _e('Booking Page', 'wp-calendar'); ?></label>
        </th>
        <td>
            <?php
            wp_dropdown_pages(array(
                'name' => 'wp_calendar_booking_page',
                'show_option_none' => __('Select a page', 'wp-calendar'),
                'option_none_value' => '0',
                'selected' => get_option('wp_calendar_booking_page', 0)
            ));
            ?>
            <p class="description"><?php _e('The page where the booking form will be displayed.', 'wp-calendar'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="wp_calendar_account_page"><?php _e('Account Page', 'wp-calendar'); ?></label>
        </th>
        <td>
            <?php
            wp_dropdown_pages(array(
                'name' => 'wp_calendar_account_page',
                'show_option_none' => __('Select a page', 'wp-calendar'),
                'option_none_value' => '0',
                'selected' => get_option('wp_calendar_account_page', 0)
            ));
            ?>
            <p class="description"><?php _e('The page where users can view their appointments.', 'wp-calendar'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="wp_calendar_login_page"><?php _e('Login Page', 'wp-calendar'); ?></label>
        </th>
        <td>
            <?php
            wp_dropdown_pages(array(
                'name' => 'wp_calendar_login_page',
                'show_option_none' => __('Select a page', 'wp-calendar'),
                'option_none_value' => '0',
                'selected' => get_option('wp_calendar_login_page', 0)
            ));
            ?>
            <p class="description"><?php _e('The page where users can log in.', 'wp-calendar'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="wp_calendar_register_page"><?php _e('Register Page', 'wp-calendar'); ?></label>
        </th>
        <td>
            <?php
            wp_dropdown_pages(array(
                'name' => 'wp_calendar_register_page',
                'show_option_none' => __('Select a page', 'wp-calendar'),
                'option_none_value' => '0',
                'selected' => get_option('wp_calendar_register_page', 0)
            ));
            ?>
            <p class="description"><?php _e('The page where users can register.', 'wp-calendar'); ?></p>
        </td>
    </tr>
</table>