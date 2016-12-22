<?php
/**
 * Manager class for sites
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Managers;

use Leaves_And_Love\Plugin_Lib\Traits\Meta_Manager;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Managers\Site_Manager' ) ) :

/**
 * Class for a sites manager
 *
 * This class represents a sites manager. Must only be used in a multisite setup.
 *
 * @since 1.0.0
 */
class Site_Manager extends Core_Manager {
	use Meta_Manager;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Leaves_And_Love\Plugin_Lib\DB    $db                  The database instance.
	 * @param Leaves_And_Love\Plugin_Lib\Cache $cache               The cache instance.
	 * @param array                            $messages            Messages printed to the user.
	 * @param array                            $additional_services Optional. Further services. Default empty.
	 */
	public function __construct( $db, $cache, $messages, $additional_services = array() ) {
		$this->class_name            = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Site';
		$this->collection_class_name = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Site_Collection';
		$this->query_class_name      = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Queries\Site_Query';

		$this->table_name     = 'blogs';
		$this->cache_group    = 'sites';
		$this->meta_type      = 'site';
		$this->fetch_callback = 'get_site';

		parent::__construct( $db, $cache, $messages, $additional_services );
	}

	/**
	 * Internal method to insert a new site into the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $args Array of column => value pairs for the new database row.
	 * @return int|false The ID of the new site, or false on failure.
	 */
	protected function insert_into_db( $args ) {
		$domain = $args['domain'];
		unset( $args['domain'] );

		$path = $args['path'];
		unset( $args['path'] );

		$network_id = $args['site_id'];
		unset( $args['site_id'] );

		unset( $args['registered'] );
		unset( $args['last_updated'] );

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			$user_id = 1;
		}

		$result = wpmu_create_blog( $domain, $path, '', $user_id, $args, $network_id );
		if ( is_wp_error( $result ) ) {
			return false;
		}

		return (int) $result;
	}

	/**
	 * Internal method to update an existing site in the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int   $site_id ID of the site to update.
	 * @param array $args    Array of column => value pairs to update in the database row.
	 * @return bool True on success, or false on failure.
	 */
	protected function update_in_db( $site_id, $args ) {
		return update_blog_details( $site_id, $args );
	}

	/**
	 * Internal method to delete a site from the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int $site_id ID of the site to delete.
	 * @return bool True on success, or false on failure.
	 */
	protected function delete_from_db( $site_id ) {
		if ( ! function_exists( 'wpmu_delete_blog' ) ) {
			require_once ABSPATH . 'wp-admin/includes/ms.php';
		}

		wpmu_delete_blog( $site_id, true );

		return true;
	}
}

endif;
