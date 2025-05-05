<?php
/**
 * Public account view
 */
if (!defined('ABSPATH')) {
    exit;
}

// Get user's appointments
$user_id = get_current_user_id();
global $wpdb;
$table_name = $wpdb->prefix . 'wp_calendar_appointments';

$appointments = $wpdb->get_results($wpdb->prepare("
    SELECT * FROM $table_name
    WHERE user_id = %d
    ORDER BY appointment_date DESC, appointment_time DESC
", $user_id), ARRAY_A);
?>

<div class="wp-calendar-account">
    <h2><?php _e('My Appointments', 'wp-calendar'); ?></h2>
    
    <div class="wp-calendar-account-actions">
        <a href="<?php echo esc_url(get_permalink(get_option('wp_calendar_calendar_page'))); ?>" class="button button-primary"><?php _e('Book New Appointment', 'wp-calendar'); ?></a>
    </div>
    
    <?php if (empty($appointments)) : ?>
        <p><?php _e('You have no appointments scheduled.', 'wp-calendar'); ?></p>
    <?php else : ?>
        <table class="wp-calendar-appointments-table">
            <thead>
                <tr>
                    <th><?php _e('Date', 'wp-calendar'); ?></th>
                    <th><?php _e('Time', 'wp-calendar'); ?></th>
                    <th><?php _e('Status', 'wp-calendar'); ?></th>
                    <th><?php _e('Notes', 'wp-calendar'); ?></th>
                    <th><?php _e('Actions', 'wp-calendar'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appointment) : ?>
                    <?php
                    $appointment_datetime = strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time']);
                    $current_time = current_time('timestamp');
                    $cancellation_period = get_option('wp_calendar_cancellation_period', 24) * 3600; // Convert hours to seconds
                    $can_cancel = ($appointment_datetime - $current_time) > $cancellation_period;
                    ?>
                    <tr>
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
                            <?php if ($appointment['status'] !== 'cancelled' && $can_cancel) : ?>
                                <a href="#" class="wp-calendar-cancel-appointment" data-id="<?php echo esc_attr($appointment['id']); ?>"><?php _e('Cancel', 'wp-calendar'); ?></a>
                            <?php elseif ($appointment['status'] !== 'cancelled' && !$can_cancel) : ?>
                                <span class="wp-calendar-notice"><?php _e('Cannot cancel', 'wp-calendar'); ?></span>
                                <span class="wp-calendar-tooltip"><?php _e('Cancellation period has passed', 'wp-calendar'); ?></span>
                            <?php else : ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Confirmation Dialog -->
<div id="wp-calendar-confirm-dialog" title="<?php _e('Confirm Cancellation', 'wp-calendar'); ?>" style="display:none;">
    <p><?php _e('Are you sure you want to cancel this appointment?', 'wp-calendar'); ?></p>
</div>
