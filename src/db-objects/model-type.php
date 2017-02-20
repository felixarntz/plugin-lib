<?php
/**
 * Model type class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type' ) ) :

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
	 * @param string       $slug Type slug.
	 * @param array|object $args Optional. Type arguments. Default empty.
	 */
	public function __construct( $slug, $args = array() ) {
		$this->slug = $slug;

		if ( is_object( $args ) ) {
			$args = get_object_vars( $args );
		}

		$this->set_args( $args );
	}

	/**
	 * Magic isset-er.
	 *
	 * Checks whether a property is set.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $property Property to check for.
	 * @return bool True if the property is set, false otherwise.
	 */
	public function __isset( $property ) {
		if ( 'slug' === $property ) {
			return true;
		}

		return isset( $this->args[ $property ] );
	}

	/**
	 * Magic getter.
	 *
	 * Returns a property value.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $property Property to get.
	 * @return mixed Property value, or null if property is not set.
	 */
	public function __get( $property ) {
		if ( 'slug' === $property ) {
			return $this->slug;
		}

		if ( isset( $this->args[ $property ] ) ) {
			return $this->args[ $property ];
		}

		return null;
	}

	/**
	 * Magic setter.
	 *
	 * Sets a property value.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $property Property to set.
	 * @param mixed  $value    Property value.
	 */
	public function __set( $property, $value ) {
		if ( 'slug' === $property ) {
			$this->slug = $value;
			return;
		}

		$this->args[ $property ] = $value;
	}

	/**
	 * Returns an array representation of the model type.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Array including all model type information.
	 */
	public function to_json() {
		return array_merge( array( 'slug' => $this->slug ), $this->args );
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

		if ( ! isset( $this->args['label'] ) ) {
			$this->args['label'] = '';
		}

		if ( ! isset( $this->args['default'] ) ) {
			$this->args['default'] = false;
		}
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

endif;
