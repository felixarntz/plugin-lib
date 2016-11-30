<?php
/**
 * Manager class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\MVC;

use Leaves_And_Love\Plugin_Lib\Service;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\MVC\Manager' ) ) :

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
	/**
	 * The database instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\DB
	 */
	protected $db;

	/**
	 * The cache instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Cache
	 */
	protected $cache;

	/**
	 * Models container.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $models = array();

	/**
	 * The model class name.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $class_name = 'Leaves_And_Love\Plugin_Lib\MVC\Model';

	/**
	 * The collection class name.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $collection_class_name = 'Leaves_And_Love\Plugin_Lib\MVC\Collection';

	/**
	 * The query class name.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $query_class_name = 'Leaves_And_Love\Plugin_Lib\MVC\Query';

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
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Leaves_And_Love\Plugin_Lib\DB                     $db           The database instance.
	 * @param Leaves_And_Love\Plugin_Lib\Cache                  $cache        The cache instance.
	 * @param array                                             $messages     Messages printed to the user.
	 * @param Leaves_And_Love\Plugin_Lib\Meta                   $meta         The meta instance. Optional, but required
	 *                                                                        for managers with meta.
	 * @param Leaves_And_Love\Plugin_Lib\MVC\Model_Type_Manager $type_manager The type manager instance. Optional,
	 *                                                                        but required for managers with types.
	 */
	public function __construct( $db, $cache, $messages, $meta = null, $type_manager = null ) {
		$this->db = $db;
		$this->cache = $cache;

		$services = array( 'db', 'cache' );

		if ( property_exists( $this, 'meta' ) ) {
			$this->meta = $meta;
			$services[] = 'meta';
		}

		if ( property_exists( $this, 'type_manager' ) ) {
			$this->type_manager = $type_manager;
			$services[] = 'type_manager';
		}

		$this->set_services( $services );
		$this->set_messages( $messages );
	}

	/**
	 * Creates a new model.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Leaves_And_Love\Plugin_Lib\MVC\Model The new model object.
	 */
	public function create() {
		$class_name = $this->class_name;
		return new $class_name( $this );
	}

	/**
	 * Returns an model with a specific ID.
	 *
	 * If an actual instance is passed to the method, it is simply passed through.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int|Leaves_And_Love\Plugin_Lib\MVC\Model $model_id ID of the model to get. Can be an
	 *                                                           actual instance too.
	 * @return Leaves_And_Love\Plugin_Lib\MVC\Model|null The model with the requested ID, or null if
	 *                                                   it does not exist.
	 */
	public function get( $model_id ) {
		if ( is_a( $model_id, $this->class_name ) ) {
			$model = $model_id;

			$primary_property = $model->get_primary_property();

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
	 * Queries models for specific criteria.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string|array $query Array or query string of model query arguments. See
	 *                            `Leaves_And_Love\Content_Organizer\Base\Item_Query::query()` for
	 *                            more information.
	 * @return Leaves_And_Love\Plugin_Lib\MVC\Collection Collection of models.
	 */
	public function query( $query = array() ) {
		$class_name = $this->query_class_name;

		$query = new $class_name( $this );

		return $query->query( $query );
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
	 * @return Leaves_And_Love\Plugin_Lib\MVC\Collection Collection of models.
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
		$result = $this->db->insert( $this->table_name, $args );
		if ( ! $result ) {
			return false;
		}

		$id = absint( $this->db->insert_id );

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

		$result = $this->db->update( $this->table_name, $args, array( 'id' => $model_id ) );
		if ( ! $result ) {
			return false;
		}

		$this->clean_cache( $model_id );

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

		$result = $this->db->delete( $this->table_name, array( 'id' => $model_id ) );
		if ( ! $result ) {
			return false;
		}

		$this->clean_cache( $model_id );

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

		$db_obj = $this->get_cache( $model_id );
		if ( ! $db_obj ) {
			$db_obj = $this->db->get_row( "SELECT * FROM %{$this->table_name}% WHERE id = %d", $model_id );

			if ( ! $db_obj ) {
				return null;
			}

			$this->add_cache( $model_id, $db_obj );
		}

		return $db_obj;
	}

	/**
	 * Returns a specific manager message.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Slug for the message.
	 * @return string The message, or an empty string if not found.
	 */
	public function get_message( $slug ) {
		if ( ! isset( $this->messages[ $slug ] ) ) {
			return '';
		}

		return $this->messages[ $slug ];
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
		return $this->cache->add( $key, $data, $this->cache_group, $expire );
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
		return $this->cache->delete( $key, $this->cache_group );
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
		return $this->cache->get( $key, $this->cache_group, $force, $found );
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
		return $this->cache->replace( $key, $data, $this->cache_group, $expire );
	}

	/**
	 * Saves data to the model cache.
	 *
	 * Differs from Leaves_And_Love\Plugin_Lib\MVC\Manager::add_cache() and
	 * Leaves_And_Love\Plugin_Lib\MVC\Manager::replace_cache() in that it will
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
		return $this->cache->set( $key, $data, $this->cache_group, $expire );
	}

	/**
	 * Cleans the cache for an model with a specific ID.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int $model_id ID of the model to clean the cache for.
	 */
	protected function clean_cache( $model_id ) {
		$model_id = absint( $model_id );

		if ( property_exists( $this, 'meta_type' ) ) {
			$this->cache->delete( $model_id, $this->meta_type . '_meta' );
		}

		$this->delete_cache( $model_id );

		$this->set_cache( 'last_changed', microtime() );
	}

	/**
	 * Sets a model in the storage.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int                                  $model_id ID of the model to set.
	 * @param Leaves_And_Love\Plugin_Lib\MVC\Model $model    Model to set for the ID.
	 * @return bool True on success, or false on failure.
	 */
	protected function storage_set( $model_id, $model ) {
		if ( property_exists( $this, '__sitewide' ) && is_multisite() ) {
			$site_id = get_current_blog_id();

			if ( ! isset( $this->models[ $site_id ] ) ) {
				$this->models[ $site_id ] = array();
			}

			$this->models[ $site_id ][ $model_id ] = $model;

			return true;
		}

		$this->models[ $model_id ] = $model;

		return true;
	}

	/**
	 * Retrieves a model from the storage.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int $model_id ID of the model to get.
	 * @return Leaves_And_Love\Plugin_Lib\MVC\Model|null The model on success, or null if it doesn't exist.
	 */
	protected function storage_get( $model_id ) {
		if ( property_exists( $this, '__sitewide' ) && is_multisite() ) {
			$site_id = get_current_blog_id();

			if ( ! isset( $this->models[ $site_id ] ) ) {
				return null;
			}

			if ( ! isset( $this->models[ $site_id ][ $model_id ] ) ) {
				return null;
			}

			return $this->models[ $site_id ][ $model_id ];
		}

		if ( ! isset( $this->models[ $model_id ] ) ) {
			return null;
		}

		return $this->models[ $model_id ];
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
		if ( property_exists( $this, '__sitewide' ) && is_multisite() ) {
			$site_id = get_current_blog_id();

			if ( ! isset( $this->models[ $site_id ] ) ) {
				return false;
			}

			return isset( $this->models[ $site_id ][ $model_id ] );
		}

		return isset( $this->models[ $model_id ] );
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
}

endif;
