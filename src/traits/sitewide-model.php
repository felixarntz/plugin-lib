<?php
/**
 * Trait for sitewide models
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Traits;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\Traits\Sitewide_Model' ) ) :

/**
 * Trait for models.
 *
 * Include this trait for sitewide models.
 *
 * @since 1.0.0
 */
trait Sitewide_Model {
	/**
	 * Site ID this model belongs to.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int
	 */
	protected $__site_id = 0;

	/**
	 * Whether sites are temporarily switched.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var bool
	 */
	protected $__switched = false;

	/**
	 * Retrieves the site ID of the model.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return int The site ID.
	 */
	public function get_site_id() {
		return $this->__site_id;
	}

	/**
	 * Synchronizes the model with the database by storing the currently pending values.
	 *
	 * If the model is new (i.e. does not have an ID yet), it will be inserted to the database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return true|WP_Error True on success, or an error object on failure.
	 */
	public function sync_upstream() {
		$this->maybe_switch();

		$result = parent::sync_upstream();

		$this->maybe_restore();

		return $result;
	}

	/**
	 * Synchronizes the model with the database by fetching the currently stored values.
	 *
	 * If the model contains unsynchronized changes, these will be overridden. This method basically allows
	 * to reset the model to the values stored in the database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return true|WP_Error True on success, or an error object on failure.
	 */
	public function sync_downstream() {
		$this->maybe_switch();

		$result = parent::sync_downstream();

		$this->maybe_restore();

		return $result;
	}

	/**
	 * Deletes the model from the database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return true|WP_Error True on success, or an error object on failure.
	 */
	public function delete() {
		$this->maybe_switch();

		$result = parent::delete();

		$this->maybe_restore();

		return $result;
	}

	/**
	 * Returns an array representation of the model.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Array including all information for the model.
	 */
	public function to_json() {
		$this->maybe_switch();

		$result = parent::to_json();

		$this->maybe_restore();

		return $result;
	}

	/**
	 * Switches to the site the model belongs to if necessary.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function maybe_switch() {
		if ( is_multisite() && $this->__site_id && $this->__site_id !== get_current_blog_id() ) {
			switch_to_blog( $this->__site_id );
			$this->__switched = true;
		}
	}

	/**
	 * Restores the current site after having switched.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function maybe_restore() {
		if ( $this->__switched ) {
			restore_current_blog();
			$this->__switched = false;
		}
	}
}

endif;
