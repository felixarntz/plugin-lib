<?php
/**
 * Model type manager class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

use Leaves_And_Love\Plugin_Lib\Service;
use WP_List_Util;
use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Manager' ) ) :

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
	protected $model_type_class_name = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $prefix The prefix.
	 */
	public function __construct( $prefix ) {
		$this->prefix = $prefix;
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
	 * @return Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type|null Type object, or null it it does not exist.
	 */
	public function get( $slug ) {
		if ( ! isset( $this->model_types[ $slug ] ) ) {
			return null;
		}

		return $this->model_types[ $slug ];
	}

	/**
	 * Retrieves a list of type objects.
	 *
	 * By default, all registered type objects will be returned.
	 * However, the result can be modified by specifying arguments.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args {
	 *     Array of arguments for querying types. Any field available on the type can be passed
	 *     as key with a value to filter the result. Furthermore the following arguments may be
	 *     provided for additional tweaks.
	 *
	 *     @type string       $operator The logical operation to perform the filter. Must be either
	 *                                  'AND', 'OR' or 'NOT'. Default 'AND'.
	 *     @type string|array $orderby  Either the field name to order by or an array of multiple
	 *                                  orderby fields as $orderby => $order. Default 'slug'.
	 *     @type string       $order    Either 'ASC' or 'DESC'. Only used if $orderby is a string.
	 *                                  Default 'ASC'.
	 *     @type string       $field    Field from the objects to return instead of the entire objects.
	 *                                  Default empty.
	 * }
	 * @return array A list of type objects or specific type object fields, depending on $args.
	 */
	public function query( $args = array() ) {
		if ( empty( $this->model_types ) ) {
			return array();
		}

		$operator = 'and';
		$orderby  = 'slug';
		$order    = 'ASC';
		$field    = '';

		foreach ( array( 'operator', 'orderby', 'order', 'field' ) as $arg ) {
			if ( isset( $args[ $arg ] ) ) {
				$$arg = $args[ $arg ];
				unset( $args[ $arg ] );
			}
		}

		if ( ! in_array( strtolower( $operator ), array( 'or', 'not' ), true ) ) {
			$operator = 'and';
		}

		$model_types = $this->model_types;
		$transformed_to_array = false;
		if ( ! empty( $args ) || ! empty( $orderby ) || ! empty( $order ) ) {
			/* `WP_List_Util::filter()` and `WP_List_Util::sort()` can't handle objects with magic properties. */
			$model_types = $this->objects_to_arrays( $model_types );
			$transformed_to_array = true;
		}

		$util = new WP_List_Util( $model_types );

		$util->filter( $args, $operator );

		if ( ! empty( $orderby ) || ! empty( $order ) ) {
			if ( empty( $order ) ) {
				$order = 'ASC';
			}
			$util->sort( $orderby, $order, true );
		}

		if ( ! empty( $field ) ) {
			$util->pluck( $field );
		} elseif ( $transformed_to_array ) {
			/* Objects transformed into arrays need to be transformed back. */
			return $this->arrays_to_objects( $util->get_output() );
		}

		return $util->get_output();
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
	public function unregister( $slug ) {
		if ( ! isset( $this->model_types[ $slug ] ) ) {
			return false;
		}

		unset( $this->model_types[ $slug ] );

		return true;
	}

	/**
	 * Transforms a list of type objects into a list of type arrays.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $model_types List of type objects.
	 * @return array List of type arrays.
	 */
	protected function objects_to_arrays( $model_types ) {
		foreach ( $model_types as $slug => $type ) {
			if ( is_array( $type ) ) {
				continue;
			}

			$model_types[ $slug ] = $type->to_json();
		}

		return $model_types;
	}

	/**
	 * Transforms a list of type arrays into a list of type objects.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $model_types List of type arrays.
	 * @return array List of type objects.
	 */
	protected function arrays_to_objects( $model_types ) {
		foreach ( $model_types as $slug => $type ) {
			if ( is_object( $type ) ) {
				continue;
			}

			$model_types[ $slug ] = $this->get( $slug );
		}

		return $model_types;
	}
}

endif;
