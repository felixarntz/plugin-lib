<?php

namespace Leaves_And_Love\Sample_Plugin;

use Leaves_And_Love\Plugin_Lib\Traits\Actions_Trait;

class Actions {
	use Actions_Trait;

	public function add( $tag, $mode = 'func' ) {
		return $this->add_action( $tag, $this->get_callback( $mode ) );
	}

	public function has( $tag, $mode = 'func' ) {
		return $this->has_action( $tag, $this->get_callback( $mode ) );
	}

	public function remove( $tag, $mode = 'func' ) {
		return $this->remove_action( $tag, $this->get_callback( $mode ) );
	}

	public function create_public_output() {
		echo 'public';
	}

	private function create_private_output() {
		echo 'private';
	}

	private function get_callback( $mode = 'func' ) {
		if ( 'public' === $mode ) {
			return array( $this, 'create_public_output' );
		}

		if ( 'private' === $mode ) {
			return array( $this, 'create_private_output' );
		}

		return 'sp_create_output';
	}
}
