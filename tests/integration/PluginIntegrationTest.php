<?php
/**
 * Integration test for plugin loading
 *
 * @package StarisianTechnologies\SparxstarAccessManager\Tests
 */

namespace StarisianTechnologies\SparxstarAccessManager\Tests;

use PHPUnit\Framework\TestCase;
use StarisianTechnologies\SparxstarAccessManager\Plugin;

/**
 * Test plugin integration
 */
class PluginIntegrationTest extends TestCase {
    /**
     * Test that plugin singleton works
     */
    public function test_plugin_singleton() {
        $plugin1 = Plugin::get_instance();
        $plugin2 = Plugin::get_instance();
        
        $this->assertSame( $plugin1, $plugin2, 'Plugin should be a singleton' );
    }

    /**
     * Test that plugin can be initialized
     */
    public function test_plugin_initialization() {
        $plugin = Plugin::get_instance();
        $plugin->init();
        
        // Should have SCF manager
        $scf_manager = $plugin->get_scf_manager();
        $this->assertInstanceOf(
            'StarisianTechnologies\SparxstarAccessManager\SecureCustomFieldManager',
            $scf_manager
        );
        
        // Should have rules engine
        $rules_engine = $plugin->get_rules_engine();
        $this->assertInstanceOf(
            'StarisianTechnologies\SparxstarAccessManager\RulesEngine',
            $rules_engine
        );
    }

    /**
     * Test full workflow
     */
    public function test_full_workflow() {
        $plugin = Plugin::get_instance();
        $plugin->init();
        
        // Get managers
        $scf_manager = $plugin->get_scf_manager();
        $rules_engine = $plugin->get_rules_engine();
        
        // Load options
        $scf_manager->load_options();
        
        // Get options (should be empty array with mocked functions)
        $options = $scf_manager->get_options();
        $this->assertIsArray( $options );
        
        // Enforce rules (should not throw exception)
        $rules_engine->enforce_rules();
        
        // Get rules
        $rules = $rules_engine->get_rules();
        $this->assertIsArray( $rules );
        
        $this->assertTrue( true, 'Full workflow completed successfully' );
    }
}
