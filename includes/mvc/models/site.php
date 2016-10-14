<?php
/**
 * Site model class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\MVC\Models;

use WP_Site;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\MVC\Models\Site' ) ) :

/**
 * Model class for a site
 *
 * This class represents a site. Must only be used in a multisite setup.
 *
 * @since 1.0.0
 *
 * @property string $domain
 * @property string $path
 * @property int    $network_id
 * @property string $registered
 * @property string $last_updated
 * @property string $public
 * @property string $archived
 * @property string $mature
 * @property string $spam
 * @property string $deleted
 * @property string $lang_id
 *
 * @property-read int    $id
 * @property-read string $name
 * @property-read string $home
 * @property-read string $siteurl
 * @property-read int    $post_count
 */
class Site extends Core_Model {
	/**
	 * Constructor.
	 *
	 * Sets the ID and fetches relevant data.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Leaves_And_Love\Plugin_Lib\MVC\Manager $manager The manager instance for the model.
	 * @param WP_Site|null                           $db_obj  Optional. The database object or
	 *                                                        null for a new instance.
	 */
	public function __construct( $manager, $db_obj = null ) {
		parent::__construct( $manager, $db_obj );
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
		return 'blog_id';
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
		if ( 'id' === $property ) {
			return true;
		}

		if ( 'network_id' === $property ) {
			return true;
		}

		if ( 'name' === $property ) {
			return true;
		}

		if ( in_array( $property, array( 'home', 'siteurl', 'post_count' ), true ) ) {
			return true;
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
		if ( 'id' === $property ) {
			return (int) $this->original->blog_id;
		}

		if ( 'network_id' === $property ) {
			return (int) $this->original->site_id;
		}

		if ( 'name' === $property ) {
			return $this->original->blogname;
		}

		if ( in_array( $property, array( 'home', 'siteurl', 'post_count' ), true ) ) {
			return $this->original->$property;
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
		$nowrite_properties = array(
			'id',
			'name',
			'blogname',
			'home',
			'siteurl',
			'post_count',
		);

		if ( in_array( $property, $nowrite_properties, true ) ) {
			return;
		}

		if ( 'network_id' === $property ) {
			$this->set_value_type_safe( 'site_id', $value );
			return;
		}

		parent::__set( $property, $value );
	}

	/**
	 * Fills the $original property with a default object.
	 *
	 * This method is called if a new object has been instantiated.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function set_default_object() {
		$this->original = new WP_Site( array() );
	}

	/**
	 * Returns the names of all properties that are part of the database object.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Array of property names.
	 */
	protected function get_db_fields() {
		return array(
			'blog_id',
			'domain',
			'path',
			'site_id',
			'registered',
			'last_updated',
			'public',
			'archived',
			'mature',
			'spam',
			'deleted',
			'lang_id',
		);
	}
}

endif;
