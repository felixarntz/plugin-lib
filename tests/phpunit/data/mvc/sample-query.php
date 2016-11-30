<?php

namespace Leaves_And_Love\Sample_MVC;

use Leaves_And_Love\Plugin_Lib\MVC\Query;
use WP_Meta_Query;

class Sample_Query extends Query {
	protected $meta_query;

	protected $meta_query_clauses;

	public function __construct( $manager ) {
		$name = $manager->get_sample_name();

		$this->table_name = $name . 's';

		parent::__construct( $manager );

		$query_vars = array(
			$name . '__in',
			$name . '__not_in',
			'type',
			'title',
			'parent',
			'parent__in',
			'parent__not_in',
			'meta_key',
			'meta_value',
			'meta_query',
		);

		foreach ( $query_vars as $query_var ) {
			$this->query_var_defaults[ $query_var ] = '';
		}
	}

	protected function parse_query( $query ) {
		parent::parse_query( $query );

		$prefix = $this->manager->db()->get_prefix();
		$name   = $this->manager->get_sample_name();

		$this->meta_query = new WP_Meta_Query();
		$this->meta_query->parse_query_vars( $this->query_vars );
		if ( ! empty( $this->meta_query->queries ) ) {
			$this->meta_query_clauses = $this->meta_query->get_sql( $prefix . $name, "%{$this->table_name}%", 'id', $this );
		}
	}

	protected function parse_join() {
		$join = parent::parse_join();

		if ( ! empty( $this->meta_query_clauses ) ) {
			$join .= $this->meta_query_clauses['join'];
		}

		return $join;
	}

	protected function parse_where() {
		list( $where, $args ) = parent::parse_where();

		$name = $this->manager->get_sample_name();

		if ( ! empty( $this->query_vars[ $name . '__in' ] ) ) {
			$ids = array_map( 'absint', $this->query_vars[ $name . '__in' ] );
			$where[ $name . '__in' ] = "%{$this->table_name}%.id IN ( " . implode( ',', array_fill( 0, count( $ids ), '%d' ) ) . ' )';
			$args = array_merge( $args, $ids );
		}

		if ( ! empty( $this->query_vars[ $name . '__not_in' ] ) ) {
			$ids = array_map( 'absint', $this->query_vars[ $name . '__not_in' ] );
			$where[ $name . '__not_in' ] = "%{$this->table_name}%.id NOT IN ( " . implode( ',', array_fill( 0, count( $ids ), '%d' ) ) . ' )';
			$args = array_merge( $args, $ids );
		}

		if ( ! empty( $this->query_vars['type'] ) ) {
			if ( is_array( $this->query_vars['type'] ) ) {
				$types = array_map( 'sanitize_key', $this->query_vars['type'] );
				$where['type'] = "%{$this->table_name}%.type IN ( " . implode( ',', array_fill( 0, count( $types ), '%s' ) ) . ' )';
				$args = array_merge( $args, $types );
			} else {
				$type = sanitize_key( $this->query_vars['type'] );
				$where['type'] = "%{$this->table_name}%.type = %s";
				$args[] = $type;
			}
		}

		if ( ! empty( $this->query_vars['title'] ) ) {
			$title = $this->query_vars['title'];
			$where['title'] = "%{$this->table_name}%.title = %s";
			$args[] = $title;
		}

		if ( ! empty( $this->query_vars['parent'] ) ) {
			$parent_id = absint( $this->query_vars['parent'] );
			$where['parent'] = "%{$this->table_name}%.parent_id = %d";
			$args[] = $parent_id;
		}

		if ( ! empty( $this->query_vars['parent__in'] ) ) {
			$parent_ids = array_map( 'absint', $this->query_vars['parent__in'] );
			$where['parent__in'] = "%{$this->table_name}%.parent_id IN ( " . implode( ',', array_fill( 0, count( $parent_ids ), '%d' ) ) . ' )';
			$args = array_merge( $args, $parent_ids );
		}

		if ( ! empty( $this->query_vars['parent__not_in'] ) ) {
			$parent_ids = array_map( 'absint', $this->query_vars['parent__not_in'] );
			$where['parent__not_in'] = "%{$this->table_name}%.parent_id NOT IN ( " . implode( ',', array_fill( 0, count( $parent_ids ), '%d' ) ) . ' )';
			$args = array_merge( $args, $parent_ids );
		}

		if ( ! empty( $this->meta_query_clauses ) ) {
			$where['meta_query'] = preg_replace( '/^\s*AND\s*/', '', $this->meta_query_clauses['where'] );
		}

		return array( $where, $args );
	}

	protected function get_valid_orderby_fields() {
		return array_merge( parent::get_valid_orderby_fields(), array( 'type', 'title', 'parent_id' ) );
	}
}
