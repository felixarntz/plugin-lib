<?php

namespace Leaves_And_Love\Sample_DB_Objects;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Query;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Meta_Query_Trait;

class Sample_Query extends Query {
	use Meta_Query_Trait {
		Meta_Query_Trait::parse_where as meta_parse_where;
		Meta_Query_Trait::parse_single_orderby as meta_parse_single_orderby;
		Meta_Query_Trait::get_valid_orderby_fields as meta_get_valid_orderby_fields;
	}

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
		);

		foreach ( $query_vars as $query_var ) {
			$this->query_var_defaults[ $query_var ] = '';
		}
	}

	protected function parse_where() {
		list( $where, $args ) = $this->meta_parse_where();

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

		return array( $where, $args );
	}

	protected function parse_single_orderby( $orderby ) {
		$name = $this->manager->get_sample_name();

		if ( $name . '__in' === $orderby ) {
			$ids = implode( ',', array_map( 'absint', $this->query_vars[ $name . '__in' ] ) );
			return "FIELD( %{$this->table_name}%.id, $ids )";
		}

		if ( 'parent__in' === $orderby ) {
			$ids = implode( ',', array_map( 'absint', $this->query_vars['parent__in'] ) );
			return "FIELD( %{$this->table_name}%.parent_id, $ids )";
		}

		return $this->meta_parse_single_orderby( $orderby );
	}

	protected function parse_single_order( $order, $orderby ) {
		$name = $this->manager->get_sample_name();

		if ( in_array( $orderby, array( $name . '__in', 'parent__in' ), true ) ) {
			return '';
		}

		return parent::parse_single_order( $order, $orderby );
	}

	protected function get_valid_orderby_fields() {
		$name = $this->manager->get_sample_name();

		return array_merge( $this->meta_get_valid_orderby_fields(), array( 'type', 'title', 'parent_id', $name . '__in', 'parent__in' ) );
	}
}
