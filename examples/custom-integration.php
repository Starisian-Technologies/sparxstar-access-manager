<?php
/**
 * Example: Custom Integration with Sparxstar Access Manager
 * 
 * This file demonstrates how to integrate custom functionality
 * with the Sparxstar Access Manager plugin.
 * 
 * Place this in your theme's functions.php or as a separate plugin.
 * 
 * @package Examples
 */

/**
 * Example 1: Add custom SCF options programmatically
 */
add_filter( 'sparxstar_access_manager_scf_options', 'my_custom_scf_options' );
function my_custom_scf_options( $options ) {
    // Add custom field configuration
    $options['api_access'] = array(
        'enabled' => true,
        'api_key' => get_option( 'my_custom_api_key' ),
        'endpoints' => array( '/api/v1/protected', '/api/v1/admin' ),
    );
    
    // Add user role configuration
    $options['user_restrictions'] = array(
        'allowed_roles' => array( 'administrator', 'editor' ),
        'denied_roles' => array( 'subscriber' ),
    );
    
    return $options;
}

/**
 * Example 2: Add custom runtime rules
 */
add_filter( 'sparxstar_access_manager_rules', 'my_custom_rules' );
function my_custom_rules( $rules ) {
    // Add custom access control rule
    $rules[] = array(
        'type' => 'custom_api_access',
        'enabled' => true,
        'endpoint_prefix' => '/api/v1/',
        'required_capability' => 'edit_posts',
    );
    
    // Add custom content restriction
    $rules[] = array(
        'type' => 'custom_content_restriction',
        'enabled' => true,
        'post_types' => array( 'page', 'post' ),
        'minimum_role' => 'editor',
    );
    
    return $rules;
}

/**
 * Example 3: Handle custom rule types
 */
add_filter( 'sparxstar_access_manager_handle_rule', 'my_handle_custom_rule', 10, 2 );
function my_handle_custom_rule( $handled, $rule ) {
    // Handle custom API access rule
    if ( $rule['type'] === 'custom_api_access' && $rule['enabled'] ) {
        add_filter( 'rest_pre_dispatch', function( $result, $server, $request ) use ( $rule ) {
            $route = $request->get_route();
            
            // Check if route matches our protected endpoint prefix
            if ( strpos( $route, $rule['endpoint_prefix'] ) === 0 ) {
                // Check user capability
                if ( ! current_user_can( $rule['required_capability'] ) ) {
                    return new WP_Error(
                        'rest_forbidden',
                        __( 'You do not have permission to access this endpoint.' ),
                        array( 'status' => 403 )
                    );
                }
            }
            
            return $result;
        }, 10, 3 );
        
        return true; // Mark as handled
    }
    
    // Handle custom content restriction
    if ( $rule['type'] === 'custom_content_restriction' && $rule['enabled'] ) {
        add_action( 'template_redirect', function() use ( $rule ) {
            if ( is_singular( $rule['post_types'] ) ) {
                $user = wp_get_current_user();
                $allowed_roles = array( $rule['minimum_role'], 'administrator' );
                
                // Check if user has required role
                if ( ! array_intersect( $allowed_roles, $user->roles ) ) {
                    // Redirect to login or show error
                    wp_redirect( wp_login_url( get_permalink() ) );
                    exit;
                }
            }
        } );
        
        return true; // Mark as handled
    }
    
    return $handled;
}

/**
 * Example 4: React to options being loaded
 */
add_action( 'sparxstar_access_manager_options_loaded', 'my_options_loaded_handler' );
function my_options_loaded_handler( $options ) {
    // Log when options are loaded (useful for debugging)
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( 'Sparxstar Access Manager options loaded for site ' . get_current_blog_id() );
    }
    
    // Initialize custom integration based on loaded options
    if ( isset( $options['api_access'] ) && $options['api_access']['enabled'] ) {
        // Initialize API access control
        do_action( 'my_custom_api_init', $options['api_access'] );
    }
}

/**
 * Example 5: React to rules being enforced
 */
add_action( 'sparxstar_access_manager_rules_enforced', 'my_rules_enforced_handler' );
function my_rules_enforced_handler( $rules ) {
    // Count enabled rules
    $enabled_count = count( array_filter( $rules, function( $rule ) {
        return isset( $rule['enabled'] ) && $rule['enabled'];
    } ) );
    
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( "Enforced {$enabled_count} rules on site " . get_current_blog_id() );
    }
}

/**
 * Example 6: Programmatically update SCF options for current site
 */
function my_update_scf_options() {
    // Get the plugin instance
    $plugin = \StarisianTechnologies\SparxstarAccessManager\Plugin::get_instance();
    $scf_manager = $plugin->get_scf_manager();
    
    // Get current options
    $options = $scf_manager->get_plugin_options();
    
    // Update SCF options
    $options['scf_options']['my_custom_field'] = 'my_custom_value';
    $options['scf_options']['security_level'] = 'high';
    
    // Save updated options
    $scf_manager->update_plugin_options( $options );
}

/**
 * Example 7: Check if plugin is enabled before running custom code
 */
function my_custom_functionality() {
    $plugin = \StarisianTechnologies\SparxstarAccessManager\Plugin::get_instance();
    $scf_manager = $plugin->get_scf_manager();
    
    // Only run if plugin is enabled for this site
    if ( $scf_manager->is_enabled() ) {
        // Your custom code here
        $secure_field = $scf_manager->get_option( 'secure_field_name', 'default_value' );
        
        // Use the secure field value
        if ( $secure_field === 'restricted' ) {
            // Apply restrictions
        }
    }
}

/**
 * Example 8: Multi-site - Run code on all sites
 */
function my_multisite_update() {
    if ( ! is_multisite() ) {
        return;
    }
    
    // Get all sites
    $sites = get_sites( array( 'number' => 1000 ) );
    
    foreach ( $sites as $site ) {
        // Switch to site
        switch_to_blog( $site->blog_id );
        
        // Get plugin instance for this site
        $plugin = \StarisianTechnologies\SparxstarAccessManager\Plugin::get_instance();
        $scf_manager = $plugin->get_scf_manager();
        
        // Update options for this site
        if ( $scf_manager->is_enabled() ) {
            // Update site-specific configuration
            $options = $scf_manager->get_plugin_options();
            $options['scf_options']['updated'] = current_time( 'mysql' );
            $scf_manager->update_plugin_options( $options );
        }
        
        // Restore original site
        restore_current_blog();
    }
}

/**
 * Example 9: Add custom admin notice based on SCF options
 */
add_action( 'admin_notices', 'my_custom_admin_notice' );
function my_custom_admin_notice() {
    $plugin = \StarisianTechnologies\SparxstarAccessManager\Plugin::get_instance();
    $scf_manager = $plugin->get_scf_manager();
    
    // Check if plugin is enabled
    if ( ! $scf_manager->is_enabled() ) {
        return;
    }
    
    // Check for specific SCF option
    $security_level = $scf_manager->get_option( 'security_level' );
    
    if ( $security_level === 'high' ) {
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p><strong>Security Notice:</strong> High security mode is enabled for this site.</p>';
        echo '</div>';
    }
}

/**
 * Example 10: Hook into specific rule types
 */
add_action( 'sparxstar_access_manager_access_control_rule', 'my_access_control_handler' );
function my_access_control_handler( $rule ) {
    // Custom handling for access control rules
    if ( isset( $rule['condition'] ) && $rule['condition'] === 'user_role' ) {
        // Add custom logic for user role conditions
        $required_role = $rule['value'];
        
        add_filter( 'user_has_cap', function( $allcaps, $caps, $args, $user ) use ( $required_role ) {
            // Custom capability checking based on required role
            return $allcaps;
        }, 10, 4 );
    }
}
