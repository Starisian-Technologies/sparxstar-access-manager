<?php
/**
 * Test for Secure Custom Field Manager
 *
 * @package StarisianTechnologies\SparxstarAccessManager\Tests
 */

namespace StarisianTechnologies\SparxstarAccessManager\Tests;

use PHPUnit\Framework\TestCase;
use StarisianTechnologies\SparxstarAccessManager\SecureCustomFieldManager;

/**
 * Test SecureCustomFieldManager class
 */
class SecureCustomFieldManagerTest extends TestCase {
    /**
     * Test that manager can be instantiated
     */
    public function test_manager_can_be_instantiated() {
        $manager = new SecureCustomFieldManager();
        $this->assertInstanceOf( SecureCustomFieldManager::class, $manager );
    }

    /**
     * Test getting options returns array
     */
    public function test_get_options_returns_array() {
        $manager = new SecureCustomFieldManager();
        $options = $manager->get_options();
        $this->assertIsArray( $options );
    }

    /**
     * Test getting specific option with default
     */
    public function test_get_option_with_default() {
        $manager = new SecureCustomFieldManager();
        $value = $manager->get_option( 'nonexistent_key', 'default_value' );
        $this->assertEquals( 'default_value', $value );
    }

    /**
     * Test plugin is enabled by default
     */
    public function test_is_enabled_default() {
        $manager = new SecureCustomFieldManager();
        // With mocked get_option returning empty array, enabled should be false
        $this->assertFalse( $manager->is_enabled() );
    }
}
