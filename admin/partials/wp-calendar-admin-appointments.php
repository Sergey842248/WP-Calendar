<?php
/**
 * Admin appointments list view
 */
if (!defined('ABSPATH')) {
    exit;
}

// Get appointments
global $wpdb;
$table_name = $wpdb->prefix . 'wp_calendar_appointments';

$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;
$offset = ($current_page - 1) * $per_page;

$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

$where = "1=1";
if (!empty($search)) {
    $where .= $wpdb->prepare(" AND (u.display_name LIKE %s OR u.user_email LIKE %s)", 
        '%' . $wpdb->esc_like($search) . '%', 
        '%' . $wpdb->esc_like($search) . '%'
    );
}

if (!empty($status)) {
    $where .= $wpdb->prepare(" AND a.status = %s", $status);
}

$total_items = $wpdb->get_var("
    SELECT COUNT(a.id) 
    FROM $table_name a
    LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID
    WHERE $where
");

$appointments = $wpdb->get_results($wpdb->prepare("
    SELECT a.*, u.display_name, u.user_email
    FROM $table_name a
    LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID
    WHERE $where
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
    LIMIT %d OFFSET %d
", $per_page, $offset), ARRAY_A);

$total_pages = ceil($total_items / $per_page);
?>

<div class="wrap wp-calendar-admin">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="get">
        <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>">
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <select name="status">
                    <option value=""><?php _e('All Statuses', 'wp-calendar'); ?></option>
                    <option value="confirmed" <?php selected($status, 'confirmed'); ?>><?php _e('Confirmed', 'wp-calendar'); ?></option>
                    <option value="pending" <?php selected($status, 'pending'); ?>><?php _e('Pending', 'wp-calendar'); ?></option>
                    <option value="cancelled" <?php selected($status, 'cancelled'); ?>><?php _e('Cancelled', 'wp-calendar'); ?></option>
                </select>
                <input type="submit" class="button" value="<?php esc_attr_e('Filter', 'wp-calendar'); ?>">
            </div>
            
            <div class="alignright">
                <p class="search-box">
                    <label class="screen-reader-text" for="appointment-search-input"><?php _e('Search Appointments', 'wp-calendar'); ?></label>
                    <input type="search" id="appointment-search-input" name="s" value="<?php echo esc_attr($search); ?>">
                    <input type="submit" id="search-submit" class="button" value="<?php esc_attr_e('Search Appointments', 'wp-calendar'); ?>">
                </p>
            </div>
            
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
    </form>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('ID', 'wp-calendar'); ?></th>
                <th><?php _e('User', 'wp-calendar'); ?></th>
                <th><?php _e('Date', 'wp-calendar'); ?></th>
                <th><?php _e('Time', 'wp-calendar'); ?></th>
                <th><?php _e('Status', 'wp-calendar'); ?></th>
                <th><?php _e('Notes', 'wp-calendar'); ?></th>
                <th><?php _e('Actions', 'wp-calendar'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($appointments)) : ?>
                <tr>
                    <td colspan="7"><?php _e('No appointments found.', 'wp-calendar'); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ($appointments as $appointment) : ?>
                    <tr>
                        <td><?php echo esc_html($appointment['id']); ?></td>
                        <td>
                            <?php if (!empty($appointment['display_name'])) : ?>
                                <?php echo esc_html($appointment['display_name']); ?><br>
                                <small><?php echo esc_html($appointment['user_email']); ?></small>
                            <?php else : ?>
                                <?php _e('Unknown User', 'wp-calendar'); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($appointment['appointment_date']))); ?></td>
                        <td><?php echo esc_html(date_i18n(get_option('time_format'), strtotime($appointment['appointment_time']))); ?></td>
                        <td>
                            <?php
                            $status_labels = array(
                                'confirmed' => __('Confirmed', 'wp-calendar'),
                                'pending' => __('Pending', 'wp-calendar'),
                                'cancelled' => __('Cancelled', 'wp-calendar'),
                            );
                            $status_class = array(
                                'confirmed' => 'status-confirmed',
                                'pending' => 'status-pending',
                                'cancelled' => 'status-cancelled',
                            );
                            $status = isset($status_labels[$appointment['status']]) ? $appointment['status'] : 'pending';
                            ?>
                            <span class="wp-calendar-status <?php echo esc_attr($status_class[$status]); ?>">
                                <?php echo esc_html($status_labels[$status]); ?>
                            </span>
                        </td>
                        <td><?php echo wp_kses_post(nl2br($appointment['notes'])); ?></td>
                        <td>
                            <a href="#" class="edit-appointment" data-id="<?php echo esc_attr($appointment['id']); ?>"><?php _e('Edit', 'wp-calendar'); ?></a> | 
                            <a href="#" class="delete-appointment" data-id="<?php echo esc_attr($appointment['id']); ?>"><?php _e('Delete', 'wp-calendar'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Appointment Dialog (same as in calendar view) -->
<div id="wp-calendar-appointment-dialog" title="<?php _e('Appointment', 'wp-calendar'); ?>" style="display:none;">
    <form id="wp-calendar-appointment-form">
        <input type="hidden" id="appointment-id" name="id" value="0">
        
        <div class="form-field">
            <label for="appointment-user"><?php _e('User', 'wp-calendar'); ?></label>
            <select id="appointment-user" name="user_id" required>
                <option value=""><?php _e('Select User', 'wp-calendar'); ?></option>
                <?php
                $users = get_users(array('role__in' => array('subscriber', 'customer')));
                foreach ($users as $user) {
                    echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . ' (' . esc_html($user->user_email) . ')</option>';
                }
                ?>
            </select>
        </div>
        
        <div class="form-field">
            <label for="appointment-date"><?php _e('Date', 'wp-calendar'); ?></label>
            <input type="text" id="appointment-date" name="date" class="datepicker" required>
        </div>
        
        <div class="form-field">
            <label for="appointment-time"><?php _e('Time', 'wp-calendar'); ?></label>
            <select id="appointment-time" name="time" required>
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
        
        <div class="form-field">
            <label for="appointment-status"><?php _e('Status', 'wp-calendar'); ?></label>
            <select id="appointment-status" name="status">
                <option value="confirmed"><?php _e('Confirmed', 'wp-calendar'); ?></option>
                <option value="pending"><?php _e('Pending', 'wp-calendar'); ?></option>
                <option value="cancelled"><?php _e('Cancelled', 'wp-calendar'); ?></option>
            </select>
        </div>
        
        <div class="form-field">
            <label for="appointment-notes"><?php _e('Notes', 'wp-calendar'); ?></label>
            <textarea id="appointment-notes" name="notes" rows="4"></textarea>
        </div>
    </form>
</div>