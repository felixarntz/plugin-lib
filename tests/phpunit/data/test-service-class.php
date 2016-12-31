<?php

class Test_Service_Class extends Leaves_And_Love\Plugin_Lib\Service {
	public function __construct( $prefix, $services = array(), $messages = array() ) {
		$this->prefix = $prefix;

		foreach ( $services as $name => $instance ) {
			$this->$name = $instance;
		}
		$this->set_services( array_keys( $services ) );

		$this->set_messages( $messages );
	}

	public function add_service( $name, $instance ) {
		$this->$name = $instance;

		$this->add_services( array( $name ) );
	}

	public function get_message( $slug ) {
		if ( ! isset( $this->messages[ $slug ] ) ) {
			return '';
		}

		return $this->messages[ $slug ];
	}
}
