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
	 * Site ID this manager belongs to.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var bool
	 */
	protected $__sitewide = true;
}

endif;
