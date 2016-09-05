<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love_Plugin;

class Tests_Plugin extends Unit_Test_Case {
	protected $plugin_instance;

	public function setUp() {
		parent::setUp();

		$this->plugin_instance = Leaves_And_Love_Plugin_Loader::get( 'SP_Main' );
	}

	public function test__call() {
		$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\Options', $this->plugin_instance->options() );

		$this->assertNull( $this->plugin_instance->main_file() );
	}

	public function test_start() {
		$this->plugin_instance->start();

		$this->assertEquals( 10, has_filter( 'populate_network_meta', array( $this->plugin_instance->options(), 'migrate_to_network' ) ) );
	}

	public function test_path() {
		$subpath = 'includes/sp-main.php';

		$expected = WP_PLUGIN_DIR . '/sample-plugin/' . $subpath;

		$this->assertEquals( $expected, $this->plugin_instance->path( $subpath ) );
		$this->assertEquals( $expected, $this->plugin_instance->path( '/' . $subpath ) );
	}

	public function test_url() {
		$subpath = 'includes/sp-main.php';

		$expected = WP_PLUGIN_URL . '/sample-plugin/' . $subpath;

		$this->assertEquals( $expected, $this->plugin_instance->url( $subpath ) );
		$this->assertEquals( $expected, $this->plugin_instance->url( '/' . $subpath ) );
	}
}
