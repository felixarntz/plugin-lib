<?php
/**
 * Comment model class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\MVC\Models;

use Leaves_And_Love\Plugin_Lib\Traits\Sitewide_Model;
use WP_Comment;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\MVC\Models\Comment' ) ) :

/**
 * Model class for a comment
 *
 * This class represents a comment.
 *
 * @since 1.0.0
 *
 * @property int    $post_id
 * @property string $author
 * @property string $author_email
 * @property string $author_url
 * @property string $author_IP
 * @property string $date
 * @property string $date_gmt
 * @property string $content
 * @property int    $karma
 * @property string $approved
 * @property string $agent
 * @property string $type
 * @property int    $parent
 * @property int    $user_id
 *
 * @property-read int $id
 */
class Comment extends Core_Model {
	use Sitewide_Model;

	/**
	 * Constructor.
	 *
	 * Sets the ID and fetches relevant data.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Leaves_And_Love\Plugin_Lib\MVC\Manager $manager The manager instance for the model.
	 * @param WP_Comment|null                        $db_obj  Optional. The database object or
	 *                                                        null for a new instance.
	 */
	public function __construct( $manager, $db_obj = null ) {
		parent::__construct( $manager, $db_obj );

		$this->redundant_prefix = 'comment_';
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
		return 'comment_ID';
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

		if ( 'post_id' === $property ) {
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
			return $this->original->comment_ID;
		}

		if ( 'post_id' === $property ) {
			return $this->original->comment_post_ID;
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
		);

		if ( in_array( $property, $nowrite_properties, true ) ) {
			return;
		}

		if ( 'post_id' === $property ) {
			$this->original->comment_post_ID = $value;
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
		$this->original = new WP_Comment( array() );
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
			'comment_ID',
			'comment_post_ID',
			'comment_author',
			'comment_author_email',
			'comment_author_url',
			'comment_author_IP',
			'comment_date',
			'comment_date_gmt',
			'comment_content',
			'comment_karma',
			'comment_approved',
			'comment_agent',
			'comment_type',
			'comment_parent',
			'user_id',
		);
	}
}

endif;
