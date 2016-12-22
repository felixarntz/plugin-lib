<?php
/**
 * Trait for sitewide managers
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Traits;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\Traits\Sitewide_Manager' ) ) :

/**
 * Trait for managers.
 *
 * Include this trait for sitewide managers.
 *
 * @since 1.0.0
 */
trait Sitewide_Manager {
	/**
	 * Sets a model in the storage.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int                                         $model_id ID of the model to set.
	 * @param Leaves_And_Love\Plugin_Lib\DB_Objects\Model $model    Model to set for the ID.
	 * @return bool True on success, or false on failure.
	 */
	protected function storage_set( $model_id, $model ) {
		if ( is_multisite() ) {
			$site_id = get_current_blog_id();

			if ( ! isset( $this->models[ $site_id ] ) ) {
				$this->models[ $site_id ] = array();
			}

			$this->models[ $site_id ][ $model_id ] = $model;

			return true;
		}

		return parent::storage_set( $model_id, $model );
	}

	/**
	 * Retrieves a model from the storage.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int $model_id ID of the model to get.
	 * @return Leaves_And_Love\Plugin_Lib\DB_Objects\Model|null The model on success, or null if it doesn't exist.
	 */
	protected function storage_get( $model_id ) {
		if ( is_multisite() ) {
			$site_id = get_current_blog_id();

			if ( ! isset( $this->models[ $site_id ] ) ) {
				return null;
			}

			if ( ! isset( $this->models[ $site_id ][ $model_id ] ) ) {
				return null;
			}

			return $this->models[ $site_id ][ $model_id ];
		}

		return parent::storage_get( $model_id );
	}

	/**
	 * Checks whether a model is set in the storage.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int $model_id ID of the model to check for.
	 * @return bool True if the model is set, or false otherwise.
	 */
	protected function storage_isset( $model_id ) {
		if ( is_multisite() ) {
			$site_id = get_current_blog_id();

			if ( ! isset( $this->models[ $site_id ] ) ) {
				return false;
			}

			return isset( $this->models[ $site_id ][ $model_id ] );
		}

		return parent::storage_isset( $model_id );
	}

	/**
	 * Unsets a model in the storage.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int $model_id ID of the model to unset.
	 * @return bool True on success, or false on failure.
	 */
	protected function storage_unset( $model_id ) {
		parent::storage_unset( $model_id );
	}
}

endif;
