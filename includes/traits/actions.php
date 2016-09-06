<?php
/**
 * Actions abstraction trait
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Traits;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\Traits\Actions' ) ) :

/**
 * Trait for Actions API.
 *
 * This is a wrapper for the Actions API that supports private methods.
 *
 * @since 1.0.0
 */
trait Actions {
	use Hooks;

	/**
	 * Hooks a function or method to a specific action.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string   $tag             The name of the action to hook the $function_to_add callback to.
	 * @param callable $function_to_add The callback to be run when the action is run.
	 * @param int      $priority        Optional. Used to specify the order in which the functions
	 *                                  associated with a particular action are executed. Default 10.
	 *                                  Lower numbers correspond with earlier execution,
	 *                                  and functions with the same priority are executed
	 *                                  in the order in which they were added to the action.
	 * @param int      $accepted_args   Optional. The number of arguments the function accepts. Default 1.
	 * @return true
	 */
	protected function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		$mapped = $this->maybe_map_hook( $tag, $function_to_add, $priority, $accepted_args );

		return add_action( $tag, $mapped, $priority, $accepted_args );
	}

	/**
	 * Checks if any action has been registered for a hook.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string        $tag               The name of the action hook.
	 * @param callable|bool $function_to_check Optional. The callback to check for. Default false.
	 * @param int           $priority          Optional. The priority to check for the callback. Must
	 *                                         be provided if the callback is a private class method.
	 *                                         Default 10.
	 * @return false|int If $function_to_check is omitted, returns boolean for whether the hook has
	 *                   anything registered. When checking a specific function, the priority of that
	 *                   hook is returned, or false if the function is not attached. When using the
	 *                   $function_to_check argument, this function may return a non-boolean value
	 *                   that evaluates to false (e.g.) 0, so use the === operator for testing the
	 *                   return value.
	 */
	protected function has_action( $tag, $function_to_check = false, $priority = 10 ) {
		if ( $function_to_check ) {
			$mapped = $this->maybe_map_hook( $tag, $function_to_check, $priority, false );

			return has_action( $tag, $mapped );
		}

		return has_action( $tag );
	}

	/**
	 * Removes a function from a specified action hook.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string   $tag                The action hook to which the function to be removed is hooked.
	 * @param callable $function_to_remove The name of the function which should be removed.
	 * @param int      $priority           Optional. The priority of the function. Default 10.
	 * @return bool    Whether the function existed before it was removed.
	 */
	protected function remove_action( $tag, $function_to_remove, $priority = 10 ) {
		$mapped = $this->maybe_map_hook( $tag, $function_to_remove, $priority, false );

		return remove_action( $tag, $mapped, $priority );
	}
}

endif;
