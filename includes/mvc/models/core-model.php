<?php
/**
 * Model class for a Core object
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\MVC\Models;

use Leaves_And_Love\Plugin_Lib\MVC\Model;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\MVC\Models\Core_Model' ) ) :

/**
 * Base class for a core model
 *
 * This class represents a general core model.
 *
 * @since 1.0.0
 */
abstract class Core_Model extends Model {
	/**
	 * The original Core object for this model.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var object
	 */
	protected $original;

	/**
	 * Core uses several redundant prefixes for property names of its objects.
	 * This property can be used to specify the prefix and thus make access easier.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $redundant_prefix = '';

	/**
	 * Constructor.
	 *
	 * Sets the ID and fetches relevant data.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Leaves_And_Love\Plugin_Lib\MVC\Manager $manager The manager instance for the item.
	 * @param object|null                            $db_obj  Optional. The database object or
	 *                                                        null for a new item.
	 */
	public function __construct( $manager, $db_obj = null ) {
		parent::__construct( $manager, $db_obj );

		if ( ! $db_obj ) {
			$this->set_default_object();
		}
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
		if ( isset( $this->original->$property ) ) {
			return true;
		}

		if ( ! empty( $this->redundant_prefix ) && 0 !== strpos( $property, $this->redundant_prefix ) ) {
			$prefixed_property = $this->redundant_prefix . $property;
			if ( isset( $this->original->$prefixed_property ) ) {
				return true;
			}
		}

		return parent::__isset( $property );
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
		if ( isset( $this->original->$property ) ) {
			return $this->original->$property;
		}

		if ( ! empty( $this->redundant_prefix ) && 0 !== strpos( $property, $this->redundant_prefix ) ) {
			$prefixed_property = $this->redundant_prefix . $property;
			if ( isset( $this->original->$prefixed_property ) ) {
				return $this->original->$prefixed_property;
			}
		}

		return parent::__get( $property );
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

		if ( isset( $this->original->$property ) ) {
			$old = $this->original->$property;

			$this->set_value_type_safe( $property, $value );

			if ( $old !== $this->original->$property && ! in_array( $property, $this->pending_properties, true ) ) {
				$this->pending_properties[] = $property;
			}
			return;
		}

		if ( ! empty( $this->redundant_prefix ) && 0 !== strpos( $property, $this->redundant_prefix ) ) {
			$prefixed_property = $this->redundant_prefix . $property;
			if ( $prefixed_property === $this->get_primary_property() ) {
				return;
			}

			if ( isset( $this->original->$prefixed_property ) ) {
				$old = $this->original->$prefixed_property;

				$this->set_value_type_safe( $prefixed_property, $value );

				if ( $old !== $this->original->$prefixed_property && ! in_array( $prefixed_property, $this->pending_properties, true ) ) {
					$this->pending_properties[] = $prefixed_property;
				}
				return;
			}
		}

		parent::__set( $property, $value );
	}

	/**
	 * Sets the properties of the item to those of a database row object.
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

			if ( ! isset( $this->original->$property ) ) {
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
		if ( $property === $this->get_primary_property() ) {
			$this->original->$property = intval( $value );
		} elseif ( is_int( $this->original->$property ) ) {
			$this->original->$property = intval( $value );
		} elseif ( is_float( $this->original->$property ) ) {
			$this->original->$property = floatval( $value );
		} elseif ( is_string( $this->original->$property ) ) {
			$this->original->$property = strval( $value );
		} elseif ( is_bool( $this->original->$property ) ) {
			$this->original->$property = (bool) $value;
		} else {
			$this->original->$property = $value;
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
			$this->original->$primary_property = $value;
		}

		return $this->original->$primary_property;
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
				$args[ $property ] = $this->original->$property;
			}

			return $args;
		}

		return array_diff_key( get_object_vars( $this->original ), array_flip( $this->get_blacklist() ) );
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
		$blacklist = parent::get_blacklist();

		$blacklist[] = 'original';

		return $blacklist;
	}

	/**
	 * Fills the $original property with a default object.
	 *
	 * This method is called if a new object has been instantiated.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected abstract function set_default_object();
}

endif;
