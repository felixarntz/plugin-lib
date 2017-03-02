<?php
/**
 * Manager class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

use Leaves_And_Love\Plugin_Lib\Service;
use Leaves_And_Love\Plugin_Lib\Traits\Container_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Traits\Hook_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Traits\Translations_Service_Trait;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Manager' ) ) :

/**
 * Base class for a manager
 *
 * This class represents a general manager.
 *
 * @since 1.0.0
 *
 * @method Leaves_And_Love\Plugin_Lib\DB    db()
 * @method Leaves_And_Love\Plugin_Lib\Cache cache()
 */
abstract class Manager extends Service {
	use Container_Service_Trait, Hook_Service_Trait, Translations_Service_Trait;

	/**
	 * The model class name.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $class_name = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Model';

	/**
	 * The collection class name.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $collection_class_name = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Collection';

	/**
	 * The query class name.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $query_class_name = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Query';

	/**
	 * Singular slug to use.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $singular_slug = 'model';

	/**
	 * Plural slug to use.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $plural_slug = 'models';

	/**
	 * The model database table name.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $table_name = 'models';

	/**
	 * The model cache group name.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $cache_group = 'models';

	/**
	 * The primary property for models.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $primary_property = 'id';

	/**
	 * The database service definition.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var string
	 */
	protected static $service_db = 'Leaves_And_Love\Plugin_Lib\DB';

	/**
	 * Cache service definition.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var string
	 */
	protected static $service_cache = 'Leaves_And_Love\Plugin_Lib\Cache';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string                                               $prefix   The instance prefix.
	 * @param array                                                $services {
	 *     Array of service instances.
	 *
	 *     @type Leaves_And_Love\Plugin_Lib\DB            $db            The database instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Cache         $cache         The cache instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Error_Handler $error_handler The error handler instance.
	 * }
	 * @param Leaves_And_Love\Plugin_Lib\Translations\Translations $translations Translations instance.
	 */
	public function __construct( $prefix, $services, $translations ) {
		$this->set_prefix( $prefix );
		$this->set_services( $services );
		$this->set_translations( $translations );

		$capabilities = $this->capabilities();
		if ( $capabilities ) {
			$capabilities->set_manager( $this );
		}

		$this->add_database_table( $this->table_name );

		$this->setup_hooks();
	}

	/**
	 * Creates a new model.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Leaves_And_Love\Plugin_Lib\DB_Objects\Model The new model object.
	 */
	public function create() {
		$class_name = $this->class_name;

		$model = new $class_name( $this );

		if ( method_exists( $this, 'get_type_property' ) ) {
			$type_property = $this->get_type_property();

			$model->$type_property = $this->types()->get_default();
		}

		if ( method_exists( $this, 'get_status_property' ) ) {
			$status_property = $this->get_status_property();

			$model->$status_property = $this->statuses()->get_default();
		}

		return $model;
	}

	/**
	 * Returns an model with a specific ID.
	 *
	 * If an actual instance is passed to the method, it is simply passed through.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int|Leaves_And_Love\Plugin_Lib\DB_Objects\Model $model_id ID of the model to get. Can be an
	 *                                                           actual instance too.
	 * @return Leaves_And_Love\Plugin_Lib\DB_Objects\Model|null The model with the requested ID, or null if
	 *                                                   it does not exist.
	 */
	public function get( $model_id ) {
		if ( is_a( $model_id, $this->class_name ) ) {
			$model = $model_id;

			$primary_property = $this->get_primary_property();

			if ( $model->$primary_property ) {
				$this->storage_set( $model->$primary_property, $model );
			}

			return $model;
		}

		$model_id = absint( $model_id );

		if ( ! $this->storage_isset( $model_id ) ) {
			$db_obj = $this->fetch( $model_id );
			if ( ! $db_obj ) {
				$this->storage_unset( $model_id );
			} else {
				$class_name = $this->class_name;
				$this->storage_set( $model_id, new $class_name( $this, $db_obj ) );
			}
		}

		return $this->storage_get( $model_id );
	}

	/**
	 * Creates a query instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Leaves_And_Love\Plugin_Lib\DB_Objects\Query Query instance.
	 */
	public function create_query_object() {
		$class_name = $this->query_class_name;

		return new $class_name( $this );
	}

	/**
	 * Queries models for specific criteria.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string|array $query Array or query string of model query arguments. See
	 *                            `Leaves_And_Love\Plugin_Lib\DB_Objects\Query::query()` for
	 *                            more information.
	 * @return Leaves_And_Love\Plugin_Lib\DB_Objects\Collection Collection of models.
	 */
	public function query( $query = array() ) {
		$query_instance = $this->create_query_object();

		return $query_instance->query( $query );
	}

	/**
	 * Transforms an array of models into a collection.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array  $models  The model IDs, objects or types for this collection.
	 * @param int    $total   Optional. The total amount of models in the collection.
	 *                        Default is the number of models.
	 * @param string $fields  Optional. Mode of the models passed. Default 'ids'.
	 *
	 * @return Leaves_And_Love\Plugin_Lib\DB_Objects\Collection Collection of models.
	 */
	public function get_collection( $models, $total = 0, $fields = 'ids' ) {
		$class_name = $this->collection_class_name;

		return new $class_name( $this, $models, $total, $fields );
	}

	/**
	 * Adds a new model to the database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Array of column => value pairs for the new database row.
	 * @return int|false The ID of the new model, or false on failure.
	 */
	public function add( $args ) {
		$result = $this->db()->insert( $this->table_name, $args );
		if ( ! $result ) {
			return false;
		}

		$id = absint( $this->db()->insert_id );

		$new_db_object = $this->fetch( $id );

		$this->clean_cache( $id, $new_db_object, null );

		return $id;
	}

	/**
	 * Updates an existing model in the database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int   $model_id ID of the model to update.
	 * @param array $args     Array of column => value pairs to update in the database row.
	 * @return bool True on success, or false on failure.
	 */
	public function update( $model_id, $args ) {
		$model_id = absint( $model_id );

		$old_db_object = $this->fetch( $model_id );

		$result = $this->db()->update( $this->table_name, $args, array( 'id' => $model_id ) );
		if ( ! $result ) {
			return false;
		}

		$new_db_object = $this->fetch( $model_id );

		$this->clean_cache( $model_id, $new_db_object, $old_db_object );

		return true;
	}

	/**
	 * Deletes an model from the database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $model_id ID of the model to delete.
	 * @return bool True on success, or false on failure.
	 */
	public function delete( $model_id ) {
		$model_id = absint( $model_id );

		$old_db_object = $this->fetch( $model_id );

		$result = $this->db()->delete( $this->table_name, array( 'id' => $model_id ) );
		if ( ! $result ) {
			return false;
		}

		$this->clean_cache( $model_id, null, $old_db_object );

		$this->storage_unset( $model_id );

		return true;
	}

	/**
	 * Fetches a database row for a specific ID.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $model_id ID of the row to fetch.
	 * @return object|null The database row for the requested ID, or null if it does not exist.
	 */
	public function fetch( $model_id ) {
		$model_id = absint( $model_id );

		$db_obj = $this->get_from_cache( $model_id );
		if ( ! $db_obj ) {
			$db_obj = $this->db()->get_row( "SELECT * FROM %{$this->table_name}% WHERE id = %d", $model_id );

			if ( ! $db_obj ) {
				return null;
			}

			$this->add_to_cache( $model_id, $db_obj );
		}

		return $db_obj;
	}

	/**
	 * Counts all existing models for this manager.
	 *
	 * If the manager supports statuses, individual counts for each status
	 * are returned as well.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $user_id Optional. If provided and the manager supports authors,
	 *                     only models by that user are counted. Default 0 (ignored).
	 * @return array Array of `$status => $count` pairs. In addition, the array
	 *               always includes a key called '_total', containing the overall
	 *               count. If the manager does not support statuses, the array
	 *               only contains the '_total' key.
	 */
	public function count( $user_id = 0 ) {
		$user_id = absint( $user_id );

		$cache_key = $this->plural_slug;
		if ( method_exists( $this, 'get_author_property' ) && $user_id > 0 ) {
			$cache_key .= '-' . $user_id;
		}

		$counts = $this->cache()->get( $cache_key, 'counts' );
		if ( false !== $counts ) {
			return $counts;
		}

		$where = '';
		$where_args = array();
		if ( method_exists( $this, 'get_author_property' ) && $user_id > 0 ) {
			$author_property = $this->get_author_property();

			$where = " WHERE $author_property = %d";
			$where_args[] = $user_id;
		}

		if ( method_exists( $this, 'get_status_property' ) ) {
			$status_property = $this->get_status_property();

			$results = $this->db()->get_results( "SELECT $status_property, COUNT( * ) AS num_models FROM %{$this->table_name}% $where GROUP BY $status_property", $where_args );

			$total = 0;
			$counts = array_fill_keys( array_keys( $this->statuses()->query() ), 0 );
			foreach ( $results as $row ) {
				$counts[ $row->$status_property ] = $row->num_models;
				$total += $row->num_models;
			}

			$counts['_total'] = $total;
		} else {
			$total = $this->db()->get_var( "SELECT COUNT( * ) FROM %{$this->table_name}% $where", $where_args );

			$counts = array( '_total' => $total );
		}

		$this->cache()->set( $cache_key, $counts, 'counts' );

		return $counts;
	}

	/**
	 * Returns the name of the primary property that identifies each model.
	 *
	 * This is usually an integer ID denoting the database row.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Name of the primary property.
	 */
	public function get_primary_property() {
		return $this->primary_property;
	}

	/**
	 * Returns the singular slug.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Singular slug.
	 */
	public function get_singular_slug() {
		return $this->singular_slug;
	}

	/**
	 * Returns the plural slug.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Plural slug.
	 */
	public function get_plural_slug() {
		return $this->plural_slug;
	}

	/**
	 * Returns the database table name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Table name.
	 */
	public function get_table_name() {
		return $this->table_name;
	}

	/**
	 * Returns a specific manager message.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $identifier Identifier for the message.
	 * @param bool   $noop       Optional. Whether this is a noop message. Default false.
	 * @return string|array Translated message, or array if $noop, or empty string if
	 *                      invalid identifier.
	 */
	public function get_message( $identifier, $noop = false ) {
		return $this->get_translation( $identifier, $noop );
	}

	/**
	 * Adds data to the model cache, if the cache key doesn't already exist.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int|string $key    The cache key to use for retrieval later.
	 * @param mixed      $data   The data to add to the cache.
	 * @param int        $expire Optional. When the cache data should expire, in seconds.
	 *                           Default 0 (no expiration).
	 * @return bool False if cache key already exists, true on success.
	 */
	public function add_to_cache( $key, $data, $expire = 0 ) {
		return $this->cache()->add( $key, $data, $this->cache_group, $expire );
	}

	/**
	 * Removes model cache contents matching key.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int|string $key What the contents in the cache are called.
	 * @return bool True on successful removal, false on failure.
	 */
	public function delete_from_cache( $key ) {
		return $this->cache()->delete( $key, $this->cache_group );
	}

	/**
	 * Retrieves model cache contents from the cache by key.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int|string  $key   The key under which the cache contents are stored.
	 * @param bool        $force Optional. Whether to force an update of the local cache from the
	 *                           persistent cache. Default false.
	 * @param bool        $found Optional. Whether the key was found in the cache. Disambiguates a
	 *                           return of false, a storable value. Passed by reference. Default null.
	 * @return bool|mixed False on failure to retrieve contents, or the cache contents on success.
	 */
	public function get_from_cache( $key, $force = false, &$found = null ) {
		return $this->cache()->get( $key, $this->cache_group, $force, $found );
	}

	/**
	 * Replaces contents of the model cache with new data.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int|string $key    The key for the cache data that should be replaced.
	 * @param mixed      $data   The new data to store in the cache.
	 * @param int        $expire Optional. When to expire the cache contents, in seconds.
	 *                           Default 0 (no expiration).
	 * @return bool False if original value does not exist, true if contents were replaced.
	 */
	public function replace_in_cache( $key, $data, $expire = 0 ) {
		return $this->cache()->replace( $key, $data, $this->cache_group, $expire );
	}

	/**
	 * Saves data to the model cache.
	 *
	 * Differs from Leaves_And_Love\Plugin_Lib\DB_Objects\Manager::add_to_cache() and
	 * Leaves_And_Love\Plugin_Lib\DB_Objects\Manager::replace_in_cache() in that it will
	 * always write data.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int|string $key    The cache key to use for retrieval later.
	 * @param mixed      $data   The contents to store in the cache.
	 * @param int        $expire Optional. When to expire the cache contents, in seconds.
	 *                           Default 0 (no expiration).
	 * @return bool False on failure, true on success.
	 */
	public function set_in_cache( $key, $data, $expire = 0 ) {
		return $this->cache()->set( $key, $data, $this->cache_group, $expire );
	}

	/**
	 * Cleans the cache for an model with a specific ID.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int         $model_id      ID of the model to clean the cache for.
	 * @param object|null $new_db_object The new raw database object, or null if deleted.
	 * @param object|null $old_db_object The old raw database object, or null if added.
	 */
	protected function clean_cache( $model_id, $new_db_object, $old_db_object ) {
		$model_id = absint( $model_id );

		$this->maybe_clean_count_cache( $new_db_object, $old_db_object );

		if ( method_exists( $this, 'get_meta_type' ) ) {
			$this->cache()->delete( $model_id, $this->get_meta_type() . '_meta' );
		}

		$this->delete_from_cache( $model_id );

		$this->set_in_cache( 'last_changed', microtime() );
	}

	/**
	 * Cleans the count cache for a model if relevant changes have been applied.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param object|null $new_db_object The new raw database object, or null if deleted.
	 * @param object|null $old_db_object The old raw database object, or null if added.
	 */
	protected function maybe_clean_count_cache( $new_db_object, $old_db_object ) {
		$status_property = method_exists( $this, 'get_status_property' ) ? $this->get_status_property() : '';
		$author_property = method_exists( $this, 'get_author_property' ) ? $this->get_author_property() : '';

		$delete_author_count = false;

		if ( null === $new_db_object || null === $old_db_object || ( ! empty( $status_property ) && $new_db_object->$status_property !== $old_db_object->$status_property ) ) {
			$this->cache()->delete( $this->plural_slug, 'counts' );

			if ( ! empty( $author_property ) ) {
				$delete_author_count = true;
			}
		} elseif ( null !== $new_db_object && null !== $old_db_object && ! empty( $author_property ) && $new_db_object->$author_property !== $old_db_object->$author_property ) {
			$delete_author_count = true;
		}

		if ( $delete_author_count ) {
			$user_ids = array();
			if ( null !== $new_db_object ) {
				$user_ids[] = absint( $new_db_object->$author_property );
			}
			if ( null !== $old_db_object && ! in_array( absint( $old_db_object->$author_property ), $user_ids, true ) ) {
				$user_ids[] = absint( $old_db_object->$author_property );
			}

			foreach ( $user_ids as $user_id ) {
				$this->cache()->delete( $this->plural_slug . '-' . $user_id, 'counts' );
			}
		}
	}

	/**
	 * Sets a model in the storage.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int                                         $model_id ID of the model to set.
	 * @param Leaves_And_Love\Plugin_Lib\DB_Objects\Model $model    Model to set for the ID.
	 * @return bool True on success, or false on failure.
	 */
	protected function storage_set( $model_id, $model ) {
		return Storage::store( $this->cache_group, $model_id, $model );
	}

	/**
	 * Retrieves a model from the storage.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int $model_id ID of the model to get.
	 * @return Leaves_And_Love\Plugin_Lib\DB_Objects\Model|null The model on success, or null if it doesn't exist.
	 */
	protected function storage_get( $model_id ) {
		return Storage::retrieve( $this->cache_group, $model_id );
	}

	/**
	 * Checks whether a model is set in the storage.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int $model_id ID of the model to check for.
	 * @return bool True if the model is set, or false otherwise.
	 */
	protected function storage_isset( $model_id ) {
		return Storage::is_stored( $this->cache_group, $model_id );
	}

	/**
	 * Unsets a model in the storage.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int $model_id ID of the model to unset.
	 * @return bool True on success, or false on failure.
	 */
	protected function storage_unset( $model_id ) {
		return $this->storage_set( $model_id, null );
	}

	/**
	 * Adds the database table.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected abstract function add_database_table();

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * This method must be implemented and then be called from the constructor.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function setup_hooks() {
		$prefix        = $this->get_prefix();
		$singular_slug = $this->get_singular_slug();

		if ( method_exists( $this, 'maybe_set_slug_property' ) ) {
			$this->filters[] = array(
				'name'     => "{$prefix}_pre_add_{$singular_slug}",
				'callback' => array( $this, 'maybe_set_slug_property' ),
				'priority' => 100,
				'num_args' => 2,
			);
			$this->filters[] = array(
				'name'     => "{$prefix}_pre_update_{$singular_slug}",
				'callback' => array( $this, 'maybe_set_slug_property' ),
				'priority' => 100,
				'num_args' => 2,
			);
		}

		if ( method_exists( $this, 'maybe_set_author_property' ) ) {
			$this->filters[] = array(
				'name'     => "{$prefix}_pre_add_{$singular_slug}",
				'callback' => array( $this, 'maybe_set_author_property' ),
				'priority' => 100,
				'num_args' => 2,
			);
		}

		if ( method_exists( $this, 'maybe_set_date_property' ) ) {
			$this->filters[] = array(
				'name'     => "{$prefix}_pre_add_{$singular_slug}",
				'callback' => array( $this, 'maybe_set_date_property' ),
				'priority' => 100,
				'num_args' => 2,
			);
		}
	}
}

endif;
