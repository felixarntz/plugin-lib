<?php
/**
 * Hooks abstraction trait
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Traits;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\Traits\Hooks' ) ) :

/**
 * Trait for Hooks API.
 *
 * This is a wrapper for the Hooks API that supports private methods.
 *
 * @since 1.0.0
 */
trait Hooks {
	/**
	 * Reference for the internal hook closures.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var array
	 */
	private $hook_map = array();

	/**
	 * Builds a unique ID for storage and retrieval.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string   $tag      Used in counting how many hooks were applied.
	 * @param callable $function Used for creating unique id.
	 * @param int|bool $priority Used in counting how many hooks were applied. If === false
	 *                           and $function is an object reference, we return the unique
	 *                           id only if it already has one, false otherwise.
	 * @return string|false Unique ID for usage as array key or false if $priority === false
	 *                      and $function is an object reference, and it does not already have
	 *                      a unique id.
	 */
	private function get_hook_id( $tag, $function, $priority ) {
		return _wp_filter_build_unique_id( $tag, $function, $priority );
	}

	/**
	 * Maps a hook to a closure that inherits the class' internal scope.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string   $id            Unique hook ID.
	 * @param callable $function      The callback to run when the hook is run.
	 * @param int|bool $accepted_args Optional. The number of arguments the callback accepts.
	 *                                Default 1.
	 * @return Closure The callable attached to the hook.
	 */
	private function map_hook( $id, $function, $accepted_args = 1 ) {
		if ( empty( $this->hook_map[ $id ] ) ) {
			if ( false === $accepted_args ) {
				return $function;
			}

			$this->hook_map[ $id ] = function() use ( $function, $accepted_args ) {
				return call_user_func_array( $function, array_slice( func_get_args(), 0, $accepted_args ) );
			};
		}

		return $this->hook_map[ $id ];
	}

	/**
	 * Maps a hook to a closure if the callback is a private class method.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string   $tag           The name of the hook.
	 * @param callable $function      The callback to run when the hook is run.
	 * @param int|bool $priority      Used to specify the order in which the functions
	 *                                associated with a particular hook are executed.
	 * @param int|bool $accepted_args Optional. The number of arguments the callback accepts.
	 *                                Default 1.
	 * @return callable The callback to use for the actual hook.
	 */
	private function maybe_map_hook( $tag, $function, $priority, $accepted_args = 1 ) {
		if ( ! is_array( $function ) || is_string( $function[0] ) || $this !== $function[0] ) {
			return $function;
		}

		return $this->map_hook( $this->get_hook_id( $tag, $function, $priority ), $function, $accepted_args );
	}
}

endif;
