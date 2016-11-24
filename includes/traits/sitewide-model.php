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
	 * Sets the site ID of the model.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function set_site_id() {
		$this->__site_id = get_current_blog_id();
	}

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
