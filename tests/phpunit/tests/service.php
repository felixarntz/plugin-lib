<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\Cache;
use Leaves_And_Love\Plugin_Lib\Options;

/**
 * @group general
 * @group service
 */
class Tests_Service extends Unit_Test_Case {
	public function test_prefix() {
		require_once LALPL_TESTS_DATA . 'test-service-class.php';

		$prefix = 'foo_bar_';

		$service = new \Test_Service_Class( $prefix, array(
			'cache'   => new Cache( $prefix ),
			'options' => new Options( $prefix ),
		) );
		$this->assertSame( $prefix, $service->get_prefix() );
	}

	public function test_services() {
		require_once LALPL_TESTS_DATA . 'test-service-class.php';

		$prefix = 'foo_bar_';

		$services = array(
			'cache'   => new Cache( $prefix ),
			'options' => new Options( $prefix ),
		);

		$service = new \Test_Service_Class( $prefix, $services );

		foreach ( $services as $name => $instance ) {
			$result = call_user_func( array( $service, $name ) );

			$this->assertInstanceOf( get_class( $instance ), $result );
		}

		$this->assertNull( $service->invalid() );
	}
}
