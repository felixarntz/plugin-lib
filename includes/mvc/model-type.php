<?php
/**
 * Model_Type class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\MVC;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\MVC\Model_Type' ) ) :

/**
 * Base class for a model type
 *
 * This class represents a general model type.
 *
 * @since 1.0.0
 */
abstract class Model_Type {
	/**
	 * Type slug.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $slug;

	/**
	 * Type arguments.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $args = array();

	/**
	 * Constructor.
	 *
	 * Sets the type slug and additional arguments.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Type slug.
	 * @param array  $args Optional. Type arguments. Default empty.
	 */
	public function __construct( $slug, $args = array() ) {
		$this->slug = $slug;

		$this->set_args( $args );
	}

	/**
	 * Sets the type arguments and fills it with defaults.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $args Type arguments.
	 */
	protected function set_args( $args ) {
		$this->args = wp_parse_args( $args, $this->get_defaults() );
	}

	/**
	 * Returns the default type arguments.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Default type arguments.
	 */
	protected abstract function get_defaults();
}
