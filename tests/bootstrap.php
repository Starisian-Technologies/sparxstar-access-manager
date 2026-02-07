<?php
/**
 * PHPUnit bootstrap file
 *
 * @package StarisianTechnologies\SparxstarAccessManager
 */

// Load Composer autoloader
if ( file_exists( dirname( __DIR__ ) . '/vendor/autoload.php' ) ) {
    require_once dirname( __DIR__ ) . '/vendor/autoload.php';
}

// Define test constants
define( 'SPARXSTAR_ACCESS_MANAGER_TESTS', true );

// Mock WordPress functions for unit testing if WordPress is not loaded
if ( ! function_exists( 'add_action' ) ) {
    function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
        // Mock implementation
    }
}

if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
        // Mock implementation
    }
}

if ( ! function_exists( 'apply_filters' ) ) {
    function apply_filters( $hook, $value, ...$args ) {
        return $value;
    }
}

if ( ! function_exists( 'do_action' ) ) {
    function do_action( $hook ) {
        // Mock implementation
    }
}

if ( ! function_exists( 'get_option' ) ) {
    function get_option( $option, $default = false ) {
        return $default;
    }
}

if ( ! function_exists( 'update_option' ) ) {
    function update_option( $option, $value ) {
        return true;
    }
}

if ( ! function_exists( 'add_option' ) ) {
    function add_option( $option, $value ) {
        return true;
    }
}

if ( ! function_exists( 'is_multisite' ) ) {
    function is_multisite() {
        return false;
    }
}

if ( ! function_exists( 'get_current_blog_id' ) ) {
    function get_current_blog_id() {
        return 1;
    }
}

if ( ! function_exists( 'switch_to_blog' ) ) {
    function switch_to_blog( $blog_id ) {
        // Mock implementation
    }
}

if ( ! function_exists( 'restore_current_blog' ) ) {
    function restore_current_blog() {
        // Mock implementation
    }
}

if ( ! function_exists( 'is_admin' ) ) {
    function is_admin() {
        return false;
    }
}

if ( ! function_exists( 'is_network_admin' ) ) {
    function is_network_admin() {
        return false;
    }
}

if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = 'default' ) {
        return $text;
    }
}

if ( ! function_exists( 'esc_html__' ) ) {
    function esc_html__( $text, $domain = 'default' ) {
        return htmlspecialchars( $text );
    }
}

if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $text ) {
        return htmlspecialchars( $text );
    }
}

if ( ! function_exists( 'esc_attr' ) ) {
    function esc_attr( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES );
    }
}

if ( ! function_exists( 'esc_textarea' ) ) {
    function esc_textarea( $text ) {
        return htmlspecialchars( $text );
    }
}

if ( ! function_exists( 'wp_json_encode' ) ) {
    function wp_json_encode( $data, $options = 0, $depth = 512 ) {
        return json_encode( $data, $options, $depth );
    }
}

if ( ! function_exists( 'register_activation_hook' ) ) {
    function register_activation_hook( $file, $callback ) {
        // Mock implementation
    }
}

if ( ! function_exists( 'register_deactivation_hook' ) ) {
    function register_deactivation_hook( $file, $callback ) {
        // Mock implementation
    }
}

if ( ! function_exists( 'plugin_dir_path' ) ) {
    function plugin_dir_path( $file ) {
        return dirname( $file ) . '/';
    }
}

if ( ! function_exists( 'plugin_dir_url' ) ) {
    function plugin_dir_url( $file ) {
        return 'https://example.com/wp-content/plugins/' . basename( dirname( $file ) ) . '/';
    }
}

if ( ! function_exists( 'plugin_basename' ) ) {
    function plugin_basename( $file ) {
        return basename( dirname( $file ) ) . '/' . basename( $file );
    }
}

if ( ! function_exists( 'current_user_can' ) ) {
    function current_user_can( $capability ) {
        return true;
    }
}

if ( ! function_exists( 'add_options_page' ) ) {
    function add_options_page( $page_title, $menu_title, $capability, $menu_slug, $callback ) {
        // Mock implementation
    }
}

if ( ! function_exists( 'register_setting' ) ) {
    function register_setting( $option_group, $option_name, $args = array() ) {
        // Mock implementation
    }
}

if ( ! function_exists( 'add_settings_section' ) ) {
    function add_settings_section( $id, $title, $callback, $page ) {
        // Mock implementation
    }
}

if ( ! function_exists( 'add_settings_field' ) ) {
    function add_settings_field( $id, $title, $callback, $page, $section, $args = array() ) {
        // Mock implementation
    }
}

if ( ! function_exists( 'settings_fields' ) ) {
    function settings_fields( $option_group ) {
        // Mock implementation
    }
}

if ( ! function_exists( 'do_settings_sections' ) ) {
    function do_settings_sections( $page ) {
        // Mock implementation
    }
}

if ( ! function_exists( 'submit_button' ) ) {
    function submit_button( $text = null, $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = null ) {
        // Mock implementation
    }
}

if ( ! function_exists( 'get_admin_page_title' ) ) {
    function get_admin_page_title() {
        return 'Admin Page';
    }
}

if ( ! function_exists( 'checked' ) ) {
    function checked( $checked, $current = true, $echo = true ) {
        $result = $checked == $current ? ' checked="checked"' : '';
        if ( $echo ) {
            echo $result;
        }
        return $result;
    }
}

if ( ! function_exists( 'add_settings_error' ) ) {
    function add_settings_error( $setting, $code, $message, $type = 'error' ) {
        // Mock implementation
    }
}

// Define plugin constants for testing
if ( ! defined( 'SPARXSTAR_ACCESS_MANAGER_PLUGIN_DIR' ) ) {
    define( 'SPARXSTAR_ACCESS_MANAGER_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
}

// Load plugin classes
require_once SPARXSTAR_ACCESS_MANAGER_PLUGIN_DIR . 'src/class-secure-custom-field-manager.php';
require_once SPARXSTAR_ACCESS_MANAGER_PLUGIN_DIR . 'src/class-rules-engine.php';
require_once SPARXSTAR_ACCESS_MANAGER_PLUGIN_DIR . 'src/class-admin-manager.php';
require_once SPARXSTAR_ACCESS_MANAGER_PLUGIN_DIR . 'src/class-plugin.php';
