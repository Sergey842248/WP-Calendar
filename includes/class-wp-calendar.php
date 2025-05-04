<?php
/**
 * The core plugin class.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 */
class WP_Calendar {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @var      WP_Calendar_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     */
    public function __construct() {
        $this->version = WP_CALENDAR_VERSION;
        $this->plugin_name = 'wp-calendar';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once WP_CALENDAR_PLUGIN_DIR . 'includes/class-wp-calendar-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once WP_CALENDAR_PLUGIN_DIR . 'includes/class-wp-calendar-i18n.php';

        /**
         * The class responsible for database operations.
         */
        require_once WP_CALENDAR_PLUGIN_DIR . 'includes/class-wp-calendar-db.php';

        /**
         * The class responsible for email notifications.
         */
        require_once WP_CALENDAR_PLUGIN_DIR . 'includes/class-wp-calendar-notifications.php';

        /**
         * The class responsible for Google Calendar integration.
         */
        require_once WP_CALENDAR_PLUGIN_DIR . 'includes/class-wp-calendar-google.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once WP_CALENDAR_PLUGIN_DIR . 'admin/class-wp-calendar-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once WP_CALENDAR_PLUGIN_DIR . 'public/class-wp-calendar-public.php';

        $this->loader = new WP_Calendar_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     */
    private function set_locale() {
        $plugin_i18n = new WP_Calendar_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     */
    private function define_admin_hooks() {
        $plugin_admin = new WP_Calendar_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');

        // AJAX handlers
        $this->loader->add_action('wp_ajax_wp_calendar_get_appointments', $plugin_admin, 'ajax_get_appointments');
        $this->loader->add_action('wp_ajax_wp_calendar_get_appointment', $plugin_admin, 'ajax_get_appointment');
        $this->loader->add_action('wp_ajax_wp_calendar_save_appointment', $plugin_admin, 'ajax_save_appointment');
        $this->loader->add_action('wp_ajax_wp_calendar_delete_appointment', $plugin_admin, 'ajax_delete_appointment');
        $this->loader->add_action('wp_ajax_wp_calendar_get_blocked_time', $plugin_admin, 'ajax_get_blocked_time');
        $this->loader->add_action('wp_ajax_wp_calendar_save_blocked_time', $plugin_admin, 'ajax_save_blocked_time');
        $this->loader->add_action('wp_ajax_wp_calendar_delete_blocked_time', $plugin_admin, 'ajax_delete_blocked_time');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     */
    private function define_public_hooks() {
        $plugin_public = new WP_Calendar_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('init', $plugin_public, 'register_shortcodes');

        // AJAX handlers
        $this->loader->add_action('wp_ajax_wp_calendar_get_public_events', $plugin_public, 'ajax_get_public_events');
        $this->loader->add_action('wp_ajax_nopriv_wp_calendar_get_public_events', $plugin_public, 'ajax_get_public_events');
        $this->loader->add_action('wp_ajax_wp_calendar_get_available_times', $plugin_public, 'ajax_get_available_times');
        $this->loader->add_action('wp_ajax_nopriv_wp_calendar_get_available_times', $plugin_public, 'ajax_get_available_times');
        $this->loader->add_action('wp_ajax_wp_calendar_book_appointment', $plugin_public, 'ajax_book_appointment');
        $this->loader->add_action('wp_ajax_wp_calendar_cancel_appointment', $plugin_public, 'ajax_cancel_appointment');
        $this->loader->add_action('wp_ajax_wp_calendar_login', $plugin_public, 'ajax_login');
        $this->loader->add_action('wp_ajax_nopriv_wp_calendar_login', $plugin_public, 'ajax_login');
        $this->loader->add_action('wp_ajax_nopriv_wp_calendar_register', $plugin_public, 'ajax_register');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return    WP_Calendar_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}