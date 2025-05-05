<?php
/**
 * Google Calendar integration
 */
class WP_Calendar_Google {
    
    /**
     * Google API Client
     */
    private static $client = null;
    
    /**
     * Initialize Google API Client
     */
    private static function init_client() {
        if (self::$client !== null) {
            return self::$client;
        }
        
        // Prüfen, ob die Integration aktiviert ist
        if (get_option('wp_calendar_google_calendar_integration') !== 'enabled') {
            return false;
        }
        
        // Prüfen, ob die erforderlichen Einstellungen vorhanden sind
        $client_id = get_option('wp_calendar_google_client_id');
        $client_secret = get_option('wp_calendar_google_client_secret');
        
        if (empty($client_id) || empty($client_secret)) {
            return false;
        }
        
        // Google API Client initialisieren
        require_once plugin_dir_path(dirname(__FILE__)) . 'vendor/autoload.php';
        
        $client = new Google_Client();
        $client->setClientId($client_id);
        $client->setClientSecret($client_secret);
        $client->setRedirectUri(admin_url('admin.php?page=wp-calendar-settings&tab=google'));
        $client->addScope(Google_Service_Calendar::CALENDAR);
        
        // Token aus den Optionen laden
        $access_token = get_option('wp_calendar_google_access_token');
        if (!empty($access_token)) {
            $client->setAccessToken($access_token);
            
            // Token aktualisieren, wenn es abgelaufen ist
            if ($client->isAccessTokenExpired()) {
                $refresh_token = $client->getRefreshToken();
                if (!empty($refresh_token)) {
                    $client->fetchAccessTokenWithRefreshToken($refresh_token);
                    update_option('wp_calendar_google_access_token', $client->getAccessToken());
                }
            }
        }
        
        self::$client = $client;
        return $client;
    }
    
    /**
     * Sync appointment with Google Calendar
     */
    public static function sync_appointment($appointment_id) {
        $client = self::init_client();
        if (!$client) {
            return false;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_calendar_appointments';
        
        // Termindetails abrufen
        $appointment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $appointment_id
        ), ARRAY_A);
        
        if (!$appointment) {
            return false;
        }
        
        // Benutzerdetails abrufen
        $user = get_userdata($appointment['user_id']);
        if (!$user) {
            return false;
        }
        
        // Google Calendar Service initialisieren
        $service = new Google_Service_Calendar($client);
        $calendar_id = get_option('wp_calendar_google_calendar_id', 'primary');
        
        // Event erstellen oder aktualisieren
        $event = new Google_Service_Calendar_Event(array(
            'summary' => sprintf(__('Appointment with %s', 'wp-calendar'), $user->display_name),
            'description' => $appointment['notes'],
            'start' => array(
                'dateTime' => date('c', strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time'])),
                'timeZone' => wp_timezone_string(),
            ),
            'end' => array(
                'dateTime' => date('c', strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time']) + (get_option('wp_calendar_time_slot_duration', 60) * 60)),
                'timeZone' => wp_timezone_string(),
            ),
            'attendees' => array(
                array('email' => $user->user_email)
            ),
            'reminders' => array(
                'useDefault' => true
            )
        ));
        
        try {
            // Prüfen, ob bereits ein Event existiert
            if (!empty($appointment['google_event_id'])) {
                // Event aktualisieren
                $service->events->update($calendar_id, $appointment['google_event_id'], $event);
            } else {
                // Neues Event erstellen
                $created_event = $service->events->insert($calendar_id, $event);
                
                // Event-ID speichern
                $wpdb->update(
                    $table_name,
                    array('google_event_id' => $created_event->getId()),
                    array('id' => $appointment_id),
                    array('%s'),
                    array('%d')
                );
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Google Calendar Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete event from Google Calendar
     */
    public static function delete_event($appointment_id) {
        $client = self::init_client();
        if (!$client) {
            return false;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_calendar_appointments';
        
        // Termindetails abrufen
        $appointment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $appointment_id
        ), ARRAY_A);
        
        if (!$appointment || empty($appointment['google_event_id'])) {
            return false;
        }
        
        // Google Calendar Service initialisieren
        $service = new Google_Service_Calendar($client);
        $calendar_id = get_option('wp_calendar_google_calendar_id', 'primary');
        
        try {
            // Event löschen
            $service->events->delete($calendar_id, $appointment['google_event_id']);
            return true;
        } catch (Exception $e) {
            error_log('Google Calendar Error: ' . $e->getMessage());
            return false;
        }
    }
}