<?php
/**
 * Service base class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib;

use Leaves_And_Love\Plugin_Lib\Traits\Container_Service_Trait;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Service' ) ) :

/**
 * Abstract class for any kind of service.
 *
 * @since 1.0.0
 */
abstract class Service {
	use Container_Service_Trait;

	/**
	 * Prefix for class functionality.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string|bool
	 */
	protected $prefix = false;

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
}

endif;
