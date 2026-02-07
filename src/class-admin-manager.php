<?php
/**
 * Admin Manager
 *
 * @package StarisianTechnologies\SparxstarAccessManager
 */

namespace StarisianTechnologies\SparxstarAccessManager;

/**
 * Manages admin interface for subsite-specific settings
 */
class AdminManager {
    /**
     * SCF Manager instance
     *
     * @var SecureCustomFieldManager
     */
    private $scf_manager;

    /**
     * Constructor
     *
     * @param SecureCustomFieldManager $scf_manager SCF Manager instance
     */
    public function __construct( SecureCustomFieldManager $scf_manager ) {
        $this->scf_manager = $scf_manager;
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // For multisite, only show on subsite admin, NOT network admin
        if ( is_multisite() && is_network_admin() ) {
            return;
        }

        add_options_page(
            __( 'Sparxstar Access Manager', 'sparxstar-access-manager' ),
            __( 'Access Manager', 'sparxstar-access-manager' ),
            'manage_options',
            'sparxstar-access-manager',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // For multisite, only register on subsite admin
        if ( is_multisite() && is_network_admin() ) {
            return;
        }

        register_setting(
            'sparxstar_access_manager_options',
            'sparxstar_access_manager_options',
            array(
                'sanitize_callback' => array( $this, 'sanitize_options' ),
            )
        );

        // General Settings Section
        add_settings_section(
            'sparxstar_access_manager_general',
            __( 'General Settings', 'sparxstar-access-manager' ),
            array( $this, 'render_general_section' ),
            'sparxstar-access-manager'
        );

        add_settings_field(
            'enabled',
            __( 'Enable Access Manager', 'sparxstar-access-manager' ),
            array( $this, 'render_enabled_field' ),
            'sparxstar-access-manager',
            'sparxstar_access_manager_general'
        );

        // SCF Options Section
        add_settings_section(
            'sparxstar_access_manager_scf',
            __( 'Secure Custom Field Options', 'sparxstar-access-manager' ),
            array( $this, 'render_scf_section' ),
            'sparxstar-access-manager'
        );

        add_settings_field(
            'scf_options',
            __( 'SCF Configuration', 'sparxstar-access-manager' ),
            array( $this, 'render_scf_options_field' ),
            'sparxstar-access-manager',
            'sparxstar_access_manager_scf'
        );

        // Rules Section
        add_settings_section(
            'sparxstar_access_manager_rules',
            __( 'Runtime Rules', 'sparxstar-access-manager' ),
            array( $this, 'render_rules_section' ),
            'sparxstar-access-manager'
        );

        add_settings_field(
            'rules',
            __( 'Rules Configuration', 'sparxstar-access-manager' ),
            array( $this, 'render_rules_field' ),
            'sparxstar-access-manager',
            'sparxstar_access_manager_rules'
        );
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Show multisite notice
        if ( is_multisite() ) {
            $site_id = get_current_blog_id();
            echo '<div class="notice notice-info"><p>';
            printf(
                /* translators: %d: Site ID */
                esc_html__( 'These settings are specific to this subsite (ID: %d). Each subsite has its own configuration.', 'sparxstar-access-manager' ),
                $site_id
            );
            echo '</p></div>';
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'sparxstar_access_manager_options' );
                do_settings_sections( 'sparxstar-access-manager' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render general section description
     */
    public function render_general_section() {
        echo '<p>' . esc_html__( 'Configure general settings for the Access Manager on this site.', 'sparxstar-access-manager' ) . '</p>';
    }

    /**
     * Render SCF section description
     */
    public function render_scf_section() {
        echo '<p>' . esc_html__( 'Configure Secure Custom Field options that will be loaded and enforced.', 'sparxstar-access-manager' ) . '</p>';
    }

    /**
     * Render rules section description
     */
    public function render_rules_section() {
        echo '<p>' . esc_html__( 'Define runtime rules that will be enforced based on SCF options.', 'sparxstar-access-manager' ) . '</p>';
    }

    /**
     * Render enabled field
     */
    public function render_enabled_field() {
        $options = get_option( 'sparxstar_access_manager_options', array() );
        $enabled = isset( $options['enabled'] ) ? $options['enabled'] : true;
        ?>
        <label>
            <input type="checkbox" name="sparxstar_access_manager_options[enabled]" value="1" <?php checked( $enabled, true ); ?> />
            <?php esc_html_e( 'Enable access management for this site', 'sparxstar-access-manager' ); ?>
        </label>
        <?php
    }

    /**
     * Render SCF options field
     */
    public function render_scf_options_field() {
        $options = get_option( 'sparxstar_access_manager_options', array() );
        $scf_options = isset( $options['scf_options'] ) ? $options['scf_options'] : array();
        $scf_json = wp_json_encode( $scf_options, JSON_PRETTY_PRINT );
        ?>
        <textarea name="sparxstar_access_manager_options[scf_options]" rows="10" class="large-text code"><?php echo esc_textarea( $scf_json ); ?></textarea>
        <p class="description"><?php esc_html_e( 'Enter SCF options in JSON format. Example: {"field_name": "value", "access_level": "admin"}', 'sparxstar-access-manager' ); ?></p>
        <?php
    }

    /**
     * Render rules field
     */
    public function render_rules_field() {
        $options = get_option( 'sparxstar_access_manager_options', array() );
        $rules = isset( $options['rules'] ) ? $options['rules'] : array();
        $rules_json = wp_json_encode( $rules, JSON_PRETTY_PRINT );
        ?>
        <textarea name="sparxstar_access_manager_options[rules]" rows="10" class="large-text code"><?php echo esc_textarea( $rules_json ); ?></textarea>
        <p class="description">
            <?php esc_html_e( 'Enter rules in JSON format. Example:', 'sparxstar-access-manager' ); ?>
            <br>
            <code>[{"type": "access_control", "enabled": true, "condition": "user_role", "value": "editor"}]</code>
        </p>
        <?php
    }

    /**
     * Sanitize options
     *
     * @param array $input Input options
     * @return array
     */
    public function sanitize_options( $input ) {
        $sanitized = array();

        // Sanitize enabled
        $sanitized['enabled'] = isset( $input['enabled'] ) && $input['enabled'];

        // Sanitize SCF options JSON
        if ( isset( $input['scf_options'] ) ) {
            $raw_scf_options = $input['scf_options'];
            if ( is_string( $raw_scf_options ) && trim( $raw_scf_options ) === '' ) {
                // Treat empty/whitespace-only input as an empty array without error.
                $sanitized['scf_options'] = array();
            } else {
                $scf_options = json_decode( $raw_scf_options, true );
                if ( json_last_error() === JSON_ERROR_NONE ) {
                    $sanitized['scf_options'] = $scf_options;
                } else {
                    add_settings_error(
                        'sparxstar_access_manager_options',
                        'invalid_scf_json',
                        __( 'Invalid JSON format for SCF options.', 'sparxstar-access-manager' )
                    );
                    $sanitized['scf_options'] = array();
                }
            }
        }

        // Sanitize rules JSON
        if ( isset( $input['rules'] ) ) {
            $raw_rules = $input['rules'];
            if ( is_string( $raw_rules ) && trim( $raw_rules ) === '' ) {
                // Treat empty/whitespace-only input as an empty array without error.
                $sanitized['rules'] = array();
            } else {
                $rules = json_decode( $raw_rules, true );
                if ( json_last_error() === JSON_ERROR_NONE ) {
                    $sanitized['rules'] = $rules;
                } else {
                    add_settings_error(
                        'sparxstar_access_manager_options',
                        'invalid_rules_json',
                        __( 'Invalid JSON format for rules.', 'sparxstar-access-manager' )
                    );
                    $sanitized['rules'] = array();
                }
            }
        }

        return $sanitized;
    }
}
