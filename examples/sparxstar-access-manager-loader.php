<?php
/**
 * Plugin Name: Sparxstar Access Manager Loader
 * Description: Loads the Sparxstar Access Manager MU-plugin for all subsites
 * Version: 1.0.0
 * Author: Starisian Technologies
 * License: MIT
 * 
 * This file should be placed in wp-content/mu-plugins/ directory
 * It loads the main plugin from the mu-plugins subdirectory
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define the path to the main plugin file
$plugin_file = WPMU_PLUGIN_DIR . '/sparxstar-access-manager/sparxstar-access-manager.php';

// Load the plugin if it exists
if ( file_exists( $plugin_file ) ) {
    require_once $plugin_file;
} else {
    // Log error if plugin file not found
    if ( function_exists( 'error_log' ) ) {
        error_log( 'Sparxstar Access Manager plugin file not found at: ' . $plugin_file );
    }
}
