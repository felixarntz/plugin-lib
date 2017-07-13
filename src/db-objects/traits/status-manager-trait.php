<?php
/**
 * Trait for managers that support statuses
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Traits;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Status_Manager;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Status;

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
	 * Internal storage for pending status changes.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $pending_status_changes = array();

	/**
	 * The status manager service definition.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var string
	 */
	protected static $service_statuses = Model_Status_Manager::class;

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
	 * @return Model_Status|null Type object, or null it it does not exist.
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

	/**
	 * Prepares data for triggering a hook for transitioning the status property on a model.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param null  $pre   Null value from the pre-filter.
	 * @param Model $model The model to modify.
	 * @return null The unmodified pre-filter value.
	 */
	public function maybe_set_transition_status_property_data( $pre, $model ) {
		$status_property = $this->get_status_property();

		$primary_property = $this->get_primary_property();
		if ( empty( $model->$primary_property ) ) {
			return $pre;
		}

		$old_model_data = $this->fetch( $model->$primary_property );
		$this->pending_status_changes[ $model->$primary_property ] = $old_model_data->$status_property;

		return $pre;
	}

	/**
	 * Triggers a hook for transitioning the status property on a model, if necessary.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param bool|WP_Error $result Result of the sync process.
	 * @param Model         $model  The model to modify.
	 * @return null The unmodified post-filter value.
	 */
	public function maybe_transition_status_property( $result, $model ) {
		if ( is_wp_error( $result ) && in_array( $result->get_error_code(), array( 'db_insert_error', 'db_update_error' ), true ) ) {
			return $result;
		}

		$primary_property = $this->get_primary_property();
		$status_property = $this->get_status_property();

		$old_status = '';
		if ( false !== strpos( current_filter(), '_add_' ) ) {
			$old_status = $this->statuses()->get_default();
		} elseif ( ! empty( $this->pending_status_changes[ $model->$primary_property ] ) ) {
			$old_status = $this->pending_status_changes[ $model->$primary_property ];
			unset( $this->pending_status_changes[ $model->$primary_property ] );
		}

		if ( ! empty( $old_status ) && ! empty( $model->$status_property ) && $old_status !== $model->$status_property ) {
			$prefix        = $this->get_prefix();
			$singular_slug = $this->get_singular_slug();

			/**
			 * Fires when the status property of a model has changed.
			 *
			 * @since 1.0.0
			 *
			 * @param string $new_status New status of the model.
			 * @param string $old_status Old status of the model.
			 * @param Model  $model      The model object.
			 */
			do_action( "{$prefix}transition_{$singular_slug}_{$status_property}", $model->$status_property, $old_status, $model );
		}

		return $result;
	}
}

endif;
