<?php
/**
 * Model class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Model' ) ) :

/**
 * Base class for a model
 *
 * This class represents a general model.
 *
 * @since 1.0.0
 */
abstract class Model {
	/**
	 * Properties pending upstream synchronization.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $pending_properties = array();

	/**
	 * Metadata pending upstream synchronization, as key => value pairs.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $pending_meta = array();

	/**
	 * The manager instance for the model.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\DB_Objects\Manager
	 */
	protected $manager;

	/**
	 * The primary property of the model.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $primary_property = 'id';

	/**
	 * Constructor.
	 *
	 * Sets the ID and fetches relevant data.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Leaves_And_Love\Plugin_Lib\DB_Objects\Manager $manager The manager instance for the model.
	 * @param object|null                                   $db_obj  Optional. The database object or
	 *                                                               null for a new instance.
	 */
	public function __construct( $manager, $db_obj = null ) {
		$this->manager = $manager;

		if ( property_exists( $this, '__site_id' ) ) {
			$this->__site_id = get_current_blog_id();
		}

		if ( $db_obj ) {
			$this->set( $db_obj );
		}
	}

	/**
	 * Returns the name of the primary property that identifies the model.
	 *
	 * This is usually an integer ID denoting the database row.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Name of the primary property.
	 */
	public function get_primary_property() {
		return $this->primary_property;
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
		$blacklist = $this->get_blacklist();
		if ( in_array( $property, $blacklist, true ) ) {
			return false;
		}

		if ( property_exists( $this, $property ) ) {
			return true;
		}

		if ( method_exists( $this->manager, 'meta_exists' ) ) {
			if ( array_key_exists( $property, $this->pending_meta ) ) {
				if ( null === $this->pending_meta[ $property ] ) {
					return false;
				}
				return true;
			}

			if ( $this->primary_property_value() ) {
				method_exists( $this, 'maybe_switch' ) && $this->maybe_switch();

				$result = $this->manager->meta_exists( $this->primary_property_value(), $property );

				method_exists( $this, 'maybe_restore' ) && $this->maybe_restore();

				return $result;
			}
		}

		return false;
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
		$blacklist = $this->get_blacklist();
		if ( in_array( $property, $blacklist, true ) ) {
			return null;
		}

		if ( property_exists( $this, $property ) ) {
			return $this->$property;
		}

		if ( method_exists( $this->manager, 'get_meta' ) ) {
			if ( array_key_exists( $property, $this->pending_meta ) ) {
				return $this->pending_meta[ $property ];
			}

			if ( $this->primary_property_value() ) {
				method_exists( $this, 'maybe_switch' ) && $this->maybe_switch();

				$meta = $this->manager->get_meta( $this->primary_property_value(), $property, true );

				method_exists( $this, 'maybe_restore' ) && $this->maybe_restore();

				if ( false === $meta ) {
					return null;
				}

				return $meta;
			}
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
		if ( $property === $this->get_primary_property() ) {
			return;
		}

		$blacklist = $this->get_blacklist();
		if ( in_array( $property, $blacklist, true ) ) {
			return;
		}

		if ( property_exists( $this, $property ) ) {
			$old = $this->$property;

			$this->set_value_type_safe( $property, $value );

			if ( $old !== $this->$property && ! in_array( $property, $this->pending_properties, true ) ) {
				$this->pending_properties[] = $property;
			}
			return;
		}

		if ( method_exists( $this->manager, 'get_meta' ) ) {
			if ( ! $this->primary_property_value() ) {
				if ( null !== $value ) {
					$this->pending_meta[ $property ] = $value;
				} elseif ( array_key_exists( $property, $this->pending_meta ) ) {
					unset( $this->pending_meta[ $property ] );
				}
			} else {
				method_exists( $this, 'maybe_switch' ) && $this->maybe_switch();

				$old_value = $this->manager->get_meta( $this->primary_property_value(), $property, true );
				if ( false === $old_value && array_key_exists( $property, $this->pending_meta ) && null === $value ) {
					unset( $this->pending_meta[ $property ] );
				} elseif ( $value != $old_value ) {
					$this->pending_meta[ $property ] = $value;
				}

				method_exists( $this, 'maybe_restore' ) && $this->maybe_restore();
			}
		}
	}

	/**
	 * Synchronizes the model with the database by storing the currently pending values.
	 *
	 * If the model is new (i.e. does not have an ID yet), it will be inserted to the database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return true|WP_Error True on success, or an error object on failure.
	 */
	public function sync_upstream() {
		if ( ! $this->primary_property_value() ) {
			$args = $this->get_property_values();

			unset( $args[ $this->get_primary_property() ] );

			$result = $this->manager->add( $args );
			if ( ! $result ) {
				return new WP_Error( 'db_insert_error', $this->manager->get_message( 'db_insert_error' ) );
			}

			$this->primary_property_value( $result );

			$this->manager->get( $this );
		} elseif ( 0 < count( $this->pending_properties ) ) {
			$args = $this->get_property_values( true );

			$result = $this->manager->update( $this->primary_property_value(), $args );
			if ( ! $result ) {
				return new WP_Error( 'db_update_error', $this->manager->get_message( 'db_update_error' ) );
			}
		}

		$this->pending_properties = array();

		if ( method_exists( $this->manager, 'update_meta' ) ) {
			$pending_meta = $this->pending_meta;

			foreach ( $pending_meta as $meta_key => $meta_value ) {
				if ( null === $meta_value ) {
					$result = $this->manager->delete_meta( $this->primary_property_value(), $meta_key );
					if ( ! $result ) {
						return new WP_Error( 'meta_delete_error', sprintf( $this->manager->get_message( 'meta_delete_error' ), $meta_key ) );
					}
				} else {
					$result = $this->manager->update_meta( $this->primary_property_value(), $meta_key, $meta_value );
					if ( ! $result ) {
						return new WP_Error( 'meta_update_error', sprintf( $this->manager->get_message( 'meta_update_error' ), $meta_key ) );
					}
				}

				unset( $this->pending_meta[ $meta_key ] );
			}
		}

		return true;
	}

	/**
	 * Synchronizes the model with the database by fetching the currently stored values.
	 *
	 * If the model contains unsynchronized changes, these will be overridden. This method basically allows
	 * to reset the model to the values stored in the database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return true|WP_Error True on success, or an error object on failure.
	 */
	public function sync_downstream() {
		if ( ! $this->primary_property_value() ) {
			return new WP_Error( 'db_fetch_error_missing_id', $this->manager->get_message( 'db_fetch_error_missing_id' ) );
		}

		$result = $this->manager->fetch( $this->primary_property_value() );
		if ( ! $result ) {
			return new WP_Error( 'db_fetch_error', $this->manager->get_message( 'db_fetch_error' ) );
		}

		$this->set( $result );

		$this->pending_properties = array();

		if ( method_exists( $this->manager, 'get_meta' ) ) {
			$this->pending_meta = array();
		}

		return true;
	}

	/**
	 * Deletes the model from the database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return true|WP_Error True on success, or an error object on failure.
	 */
	public function delete() {
		if ( ! $this->primary_property_value() ) {
			return new WP_Error( 'db_delete_error_missing_id', $this->manager->get_message( 'db_delete_error_missing_id' ) );
		}

		$result = $this->manager->delete( $this->primary_property_value() );
		if ( ! $result ) {
			return new WP_Error( 'db_delete_error', $this->manager->get_message( 'db_delete_error' ) );
		}

		$this->primary_property_value( 0 );

		if ( method_exists( $this->manager, 'delete_all_meta' ) ) {
			$result = $this->manager->delete_all_meta( $this->primary_property_value() );
			if ( ! $result ) {
				return new WP_Error( 'meta_delete_all_error', $this->manager->get_message( 'meta_delete_all_error' ) );
			}
		}

		return true;
	}

	/**
	 * Returns an array representation of the model.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Array including all information for the model.
	 */
	public function to_json() {
		$data = $this->get_property_values();

		if ( method_exists( $this->manager, 'get_meta' ) ) {
			$meta = $this->pending_meta;
			if ( $this->primary_property_value() ) {
				$_meta = $this->manager->get_meta( $this->primary_property_value() );

				foreach ( $_meta as $key => $value ) {
					if ( array_key_exists( $key, $meta ) ) {
						if ( null === $meta[ $key ] ) {
							unset( $meta[ $key ] );
						}
						continue;
					}

					$meta[ $key ] = maybe_unserialize( $value[0] );
				}
			}

			$data = array_merge( $data, $meta );
		}

		return $data;
	}

	/**
	 * Sets the properties of the model to those of a database row object.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param object $db_obj The database object.
	 */
	protected function set( $db_obj ) {
		$blacklist = $this->get_blacklist();

		$args = get_object_vars( $db_obj );
		foreach ( $args as $property => $value ) {
			if ( in_array( $property, $blacklist, true ) ) {
				continue;
			}

			if ( ! property_exists( $this, $property ) ) {
				continue;
			}

			$this->set_value_type_safe( $property, $value );
		}
	}

	/**
	 * Sets the value of an existing property in a type-safe way.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $property Property to set.
	 * @param mixed  $value    Property value.
	 */
	protected function set_value_type_safe( $property, $value ) {
		if ( is_int( $this->$property ) ) {
			$this->$property = intval( $value );
		} elseif ( is_float( $this->$property ) ) {
			$this->$property = floatval( $value );
		} elseif ( is_string( $this->$property ) ) {
			$this->$property = strval( $value );
		} elseif ( is_bool( $this->$property ) ) {
			$this->$property = (bool) $value;
		} else {
			$this->$property = $value;
		}
	}

	/**
	 * Sets or gets the value of the primary property.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int|null $value Integer to set the value, null to retrieve it. Default null.
	 * @return return int Current value of the primary property.
	 */
	protected function primary_property_value( $value = null ) {
		$primary_property = $this->get_primary_property();

		if ( is_int( $value ) ) {
			$this->$primary_property = $value;
		}

		return $this->$primary_property;
	}

	/**
	 * Returns all current values as $property => $value pairs.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param bool $pending_only Whether to only return pending properties. Default false.
	 * @return array Array of $property => $value pairs.
	 */
	protected function get_property_values( $pending_only = false ) {
		if ( $pending_only ) {
			$args = array();
			foreach ( $this->pending_properties as $property ) {
				$args[ $property ] = $this->$property;
			}

			return $args;
		}

		return array_diff_key( get_object_vars( $this ), array_flip( $this->get_blacklist() ) );
	}

	/**
	 * Returns a list of internal properties that are not publicly accessible.
	 *
	 * When overriding this method, always make sure to merge with the parent result.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Property blacklist.
	 */
	protected function get_blacklist() {
		$blacklist = array(
			'pending_properties',
			'pending_meta',
			'manager',
			'primary_property',
		);

		if ( property_exists( $this, 'type_property' ) ) {
			$blacklist[] = 'type_property';
		}

		if ( property_exists( $this, '__site_id' ) ) {
			$blacklist[] = '__site_id';
			$blacklist[] = '__switched';
		}

		return $blacklist;
	}
}

endif;
