<?php
/**
 * Query class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Query' ) ) :

/**
 * Base class for a query
 *
 * This class represents a general query.
 *
 * @since 1.0.0
 *
 * @property-read string                                    $request
 * @property-read array                                     $request_args
 * @property-read array                                     $sql_clauses
 * @property-read array                                     $query_vars
 * @property-read array                                     $query_var_defaults
 * @property-read Leaves_And_Love\Plugin_Lib\DB_Objects\Collection $results
 */
abstract class Query {
	/**
	 * The manager instance for the query.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\DB_Objects\Manager
	 */
	protected $manager;

	/**
	 * The model database table name.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $table_name = 'models';

	/**
	 * SQL for database query.
	 *
	 * Contains placeholders that need to be filled with `$request_args`.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $request = '';

	/**
	 * Arguments that need to be escaped in the database query.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $request_args = array();

	/**
	 * SQL query clauses.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $sql_clauses = array(
		'select'  => '',
		'from'    => '',
		'where'   => array(),
		'groupby' => '',
		'orderby' => '',
		'limits'  => '',
	);

	/**
	 * Query vars set by the user.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $query_vars = array();

	/**
	 * Default values for query vars.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $query_var_defaults = array();

	/**
	 * The results for the query.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\DB_Objects\Collection
	 */
	protected $results;

	/**
	 * Constructor.
	 *
	 * Sets the manager instance and assigns the defaults.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Leaves_And_Love\Plugin_Lib\DB_Objects\Manager $manager The manager instance for the model query.
	 */
	public function __construct( $manager ) {
		$this->manager = $manager;

		$this->query_var_defaults = array(
			'fields'        => 'objects',
			'number'        => -1,
			'offset'        => 0,
			'no_found_rows' => null,
			'orderby'       => array( 'id' => 'ASC' ),
		);

		if ( method_exists( $this, 'adjust_query_var_defaults' ) ) {
			$this->adjust_query_var_defaults();
		}
	}

	/**
	 * Magic isset-er.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $property Property to check for.
	 * @return bool True if property is set, false otherwise.
	 */
	public function __isset( $property ) {
		switch ( $property ) {
			case 'request':
			case 'request_args':
			case 'query_vars':
			case 'query_var_defaults':
			case 'results':
				return true;
		}

		return false;
	}

	/**
	 * Magic getter.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $property Property to get.
	 * @return mixed Property value.
	 */
	public function __get( $property ) {
		switch ( $property ) {
			case 'request':
			case 'request_args':
			case 'query_vars':
			case 'query_var_defaults':
			case 'results':
				return $this->$property;
		}

		return null;
	}

	/**
	 * Sets up the query for retrieving models.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string|array $query {
	 *      Array or query string of model query arguments.
	 *
	 *      @type string       $fields        Fields to return. Accepts 'ids' (returns a collection of model
	 *                                        IDs) or 'objects' (returns a collection of full model objects).
	 *                                        Default 'objects'.
	 *      @type int          $number        Maximum number of models to retrieve. Default -1 (no limit).
	 *      @type int          $offset        Number of models to offset the query. Used to build the LIMIT clause.
	 *                                        Default 0.
	 *      @type bool         $no_found_rows Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default depends
	 *                                        on the $number parameter: If unlimited, default is true, otherwise
	 *                                        default is false.
	 *      @type array        $orderby       Array of orderby => order pairs. Accepted orderby key is 'id'.
	 *                                        The orderby values must be either 'ASC' or 'DESC'. Default
	 *                                        array( 'id' => 'ASC' ).
	 * }
	 * @return Leaves_And_Love\Plugin_Lib\DB_Objects\Collection Collection of models.
	 */
	public function query( $query ) {
		$this->parse_query( $query );

		return $this->get_results();
	}

	/**
	 * Parses arguments passed to the model query with default query arguments.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @see Leaves_And_Love\Plugin_Lib\DB_Objects\Query::query()
	 *
	 * @param string|array $query Array or query string of model query arguments. See
	 *                            Leaves_And_Love\Plugin_Lib\DB_Objects\Query::query().
	 */
	protected function parse_query( $query ) {
		$this->query_vars = wp_parse_args( $query, $this->query_var_defaults );

		$this->query_vars['number'] = intval( $this->query_vars['number'] );
		if ( $this->query_vars['number'] < 0 ) {
			$this->query_vars['number'] = 0;
		}

		$this->query_vars['offset'] = absint( $this->query_vars['offset'] );

		if ( null === $this->query_vars['no_found_rows'] ) {
			if ( $this->query_vars['number'] === 0 ) {
				$this->query_vars['no_found_rows'] = true;
			} else {
				$this->query_vars['no_found_rows'] = false;
			}
		}
	}

	/**
	 * Retrieves a list of models matching the query vars.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return Leaves_And_Love\Plugin_Lib\DB_Objects\Collection Collection of models.
	 */
	protected function get_results() {
		$key = md5( serialize( wp_array_slice_assoc( $this->query_vars, array_keys( $this->query_var_defaults ) ) ) );
		$last_changed = $this->manager->get_from_cache( 'last_changed' );
		if ( ! $last_changed ) {
			$last_changed = microtime();
			$this->manager->set_in_cache( 'last_changed', $last_changed );
		}

		$cache_key = "get_results:$key:$last_changed";
		$cache_value = $this->manager->get_from_cache( $cache_key );

		if ( false === $cache_value ) {
			$total = 0;
			$model_ids = $this->query_results();
			if ( $model_ids && ! $this->query_vars['no_found_rows'] ) {
				$total_models_query = "SELECT FOUND_ROWS()";
				$total = (int) $this->manager->db()->get_var( $total_models_query );
			}

			$cache_value = array(
				'model_ids' => $model_ids,
				'total'     => $total,
			);

			$this->manager->add_to_cache( $cache_key, $cache_value );
		} else {
			$model_ids = $cache_value['model_ids'];
			$total = $cache_value['total'];
		}

		return $this->create_collection( $model_ids, $total, $this->query_vars['fields'] );
	}

	/**
	 * Creates a collection object from the query results.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array  $models  The model IDs, or objects for this collection.
	 * @param int    $total   Optional. The total amount of models in the collection. Default is the
	 *                        number of models.
	 * @param string $fields  Optional. Mode of the models passed. Default 'ids'.
	 * @return Leaves_And_Love\Plugin_Lib\DB_Objects\Collection Collection of models.
	 */
	protected function create_collection( $model_ids, $total, $fields ) {
		$model_ids = array_map( 'intval', $model_ids );

		$this->results = $this->manager->get_collection( $model_ids, $total, 'ids' );

		if ( 'objects' === $fields ) {
			$this->results->transform_into_objects();
		}

		return $this->results;
	}

	/**
	 * Used internally to get a list of model IDs or model types matching the query vars.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Array of model IDs or model types (if the $fields query var is set to 'types').
	 */
	protected function query_results() {
		list( $fields, $distinct ) = $this->parse_fields();
		if ( is_bool( $distinct ) ) {
			$distinct = $distinct ? 'DISTINCT' : '';
		}

		$number = $this->query_vars['number'];
		$offset = $this->query_vars['offset'];

		if ( $number ) {
			if ( $offset ) {
				$limits = "LIMIT $offset,$number";
			} else {
				$limits = "LIMIT $number";
			}
		}

		$found_rows = '';
		if ( ! $this->query_vars['no_found_rows'] ) {
			$found_rows = 'SQL_CALC_FOUND_ROWS';
		}

		$orderby = $this->parse_orderby( $this->query_vars['orderby'] );

		list( $this->sql_clauses['where'], $this->request_args ) = $this->parse_where();

		$join = $this->parse_join();

		$where = implode( ' AND ', $this->sql_clauses['where'] );

		$pieces = array( 'fields', 'join', 'where', 'orderby', 'limits', 'groupby' );

		$clauses = compact( $pieces );

		$fields = isset( $clauses['fields'] ) ? $clauses['fields'] : '';
		$join = isset( $clauses['join'] ) ? $clauses['join'] : '';
		$where = isset( $clauses['where'] ) ? $clauses['where'] : '';
		$orderby = isset( $clauses['orderby'] ) ? $clauses['orderby'] : '';
		$limits = isset( $clauses['limits'] ) ? $clauses['limits'] : '';
		$groupby = isset( $clauses['groupby'] ) ? $clauses['groupby'] : '';

		if ( $where ) {
			$where = "WHERE $where";
		}

		if ( $orderby ) {
			$orderby = "ORDER BY $orderby";
		}

		if ( $groupby ) {
			$groupby = "GROUP BY $groupby";
		}

		$this->sql_clauses['select']  = "SELECT $distinct $found_rows $fields";
		$this->sql_clauses['from']    = "FROM %{$this->table_name}% $join";
		$this->sql_clauses['groupby'] = $groupby;
		$this->sql_clauses['orderby'] = $orderby;
		$this->sql_clauses['limits']  = $limits;

		$this->request = "{$this->sql_clauses['select']} {$this->sql_clauses['from']} {$where} {$this->sql_clauses['groupby']} {$this->sql_clauses['orderby']} {$this->sql_clauses['limits']}";

		return $this->manager->db()->get_col( $this->request, $this->request_args );
	}

	/**
	 * Parses the SQL fields value.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Array with the first element being the fields part of the SQL query and the second
	 *               being a boolean specifying whether to use the DISTINCT keyword.
	 */
	protected function parse_fields() {
		return array( '%' . $this->table_name . '%.id', false );
	}

	/**
	 * Parses the SQL join value.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string Join value for the SQL query.
	 */
	protected function parse_join() {
		return '';
	}

	/**
	 * Parses the SQL where clause.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Array with the first element being the array of SQL where clauses and the second
	 *               being the array of arguments for those where clauses.
	 */
	protected function parse_where() {
		return array( array(), array() );
	}

	/**
	 * Parses the SQL orderby clause.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string|array $orderby The $orderby query var.
	 * @return string The orderby clause.
	 */
	protected function parse_orderby( $orderby ) {
		if ( in_array( $orderby, array( 'none', array(), false ), true ) ) {
			return '';
		}

		if ( empty( $orderby ) ) {
			return '%' . $this->table_name . '%.id ASC';
		}

		$orderby_array = array();
		foreach ( $orderby as $_orderby => $_order ) {
			if ( ! in_array( $_orderby, $this->get_valid_orderby_fields(), true ) ) {
				continue;
			}

			$parsed_orderby = $this->parse_single_orderby( $_orderby );
			$parsed_order   = $this->parse_single_order( $_order, $_orderby );
			if ( ! empty( $parsed_order ) ) {
				$parsed_orderby .= ' ' . $parsed_order;
			}

			$orderby_array[] = $parsed_orderby;
		}

		return implode( ', ', array_unique( $orderby_array ) );
	}

	/**
	 * Parses a single $orderby element.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $orderby The orderby field. Must be valid.
	 * @return string The parsed orderby SQL string.
	 */
	protected function parse_single_orderby( $orderby ) {
		return '%' . $this->table_name . '%.' . $orderby;
	}

	/**
	 * Parses a single $order element.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $order   The order value. Either 'ASC' or 'DESC'.
	 * @param string $orderby The orderby field. Must be valid.
	 * @return string The parsed order SQL string, or empty if not necessary.
	 */
	protected function parse_single_order( $order, $orderby ) {
		return 'DESC' === strtoupper( $order ) ? 'DESC' : 'ASC';
	}

	/**
	 * Returns the fields that are valid to be used in orderby clauses.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Array of valid orderby fields.
	 */
	protected function get_valid_orderby_fields() {
		return array( 'id' );
	}
}

endif;
