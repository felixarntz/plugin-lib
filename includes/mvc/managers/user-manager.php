<?php
/**
 * Manager class for users
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\MVC\Managers;

use Leaves_And_Love\Plugin_Lib\Traits\Meta_Manager;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\MVC\Managers\User_Manager' ) ) :

/**
 * Class for a users manager
 *
 * This class represents a users manager.
 *
 * @since 1.0.0
 */
class User_Manager extends Core_Manager {
	use Meta_Manager;

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
		$this->class_name            = 'Leaves_And_Love\Plugin_Lib\MVC\Models\User';
		$this->collection_class_name = 'Leaves_And_Love\Plugin_Lib\MVC\Collections\User_Collection';
		$this->query_class_name      = 'Leaves_And_Love\Plugin_Lib\MVC\Queries\User_Query';

		$this->table_name     = 'users';
		$this->cache_group    = 'users';
		$this->meta_type      = 'user';
		$this->fetch_callback = 'get_userdata';

		parent::__construct( $db, $cache, $messages, $meta );
	}

	/**
	 * Internal method to insert a new user into the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $args Array of column => value pairs for the new database row.
	 * @return int|false The ID of the new user, or false on failure.
	 */
	protected function insert_into_db( $args ) {
		$result = wp_insert_user( $args );
		if ( is_wp_error( $result ) ) {
			return false;
		}

		return $result;
	}

	/**
	 * Internal method to update an existing user in the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int   $user_id ID of the user to update.
	 * @param array $args    Array of column => value pairs to update in the database row.
	 * @return bool True on success, or false on failure.
	 */
	protected function update_in_db( $user_id, $args ) {
		$args['ID'] = $user_id;

		$result = wp_update_user( $args );
		if ( is_wp_error( $result ) ) {
			return false;
		}

		return $result;
	}

	/**
	 * Internal method to delete a user from the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int $user_id ID of the user to delete.
	 * @return bool True on success, or false on failure.
	 */
	protected function delete_from_db( $user_id ) {
		if ( ! function_exists( 'wp_delete_user' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}

		return wp_delete_user( $user_id );
	}
}

endif;
