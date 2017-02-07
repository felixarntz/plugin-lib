<?php

namespace Leaves_And_Love\Sample_DB_Objects;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Query;

class Sample_Query extends Query {
	public function __construct( $manager ) {
		$name = $manager->get_sample_name();

		$this->table_name = $name . 's';
		$this->singular_slug = $name;

		parent::__construct( $manager );

		$query_vars = array(
			'title',
			'parent',
			'parent__in',
			'parent__not_in',
		);

		foreach ( $query_vars as $query_var ) {
			$this->query_var_defaults[ $query_var ] = '';
		}
	}

	protected function parse_where() {
		list( $where, $args ) = parent::parse_where();

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

		return array( $where, $args );
	}

	protected function parse_single_orderby( $orderby ) {
		if ( 'parent__in' === $orderby ) {
			$ids = implode( ',', array_map( 'absint', $this->query_vars['parent__in'] ) );
			return "FIELD( %{$this->table_name}%.parent_id, $ids )";
		}

		return parent::parse_single_orderby( $orderby );
	}

	protected function parse_single_order( $order, $orderby ) {
		if ( 'parent__in' === $orderby ) {
			return '';
		}

		return parent::parse_single_order( $order, $orderby );
	}

	protected function get_valid_orderby_fields() {
		$orderby_fields = parent::get_valid_orderby_fields();

		$orderby_fields = array_merge( $orderby_fields, array( 'title', 'parent_id', 'parent__in' ) );

		return $orderby_fields;
	}
}
