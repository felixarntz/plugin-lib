<?php
/**
 * Hook_Service base class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Hook_Service' ) ) :

/**
 * Abstract class for a service that contains hooks.
 *
 * @since 1.0.0
 *
 * @method void add_hooks()
 * @method void remove_hooks()
 */
abstract class Hook_Service extends Service {
	/**
	 * Whether the hooks for this service have been added.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var bool
	 */
	protected $hooks_added = false;

	/**
	 * Magic caller.
	 *
	 * Supports methods `add_hooks()` and `remove_hooks()`.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $method_name Name of the method to call.
	 * @param array  $args        Method arguments.
	 * @return mixed Method results, or void if the method does not exist.
	 */
	public function __call( $method, $args ) {
		switch ( $method ) {
			case 'add_hooks':
				if ( $this->hooks_added ) {
					return false;
				}

				$this->add_hooks();

				$this->hooks_added = true;

				return true;
			case 'remove_hooks':
				if ( ! $this->hooks_added ) {
					return false;
				}

				$this->remove_hooks();

				$this->hooks_added = false;

				return true;
			default:
				return parent::__call( $method, $args );
		}
	}

	/**
	 * Adds all hooks for this service.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected abstract function add_hooks();

	/**
	 * Removes all hooks for this service.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected abstract function remove_hooks();
}

endif;
