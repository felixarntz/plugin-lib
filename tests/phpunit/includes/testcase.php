<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use WP_UnitTestCase;

class Unit_Test_Case extends WP_UnitTestCase {
	protected static function setUpHooks( $instances ) {
		if ( ! is_array( $instances ) ) {
			$instances = array( $instances );
		}

		foreach ( $instances as $instance ) {
			$instance->add_hooks();
		}

		self::$hooks_saved = array();
	}

	protected static function tearDownHooks( $instances ) {
		if ( ! is_array( $instances ) ) {
			$instances = array( $instances );
		}

		foreach ( $instances as $instance ) {
			$instance->remove_hooks();
		}

		self::$hooks_saved = array();
	}
}
