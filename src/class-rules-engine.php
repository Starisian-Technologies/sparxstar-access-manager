<?php
/**
 * Rules Engine
 *
 * @package StarisianTechnologies\SparxstarAccessManager
 */

namespace StarisianTechnologies\SparxstarAccessManager;

/**
 * Enforces runtime rules based on SCF options
 */
class RulesEngine {
    /**
     * SCF Manager instance
     *
     * @var SecureCustomFieldManager
     */
    private $scf_manager;

    /**
     * Rules loaded from options
     *
     * @var array
     */
    private $rules = array();

    /**
     * Constructor
     *
     * @param SecureCustomFieldManager $scf_manager SCF Manager instance
     */
    public function __construct( SecureCustomFieldManager $scf_manager ) {
        $this->scf_manager = $scf_manager;
    }

    /**
     * Enforce rules
     */
    public function enforce_rules() {
        // Skip if plugin is not enabled
        if ( ! $this->scf_manager->is_enabled() ) {
            return;
        }

        // Load rules from plugin options
        $plugin_options = $this->scf_manager->get_plugin_options();
        if ( isset( $plugin_options['rules'] ) ) {
            $this->rules = $plugin_options['rules'];
        }

        // Allow filtering of rules
        $this->rules = apply_filters( 'sparxstar_access_manager_rules', $this->rules );

        // Apply each rule
        foreach ( $this->rules as $rule ) {
            $this->apply_rule( $rule );
        }

        // Action hook after rules are enforced
        do_action( 'sparxstar_access_manager_rules_enforced', $this->rules );
    }

    /**
     * Apply a single rule
     *
     * @param array $rule Rule configuration
     */
    private function apply_rule( $rule ) {
        if ( ! is_array( $rule ) ) {
            return;
        }
        if ( ! isset( $rule['type'] ) || ! isset( $rule['enabled'] ) || ! $rule['enabled'] ) {
            return;
        }

        $rule_type = $rule['type'];
        
        // Allow custom rule handlers
        $handled = apply_filters( 'sparxstar_access_manager_handle_rule', false, $rule );
        
        if ( $handled ) {
            return;
        }

        // Built-in rule types
        switch ( $rule_type ) {
            case 'access_control':
                $this->apply_access_control_rule( $rule );
                break;
            case 'field_validation':
                $this->apply_field_validation_rule( $rule );
                break;
            case 'content_restriction':
                $this->apply_content_restriction_rule( $rule );
                break;
            default:
                // Unknown rule type
                do_action( 'sparxstar_access_manager_unknown_rule_type', $rule_type, $rule );
                break;
        }
    }

    /**
     * Apply access control rule
     *
     * @param array $rule Rule configuration
     */
    private function apply_access_control_rule( $rule ) {
        // Access control implementation
        do_action( 'sparxstar_access_manager_access_control_rule', $rule );
    }

    /**
     * Apply field validation rule
     *
     * @param array $rule Rule configuration
     */
    private function apply_field_validation_rule( $rule ) {
        // Field validation implementation
        do_action( 'sparxstar_access_manager_field_validation_rule', $rule );
    }

    /**
     * Apply content restriction rule
     *
     * @param array $rule Rule configuration
     */
    private function apply_content_restriction_rule( $rule ) {
        // Content restriction implementation
        do_action( 'sparxstar_access_manager_content_restriction_rule', $rule );
    }

    /**
     * Get all rules
     *
     * @return array
     */
    public function get_rules() {
        return $this->rules;
    }
}
