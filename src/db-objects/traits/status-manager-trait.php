<?php
/**
 * Trait for managers that support statuses
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Traits;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Status_Manager_Trait' ) ) :

/**
 * Trait for managers.
 *
 * Include this trait for managers that support statuses.
 *
 * @since 1.0.0
 */
trait Status_Manager_Trait {
	/**
	 * The status property of the model.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $status_property = 'status';

	/**
	 * The status manager service definition.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var string
	 */
	protected static $service_statuses = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Status_Manager';

	/**
	 * Registers a new status.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Unique slug for the status.
	 * @param array  $args Optional. Array of status arguments. Default empty.
	 * @return bool True on success, false on failure.
	 */
	public function register_status( $slug, $args = array() ) {
		return $this->statuses()->register( $slug, $args );
	}

	/**
	 * Retrieves a status object.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Unique slug of the status.
	 * @return Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type|null Type object, or null it it does not exist.
	 */
	public function get_status( $slug ) {
		return $this->statuses()->get( $slug );
	}

	/**
	 * Queries for multiple status objects.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Array of query arguments.
	 * @return array Array of status objects.
	 */
	public function query_statuses( $args ) {
		return $this->statuses()->query( $args );
	}

	/**
	 * Unregisters an existing status.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Unique slug of the status.
	 * @return bool True on success, false on failure.
	 */
	public function unregister_status( $slug ) {
		return $this->statuses()->unregister( $slug );
	}

	/**
	 * Returns the name of the status property in a model.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Name of the status property.
	 */
	public function get_status_property() {
		return $this->status_property;
	}
}

endif;
