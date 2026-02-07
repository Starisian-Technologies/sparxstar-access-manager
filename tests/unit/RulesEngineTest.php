<?php
/**
 * Test for Rules Engine
 *
 * @package StarisianTechnologies\SparxstarAccessManager\Tests
 */

namespace StarisianTechnologies\SparxstarAccessManager\Tests;

use PHPUnit\Framework\TestCase;
use StarisianTechnologies\SparxstarAccessManager\SecureCustomFieldManager;
use StarisianTechnologies\SparxstarAccessManager\RulesEngine;

/**
 * Test RulesEngine class
 */
class RulesEngineTest extends TestCase {
    /**
     * Test that rules engine can be instantiated
     */
    public function test_engine_can_be_instantiated() {
        $scf_manager = new SecureCustomFieldManager();
        $engine = new RulesEngine( $scf_manager );
        $this->assertInstanceOf( RulesEngine::class, $engine );
    }

    /**
     * Test getting rules returns array
     */
    public function test_get_rules_returns_array() {
        $scf_manager = new SecureCustomFieldManager();
        $engine = new RulesEngine( $scf_manager );
        $rules = $engine->get_rules();
        $this->assertIsArray( $rules );
    }

    /**
     * Test enforce rules doesn't throw exception
     */
    public function test_enforce_rules_executes() {
        $scf_manager = new SecureCustomFieldManager();
        $engine = new RulesEngine( $scf_manager );
        
        // Should not throw exception
        $engine->enforce_rules();
        $this->assertTrue( true );
    }
}
