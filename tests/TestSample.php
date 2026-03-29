<?php
/**
 * Sample test to verify PHPUnit is working.
 *
 * @package Alynt_404_Sitemap
 */

use Brain\Monkey;

class TestSample extends \PHPUnit\Framework\TestCase {

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_plugin_constants_defined() {
        $this->assertTrue( defined( 'ALYNT_404_VERSION' ) );
    }
}

