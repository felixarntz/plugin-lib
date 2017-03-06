<?php
/**
 * Trait for managers that support view routing
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Traits;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\View_Routing_Manager_Trait' ) ) :

/**
 * Trait for managers.
 *
 * Include this trait for managers that support view routing.
 *
 * @since 1.0.0
 */
trait View_Routing_Manager_Trait {
	/**
	 * The view routing service definition.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var string
	 */
	protected static $service_view_routing = 'Leaves_And_Love\Plugin_Lib\DB_Objects\View_Routing';
}

endif;
