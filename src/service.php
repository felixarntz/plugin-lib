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
	 * Error handler instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Error_Handler
	 */
	protected $service_error_handler = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $prefix The instance prefix.
	 * @param array  $services {
	 *     Array of service instances.
	 *
	 *     @type Leaves_And_Love\Plugin_Lib\Error_Handler $error_handler The error handler instance.
	 * }
	 */
	public function __construct( $prefix, $services ) {
		$this->set_prefix( $prefix );
		$this->set_services( $services );
	}

	/**
	 * Returns the instance prefix.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string|bool Instance prefix, or false if no prefix is set.
	 */
	public function get_prefix() {
		return $this->prefix;
	}

	/**
	 * Magic caller.
	 *
	 * Supports methods to get internally used services.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $method_name Name of the method to call.
	 * @param array  $args        Method arguments.
	 * @return mixed Method results, or void if the method does not exist.
	 */
	public function __call( $method, $args ) {
		$service_property = 'service_' . $method;
		if ( isset( $this->$service_property ) ) {
			return $this->$service_property;
		}

		return null;
	}

	/**
	 * Sets the instance prefix.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $prefix Instance prefix.
	 */
	protected function set_prefix( $prefix ) {
		$this->prefix = $prefix;
	}

	/**
	 * Sets the services for this class.
	 *
	 * Services are class properties whose names are prefixed with 'service_'.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $services Array of passed services.
	 */
	protected function set_services( $services ) {
		$missing_services = array();

		foreach ( get_class_vars( get_class( $this ) ) as $property => $value ) {
			if ( 0 !== strpos( $property, 'service_' ) ) {
				continue;
			}

			$unprefixed_property = substr( $property, 8 );

			if ( ! isset( $services[ $unprefixed_property ] ) ) {
				$missing_services[] = $unprefixed_property;
				continue;
			}

			$this->$property = $services[ $unprefixed_property ];
		}

		if ( ! empty( $missing_services ) ) {
			$this->error_handler()->missing_services( __METHOD__, $missing_services );
		}
	}
}

endif;
