<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love_Plugin_Loader;

class Tests_Filters extends Unit_Test_Case {
	protected $filters;

	public function setUp() {
		parent::setUp();

		$this->filters = Leaves_And_Love_Plugin_Loader::get( 'SP_Main' )->filters();
	}

	public function test_func() {
		$mode = 'func';

		$this->filters->add( $this->prefix . 'some_filter', $mode );

		$result = $this->filters->has( $this->prefix . 'some_filter', $mode );
		$this->assertEquals( 10, $result );

		$result = apply_filters( $this->prefix . 'some_filter', '' );
		$this->assertEquals( $mode, $result );

		$this->filters->remove( $this->prefix . 'some_filter', $mode );

		$result = $this->filters->has( $this->prefix . 'some_filter', $mode );
		$this->assertFalse( $result );

		$result = apply_filters( $this->prefix . 'some_filter', '' );
		$this->assertEmpty( $result );
	}

	public function test_public_method() {
		$mode = 'public';

		$this->filters->add( $this->prefix . 'some_filter', $mode );

		$result = $this->filters->has( $this->prefix . 'some_filter', $mode );
		$this->assertEquals( 10, $result );

		$result = apply_filters( $this->prefix . 'some_filter', '' );
		$this->assertEquals( $mode, $result );

		$this->filters->remove( $this->prefix . 'some_filter', $mode );

		$result = $this->filters->has( $this->prefix . 'some_filter', $mode );
		$this->assertFalse( $result );

		$result = apply_filters( $this->prefix . 'some_filter', '' );
		$this->assertEmpty( $result );
	}

	public function test_private_method() {
		$mode = 'private';

		$this->filters->add( $this->prefix . 'some_filter', $mode );

		$result = $this->filters->has( $this->prefix . 'some_filter', $mode );
		$this->assertEquals( 10, $result );

		$result = apply_filters( $this->prefix . 'some_filter', '' );
		$this->assertEquals( $mode, $result );

		$this->filters->remove( $this->prefix . 'some_filter', $mode );

		$result = $this->filters->has( $this->prefix . 'some_filter', $mode );
		$this->assertFalse( $result );

		$result = apply_filters( $this->prefix . 'some_filter', '' );
		$this->assertEmpty( $result );
	}
}
