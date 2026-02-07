<?php
/**
 * Plugin Name: Sparxstar Access Manager
 * Plugin URI: https://github.com/Starisian-Technologies/sparxstar-access-manager
 * Description: Infrastructure plugin that loads Secure Custom Field options and enforces runtime rules with multi-site support
 * Version: 1.0.0
 * Author: Starisian Technologies
 * Author URI: https://starisian.tech
 * License: MIT
 * Text Domain: sparxstar-access-manager
 * Domain Path: /languages
 * Network: true
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define plugin constants
define( 'SPARXSTAR_ACCESS_MANAGER_VERSION', '1.0.0' );
define( 'SPARXSTAR_ACCESS_MANAGER_PLUGIN_FILE', __FILE__ );
define( 'SPARXSTAR_ACCESS_MANAGER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SPARXSTAR_ACCESS_MANAGER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SPARXSTAR_ACCESS_MANAGER_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Load Composer autoloader if available
if ( file_exists( SPARXSTAR_ACCESS_MANAGER_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
    require_once SPARXSTAR_ACCESS_MANAGER_PLUGIN_DIR . 'vendor/autoload.php';
}

// Load the plugin
require_once SPARXSTAR_ACCESS_MANAGER_PLUGIN_DIR . 'src/class-plugin.php';

/**
 * Initialize the plugin
 */
function sparxstar_access_manager_init() {
    $plugin = StarisianTechnologies\SparxstarAccessManager\Plugin::get_instance();
    $plugin->init();
}

// Initialize on plugins_loaded hook to ensure all plugins are loaded
add_action( 'plugins_loaded', 'sparxstar_access_manager_init', 10 );

/**
 * Activation hook - runs on each subsite when plugin is activated
 */
function sparxstar_access_manager_activate() {
    // Check if we're on a multisite
    if ( is_multisite() ) {
        // Get the current site ID
        $current_site_id = get_current_blog_id();
        
        // Initialize default options for this subsite
        StarisianTechnologies\SparxstarAccessManager\Plugin::activate_for_site( $current_site_id );
    } else {
        // Single site activation
        StarisianTechnologies\SparxstarAccessManager\Plugin::activate_for_site( null );
    }
}
register_activation_hook( __FILE__, 'sparxstar_access_manager_activate' );

/**
 * Deactivation hook
 */
function sparxstar_access_manager_deactivate() {
    // Cleanup if needed (but preserve settings)
    StarisianTechnologies\SparxstarAccessManager\Plugin::deactivate();
}
register_deactivation_hook( __FILE__, 'sparxstar_access_manager_deactivate' );
