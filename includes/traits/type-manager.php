<?php
/**
 * Trait for managers that support types
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Traits;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\Traits\Type_Manager' ) ) :

/**
 * Trait for managers.
 *
 * Include this trait for managers that support types.
 *
 * @since 1.0.0
 */
trait Type_Manager {
	/**
	 * The type manager instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Manager
	 */
	protected $type_manager;

	/**
	 * Registers a new type.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Unique slug for the type.
	 * @param array  $args Optional. Array of type arguments. Default empty.
	 * @return bool True on success, false on failure.
	 */
	public function register_type( $slug, $args = array() ) {
		return $this->type_manager->register( $slug, $args );
	}

	/**
	 * Retrieves a type object.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Unique slug of the type.
	 * @return Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type|null Type object, or null it it does not exist.
	 */
	public function get_type( $slug ) {
		return $this->type_manager->get( $slug );
	}

	/**
	 * Queries for multiple type objects.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Array of query arguments.
	 * @return array Array of type objects.
	 */
	public function query_types( $args ) {
		return $this->type_manager->query( $args );
	}

	/**
	 * Unregisters an existing type.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Unique slug of the type.
	 * @return bool True on success, false on failure.
	 */
	public function unregister_type( $slug ) {
		return $this->type_manager->unregister( $slug );
	}
}

endif;
