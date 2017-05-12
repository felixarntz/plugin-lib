<?php
/**
 * Trait for managers that support types
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Traits;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Manager;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Type_Manager_Trait' ) ) :

/**
 * Trait for managers.
 *
 * Include this trait for managers that support types.
 *
 * @since 1.0.0
 */
trait Type_Manager_Trait {
	/**
	 * The type property of the model.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $type_property = 'type';

	/**
	 * The type manager service definition.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var string
	 */
	protected static $service_types = Model_Type_Manager::class;

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
		return $this->types()->register( $slug, $args );
	}

	/**
	 * Retrieves a type object.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Unique slug of the type.
	 * @return Model_Type|null Type object, or null it it does not exist.
	 */
	public function get_type( $slug ) {
		return $this->types()->get( $slug );
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
		return $this->types()->query( $args );
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
		return $this->types()->unregister( $slug );
	}

	/**
	 * Returns the name of the type property in a model.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Name of the type property.
	 */
	public function get_type_property() {
		return $this->type_property;
	}
}

endif;
