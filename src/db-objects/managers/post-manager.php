<?php
/**
 * Manager class for posts
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Managers;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Meta_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Type_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Status_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Author_Manager_Trait;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Managers\Post_Manager' ) ) :

/**
 * Class for a posts manager
 *
 * This class represents a posts manager.
 *
 * @since 1.0.0
 */
class Post_Manager extends Core_Manager {
	use Meta_Manager_Trait, Type_Manager_Trait, Status_Manager_Trait, Author_Manager_Trait;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string                                                            $prefix   The instance prefix.
	 * @param array                                                             $services {
	 *     Array of service instances.
	 *
	 *     @type Leaves_And_Love\Plugin_Lib\DB                                                   $db            The database instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Cache                                                $cache         The cache instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Meta                                                 $meta          The meta instance.
	 *     @type Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Managers\Post_Type_Manager     $types         The type manager instance.
	 *     @type Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Status_Managers\Post_Status_Manager $statuses      The status manager instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Error_Handler                                        $error_handler The error handler instance.
	 * }
	 * @param Leaves_And_Love\Plugin_Lib\Translations\Translations_Post_Manager $translations Translations instance.
	 */
	public function __construct( $prefix, $services, $translations ) {
		$this->class_name            = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Post';
		$this->collection_class_name = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Post_Collection';
		$this->query_class_name      = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Queries\Post_Query';

		$this->table_name       = 'posts';
		$this->cache_group      = 'posts';
		$this->meta_type        = 'post';
		$this->fetch_callback   = 'get_post';
		$this->primary_property = 'ID';
		$this->type_property    = 'post_type';
		$this->status_property  = 'post_status';
		$this->author_property  = 'post_author';

		parent::__construct( $prefix, $services, $translations );
	}

	/**
	 * Internal method to insert a new post into the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $args Array of column => value pairs for the new database row.
	 * @return int|false The ID of the new post, or false on failure.
	 */
	protected function insert_into_db( $args ) {
		$result = wp_insert_post( $args, true );
		if ( is_wp_error( $result ) ) {
			return false;
		}

		return $result;
	}

	/**
	 * Internal method to update an existing post in the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int   $post_id ID of the post to update.
	 * @param array $args    Array of column => value pairs to update in the database row.
	 * @return bool True on success, or false on failure.
	 */
	protected function update_in_db( $post_id, $args ) {
		$args['ID'] = $post_id;

		$result = wp_update_post( $args, true );
		if ( is_wp_error( $result ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Internal method to delete a post from the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int $post_id ID of the post to delete.
	 * @return bool True on success, or false on failure.
	 */
	protected function delete_from_db( $post_id ) {
		return (bool) wp_delete_post( $post_id, true );
	}
}

endif;
