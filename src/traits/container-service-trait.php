<?php
/**
 * Container service trait
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Traits;

use Leaves_And_Love\Plugin_Lib\Error_Handler;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\Traits\Container_Service_Trait' ) ) :

/**
 * Container service trait.
 *
 * This adds functionality to better manage dependency injection of internal services.
 *
 * @since 1.0.0
 */
trait Container_Service_Trait {
	/**
	 * Error handler instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Error_Handler
	 */
	protected $service_error_handler = null;

	/**
	 * Magic call method.
	 *
	 * Supports retrieval of an internally used service.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $method_name Method name. Should be the name of a service.
	 * @param array  $args        Method arguments. Unused here.
	 * @return Leaves_And_Love\Plugin_Lib\Service The service instance, or null
	 *                                            if it does not exist.
	 */
	public function __call( $method_name, $args ) {
		$service_property = 'service_' . $method_name;
		if ( isset( $this->$service_property ) ) {
			return $this->$service_property;
		}

		return null;
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
			$error_handler = $this->service( 'error_handler' );
			if ( null === $error_handler ) {
				$error_handler = Error_Handler::get_base_handler();
			}

			$method_name = get_class( $this ) . '::set_services';

			$error_handler->missing_services( $method_name, $missing_services );
		}
	}
}

endif;
