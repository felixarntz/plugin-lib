<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love_Plugin_Loader;

/**
 * @group traits
 * @group hooks
 * @group filters
 */
class Tests_Filters_Trait extends Unit_Test_Case {
	protected static $filters;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$filters = Leaves_And_Love_Plugin_Loader::get( 'SP_Main' )->filters();
	}

	public function test_func() {
		$mode = 'func';

		self::$filters->add( $this->prefix . 'some_filter', $mode );

		$result = self::$filters->has( $this->prefix . 'some_filter', $mode );
		$this->assertEquals( 10, $result );

		$result = apply_filters( $this->prefix . 'some_filter', '' );
		$this->assertEquals( $mode, $result );

		self::$filters->remove( $this->prefix . 'some_filter', $mode );

		$result = self::$filters->has( $this->prefix . 'some_filter', $mode );
		$this->assertFalse( $result );

		$result = apply_filters( $this->prefix . 'some_filter', '' );
		$this->assertEmpty( $result );
	}

	public function test_public_method() {
		$mode = 'public';

		self::$filters->add( $this->prefix . 'some_filter', $mode );

		$result = self::$filters->has( $this->prefix . 'some_filter', $mode );
		$this->assertEquals( 10, $result );

		$result = apply_filters( $this->prefix . 'some_filter', '' );
		$this->assertEquals( $mode, $result );

		self::$filters->remove( $this->prefix . 'some_filter', $mode );

		$result = self::$filters->has( $this->prefix . 'some_filter', $mode );
		$this->assertFalse( $result );

		$result = apply_filters( $this->prefix . 'some_filter', '' );
		$this->assertEmpty( $result );
	}

	public function test_private_method() {
		$mode = 'private';

		self::$filters->add( $this->prefix . 'some_filter', $mode );

		$result = self::$filters->has( $this->prefix . 'some_filter', $mode );
		$this->assertEquals( 10, $result );

		$result = apply_filters( $this->prefix . 'some_filter', '' );
		$this->assertEquals( $mode, $result );

		self::$filters->remove( $this->prefix . 'some_filter', $mode );

		$result = self::$filters->has( $this->prefix . 'some_filter', $mode );
		$this->assertFalse( $result );

		$result = apply_filters( $this->prefix . 'some_filter', '' );
		$this->assertEmpty( $result );
	}
}
