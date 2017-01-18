<?php
/**
 * Trait for queries that support meta
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Traits;

use WP_Meta_Query;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Meta_Query_Trait' ) ) :

/**
 * Trait for queries.
 *
 * Include this trait for queries that support meta.
 *
 * @since 1.0.0
 */
trait Meta_Query_Trait {
	/**
	 * Metadata query container.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var WP_Meta_Query
	 */
	protected $meta_query;

	/**
	 * Metadata query clauses.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $meta_query_clauses = array();

	/**
	 * Adjusts query var defaults.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function adjust_query_var_defaults() {
		$this->query_var_defaults = array_merge( $this->query_var_defaults, array(
			'meta_key'   => '',
			'meta_value' => '',
			'meta_query' => '',
		) );
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
			case 'meta_query':
			case 'meta_query_clauses':
				return true;
		}

		return parent::__isset( $property );
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
			case 'meta_query':
			case 'meta_query_clauses':
				return $this->$property;
		}

		return parent::__get( $property );
	}

	/**
	 * Parses arguments passed to the model query with default query arguments.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string|array $query Array or query string of model query arguments. See
	 *                            Leaves_And_Love\Plugin_Lib\DB_Objects\Query::query().
	 */
	protected function parse_query( $query ) {
		parent::parse_query( $query );

		$this->meta_query = new WP_Meta_Query();
		$this->meta_query->parse_query_vars( $this->query_vars );

		if ( ! empty( $this->meta_query->queries ) && is_callable( array( $this->manager, 'get_meta_type' ) ) ) {
			$prefix = $this->manager->db()->get_prefix();
			$name   = $this->manager->get_meta_type();

			$this->meta_query_clauses = $this->meta_query->get_sql( $prefix . $name, "%{$this->table_name}%", 'id', $this );
		}
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
		$join = parent::parse_join();

		if ( ! empty( $this->meta_query_clauses ) ) {
			$join .= $this->meta_query_clauses['join'];
		}

		return $join;
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
		list( $where, $args ) = parent::parse_where();

		if ( ! empty( $this->meta_query_clauses ) ) {
			$where['meta_query'] = preg_replace( '/^\s*AND\s*/', '', $this->meta_query_clauses['where'] );
		}

		return array( $where, $args );
	}

	/**
	 * Parses the SQL groupby clause.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string Groupby value for the SQL query.
	 */
	protected function parse_groupby() {
		if ( ! empty( $this->meta_query_clauses ) ) {
			return '%' . $this->table_name . '%.id';
		}

		return parent::parse_groupby();
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
		if ( in_array( $orderby, $this->get_meta_orderby_fields(), true ) ) {
			$meta_table = _get_meta_table( $this->manager->db()->get_prefix() . $this->manager->get_meta_type() );

			if ( $this->query_vars['meta_key'] === $orderby || 'meta_value' === $orderby ) {
				return "$meta_table.meta_value";
			}

			if ( 'meta_value_num' === $orderby ) {
				return "$meta_table.meta_value+0";
			}

			$meta_query_clauses = $this->meta_query->get_clauses();

			return sprintf( "CAST(%s.meta_value AS %s)", esc_sql( $meta_query_clauses[ $orderby ]['alias'] ), esc_sql( $meta_query_clauses[ $orderby ]['cast'] ) );
		}

		return parent::parse_single_orderby( $orderby );
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
		return array_merge( parent::get_valid_orderby_fields(), $this->get_meta_orderby_fields() );
	}

	/**
	 * Returns the meta orderby fields to use in orderby clauses.
	 *
	 * These depend on the current meta query.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Array of meta orderby fields.
	 */
	protected function get_meta_orderby_fields() {
		if ( empty( $this->meta_query->queries ) || ! is_callable( array( $this->manager, 'get_meta_type' ) ) ) {
			return array();
		}

		$meta_orderby_fields = array();

		if ( ! empty( $this->query_vars['meta_key'] ) ) {
			$meta_orderby_fields[] = $this->query_vars['meta_key'];
			$meta_orderby_fields[] = 'meta_value';
			$meta_orderby_fields[] = 'meta_value_num';
		}

		$meta_query_clauses = $this->meta_query->get_clauses();
		if ( $meta_query_clauses ) {
			$meta_orderby_fields = array_merge( $meta_orderby_fields, array_keys( $meta_query_clauses ) );
		}

		return $meta_orderby_fields;
	}
}

endif;
