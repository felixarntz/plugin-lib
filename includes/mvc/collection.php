<?php
/**
 * Collection class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\MVC;

use ArrayAccess;
use Iterator;
use Countable;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\MVC\Collection' ) ) :

/**
 * Base class for a collection
 *
 * This class represents a general collection.
 *
 * @since 1.0.0
 */
abstract class Collection implements ArrayAccess, Iterator, Countable {
	/**
	 * The manager instance for the collection.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\MVC\Manager
	 */
	protected $manager;

	/**
	 * Models in this collection.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $models = array();

	/**
	 * The total amount of models for the collection.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int
	 */
	protected $total = 0;

	/**
	 * The position in $models for the Iterator interface.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int
	 */
	protected $position = 0;

	/**
	 * Field mode of the collection. Either 'ids' or 'objects'.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $fields = 'ids';

	/**
	 * Constructor.
	 *
	 * Sets the manager instance and passes data to the collection.
	 * The `$fields` parameter must be set according to the kind of `$models` specified.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Leaves_And_Love\Plugin_Lib\MVC\Manager $manager The manager instance for the model
	 *                                                        collection.
	 * @param array                                  $models  The model IDs, or objects for
	 *                                                        this collection.
	 * @param int                                    $total   Optional. The total amount of models
	 *                                                        in the collection. Default is the
	 *                                                        number of models.
	 * @param string                                 $fields  Optional. Mode of the models passed.
	 *                                                        Default 'ids'.
	 */
	public function __construct( $manager, $models, $total = 0, $fields = 'ids' ) {
		$this->manager = $manager;
		$this->models = $models;

		if ( ! $total ) {
			$total = count( $models );
		}
		$this->total = $total;

		if ( ! in_array( $fields, $this->get_valid_fields(), true ) ) {
			$fields = 'ids';
		}
		$this->fields = $fields;
	}

	/**
	 * Transforms all models in the collection into model objects.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool True on success, false on failure.
	 */
	public function transform_into_objects() {
		$this->models = array_map( array( $this, 'transform_into_object' ), $this->models );
		$this->fields = 'objects';

		return true;
	}

	/**
	 * Transforms all models in the collection into model IDs.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool True on success, false on failure.
	 */
	public function transform_into_ids() {
		$this->models = array_map( array( $this, 'transform_into_id' ), $this->models );
		$this->fields = 'ids';

		return true;
	}

	/**
	 * Returns the mode of the models.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Either 'ids', or 'objects'.
	 */
	public function get_fields() {
		return $this->fields;
	}

	/**
	 * Returns the total amount of models in the collection.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return int The total amount of models in the collection.
	 */
	public function get_total() {
		return $this->total;
	}

	/**
	 * Returns an array representation of the collection.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Array including all information for the collection.
	 */
	public function to_json() {
		$data = array(
			'total'  => $this->total,
			'fields' => $this->fields,
			'models' => $this->models,
		);

		if ( 'objects' === $this->fields ) {
			$data['models'] = array_map( array( $this, 'transform_into_json' ), $this->models );
		}

		return $data;
	}

	/**
	 * Implements ArrayAccess.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $offset Offset to check for.
	 * @return bool True if the model and that offset exists, false otherwise.
	 */
	public function offsetExists( $offset ) {
		return isset( $this->models[ $offset ] );
	}

	/**
	 * Implements ArrayAccess.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $offset Offset to get model for.
	 * @return Leaves_And_Love\Plugin_Lib\MVC\Model|null Model at the offset, or null if it does not exist.
	 */
	public function offsetGet( $offset ) {
		if ( ! isset( $this->models[ $offset ] ) ) {
			return null;
		}

		return $this->models[ $offset ];
	}

	/**
	 * Implements ArrayAccess.
	 *
	 * Setting an model is not allowed here though.
	 *
	 * @param int   $offset Offset to set.
	 * @param mixed $value  Value to set at the offset.
	 */
	public function offsetSet( $offset, $value ) {
		// Empty method body.
	}

	/**
	 * Implements ArrayAccess.
	 *
	 * Unsetting an model is not allowed here though.
	 *
	 * @param int   $offset Offset to unset.
	 */
	public function offsetUnset( $offset ) {
		// Empty method body.
	}

	/**
	 * Implements Iterator.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Leaves_And_Love\Plugin_Lib\MVC\Model|null Model at the current position, or null if it is invalid.
	 */
	public function current() {
		if ( ! isset( $this->models[ $this->position ] ) ) {
			return null;
		}

		return $this->models[ $this->position ];
	}

	/**
	 * Implements Iterator.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return int Current position.
	 */
	public function key() {
		return $this->position;
	}

	/**
	 * Implements Iterator.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function next() {
		++$this->position;
	}

	/**
	 * Implements Iterator.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function rewind() {
		$this->position = 0;
	}

	/**
	 * Implements Iterator.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool True if the current position is valid, false otherwise.
	 */
	public function valid() {
		return isset( $this->models[ $this->position ] );
	}

	/**
	 * Implements Countable.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return int Number of models.
	 */
	public function count() {
		return count( $this->models );
	}

	/**
	 * Transforms an model ID into an model object.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Leaves_And_Love\Plugin_Lib\MVC\Model|int $model Model object or model ID.
	 * @return Leaves_And_Love\Plugin_Lib\MVC\Model The model object.
	 */
	protected function transform_into_object( $model ) {
		return $this->manager->get( $model );
	}

	/**
	 * Transforms an model object into an model ID.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Leaves_And_Love\Plugin_Lib\MVC\Model|int $model Model object or model ID.
	 * @return int The model ID.
	 */
	protected function transform_into_id( $model ) {
		if ( is_int( $model ) ) {
			return $model;
		}

		if ( null === $model ) {
			return 0;
		}

		$primary_property = $model->get_primary_property();

		return $model->$primary_property;
	}

	/**
	 * Transforms an model object into an array representation.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Leaves_And_Love\Plugin_Lib\MVC\Model $model Model object.
	 * @return array Array including all information for the model.
	 */
	protected function transform_into_json( $model ) {
		if ( null === $model ) {
			return array( 'id' => 0 );
		}

		return $model->to_json();
	}

	/**
	 * Returns the valid modes for `$fields`.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Array of valid modes.
	 */
	protected function get_valid_fields() {
		return array( 'objects', 'ids' );
	}
}

endif;
