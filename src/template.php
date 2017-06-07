<?php
/**
 * Template class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib;

use Leaves_And_Love\Plugin_Lib\Traits\Args_Service_Trait;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Template' ) ) :

/**
 * Class for Template API
 *
 * The class includes a reusable template hierarchy that lets themes override each template.
 *
 * @since 1.0.0
 */
class Template extends Service {
	use Args_Service_Trait;

	/**
	 * Custom template locations.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var array
	 */
	private $locations = array();

	/**
	 * Constructor.
	 *
	 * Sets the default template location and the theme subdirectory name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $prefix The prefix for the theme subdirectory.
	 * @param array  $args   {
	 *     Array of arguments.
	 *
	 *     @type string $default_location The default location for all templates.
	 * }
	 */
	public function __construct( $prefix, $args ) {
		$this->set_prefix( $prefix );
		$this->set_args( $args );
	}

	/**
	 * Renders a template partial.
	 *
	 * Works in a similar way like the WordPress function `get_template_part()`, but also checks for
	 * the template in the default location. It furthermore allows to pass data to the template.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug The template slug.
	 * @param array  $data Optional. Data to pass on to the template. May contain a 'template_suffix'
	 *                     key with a string to append to the template slug for more detailed partial
	 *                     lookup. Default empty array.
	 */
	public function get_partial( $slug, $data = array() ) {
		$templates = array();

		if ( isset( $data['template_suffix'] ) ) {
			$templates[] = $slug . '-' . $data['template_suffix'] . '.php';
			unset( $data['template_suffix'] );
		}

		$templates[] = $slug . '.php';

		$this->locate_file( $templates, $data, true, false );
	}

	/**
	 * Locates and optionally loads a specific template file.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $template_names Template names to look for.
	 * @param array $data           Optional. Data to pass on to the template. Default empty array.
	 * @param bool  $load           Optional. Whether to load the file if found. Default false.
	 * @param bool  $require_once   Optional. Whether to use require_once to load the file. This only
	 *                              has an effect if $load is true. Default true.
	 * @return string The full path to the located file, or an empty string if not found.
	 */
	public function locate_file( $template_names, $data = array(), $load = false, $require_once = true ) {
		$located = '';

		$locations = array_values( $this->locations );

		if ( ! empty( $locations ) ) {
			usort( $locations, function( $a, $b ) {
				if ( $a['priority'] < $b['priority'] ) {
					return -1;
				}

				if ( $a['priority'] > $b['priority'] ) {
					return 1;
				}

				return 0;
			});
		}

		if ( STYLESHEETPATH !== TEMPLATEPATH ) {
			array_unshift( $locations, array(
				'priority' => -1,
				'path'     => TEMPLATEPATH . '/' . $this->get_prefix() . 'templates/',
			) );
		}

		array_unshift( $locations, array(
			'priority' => -2,
			'path'     => STYLESHEETPATH . '/' . $this->get_prefix() . 'templates/',
		) );

		if ( ! empty( $this->default_location ) ) {
			array_push( $locations, array(
				'priority' => 1000,
				'path'     => $this->default_location,
			) );
		}

		foreach ( (array) $template_names as $template_name ) {
			if ( ! $template_name ) {
				continue;
			}

			foreach ( $locations as $location ) {
				if ( file_exists( $location['path'] . $template_name ) ) {
					$located = $location['path'] . $template_name;
					break 2;
				}
			}
		}

		if ( $load && '' !== $located ) {
			$this->load_file( $located, $data, $require_once );
		}

		return $located;
	}

	/**
	 * Loads a specific file.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $_template_file The file to load.
	 * @param array  $data           Optional. Data to pass on to the template. Default empty array.
	 * @param bool   $require_once   Optional. Whether to use require_once to load the file. Default true.
	 */
	public function load_file( $_template_file, $data = array(), $require_once = true ) {
		if ( ! empty( $data ) ) {
			extract( $data, EXTR_SKIP );
		}
		unset( $data );

		if ( $require_once ) {
			require_once $_template_file;
		} else {
			require $_template_file;
		}
	}

	/**
	 * Transforms an array of attributes into an attribute string.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $attrs Array of `$key => $value` pairs.
	 * @return string Attribute string.
	 */
	public function attrs( $attrs ) {
		$output = '';

		foreach ( $attrs as $attr => $value ) {
			if ( is_bool( $value ) ) {
				if ( $value ) {
					$output .= ' ' . $attr;
				}
			} else {
				if ( is_array( $value ) || is_object( $value ) ) {
					$value = wp_json_encode( $value );
				}

				if ( is_string( $value ) && false !== strpos( $value, '"' ) ) {
					$output .= ' ' . $attr . "='" . esc_attr( $value ) . "'";
				} else {
					$output .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
				}
			}
		}

		return $output;
	}

	/**
	 * Registers an additional template location.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $name     Unique identifier for the location.
	 * @param string $path     Path to the location.
	 * @param int    $priority Optional. Priority for the location. A lower number denotes a higher priority.
	 *                         Default 10.
	 * @return bool True on success, false on failure.
	 */
	public function register_location( $name, $path, $priority = 10 ) {
		$this->locations[ $name ] = array(
			'priority' => intval( $priority ),
			'path'     => trailingslashit( $path ),
		);

		return true;
	}

	/**
	 * Unregisters an additional template location.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $name Unique identifier for the location.
	 * @return bool True on success, false on failure.
	 */
	public function unregister_location( $name ) {
		if ( ! isset( $this->locations[ $name ] ) ) {
			return false;
		}

		unset( $this->locations[ $name ] );

		return true;
	}

	/**
	 * Parses the default location.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 *
	 * @param mixed $value The input value.
	 * @return string The parsed value.
	 */
	protected static function parse_arg_default_location( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		return trailingslashit( $value );
	}
}

endif;
