<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\Error_Handler;
use Leaves_And_Love\Plugin_Lib\Cache;
use Leaves_And_Love\Plugin_Lib\Options;
use Leaves_And_Love\Plugin_Lib\Translations\Translations_Error_Handler;

/**
 * @group general
 * @group service
 */
class Tests_Service extends Unit_Test_Case {
	public function test_prefix() {
		require_once LALPL_TESTS_DATA . 'test-service-class.php';

		$prefix = 'foo_bar_';

		$error_handler = new Error_Handler( new Translations_Error_Handler() );

		$service = new \Test_Service_Class( $prefix, array(
			'cache'         => new Cache( $prefix, array(
				'error_handler' => $error_handler,
			) ),
			'options'       => new Options( $prefix, array(
				'error_handler' => $error_handler,
			) ),
			'error_handler' => $error_handler,
		) );
		$this->assertSame( $prefix, $service->get_prefix() );
	}

	public function test_services() {
		require_once LALPL_TESTS_DATA . 'test-service-class.php';

		$prefix = 'foo_bar_';

		$error_handler = new Error_Handler( new Translations_Error_Handler() );

		$services = array(
			'cache'         => new Cache( $prefix, array(
				'error_handler' => $error_handler,
			) ),
			'options'       => new Options( $prefix, array(
				'error_handler' => $error_handler,
			) ),
			'error_handler' => $error_handler,
		);

		$service = new \Test_Service_Class( $prefix, $services );

		foreach ( $services as $name => $instance ) {
			$result = call_user_func( array( $service, $name ) );

			$this->assertInstanceOf( get_class( $instance ), $result );
		}

		$this->assertNull( $service->invalid() );
	}
}
