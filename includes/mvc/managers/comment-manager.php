<?php
/**
 * Manager class for comments
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\MVC\Managers;

use Leaves_And_Love\Plugin_Lib\Traits\Sitewide_Manager;
use Leaves_And_Love\Plugin_Lib\Traits\Meta_Manager;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\MVC\Managers\Comment_Manager' ) ) :

/**
 * Class for a comments manager
 *
 * This class represents a comments manager.
 *
 * @since 1.0.0
 */
class Comment_Manager extends Core_Manager {
	use Sitewide_Manager, Meta_Manager;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Leaves_And_Love\Plugin_Lib\DB    $db       The database instance.
	 * @param Leaves_And_Love\Plugin_Lib\Cache $cache    The cache instance.
	 * @param array                            $messages Messages printed to the user.
	 * @param Leaves_And_Love\Plugin_Lib\Meta  $meta     The meta instance. Optional, but required for managers
	 *                                                   with meta.
	 */
	public function __construct( $db, $cache, $messages, $meta = null ) {
		$this->class_name            = 'Leaves_And_Love\Plugin_Lib\MVC\Models\Comment';
		$this->collection_class_name = 'Leaves_And_Love\Plugin_Lib\MVC\Collections\Comment_Collection';
		$this->query_class_name      = 'Leaves_And_Love\Plugin_Lib\MVC\Queries\Comment_Query';

		$this->table_name     = 'comments';
		$this->cache_group    = 'comment';
		$this->meta_type      = 'comment';
		$this->fetch_callback = 'get_comment';

		parent::__construct( $db, $cache, $messages, $meta );
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
