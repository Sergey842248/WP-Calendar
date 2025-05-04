<?php
/**
 * Admin blocked times template.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get action
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle delete action
if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Blocked time deleted successfully.', 'wp-calendar') . '</p></div>';
}

// Display appropriate view based on action
if ($action == 'add' || $action == 'edit') {
    // Get blocked time data if editing
    $blocked_time = array(
        'id' => 0,
        'blocked_date' => '',
        'blocked_time' => '',
        'is_recurring' => 0,
        'day_of_week' => 0,
    );

    if ($action == 'edit' && $id > 0) {
        $blocked_time_data = WP_Calendar_DB::get_blocked_time($id);
        if ($blocked_time_data) {
            $blocked_time = $blocked_time_data;
        }
    }
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">
            <?php echo $action == 'add' ? __('Add Blocked Time', 'wp-calendar') : __('Edit Blocked Time', 'wp-calendar'); ?>
        </h1>
        <a href="?page=wp-calendar-blocked" class="page-title-action"><?php _e('Back to List', 'wp-calendar'); ?></a>
        <hr class="wp-header-end">

        <div id="wp-calendar-message" class="notice" style="display:none;"></div>

        <form id="wp-calendar-blocked-time-form" method="post">
            <input type="hidden" id="blocked_id" name="blocked_id" value="<?php echo esc_attr($blocked_time['id']); ?>">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="wp_calendar_is_recurring"><?php _e('Recurring Block', 'wp-calendar'); ?></label></th>
                    <td>
                        <label>
                            <input type="checkbox" id="wp_calendar_is_recurring" name="wp_calendar_is_recurring" value="1" <?php checked($blocked_time['is_recurring'], 1); ?>>
                            <?php _e('This is a recurring block', 'wp-calendar'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr class="wp-calendar-date-options">
                    <th scope="row"><label for="blocked_date"><?php _e('Date', 'wp-calendar'); ?></label></th>
                    <td>
                        <input type="text" id="blocked_date" name="blocked_date" class="wp-calendar-datepicker" value="<?php echo esc_attr($blocked_time['blocked_date']); ?>" required>
                    </td>
                </tr>
                
                <tr class="wp-calendar-recurring-options" style="display:none;">
                    <th scope="row"><label for="day_of_week"><?php _e('Day of Week', 'wp-calendar'); ?></label></th>
                    <td>
                        <select id="day_of_week" name="day_of_week">
                            <option value="0" <?php selected($blocked_time['day_of_week'], 0); ?>><?php _e('Sunday', 'wp-calendar'); ?></option>
                            <option value="1" <?php selected($blocked_time['day_of_week'], 1); ?>><?php _e('Monday', 'wp-calendar'); ?></option>
                            <option value="2" <?php selected($blocked_time['day_of_week'], 2); ?>><?php _e('Tuesday', 'wp-calendar'); ?></option>
                            <option value="3" <?php selected($blocked_time['day_of_week'], 3); ?>><?php _e('Wednesday', 'wp-calendar'); ?></option>
                            <option value="4" <?php selected($blocked_time['day_of_week'], 4); ?>><?php _e('Thursday', 'wp-calendar'); ?></option>
                            <option value="5" <?php selected($blocked_time['day_of_week'], 5); ?>><?php _e('Friday', 'wp-calendar'); ?></option>
                            <option value="6" <?php selected($blocked_time['day_of_week'], 6); ?>><?php _e('Saturday', 'wp-calendar'); ?></option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="blocked_time"><?php _e('Time (optional)', 'wp-calendar'); ?></label></th>
                    <td>
                        <input type="time" id="blocked_time" name="blocked_time" value="<?php echo esc_attr($blocked_time['blocked_time']); ?>">
                        <p class="description"><?php _e('Leave empty to block the entire day', 'wp-calendar'); ?></p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" class="button button-primary"><?php _e('Save Blocked Time', 'wp-calendar'); ?></button>
            </p>
        </form>
    </div>
    <?php
} else {
    // List view
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php _e('Blocked Times', 'wp-calendar'); ?></h1>
        <a href="?page=wp-calendar-blocked&action=add" class="page-title-action"><?php _e('Add New', 'wp-calendar'); ?></a>
        <hr class="wp-header-end">

        <?php
        // Get blocked times
        $blocked_times = WP_Calendar_DB::get_blocked_times();
        ?>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col"><?php _e('ID', 'wp-calendar'); ?></th>
                    <th scope="col"><?php _e('Type', 'wp-calendar'); ?></th>
                    <th scope="col"><?php _e('Date/Day', 'wp-calendar'); ?></th>
                    <th scope="col"><?php _e('Time', 'wp-calendar'); ?></th>
                    <th scope="col"><?php _e('Actions', 'wp-calendar'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($blocked_times)) : ?>
                    <tr>
                        <td colspan="5"><?php _e('No blocked times found.', 'wp-calendar'); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($blocked_times as $blocked) : ?>
                        <tr>
                            <td><?php echo esc_html($blocked['id']); ?></td>
                            <td>
                                <?php 
                                if ($blocked['is_recurring']) {
                                    _e('Recurring', 'wp-calendar');
                                } else {
                                    _e('One-time', 'wp-calendar');
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                if ($blocked['is_recurring']) {
                                    $days = array(
                                        0 => __('Sunday', 'wp-calendar'),
                                        1 => __('Monday', 'wp-calendar'),
                                        2 => __('Tuesday', 'wp-calendar'),
                                        3 => __('Wednesday', 'wp-calendar'),
                                        4 => __('Thursday', 'wp-calendar'),
                                        5 => __('Friday', 'wp-calendar'),
                                        6 => __('Saturday', 'wp-calendar'),
                                    );
                                    echo esc_html($days[$blocked['day_of_week']]);
                                } else {
                                    echo esc_html($blocked['blocked_date']);
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                if (empty($blocked['blocked_time'])) {
                                    _e('All Day', 'wp-calendar');
                                } else {
                                    echo esc_html(date('g:i A', strtotime($blocked['blocked_time'])));
                                }
                                ?>
                            </td>
                            <td>
                                <a href="?page=wp-calendar-blocked&action=edit&id=<?php echo esc_attr($blocked['id']); ?>" class="button button-small"><?php _e('Edit', 'wp-calendar'); ?></a>
                                <a href="#" class="button button-small wp-calendar-delete-blocked-time" data-id="<?php echo esc_attr($blocked['id']); ?>"><?php _e('Delete', 'wp-calendar'); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}