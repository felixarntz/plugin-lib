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
 * @group actions
 */
class Tests_Actions_Trait extends Unit_Test_Case {
	protected static $actions;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$actions = Leaves_And_Love_Plugin_Loader::get( 'SP_Main' )->actions();
	}

	public function test_func() {
		$mode = 'func';

		self::$actions->add( $this->prefix . 'some_action', $mode );

		$result = self::$actions->has( $this->prefix . 'some_action', $mode );
		$this->assertEquals( 10, $result );

		ob_start();
		do_action( $this->prefix . 'some_action' );
		$result = ob_get_clean();
		$this->assertEquals( $mode, $result );

		self::$actions->remove( $this->prefix . 'some_action', $mode );

		$result = self::$actions->has( $this->prefix . 'some_action', $mode );
		$this->assertFalse( $result );

		ob_start();
		do_action( $this->prefix . 'some_action' );
		$result = ob_get_clean();
		$this->assertEmpty( $result );
	}

	public function test_public_method() {
		$mode = 'public';

		self::$actions->add( $this->prefix . 'some_action', $mode );

		$result = self::$actions->has( $this->prefix . 'some_action', $mode );
		$this->assertEquals( 10, $result );

		ob_start();
		do_action( $this->prefix . 'some_action' );
		$result = ob_get_clean();
		$this->assertEquals( $mode, $result );

		self::$actions->remove( $this->prefix . 'some_action', $mode );

		$result = self::$actions->has( $this->prefix . 'some_action', $mode );
		$this->assertFalse( $result );

		ob_start();
		do_action( $this->prefix . 'some_action' );
		$result = ob_get_clean();
		$this->assertEmpty( $result );
	}

	public function test_private_method() {
		$mode = 'private';

		self::$actions->add( $this->prefix . 'some_action', $mode );

		$result = self::$actions->has( $this->prefix . 'some_action', $mode );
		$this->assertEquals( 10, $result );

		ob_start();
		do_action( $this->prefix . 'some_action' );
		$result = ob_get_clean();
		$this->assertEquals( $mode, $result );

		self::$actions->remove( $this->prefix . 'some_action', $mode );

		$result = self::$actions->has( $this->prefix . 'some_action', $mode );
		$this->assertFalse( $result );

		ob_start();
		do_action( $this->prefix . 'some_action' );
		$result = ob_get_clean();
		$this->assertEmpty( $result );
	}
}
