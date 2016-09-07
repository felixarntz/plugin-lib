<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love_Plugin_Loader;

class Tests_Template extends Unit_Test_Case {
	protected $template_instance;

	public function setUp() {
		parent::setUp();

		$this->template_instance = Leaves_And_Love_Plugin_Loader::get( 'SP_Main' )->template();
	}

	public function test_get_partial() {
		$base = 'Basic template.';
		$base_suffixed = 'Template with suffix.';
		$data = 'Data: %s';

		$content = 'Some custom content.';

		ob_start();
		$this->template_instance->get_partial( 'base' );
		$result = ob_get_clean();
		$this->assertEquals( $base, $result );

		ob_start();
		$this->template_instance->get_partial( 'invalid' );
		$result = ob_get_clean();
		$this->assertEmpty( $result );

		ob_start();
		$this->template_instance->get_partial( 'base', array( 'template_suffix' => 'suffixed' ) );
		$result = ob_get_clean();
		$this->assertEquals( $base_suffixed, $result );

		ob_start();
		$this->template_instance->get_partial( 'base', array( 'template_suffix' => 'invalid' ) );
		$result = ob_get_clean();
		$this->assertEquals( $base, $result );

		ob_start();
		$this->template_instance->get_partial( 'data', array( 'content' => $content ) );
		$result = ob_get_clean();
		$this->assertEquals( sprintf( $data, $content ), $result );
	}

	public function test_locate_file() {
		$plugin_template_location = Leaves_And_Love_Plugin_Loader::get( 'SP_Main' )->path( 'templates/' );

		$base = 'base.php';

		$result = $this->template_instance->locate_file( array( $base ) );
		$this->assertEquals( $plugin_template_location . $base, $result );

		$result = $this->template_instance->locate_file( array( 'something-invalid.php', $base ) );
		$this->assertEquals( $plugin_template_location . $base, $result );

		$result = $this->template_instance->locate_file( $base );
		$this->assertEquals( $plugin_template_location . $base, $result );

		$result = $this->template_instance->locate_file( array( 'something-invalid.php' ) );
		$this->assertEmpty( $result );
	}

	public function test_load_file() {
		$require_file = Leaves_And_Love_Plugin_Loader::get( 'SP_Main' )->path( 'templates/base.php' );
		$require_once_file = dirname( dirname( __FILE__ ) ) . '/data/templates/require-this-once.php';

		$require_content = 'Basic template.';
		$require_once_content = 'Require this once!';

		ob_start();
		$this->template_instance->load_file( $require_file, array(), false );
		$result = ob_get_clean();
		$this->assertEquals( $require_content, $result );

		ob_start();
		$this->template_instance->load_file( $require_once_file, array(), true );
		$result = ob_get_clean();
		$this->assertEquals( $require_once_content, $result );

		ob_start();
		$this->template_instance->load_file( $require_once_file, array(), true );
		$result = ob_get_clean();
		$this->assertEmpty( $result );
	}

	public function test_register_location() {
		$custom_location = dirname( dirname( __FILE__ ) ) . '/data/templates/';

		$base = Leaves_And_Love_Plugin_Loader::get( 'SP_Main' )->path( 'templates/base.php' );
		$base_overridden = $custom_location . 'base.php';

		$result = $this->template_instance->locate_file( 'base.php' );
		$this->assertEquals( $base, $result );

		$result = $this->template_instance->register_location( 'custom_loc', $custom_location );
		$this->assertTrue( $result );

		$result = $this->template_instance->locate_file( 'base.php' );
		$this->assertEquals( $base_overridden, $result );

		$this->template_instance->unregister_location( 'custom_loc' );

		$result = $this->template_instance->locate_file( 'base.php' );
		$this->assertEquals( $base, $result );
	}

	public function test_unregister_location() {
		$location_name = 'my_location';

		$this->template_instance->register_location( $location_name, dirname( dirname( __FILE__ ) ) . '/data/templates/' );

		$result = $this->template_instance->unregister_location( $location_name );
		$this->assertTrue( $result );

		$result = $this->template_instance->unregister_location( $location_name );
		$this->assertFalse( $result );
	}
}