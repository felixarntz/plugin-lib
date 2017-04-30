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
	 * Manager instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\DB_Objects\Manager
	 */
	protected $manager = null;

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
	}

	/**
	 * Checks whether a user can read items.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $user_id Optional. User ID. Default is the current user.
	 * @param int $item_id Optional. Item ID, if checking for a specific item. Default null.
	 * @return bool True if the user can read items, false otherwise.
	 */
	public function user_can_read( $user_id = null, $item_id = null ) {
		return $this->user_can_perform_item_action( 'read', $user_id, $item_id );
	}

	/**
	 * Checks whether a user can create items.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $user_id Optional. User ID. Default is the current user.
	 * @return bool True if the user can create items, false otherwise.
	 */
	public function user_can_create( $user_id = null ) {
		if ( ! $user_id ) {
			return $this->current_user_can( 'create_items' );
		}

		return $this->user_can( $user_id, 'create_items' );
	}

	/**
	 * Checks whether a user can edit items.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $user_id Optional. User ID. Default is the current user.
	 * @param int $item_id Optional. Item ID, if checking for a specific item. Default null.
	 * @return bool True if the user can edit items, false otherwise.
	 */
	public function user_can_edit( $user_id = null, $item_id = null ) {
		return $this->user_can_perform_item_action( 'edit', $user_id, $item_id );
	}

	/**
	 * Checks whether a user can delete items.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $user_id Optional. User ID. Default is the current user.
	 * @param int $item_id Optional. Item ID, if checking for a specific item. Default null.
	 * @return bool True if the user can delete items, false otherwise.
	 */
	public function user_can_delete( $user_id = null, $item_id = null ) {
		return $this->user_can_perform_item_action( 'delete', $user_id, $item_id );
	}

	/**
	 * Checks whether a user can publish items.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $user_id Optional. User ID. Default is the current user.
	 * @param int $item_id Optional. Item ID, if checking for a specific item. Default null.
	 * @return bool True if the user can publish items, false otherwise.
	 */
	public function user_can_publish( $user_id = null, $item_id = null ) {
		return $this->user_can_perform_item_action( 'publish', $user_id, $item_id );
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
			$this->capability_mappings[ $this->meta_capabilities['read_item'] ]   = array( $this, 'map_read_item' );
			$this->capability_mappings[ $this->meta_capabilities['edit_item'] ]   = array( $this, 'map_edit_item' );
			$this->capability_mappings[ $this->meta_capabilities['delete_item'] ] = array( $this, 'map_delete_item' );

			if ( isset( $this->meta_capabilities['publish_item'] ) ) {
				$this->capability_mappings[ $this->meta_capabilities['publish_item'] ] = $this->base_capabilities['publish_items'];
			}

			if ( is_string( $mode ) && 'meta' !== $mode ) {
				foreach ( $this->base_capabilities as $name => $real_name ) {
					if ( in_array( $mode, array( 'posts', 'pages' ), true ) ) {
						if ( 'read_items' === $name || 'read_others_items' === $name ) {
							$this->capability_mappings[ $real_name ] = 'read';
							continue;
						}

						if ( 'create_items' === $name ) {
							$this->capability_mappings[ $real_name ] = sprintf( 'edit_%s', $mode );
							continue;
						}
					}

					$this->capability_mappings[ $real_name ] = str_replace( '_items', '_' . $mode, $name );
				}
			} elseif ( is_array( $mode ) ) {
				foreach ( $this->base_capabilities as $name => $real_name ) {
					if ( ! isset( $mode[ $name ] ) ) {
						continue;
					}

					$this->capability_mappings[ $real_name ] = $mode[ $name ];
				}
			}
		}
	}

	/**
	 * Sets the manager instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Leaves_And_Love\Plugin_Lib\DB_Objects\Manager $manager Manager instance.
	 */
	public function set_manager( $manager ) {
		$this->manager = $manager;

		$this->set_capabilities();
		$this->setup_hooks();
	}

	/**
	 * Sets the supported capabilities.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function set_capabilities() {
		$prefix = $this->get_prefix();

		$singular_slug = $this->manager->get_singular_slug();
		$plural_slug   = $this->manager->get_plural_slug();

		$this->base_capabilities = array(
			'read_items'   => sprintf( 'read_%s', $prefix . $plural_slug ),
			'create_items' => sprintf( 'create_%s', $prefix . $plural_slug ),
			'edit_items'   => sprintf( 'edit_%s', $prefix . $plural_slug ),
			'delete_items' => sprintf( 'delete_%s', $prefix . $plural_slug ),
		);

		$this->meta_capabilities = array(
			'read_item'    => sprintf( 'read_%s', $prefix . $singular_slug ),
			'edit_item'    => sprintf( 'edit_%s', $prefix . $singular_slug ),
			'delete_item'  => sprintf( 'delete_%s', $prefix . $singular_slug ),
		);

		if ( method_exists( $this->manager, 'get_status_property' ) ) {
			$this->base_capabilities['publish_items'] = sprintf( 'publish_%s', $prefix . $plural_slug );
			$this->meta_capabilities['publish_item'] = sprintf( 'publish_%s', $prefix . $singular_slug );
		}

		if ( method_exists( $this->manager, 'get_author_property' ) ) {
			$this->base_capabilities['read_others_items'] = sprintf( 'read_others_%s', $prefix . $plural_slug );
			$this->base_capabilities['edit_others_items'] = sprintf( 'edit_others_%s', $prefix . $plural_slug );
			$this->base_capabilities['delete_others_items'] = sprintf( 'delete_others_%s', $prefix . $plural_slug );
		}
	}

	/**
	 * Checks whether the user can perform a specific action on a given item.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $action  Action name. Either 'read', 'edit', 'delete' or 'publish'.
	 * @param int    $user_id Optional. User ID. Default is the current user.
	 * @param int    $item_id Optional. Item ID. If omitted, a general check is performed.
	 *                        Default null.
	 * @return bool True if the user can perform the action, false otherwise.
	 */
	protected function user_can_perform_item_action( $action, $user_id = null, $item_id = null ) {
		$args = array();
		if ( null !== $item_id ) {
			$args[] = $action . '_item';
			$args[] = $item_id;
		} else {
			$args[] = $action . '_items';
		}

		if ( ! $user_id ) {
			return call_user_func_array( array( $this, 'current_user_can' ), $args );
		}

		array_unshift( $args, $user_id );

		return call_user_func_array( array( $this, 'user_can' ), $args );
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

		$user_id = absint( $user_id );

		if ( is_callable( $this->capability_mappings[ $cap ] ) ) {
			$mapped_cap = call_user_func( $this->capability_mappings[ $cap ], $user_id, $args );
		} else {
			$mapped_cap = $this->capability_mappings[ $cap ];
		}

		$caps = array( $mapped_cap );

		return $this->map_meta_cap( $caps, $mapped_cap, $user_id, $args );
	}

	/**
	 * Maps the item reading capability.
	 *
	 * If the model uses author IDs and the item belongs to another author, the capability is
	 * mapped to the reading others items capability. Otherwise it is mapped to the basic
	 * reading items capability.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int    $user_id  User ID.
	 * @param array  $args     Additional arguments.
	 * @return string Mapped capability name.
	 */
	protected function map_read_item( $user_id, $args ) {
		return $this->map_item_action( 'read', $user_id, $args );
	}

	/**
	 * Maps the item editing capability.
	 *
	 * If the model uses author IDs and the item belongs to another author, the capability is
	 * mapped to the editing others items capability. Otherwise it is mapped to the basic
	 * editing items capability.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int    $user_id  User ID.
	 * @param array  $args     Additional arguments.
	 * @return string Mapped capability name.
	 */
	protected function map_edit_item( $user_id, $args ) {
		return $this->map_item_action( 'edit', $user_id, $args );
	}

	/**
	 * Maps the item deleting capability.
	 *
	 * If the model uses author IDs and the item belongs to another author, the capability is
	 * mapped to the deleting others items capability. Otherwise it is mapped to the basic
	 * deleting items capability.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int   $user_id  User ID.
	 * @param array $args     Additional arguments.
	 * @return string Mapped capability name.
	 */
	protected function map_delete_item( $user_id, $args ) {
		return $this->map_item_action( 'delete', $user_id, $args );
	}

	/**
	 * Maps a specific item capability.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $action  Action name. Either 'read', 'edit' or 'delete'.
	 * @param int    $user_id User ID.
	 * @param int    $args    Additional arguments.
	 * @return string Mapped capability name.
	 */
	protected function map_item_action( $action, $user_id, $args ) {
		/* Require an ID to be passed to this capability check. */
		if ( ! isset( $args[0] ) || ! is_numeric( $args[0] ) ) {
			return 'do_not_allow';
		}

		$item = $this->manager->get( $args[0] );
		if ( null === $item ) {
			return 'do_not_allow';
		}

		if ( method_exists( $this->manager, 'get_author_property' ) ) {
			$author_property = $this->manager->get_author_property();

			$author_id = $item->$author_property;
			if ( $author_id !== $user_id ) {
				return $this->base_capabilities[ $action . '_others_items' ];
			}
		}

		return $this->base_capabilities[ $action . '_items' ];
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
