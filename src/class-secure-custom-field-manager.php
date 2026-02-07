<?php
/**
 * Secure Custom Field Manager
 *
 * @package StarisianTechnologies\SparxstarAccessManager
 */

namespace StarisianTechnologies\SparxstarAccessManager;

/**
 * Manages Secure Custom Field options for the current site
 */
class SecureCustomFieldManager {
    /**
     * Loaded SCF options
     *
     * @var array
     */
    private $scf_options = array();

    /**
     * Plugin options
     *
     * @var array
     */
    private $plugin_options = array();

    /**
     * Constructor
     */
    public function __construct() {
        // Constructor
    }

    /**
     * Load SCF options from database
     * This loads site-specific options (no network-level options)
     */
    public function load_options() {
        // Get site-specific options. Use false as default so we can detect "option missing".
        $this->plugin_options = get_option( 'sparxstar_access_manager_options', false );

        // Initialize defaults if the option does not exist yet.
        if ( false === $this->plugin_options ) {
            $this->plugin_options = array(
                'enabled'     => true,
                'scf_options' => array(),
            );

            // Persist defaults so subsequent requests see a consistent structure.
            update_option( 'sparxstar_access_manager_options', $this->plugin_options );
        }

        // Load SCF options, always ensuring an array.
        if ( isset( $this->plugin_options['scf_options'] ) && is_array( $this->plugin_options['scf_options'] ) ) {
            $this->scf_options = $this->plugin_options['scf_options'];
        } else {
            $this->scf_options = array();
        }

        // Apply filters to allow other plugins to modify options
        $this->scf_options = apply_filters( 'sparxstar_access_manager_scf_options', $this->scf_options );

        // Hook to allow external SCF integration
        do_action( 'sparxstar_access_manager_options_loaded', $this->scf_options );
    }

    /**
     * Get all SCF options
     *
     * @return array
     */
    public function get_options() {
        return $this->scf_options;
    }

    /**
     * Get a specific SCF option
     *
     * @param string $key Option key
     * @param mixed  $default Default value
     * @return mixed
     */
    public function get_option( $key, $default = null ) {
        return isset( $this->scf_options[ $key ] ) ? $this->scf_options[ $key ] : $default;
    }

    /**
     * Set SCF options
     *
     * @param array $options Options to set
     * @return bool
     */
    public function set_options( $options ) {
        $this->scf_options = $options;
        $this->plugin_options['scf_options'] = $options;
        
        return update_option( 'sparxstar_access_manager_options', $this->plugin_options );
    }

    /**
     * Check if plugin is enabled for current site
     *
     * @return bool
     */
    public function is_enabled() {
        return isset( $this->plugin_options['enabled'] ) && $this->plugin_options['enabled'];
    }

    /**
     * Get plugin options
     *
     * @return array
     */
    public function get_plugin_options() {
        return $this->plugin_options;
    }

    /**
     * Update plugin options
     *
     * @param array $options Options to update
     * @return bool
     */
    public function update_plugin_options( $options ) {
        $this->plugin_options = array_merge( $this->plugin_options, $options );
        return update_option( 'sparxstar_access_manager_options', $this->plugin_options );
    }
}
