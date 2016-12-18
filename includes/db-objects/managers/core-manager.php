<?php
/**
 * Manager class for Core objects
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Managers;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Manager;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Managers\Core_Manager' ) ) :

/**
 * Base class for a core manager
 *
 * This class represents a general core manager.
 *
 * @since 1.0.0
 */
abstract class Core_Manager extends Manager {
	/**
	 * The callback to fetch an item from the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var callable
	 */
	protected $fetch_callback;

	/**
	 * Adds a new model to the database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Array of column => value pairs for the new database row.
	 * @return int|false The ID of the new model, or false on failure.
	 */
	public function add( $args ) {
		$id = $this->insert_into_db( $args );
		if ( ! $id ) {
			return false;
		}

		return $id;
	}

	/**
	 * Updates an existing model in the database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int   $model_id ID of the model to update.
	 * @param array $args     Array of column => value pairs to update in the database row.
	 * @return bool True on success, or false on failure.
	 */
	public function update( $model_id, $args ) {
		$model_id = absint( $model_id );

		$result = $this->update_in_db( $model_id, $args );
		if ( ! $result ) {
			return false;
		}

		return true;
	}

	/**
	 * Deletes an model from the database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $model_id ID of the model to delete.
	 * @return bool True on success, or false on failure.
	 */
	public function delete( $model_id ) {
		$model_id = absint( $model_id );

		$result = $this->delete_from_db( $model_id );
		if ( ! $result ) {
			return false;
		}

		$this->storage_unset( $model_id );

		return true;
	}

	/**
	 * Fetches the Core object for a specific ID.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $model_id ID of the object to fetch.
	 * @return object|null The Core object for the requested ID, or null if it does not exist.
	 */
	public function fetch( $model_id ) {
		return call_user_func( $this->fetch_callback, $model_id );
	}

	/**
	 * Internal method to insert a new model into the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $args Array of column => value pairs for the new database row.
	 * @return int|false The ID of the new model, or false on failure.
	 */
	protected abstract function insert_into_db( $args );

	/**
	 * Internal method to update an existing model in the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int   $model_id ID of the model to update.
	 * @param array $args     Array of column => value pairs to update in the database row.
	 * @return bool True on success, or false on failure.
	 */
	protected abstract function update_in_db( $model_id, $args );

	/**
	 * Internal method to delete a model from the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int $model_id ID of the model to delete.
	 * @return bool True on success, or false on failure.
	 */
	protected abstract function delete_from_db( $model_id );
}

endif;
