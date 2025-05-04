<?php
/**
 * Admin blocked times view
 */
if (!defined('ABSPATH')) {
    exit;
}

// Get blocked times
global $wpdb;
$table_name = $wpdb->prefix . 'wp_calendar_blocked_times';

$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;
$offset = ($current_page - 1) * $per_page;

$total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

$blocked_times = $wpdb->get_results($wpdb->prepare("
    SELECT * FROM $table_name
    ORDER BY blocked_date DESC, blocked_time DESC
    LIMIT %d OFFSET %d
", $per_page, $offset), ARRAY_A);

$total_pages = ceil($total_items / $per_page);

// Days of week
$days_of_week = array(
    0 => __('Sunday', 'wp-calendar'),
    1 => __('Monday', 'wp-calendar'),
    2 => __('Tuesday', 'wp-calendar'),
    3 => __('Wednesday', 'wp-calendar'),
    4 => __('Thursday', 'wp-calendar'),
    5 => __('Friday', 'wp-calendar'),
    6 => __('Saturday', 'wp-calendar'),
);
?>

<div class="wrap wp-calendar-admin">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="wp-calendar-admin-actions">
        <button id="wp-calendar-add-blocked" class="button button-primary"><?php _e('Add Blocked Time', 'wp-calendar'); ?></button>
    </div>
    
    <div class="tablenav top">
        <div class="tablenav-pages">
            <?php if ($total_pages > 1) : ?>
            <span class="displaying-num"><?php printf(_n('%s item', '%s items', $total_items, 'wp-calendar'), number_format_i18n($total_items)); ?></span>
            <span class="pagination-links">
                <?php
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total' => $total_pages,
                    'current' => $current_page,
                ));
                ?>
            </span>
            <?php endif; ?>
        </div>
    </div>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('ID', 'wp-calendar'); ?></th>
                <th><?php _e('Type', 'wp-calendar'); ?></th>
                <th><?php _e('Date', 'wp-calendar'); ?></th>
                <th><?php _e('Time', 'wp-calendar'); ?></th>
                <th><?php _e('Day of Week', 'wp-calendar'); ?></th>
                <th><?php _e('Actions', 'wp-calendar'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($blocked_times)) : ?>
                <tr>
                    <td colspan="6"><?php _e('No blocked times found.', 'wp-calendar'); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ($blocked_times as $blocked) : ?>
                    <tr>
                        <td><?php echo esc_html($blocked['id']); ?></td>
                        <td>
                            <?php
                            if (!empty($blocked['blocked_date']) && !empty($blocked['blocked_time'])) {
                                _e('Specific Date/Time', 'wp-calendar');
                            } elseif (!empty($blocked['blocked_date']) && empty($blocked['blocked_time'])) {
                                _e('Full Day', 'wp-calendar');
                            } elseif (empty($blocked['blocked_date']) && !empty($blocked['blocked_time']) && $blocked['is_recurring']) {
                                _e('Recurring Time Slot', 'wp-calendar');
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if (!empty($blocked['blocked_date'])) {
                                echo esc_html(date_i18n(get_option('date_format'), strtotime($blocked['blocked_date'])));
                            } else {
                                echo '—';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if (!empty($blocked['blocked_time'])) {
                                echo esc_html(date_i18n(get_option('time_format'), strtotime($blocked['blocked_time'])));
                            } else {
                                echo '—';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if ($blocked['is_recurring'] && isset($days_of_week[$blocked['day_of_week']])) {
                                echo esc_html($days_of_week[$blocked['day_of_week']]);
                            } else {
                                echo '—';
                            }
                            ?>
                        </td>
                        <td>
                            <a href="#" class="edit-blocked" data-id="<?php echo esc_attr($blocked['id']); ?>"><?php _e('Edit', 'wp-calendar'); ?></a> | 
                            <a href="#" class="delete-blocked" data-id="<?php echo esc_attr($blocked['id']); ?>"><?php _e('Delete', 'wp-calendar'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Block Time Dialog -->
<div id="wp-calendar-block-dialog" title="<?php _e('Block Time', 'wp-calendar'); ?>" style="display:none;">
    <form id="wp-calendar-block-form">
        <input type="hidden" id="block-id" name="id" value="0">
        
        <div class="form-field">
            <label for="block-type"><?php _e('Block Type', 'wp-calendar'); ?></label>
            <select id="block-type" name="block_type">
                <option value="specific"><?php _e('Specific Date/Time', 'wp-calendar'); ?></option>
                <option value="full_day"><?php _e('Full Day', 'wp-calendar'); ?></option>
                <option value="recurring"><?php _e('Recurring Time Slot', 'wp-calendar'); ?></option>
            </select>
        </div>
        
        <div class="form-field block-specific block-full-day">
            <label for="block-date"><?php _e('Date', 'wp-calendar'); ?></label>
            <input type="text" id="block-date" name="date" class="datepicker">
        </div>
        
        <div class="form-field block-specific block-recurring">
            <label for="block-time"><?php _e('Time', 'wp-calendar'); ?></label>
            <select id="block-time" name="time">
                <option value=""><?php _e('Select Time', 'wp-calendar'); ?></option>
                <?php
                $business_hours_start = get_option('wp_calendar_business_hours_start', '09:00');
                $business_hours_end = get_option('wp_calendar_business_hours_end', '17:00');
                $slot_duration = get_option('wp_calendar_time_slot_duration', 60);
                
                $start = new DateTime('today ' . $business_hours_start);
                $end = new DateTime('today ' . $business_hours_end);
                $interval = new DateInterval('PT' . $slot_duration . 'M');
                
                $current = clone $start;
                
                while ($current < $end) {
                    $time_value = $current->format('H:i:s');
                    $time_display = $current->format(get_option('time_format'));
                    echo '<option value="' . esc_attr($time_value) . '">' . esc_html($time_display) . '</option>';
                    $current->add($interval);
                }
                ?>
            </select>
        </div>
        
        <div class="form-field block-recurring">
            <label for="block-day"><?php _e('Day of Week', 'wp-calendar'); ?></label>
            <select id="block-day" name="day_of_week">
                <?php foreach ($days_of_week as $value => $label) : ?>
                    <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>