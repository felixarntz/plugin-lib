<?php
/**
 * Model type class for a Core object
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Types;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Managers\Core_Model_Type_Manager;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Types\Core_Model_Type' ) ) :

	/**
	 * Base class for a core model type
	 *
	 * This class represents a general core model type.
	 *
	 * @since 1.0.0
	 */
	abstract class Core_Model_Type extends Model_Type {
		/**
		 * The original Core object for this model type.
		 *
		 * @since 1.0.0
		 * @var object
		 */
		protected $original;

		/**
		 * Constructor.
		 *
		 * Sets the type slug and additional arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param Core_Model_Type_Manager $owner Parent registry.
		 * @param string                  $slug  Type slug.
		 * @param array|object            $args  Optional. Type arguments. Default empty.
		 */
		public function __construct( $owner, $slug, $args = array() ) {
			if ( is_object( $args ) ) {
				$this->original = $args;
				$args = array();
			}

			parent::__construct( $owner, $slug, $args );
		}

		/**
		 * Magic isset-er.
		 *
		 * Checks whether a property is set.
		 *
		 * @since 1.0.0
		 *
		 * @param string $property Property to check for.
		 * @return bool True if the property is set, false otherwise.
		 */
		public function __isset( $property ) {
			if ( 'slug' === $property ) {
				return true;
			}

			if ( null !== $this->original ) {
				return isset( $this->original->$property );
			}

			return isset( $this->args[ $property ] );
		}

		/**
		 * Magic getter.
		 *
		 * Returns a property value.
		 *
		 * @since 1.0.0
		 *
		 * @param string $property Property to get.
		 * @return mixed Property value, or null if property is not set.
		 */
		public function __get( $property ) {
			if ( 'slug' === $property ) {
				if ( null !== $this->original ) {
					return $this->original->name;
				}

				return $this->slug;
			}

			if ( null !== $this->original ) {
				if ( isset( $this->original->$property ) ) {
					return $this->original->$property;
				}

				return null;
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
		 *
		 * @param string $property Property to set.
		 * @param mixed  $value    Property value.
		 */
		public function __set( $property, $value ) {
			if ( 'slug' === $property ) {
				$this->slug = $value;

				if ( null !== $this->original ) {
					$this->original->name = $value;
				}
				return;
			}

			if ( null !== $this->original ) {
				$this->original->$property = $value;
				return;
			}

			$this->args[ $property ] = $value;
		}

		/**
		 * Returns an array representation of the model type.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array including all model type information.
		 */
		public function to_json() {
			if ( null !== $this->original ) {
				return array_merge( array( 'slug' => $this->slug ), get_object_vars( $this->original ) );
			}

			return parent::to_json();
		}

		/**
		 * Returns the default type arguments.
		 *
		 * @since 1.0.0
		 *
		 * @return array Default type arguments.
		 */
		protected function get_defaults() {
			return array();
		}
	}

endif;
