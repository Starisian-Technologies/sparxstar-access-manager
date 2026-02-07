<?php
/**
 * Main Plugin Class
 *
 * @package StarisianTechnologies\SparxstarAccessManager
 */

namespace StarisianTechnologies\SparxstarAccessManager;

/**
 * Main plugin class
 */
class Plugin {
    /**
     * Plugin instance
     *
     * @var Plugin
     */
    private static $instance = null;

    /**
     * Secure Custom Field Manager
     *
     * @var SecureCustomFieldManager
     */
    private $scf_manager;

    /**
     * Rules Engine
     *
     * @var RulesEngine
     */
    private $rules_engine;

    /**
     * Admin Manager
     *
     * @var AdminManager
     */
    private $admin_manager;

    /**
     * Get plugin instance
     *
     * @return Plugin
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Private constructor for singleton
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        // Load required files
        $this->load_dependencies();

        // Initialize managers
        $this->scf_manager = new SecureCustomFieldManager();
        $this->rules_engine = new RulesEngine( $this->scf_manager );
        $this->admin_manager = new AdminManager( $this->scf_manager );

        // Setup hooks
        $this->setup_hooks();
    }

    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once SPARXSTAR_ACCESS_MANAGER_PLUGIN_DIR . 'src/class-secure-custom-field-manager.php';
        require_once SPARXSTAR_ACCESS_MANAGER_PLUGIN_DIR . 'src/class-rules-engine.php';
        require_once SPARXSTAR_ACCESS_MANAGER_PLUGIN_DIR . 'src/class-admin-manager.php';
    }

    /**
     * Setup WordPress hooks
     */
    private function setup_hooks() {
        // Initialize on init hook
        add_action( 'init', array( $this->scf_manager, 'load_options' ), 5 );
        add_action( 'init', array( $this->rules_engine, 'enforce_rules' ), 10 );

        // Admin hooks
        if ( is_admin() ) {
            add_action( 'admin_menu', array( $this->admin_manager, 'add_admin_menu' ) );
            add_action( 'admin_init', array( $this->admin_manager, 'register_settings' ) );
        }

        // For multisite, ensure plugin is loaded on each subsite
        if ( is_multisite() ) {
            add_action( 'wpmu_new_blog', array( $this, 'activate_on_new_site' ), 10, 1 );
        }
    }

    /**
     * Activate plugin for a specific site
     *
     * @param int|null $site_id Site ID or null for single site
     */
    public static function activate_for_site( $site_id = null ) {
        if ( $site_id ) {
            switch_to_blog( $site_id );
        }

        // Set default options
        $default_options = array(
            'enabled' => true,
            'scf_options' => array(),
            'rules' => array(),
        );

        add_option( 'sparxstar_access_manager_options', $default_options );

        if ( $site_id ) {
            restore_current_blog();
        }
    }

    /**
     * Activate on new multisite blog
     *
     * @param int $site_id Site ID
     */
    public function activate_on_new_site( $site_id ) {
        self::activate_for_site( $site_id );
    }

    /**
     * Deactivation cleanup
     */
    public static function deactivate() {
        // Cleanup tasks if needed
        // Note: We don't delete options on deactivation to preserve settings
    }

    /**
     * Get SCF Manager
     *
     * @return SecureCustomFieldManager
     */
    public function get_scf_manager() {
        return $this->scf_manager;
    }

    /**
     * Get Rules Engine
     *
     * @return RulesEngine
     */
    public function get_rules_engine() {
        return $this->rules_engine;
    }
}
