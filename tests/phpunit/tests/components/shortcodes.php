<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love_Plugin_Loader;
use Leaves_And_Love\Plugin_Lib\Cache;
use Leaves_And_Love\Plugin_Lib\Components\Shortcodes;

class Tests_Shortcodes extends Unit_Test_Case {
	protected $shortcodes;

	public function setUp() {
		parent::setUp();

		$cache = new Cache( $this->prefix );
		$template = Leaves_And_Love_Plugin_Loader::get( 'SP_Main' )->template();

		$this->shortcodes = new Shortcodes( $this->prefix, $cache, $template );
	}

	public function test_add() {
		$shortcode_name = 'test_add_shortcode';

		$result = $this->shortcodes->add( '', '__return_empty_string' );
		$this->assertFalse( $result );
		$this->assertFalse( shortcode_exists( $this->prefix ) );

		$result = $this->shortcodes->add( $shortcode_name, '__return_empty_string' );
		$this->assertTrue( $result );
		$this->assertTrue( shortcode_exists( $this->prefix . $shortcode_name ) );
	}

	public function test_has() {
		$shortcode_name = 'test_has_shortcode';
		$shortcode_name2 = 'test_has_shortcode2';

		$result = $this->shortcodes->has( 'non_existing_shortcode' );
		$this->assertFalse( $result );

		add_shortcode( $this->prefix . $shortcode_name, '__return_empty_string' );
		$result = $this->shortcodes->has( $shortcode_name );
		$this->assertFalse( $result );

		$this->shortcodes->add( $shortcode_name2, '__return_empty_string' );
		$result = $this->shortcodes->has( $shortcode_name2 );
		$this->assertTrue( $result );
	}

	public function test_get() {
		$shortcode_name = 'test_get_shortcode';
		$shortcode_name2 = 'test_get_shortcode2';

		$result = $this->shortcodes->get( 'non_existing_shortcode' );
		$this->assertNull( $result );

		add_shortcode( $this->prefix . $shortcode_name, '__return_empty_string' );
		$result = $this->shortcodes->get( $shortcode_name );
		$this->assertNull( $result );

		$this->shortcodes->add( $shortcode_name2, '__return_empty_string' );
		$result = $this->shortcodes->get( $shortcode_name2 );
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\Components\Shortcode', $result );
		$this->assertSame( $this->prefix . $shortcode_name2, $result->get_tag() );
	}

	public function test_remove() {
		$shortcode_name = 'test_remove_shortcode';
		$shortcode_name2 = 'test_remove_shortcode2';

		$result = $this->shortcodes->remove( 'non_existing_shortcode' );
		$this->assertFalse( $result );

		add_shortcode( $this->prefix . $shortcode_name, '__return_empty_string' );
		$result = $this->shortcodes->remove( $shortcode_name );
		$this->assertFalse( $result );

		$this->shortcodes->add( $shortcode_name2, '__return_empty_string' );
		$result = $this->shortcodes->remove( $shortcode_name2 );
		$this->assertTrue( $result );
		$this->assertFalse( shortcode_exists( $this->prefix . $shortcode_name2 ) );
	}
}
