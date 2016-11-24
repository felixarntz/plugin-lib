<?php
/**
 * Model_Type_Manager class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\MVC;

use Leaves_And_Love\Plugin_Lib\Service;
use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\MVC\Model_Type_Manager' ) ) :

/**
 * Base class for a model type manager
 *
 * This class represents a general model type manager.
 *
 * @since 1.0.0
 */
abstract class Model_Type_Manager extends Service {
	/**
	 * Model types container.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $model_types = array();

	/**
	 * The model type class name.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $model_type_class_name = 'Leaves_And_Love\Plugin_Lib\MVC\Model_Type';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $prefix   The prefix for all post types.
	 * @param array  $messages Messages printed to the user.
	 */
	public function __construct( $prefix, $messages ) {
		//TODO: Do we really need these things?
		$this->prefix = $prefix;

		$this->set_messages( $messages );
	}

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
	public function register( $slug, $args = array() ) {
		if ( isset( $this->model_types[ $slug ] ) ) {
			return false;
		}

		$class_name = $this->model_type_class_name;

		$this->model_types[ $slug ] = new $class_name( $slug, $args );

		return true;
	}

	/**
	 * Retrieves a type object.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Unique slug of the type.
	 * @return Leaves_And_Love\Plugin_Lib\MVC\Model_Type|null Type object, or null it it does not exist.
	 */
	public function get( $slug ) {
		if ( ! isset( $this->model_types[ $slug ] ) ) {
			return null;
		}

		return $this->model_types[ $slug ];
	}

	//TODO: implement query

	/**
	 * Unregisters an existing type.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Unique slug of the type.
	 * @return bool True on success, false on failure.
	 */
	public function unregister( $slug ) {
		if ( ! isset( $this->model_types[ $slug ] ) ) {
			return false;
		}

		unset( $this->model_types[ $slug ] );

		return true;
	}
}
