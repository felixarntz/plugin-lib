<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use WP_UnitTestCase;

class Unit_Test_Case extends WP_UnitTestCase {
	protected $prefix = '';

	public function setUp() {
		parent::setUp();

		$this->prefix = strtolower( str_replace( 'Leaves_And_Love\\Plugin_Lib\\Tests\\', 'lalplt_', get_class( $this ) ) ) . '_';
	}
}
