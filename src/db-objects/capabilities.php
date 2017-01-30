<?php
/**
 * Capability manager class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

use Leaves_And_Love\Plugin_Lib\Service;
use Leaves_And_Love\Plugin_Lib\Traits\Hook_Service_Trait;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Capabilities' ) ) :

/**
 * Base class for a capability manager
 *
 * This class represents a general capability manager.
 *
 * @since 1.0.0
 */
abstract class Capabilities extends Service {
	use Hook_Service_Trait;

	/**
	 * Singular slug to use for capability names.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $singular = '';

	/**
	 * Plural slug to use for capability names.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $plural = '';

	/**
	 * Base capabilities.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $base_capabilities = array();

	/**
	 * Meta capabilities.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $meta_capabilities = array();

	/**
	 * Capability mappings, as `$original_cap => $mapped_cap` pairs.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $capability_mappings = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $prefix The instance prefix.
	 */
	public function __construct( $prefix ) {
		$this->set_prefix( $prefix );
		$this->set_slugs();
		$this->set_capabilities();

		$this->setup_hooks();
	}

	/**
	 * Checks whether the current user has the requested capability.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $capability Capability to check for.
	 * @return bool Whether the current user has the capability.
	 */
	public function current_user_can( $capability ) {
		if ( isset( $this->base_capabilities[ $capability ] ) ) {
			$capability = $this->base_capabilities[ $capability ];
		} elseif ( isset( $this->meta_capabilities[ $capability ] ) ) {
			$capability = $this->meta_capabilities[ $capability ];
		}

		$args = array_merge( array( $capability ), array_slice( func_get_args(), 1 ) );

		return call_user_func_array( 'current_user_can', $args );
	}

	/**
	 * Checks whether a specific user has the requested capability.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int    $user_id    The user ID.
	 * @param string $capability Capability to check for.
	 * @return bool Whether the user has the capability.
	 */
	public function user_can( $user_id, $capability ) {
		if ( isset( $this->base_capabilities[ $capability ] ) ) {
			$capability = $this->base_capabilities[ $capability ];
		} elseif ( isset( $this->meta_capabilities[ $capability ] ) ) {
			$capability = $this->meta_capabilities[ $capability ];
		}

		$args = array_merge( array( $user_id, $capability ), array_slice( func_get_args(), 2 ) );

		return call_user_func_array( 'user_can', $args );
	}

	/**
	 * Returns all available capabilities.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $mode Optional. Either 'all', 'base' or 'meta'. Default 'all'.
	 * @return array List of capabilities.
	 */
	public function get_capabilities( $mode = 'all' ) {
		if ( 'base' === $mode ) {
			return $this->base_capabilities;
		}

		if ( 'meta' === $mode ) {
			return $this->meta_capabilities;
		}

		return array_merge( $this->base_capabilities, $this->meta_capabilities );
	}

	/**
	 * Sets the mapping mode for capabilities.
	 *
	 * Capabilities can be dealt with manually, or meta capabilities can be mapped to
	 * base capabilities, or all capabilities can be mapped to other WordPress capabilities.
	 *
	 * By default, mapping is entirely disabled.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string|array|false $mode The new mapping mode. This can either be set to 'meta'
	 *                                 in order to map meta capabilities only, a plural slug
	 *                                 like 'posts' in order to map to WordPress capabilities
	 *                                 of that slug, an array with individual key mappings, or
	 *                                 false to disable mapping.
	 */
	public function map_capabilities( $mode ) {
		$this->capability_mappings = array();

		if ( $mode ) {
			$this->capability_mappings[ $this->meta_capabilities['edit_item'] ]   = $this->base_capabilities['edit_items'];
			$this->capability_mappings[ $this->meta_capabilities['delete_item'] ] = $this->base_capabilities['delete_items'];

			if ( is_string( $mode ) && 'meta' !== $mode ) {
				$this->capability_mappings[ $this->base_capabilities['create_items'] ] = sprintf( 'edit_%s', $mode );
				$this->capability_mappings[ $this->base_capabilities['edit_items'] ]   = sprintf( 'edit_%s', $mode );
				$this->capability_mappings[ $this->base_capabilities['delete_items'] ] = sprintf( 'delete_%s', $mode );
			} elseif ( is_array( $mode ) ) {
				$this->capability_mappings[ $this->base_capabilities['create_items'] ] = $mode['create_items'];
				$this->capability_mappings[ $this->base_capabilities['edit_items'] ]   = $mode['edit_items'];
				$this->capability_mappings[ $this->base_capabilities['delete_items'] ] = $mode['delete_items'];
			}
		}
	}

	/**
	 * Sets the singular and plural slugs to use for capabilities.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected abstract function set_slugs();

	/**
	 * Sets the supported capabilities.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function set_capabilities() {
		$prefix = $this->get_prefix();

		$this->base_capabilities = array(
			'create_items' => sprintf( 'create_%s', $prefix . $this->plural ),
			'edit_items'   => sprintf( 'edit_%s', $prefix . $this->plural ),
			'delete_items' => sprintf( 'delete_%s', $prefix . $this->plural ),
		);

		$this->meta_capabilities = array(
			'edit_item'    => sprintf( 'edit_%s', $prefix . $this->singular ),
			'delete_item'  => sprintf( 'delete_%s', $prefix . $this->singular ),
		);
	}

	/**
	 * Maps capabilities via the `map_meta_cap` filter.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array  $caps    Required capabilities.
	 * @param string $cap     Capability name.
	 * @param int    $user_id User ID.
	 * @param array  $args    Additional arguments.
	 * @return array Required mapped capabilities.
	 */
	protected function map_meta_cap( $caps, $cap, $user_id, $args ) {
		if ( empty( $this->capability_mappings ) ) {
			return $caps;
		}

		if ( ! isset( $this->capability_mappings[ $cap ] ) ) {
			return $caps;
		}

		$caps = array( $this->capability_mappings[ $cap ] );

		return $this->map_meta_cap( $caps, $this->capability_mappings[ $cap ], $user_id, $args );
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * This method must be implemented and then be called from the constructor.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function setup_hooks() {
		$this->filters = array(
			array(
				'name'     => 'map_meta_cap',
				'callback' => array( $this, 'map_meta_cap' ),
				'priority' => 10,
				'num_args' => 4,
			),
		);
	}
}

endif;
