<?php
/**
 * Manager class for comments
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Managers;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Meta_Manager_Trait;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Managers\Comment_Manager' ) ) :

/**
 * Class for a comments manager
 *
 * This class represents a comments manager.
 *
 * @since 1.0.0
 */
class Comment_Manager extends Core_Manager {
	use Meta_Manager_Trait;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string                                                               $prefix   The instance prefix.
	 * @param array                                                                $services {
	 *     Array of service instances.
	 *
	 *     @type Leaves_And_Love\Plugin_Lib\DB            $db            The database instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Cache         $cache         The cache instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Meta          $meta          The meta instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Error_Handler $error_handler The error handler instance.
	 * }
	 * @param Leaves_And_Love\Plugin_Lib\Translations\Translations_Comment_Manager $translations Translations instance.
	 */
	public function __construct( $prefix, $services, $translations ) {
		$this->class_name            = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Comment';
		$this->collection_class_name = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Comment_Collection';
		$this->query_class_name      = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Queries\Comment_Query';

		$this->table_name     = 'comments';
		$this->cache_group    = 'comment';
		$this->meta_type      = 'comment';
		$this->fetch_callback = 'get_comment';

		parent::__construct( $prefix, $services, $translations );
	}

	/**
	 * Fetches the comment object for a specific ID.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $model_id ID of the object to fetch.
	 * @return object|null The comment object for the requested ID, or null if it does not exist.
	 */
	public function fetch( $model_id ) {
		// This method requires the first parameter to be a reference, thus a direct call.
		return get_comment( $model_id );
	}

	/**
	 * Internal method to insert a new comment into the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $args Array of column => value pairs for the new database row.
	 * @return int|false The ID of the new comment, or false on failure.
	 */
	protected function insert_into_db( $args ) {
		return wp_insert_comment( $args );
	}

	/**
	 * Internal method to update an existing comment in the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int   $comment_id ID of the comment to update.
	 * @param array $args       Array of column => value pairs to update in the database row.
	 * @return bool True on success, or false on failure.
	 */
	protected function update_in_db( $comment_id, $args ) {
		$args['comment_ID'] = $comment_id;

		return (bool) wp_update_comment( $args );
	}

	/**
	 * Internal method to delete a comment from the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int $comment_id ID of the comment to delete.
	 * @return bool True on success, or false on failure.
	 */
	protected function delete_from_db( $comment_id ) {
		return wp_delete_comment( $comment_id, true );
	}
}

endif;
