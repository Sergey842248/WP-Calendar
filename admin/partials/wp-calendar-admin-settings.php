<?php
// Sicherstellen, dass die Seite nicht direkt aufgerufen werden kann
if (!defined('ABSPATH')) {
    exit;
}

// Speichern der Einstellungen, wenn das Formular abgeschickt wurde
if (isset($_POST['submit'])) {
    // Überprüfen des Nonce-Feldes
    check_admin_referer('wp_calendar_settings');
    
    // Speichern der Einstellungen basierend auf dem aktiven Tab
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
    
    switch ($active_tab) {
        case 'general':
            update_option('wp_calendar_business_hours_start', sanitize_text_field($_POST['wp_calendar_business_hours_start']));
            update_option('wp_calendar_business_hours_end', sanitize_text_field($_POST['wp_calendar_business_hours_end']));
            update_option('wp_calendar_time_slot_duration', intval($_POST['wp_calendar_time_slot_duration']));
            update_option('wp_calendar_require_login', isset($_POST['wp_calendar_require_login']) ? 1 : 0);
            update_option('wp_calendar_cancellation_period', intval($_POST['wp_calendar_cancellation_period']));
            break;
            
        case 'pages':
            update_option('wp_calendar_booking_page', intval($_POST['wp_calendar_booking_page']));
            update_option('wp_calendar_account_page', intval($_POST['wp_calendar_account_page']));
            update_option('wp_calendar_login_page', intval($_POST['wp_calendar_login_page']));
            update_option('wp_calendar_register_page', intval($_POST['wp_calendar_register_page']));
            break;
            
        case 'email':
            update_option('wp_calendar_admin_email', sanitize_email($_POST['wp_calendar_admin_email']));
            update_option('wp_calendar_email_notifications', isset($_POST['wp_calendar_email_notifications']) ? 1 : 0);
            update_option('wp_calendar_confirmation_email_subject', sanitize_text_field($_POST['wp_calendar_confirmation_email_subject']));
            update_option('wp_calendar_confirmation_email_body', wp_kses_post($_POST['wp_calendar_confirmation_email_body']));
            break;
            
        case 'google':
            update_option('wp_calendar_google_calendar_integration', sanitize_text_field($_POST['wp_calendar_google_calendar_integration']));
            update_option('wp_calendar_google_client_id', sanitize_text_field($_POST['wp_calendar_google_client_id']));
            update_option('wp_calendar_google_client_secret', sanitize_text_field($_POST['wp_calendar_google_client_secret']));
            update_option('wp_calendar_google_calendar_id', sanitize_text_field($_POST['wp_calendar_google_calendar_id']));
            break;
    }
    
    // Erfolgsmeldung anzeigen
    add_settings_error('wp_calendar_settings', 'settings_updated', __('Settings saved.', 'wp-calendar'), 'updated');
}

// Aktiven Tab bestimmen
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <h2 class="nav-tab-wrapper">
        <a href="?page=wp-calendar-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php _e('General', 'wp-calendar'); ?></a>
        <a href="?page=wp-calendar-settings&tab=pages" class="nav-tab <?php echo $active_tab == 'pages' ? 'nav-tab-active' : ''; ?>"><?php _e('Pages', 'wp-calendar'); ?></a>
        <a href="?page=wp-calendar-settings&tab=email" class="nav-tab <?php echo $active_tab == 'email' ? 'nav-tab-active' : ''; ?>"><?php _e('Email', 'wp-calendar'); ?></a>
        <a href="?page=wp-calendar-settings&tab=google" class="nav-tab <?php echo $active_tab == 'google' ? 'nav-tab-active' : ''; ?>"><?php _e('Google Calendar', 'wp-calendar'); ?></a>
    </h2>
    
    <form method="post" action="">
        <?php
        // Nonce-Feld hinzufügen
        wp_nonce_field('wp_calendar_settings');
        
        // Einstellungsfelder basierend auf dem aktiven Tab anzeigen
        switch ($active_tab) {
            case 'general':
                include_once plugin_dir_path(__FILE__) . 'settings/general-settings.php';
                break;
                
            case 'pages':
                include_once plugin_dir_path(__FILE__) . 'settings/page-settings.php';
                break;
                
            case 'email':
                include_once plugin_dir_path(__FILE__) . 'settings/email-settings.php';
                break;
                
            case 'google':
                include_once plugin_dir_path(__FILE__) . 'settings/google-settings.php';
                break;
        }
        ?>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'wp-calendar'); ?>">
        </p>
    </form>
</div>
