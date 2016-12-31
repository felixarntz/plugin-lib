<?php

class Test_Hook_Service_Class extends Leaves_And_Love\Plugin_Lib\Hook_Service {
	protected $hooks;

	public function __construct( $prefix, $hooks = array() ) {
		$this->prefix = $prefix;

		$this->hooks = $hooks;
	}

	protected function add_hooks() {
		foreach ( $this->hooks as $hook ) {
			$type = isset( $hook['type'] ) && 'filter' === $hook['type'] ? 'filter' : 'action';

			if ( 'filter' === $type ) {
				add_filter( $hook['name'], $hook['callback'], 10, 1 );
			} else {
				add_action( $hook['name'], $hook['callback'], 10, 0 );
			}
		}
	}

	protected function remove_hooks() {
		foreach ( $this->hooks as $hook ) {
			$type = isset( $hook['type'] ) && 'filter' === $hook['type'] ? 'filter' : 'action';

			if ( 'filter' === $type ) {
				remove_filter( $hook['name'], $hook['callback'], 10 );
			} else {
				remove_action( $hook['name'], $hook['callback'], 10 );
			}
		}
	}
}
