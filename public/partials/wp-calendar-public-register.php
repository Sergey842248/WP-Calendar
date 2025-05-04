<?php
/**
 * Public registration view
 */
if (!defined('ABSPATH')) {
    exit;
}

$redirect = !empty($atts['redirect']) ? $atts['redirect'] : '';
?>

<div class="wp-calendar-register">
    <form id="wp-calendar-register-form">
        <?php wp_nonce_field('wp_calendar_register', 'wp_calendar_register_nonce'); ?>
        
        <?php if (!empty($redirect)) : ?>
            <input type="hidden" name="redirect" value="<?php echo esc_attr($redirect); ?>">
        <?php endif; ?>
        
        <div class="form-field">
            <label for="register-username"><?php _e('Username', 'wp-calendar'); ?></label>
            <input type="text" id="register-username" name="username" required>
        </div>
        
        <div class="form-field">
            <label for="register-email"><?php _e('Email', 'wp-calendar'); ?></label>
            <input type="email" id="register-email" name="email" required>
        </div>
        
        <div class="form-field">
            <label for="register-password"><?php _e('Password', 'wp-calendar'); ?></label>
            <input type="password" id="register-password" name="password" required>
        </div>
        
        <div class="form-field">
            <label for="register-password-confirm"><?php _e('Confirm Password', 'wp-calendar'); ?></label>
            <input type="password" id="register-password-confirm" name="password_confirm" required>
        </div>
        
        <div class="form-field">
            <button type="submit" class="button button-primary"><?php _e('Register', 'wp-calendar'); ?></button>
        </div>
        
        <div class="wp-calendar-register-links">
            <a href="<?php echo esc_url(get_permalink(get_option('wp_calendar_login_page'))); ?>"><?php _e('Already have an account? Log in', 'wp-calendar'); ?></a>
        </div>
    </form>
    
    <div id="wp-calendar-register-message" style="display:none;"></div>
</div>