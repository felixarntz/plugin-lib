<?php
/**
 * Model status manager class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

use Leaves_And_Love\Plugin_Lib\Service;
use WP_List_Util;
use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Status_Manager' ) ) :

/**
 * Base class for a model status manager
 *
 * This class represents a general model status manager.
 *
 * @since 1.0.0
 */
abstract class Model_Status_Manager extends Service {
	/**
	 * Model statuses container.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $model_statuses = array();

	/**
	 * The model status class name.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $model_status_class_name = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Status';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $prefix The instance prefix.
	 */
	public function __construct( $prefix ) {
		$this->set_prefix( $prefix );
	}

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
	public function register( $slug, $args = array() ) {
		if ( isset( $this->model_statuses[ $slug ] ) ) {
			return false;
		}

		$class_name = $this->model_status_class_name;

		$this->model_statuses[ $slug ] = new $class_name( $slug, $args );

		return true;
	}

	/**
	 * Retrieves a status object.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Unique slug of the status.
	 * @return Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Status|null Type object, or null it it does not exist.
	 */
	public function get( $slug ) {
		if ( ! isset( $this->model_statuses[ $slug ] ) ) {
			return null;
		}

		return $this->model_statuses[ $slug ];
	}

	/**
	 * Retrieves a list of status objects.
	 *
	 * By default, all registered status objects will be returned.
	 * However, the result can be modified by specifying arguments.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args {
	 *     Array of arguments for querying statuses. Any field available on the status can be passed
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
	 * @return array A list of status objects or specific status object fields, depending on $args.
	 */
	public function query( $args = array() ) {
		if ( empty( $this->model_statuses ) ) {
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

		$model_statuses = $this->model_statuses;
		$transformed_to_array = false;
		if ( ! empty( $args ) || ! empty( $orderby ) || ! empty( $order ) ) {
			/* `WP_List_Util::filter()` and `WP_List_Util::sort()` can't handle objects with magic properties. */
			$model_statuses = $this->objects_to_arrays( $model_statuses );
			$transformed_to_array = true;
		}

		$util = new WP_List_Util( $model_statuses );

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
	 * Unregisters an existing status.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Unique slug of the status.
	 * @return bool True on success, false on failure.
	 */
	public function unregister( $slug ) {
		if ( ! isset( $this->model_statuses[ $slug ] ) ) {
			return false;
		}

		unset( $this->model_statuses[ $slug ] );

		return true;
	}

	/**
	 * Transforms a list of status objects into a list of status arrays.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $model_statuses List of status objects.
	 * @return array List of status arrays.
	 */
	protected function objects_to_arrays( $model_statuses ) {
		foreach ( $model_statuses as $slug => $status ) {
			if ( is_array( $status ) ) {
				continue;
			}

			$model_statuses[ $slug ] = $status->to_json();
		}

		return $model_statuses;
	}

	/**
	 * Transforms a list of status arrays into a list of status objects.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $model_statuses List of status arrays.
	 * @return array List of status objects.
	 */
	protected function arrays_to_objects( $model_statuses ) {
		foreach ( $model_statuses as $slug => $status ) {
			if ( is_object( $status ) ) {
				continue;
			}

			$model_statuses[ $slug ] = $this->get( $slug );
		}

		return $model_statuses;
	}
}

endif;
