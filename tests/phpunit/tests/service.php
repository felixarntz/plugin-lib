<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\Cache;

/**
 * @group general
 * @group service
 */
class Tests_Service extends Unit_Test_Case {
	public function test_prefix() {
		require_once LALPL_TESTS_DATA . 'test-service-class.php';

		$prefix = 'foo_bar_';

		$service = new \Test_Service_Class( $prefix );
		$this->assertSame( $prefix, $service->get_prefix() );
	}

	public function test_services() {
		require_once LALPL_TESTS_DATA . 'test-service-class.php';

		$prefix = 'foo_bar_';

		$services = array();
		for ( $i = 1; $i <= 3; $i++ ) {
			$services[ 'cache' . $i ] = new Cache( $prefix . $i . '_' );
		}

		$service = new \Test_Service_Class( $prefix, $services );

		$services['cache4'] = new Cache( $prefix . '4_' );
		$service->add_service( 'cache4', $services['cache4'] );

		$i = 0;
		foreach ( $services as $name => $instance ) {
			$i++;

			$result = call_user_func( array( $service, $name ) );

			$this->assertInstanceOf( 'Leaves_And_Love\Plugin_Lib\Cache', $result );
			$this->assertSame( $prefix . $i . '_', $result->get_prefix() );
		}
	}

	public function test_messages() {
		require_once LALPL_TESTS_DATA . 'test-service-class.php';

		$prefix = 'foo_bar_';

		$messages = array(
			'hello' => 'Hello World!',
			'bye'   => 'Goodbye World!',
		);

		$service = new \Test_Service_Class( $prefix, array(), $messages );

		$this->assertSame( $messages['hello'], $service->get_message( 'hello' ) );
		$this->assertSame( $messages['bye'], $service->get_message( 'bye' ) );
		$this->assertSame( '', $service->get_message( 'invalid' ) );
	}
}
