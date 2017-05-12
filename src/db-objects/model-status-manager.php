<?php
/**
 * Model status manager class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

use Leaves_And_Love\Plugin_Lib\Components\Item_Registry;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Status_Manager' ) ) :

/**
 * Base class for a model status manager
 *
 * This class represents a general model status manager.
 *
 * @since 1.0.0
 */
abstract class Model_Status_Manager extends Item_Registry {
	/**
	 * The model status class name.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $item_class_name = Model_Status::class;

	/**
	 * Returns the slug of the default status.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Default status.
	 */
	public function get_default() {
		$results = $this->query( array( 'default' => true ) );
		if ( empty( $results ) ) {
			return '';
		}

		return key( $results );
	}

	/**
	 * Returns the slugs of all public statuses.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Array of public statuses.
	 */
	public function get_public() {
		$results = $this->query( array( 'public' => true ) );
		if ( empty( $results ) ) {
			return array();
		}

		return array_keys( $results );
	}
}

endif;
