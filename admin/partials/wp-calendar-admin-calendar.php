<?php
/**
 * Admin calendar view
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap wp-calendar-admin">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="wp-calendar-admin-container">
        <div class="wp-calendar-sidebar">
            <div class="wp-calendar-legend">
                <h3><?php _e('Legend', 'wp-calendar'); ?></h3>
                <ul>
                    <li><span class="wp-calendar-legend-color" style="background-color: #4CAF50;"></span> <?php _e('Confirmed Appointments', 'wp-calendar'); ?></li>
                    <li><span class="wp-calendar-legend-color" style="background-color: #FFC107;"></span> <?php _e('Pending Appointments', 'wp-calendar'); ?></li>
                    <li><span class="wp-calendar-legend-color" style="background-color: #F44336;"></span> <?php _e('Cancelled Appointments', 'wp-calendar'); ?></li>
                    <li><span class="wp-calendar-legend-color" style="background-color: #9E9E9E;"></span> <?php _e('Blocked Times', 'wp-calendar'); ?></li>
                </ul>
            </div>
            
            <div class="wp-calendar-actions">
                <h3><?php _e('Actions', 'wp-calendar'); ?></h3>
                <button id="wp-calendar-new-appointment" class="button button-primary"><?php _e('New Appointment', 'wp-calendar'); ?></button>
                <button id="wp-calendar-block-time" class="button"><?php _e('Block Time', 'wp-calendar'); ?></button>
            </div>
        </div>
        
        <div class="wp-calendar-main">
            <!-- Calendar removed as per user request -->
        </div>
    </div>
</div>

<!-- Appointment Dialog -->
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
                
                $times = array();
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
                <option value="0"><?php _e('Sunday', 'wp-calendar'); ?></option>
                <option value="1"><?php _e('Monday', 'wp-calendar'); ?></option>
                <option value="2"><?php _e('Tuesday', 'wp-calendar'); ?></option>
                <option value="3"><?php _e('Wednesday', 'wp-calendar'); ?></option>
                <option value="4"><?php _e('Thursday', 'wp-calendar'); ?></option>
                <option value="5"><?php _e('Friday', 'wp-calendar'); ?></option>
                <option value="6"><?php _e('Saturday', 'wp-calendar'); ?></option>
            </select>
        </div>
    </form>
</div>
