<?php
/**
 * Public login view
 */
if (!defined('ABSPATH')) {
    exit;
}

$redirect = !empty($atts['redirect']) ? $atts['redirect'] : '';
?>

<div class="wp-calendar-login">
    <form id="wp-calendar-login-form">
        <?php wp_nonce_field('wp_calendar_login', 'wp_calendar_login_nonce'); ?>
        
        <?php if (!empty($redirect)) : ?>
            <input type="hidden" name="redirect" value="<?php echo esc_attr($redirect); ?>">
        <?php endif; ?>
        
        <div class="form-field">
            <label for="login-username"><?php _e('Username or Email', 'wp-calendar'); ?></label>
            <input type="text" id="login-username" name="username" required>
        </div>
        
        <div class="form-field">
            <label for="login-password"><?php _e('Password', 'wp-calendar'); ?></label>
            <input type="password" id="login-password" name="password" required>
        </div>
        
        <div class="form-field">
            <label>
                <input type="checkbox" name="remember" value="1">
                <?php _e('Remember Me', 'wp-calendar'); ?>
            </label>
        </div>
        
        <div class="form-field">
            <button type="submit" class="button button-primary"><?php _e('Log In', 'wp-calendar'); ?></button>
        </div>
        
        <div class="wp-calendar-login-links">
            <a href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php _e('Forgot Password?', 'wp-calendar'); ?></a>
            
            <?php if (get_option('users_can_register')) : ?>
                | <a href="<?php echo esc_url(get_permalink(get_option('wp_calendar_register_page'))); ?>"><?php _e('Register', 'wp-calendar'); ?></a>
            <?php endif; ?>
        </div>
    </form>
    
    <div id="wp-calendar-login-message" style="display:none;"></div>
</div>