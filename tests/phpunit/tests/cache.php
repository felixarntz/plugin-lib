<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\Cache;

class Tests_Cache extends Unit_Test_Case {
	protected $cache;

	public function setUp() {
		parent::setUp();

		$this->cache = new Cache( $this->prefix );
	}

	public function test_add() {
		$result = $this->cache->add( 'somekey', 'value1' );
		$this->assertTrue( $result );

		$result = $this->cache->add( 'somekey', 'value2' );
		$this->assertFalse( $result );
	}

	public function test_delete() {
		$result = $this->cache->delete( 'deletekey' );
		$this->assertFalse( $result );

		wp_cache_add( 'deletekey', 'value', $this->prefix . 'general' );
		$result = $this->cache->delete( 'deletekey' );
		$this->assertTrue( $result );
	}

	public function test_get() {
		$result = $this->cache->get( 'getkey' );
		$this->assertFalse( $result );

		wp_cache_add( 'getkey', 'getvalue', $this->prefix . 'general' );
		$result = $this->cache->get( 'getkey' );
		$this->assertSame( 'getvalue', $result );
	}

	public function test_replace() {
		$result = $this->cache->replace( 'replacekey', 'value' );
		$this->assertFalse( $result );

		wp_cache_add( 'replacekey', 'value', $this->prefix . 'general' );
		$result = $this->cache->replace( 'replacekey', 'value2' );
		$this->assertTrue( $result );
	}

	public function test_set() {
		$result = $this->cache->set( 'setkey', 'setvalue' );
		$this->assertTrue( $result );

		$result = wp_cache_get( 'setkey', $this->prefix . 'general' );
		$this->assertSame( 'setvalue', $result );
	}
}
