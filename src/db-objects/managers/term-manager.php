<?php
/**
 * Manager class for terms
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Managers;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Meta_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Title_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Content_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Slug_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Type_Manager_Trait;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Managers\Term_Manager' ) ) :

/**
 * Class for a terms manager
 *
 * This class represents a terms manager.
 *
 * @since 1.0.0
 */
class Term_Manager extends Core_Manager {
	use Meta_Manager_Trait, Title_Manager_Trait, Content_Manager_Trait, Slug_Manager_Trait, Type_Manager_Trait;

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
	 *     @type Leaves_And_Love\Plugin_Lib\DB                                              $db            The database instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Cache                                           $cache         The cache instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Meta                                            $meta          The meta instance.
	 *     @type Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Managers\Taxonomy_Manager $types         The type manager instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Error_Handler                                   $error_handler The error handler instance.
	 * }
	 * @param Leaves_And_Love\Plugin_Lib\Translations\Translations_Term_Manager $translations Translations instance.
	 */
	public function __construct( $prefix, $services, $translations ) {
		$this->class_name            = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Term';
		$this->collection_class_name = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Collections\Term_Collection';
		$this->query_class_name      = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Queries\Term_Query';

		$this->singular_slug = 'term';
		$this->plural_slug   = 'terms';

		$this->table_name       = 'terms';
		$this->cache_group      = 'terms';
		$this->meta_type        = 'term';
		$this->fetch_callback   = 'get_term';
		$this->primary_property = 'term_id';
		$this->title_property   = 'name';
		$this->content_property = 'description';
		$this->slug_property    = 'slug';
		$this->type_property    = 'taxonomy';

		$this->public = true;

		parent::__construct( $prefix, $services, $translations );
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
		if ( ! isset( $args['name'] ) || ! isset( $args['taxonomy'] ) ) {
			return false;
		}

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
		if ( isset( $args['taxonomy'] ) ) {
			$taxonomy = $args['taxonomy'];
			unset( $args['taxonomy'] );
		} else {
			$term = get_term( $term_id );
			if ( ! $term || is_wp_error( $term ) ) {
				return false;
			}

			$taxonomy = $term->taxonomy;
		}

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
		if ( ! $term || is_wp_error( $term ) ) {
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
