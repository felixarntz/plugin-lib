<?php
/**
 * Manager class for terms
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\MVC\Managers;

use Leaves_And_Love\Plugin_Lib\Traits\Sitewide_Manager;
use Leaves_And_Love\Plugin_Lib\Traits\Meta_Manager;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\MVC\Managers\Term_Manager' ) ) :

/**
 * Class for a terms manager
 *
 * This class represents a terms manager.
 *
 * @since 1.0.0
 */
class Term_Manager extends Core_Manager {
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
		$this->class_name            = 'Leaves_And_Love\Plugin_Lib\MVC\Models\Term';
		$this->collection_class_name = 'Leaves_And_Love\Plugin_Lib\MVC\Collections\Terms_Collection';
		$this->query_class_name      = 'Leaves_And_Love\Plugin_Lib\MVC\Queries\Term_Query';

		$this->table_name     = 'terms';
		$this->cache_group    = 'terms';
		$this->meta_type      = 'term';
		$this->fetch_callback = 'get_term';

		parent::__construct( $db, $cache, $messages, $meta );
	}

	/**
	 * Internal method to insert a new term into the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $args Array of column => value pairs for the new database row.
	 * @return int|false The ID of the new term, or false on failure.
	 */
	protected function insert_into_db( $args ) {
		$name = $args['name'];
		unset( $args['name'] );

		$taxonomy = $args['taxonomy'];
		unset( $args['taxonomy'] );

		$result = wp_insert_term( $name, $taxonomy, $args );
		if ( is_wp_error( $result ) ) {
			return false;
		}

		return $result['term_id'];
	}

	/**
	 * Internal method to update an existing term in the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int   $term_id ID of the term to update.
	 * @param array $args    Array of column => value pairs to update in the database row.
	 * @return bool True on success, or false on failure.
	 */
	protected function update_in_db( $term_id, $args ) {
		$taxonomy = $args['taxonomy'];
		unset( $args['taxonomy'] );

		$result = wp_update_term( $term_id, $taxonomy, $args );
		if ( is_wp_error( $result ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Internal method to delete a term from the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int $term_id ID of the term to delete.
	 * @return bool True on success, or false on failure.
	 */
	protected function delete_from_db( $term_id ) {
		$term = get_term( $term_id );
		if ( ! $term ) {
			return false;
		}

		$result = wp_delete_term( $term_id, $term->taxonomy );
		if ( ! $result || is_wp_error( $result ) ) {
			return false;
		}

		return true;
	}
}

endif;
