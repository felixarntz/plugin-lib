<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love_Plugin_Loader;

class Tests_Actions extends Unit_Test_Case {
	protected $actions;

	public function setUp() {
		parent::setUp();

		$this->actions = Leaves_And_Love_Plugin_Loader::get( 'SP_Main' )->actions();
	}

	public function test_func() {
		$mode = 'func';

		$this->actions->add( $this->prefix . 'some_action', $mode );

		$result = $this->actions->has( $this->prefix . 'some_action', $mode );
		$this->assertEquals( 10, $result );

		ob_start();
		do_action( $this->prefix . 'some_action' );
		$result = ob_get_clean();
		$this->assertEquals( $mode, $result );

		$this->actions->remove( $this->prefix . 'some_action', $mode );

		$result = $this->actions->has( $this->prefix . 'some_action', $mode );
		$this->assertFalse( $result );

		ob_start();
		do_action( $this->prefix . 'some_action' );
		$result = ob_get_clean();
		$this->assertEmpty( $result );
	}

	public function test_public_method() {
		$mode = 'public';

		$this->actions->add( $this->prefix . 'some_action', $mode );

		$result = $this->actions->has( $this->prefix . 'some_action', $mode );
		$this->assertEquals( 10, $result );

		ob_start();
		do_action( $this->prefix . 'some_action' );
		$result = ob_get_clean();
		$this->assertEquals( $mode, $result );

		$this->actions->remove( $this->prefix . 'some_action', $mode );

		$result = $this->actions->has( $this->prefix . 'some_action', $mode );
		$this->assertFalse( $result );

		ob_start();
		do_action( $this->prefix . 'some_action' );
		$result = ob_get_clean();
		$this->assertEmpty( $result );
	}

	public function test_private_method() {
		$mode = 'private';

		$this->actions->add( $this->prefix . 'some_action', $mode );

		$result = $this->actions->has( $this->prefix . 'some_action', $mode );
		$this->assertEquals( 10, $result );

		ob_start();
		do_action( $this->prefix . 'some_action' );
		$result = ob_get_clean();
		$this->assertEquals( $mode, $result );

		$this->actions->remove( $this->prefix . 'some_action', $mode );

		$result = $this->actions->has( $this->prefix . 'some_action', $mode );
		$this->assertFalse( $result );

		ob_start();
		do_action( $this->prefix . 'some_action' );
		$result = ob_get_clean();
		$this->assertEmpty( $result );
	}
}
