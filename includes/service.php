<?php
/**
 * Service base class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Service' ) ) :

/**
 * Abstract class for any kind of service.
 *
 * @since 1.0.0
 */
abstract class Service {
	/**
	 * Prefix for class functionality.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string|bool
	 */
	protected $prefix = false;

	/**
	 * Messages printed to the user.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $messages = array();

	/**
	 * Array of property names that denote services.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $services = array();

	/**
	 * Whether the hooks for this service have been added.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var bool
	 */
	private $hooks_added = false;

	/**
	 * Magic caller.
	 *
	 * Supports a `get_prefix()` method (if property has been set by the extending class)
	 * and methods to get internally used services.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $method_name Name of the method to call.
	 * @param array  $args        Method arguments.
	 * @return mixed Method results, or void if the method does not exist.
	 */
	public function __call( $method, $args ) {
		if ( 'get_prefix' === $method ) {
			if ( is_string( $this->prefix ) ) {
				return $this->prefix;
			}
		} elseif ( 'add_hooks' === $method ) {
			if ( $this->hooks_added ) {
				return;
			}

			$this->add_hooks();

			$this->hooks_added = true;
		} elseif ( in_array( $method, $this->services, true ) && isset( $this->$method ) ) {
			return $this->$method;
		}
	}

	/**
	 * Adds all hooks for this service.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function add_hooks() {
		// Empty method body.
	}

	/**
	 * Specifies the messages printed to the user.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $messages Messages printed to the user.
	 */
	protected function set_messages( $messages ) {
		$this->messages = $messages;
	}

	/**
	 * Specifies the services included in this service.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $services Property names that denote services.
	 */
	protected function set_services( $services ) {
		$this->services = $services;
	}

	/**
	 * Adds included services to this service.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $services Property names that denote services.
	 */
	protected function add_services( $services ) {
		$services = (array) $services;

		$this->services = array_merge( $this->services, $services );
	}
}

endif;
