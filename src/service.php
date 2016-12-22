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
 *
 * @method string get_prefix()
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
	 * Messages to print to the user.
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
		switch ( $method ) {
			case 'get_prefix':
				return $this->prefix;
			default:
				if ( in_array( $method, $this->services, true ) && isset( $this->$method ) ) {
					return $this->$method;
				}
		}
	}

	/**
	 * Specifies the messages to print to the user.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $messages Messages to print to the user.
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
