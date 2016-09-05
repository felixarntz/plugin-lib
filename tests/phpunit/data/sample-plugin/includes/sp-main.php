<?php

class SP_Main extends Leaves_And_Love_Plugin {
	protected $options;

	/**
	 * Loads the base properties of the class.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function load_base_properties() {
		$this->version = '1.0.0';
		$this->prefix = 'sp_';
		$this->vendor_name = 'Leaves_And_Love';
		$this->project_name = 'Sample_Plugin';
		$this->minimum_php = '5.4';
		$this->minimum_wp = '4.5';
	}

	protected function load_textdomain() {
		if ( version_compare( get_bloginfo( 'version' ), '4.6', '>=' ) ) {
			return;
		}

		load_plugin_textdomain( 'sample-plugin' );
	}

	protected function load_messages() {
		$this->messages['cheatin_huh']  = __( 'Cheatin&#8217; huh?', 'sample-plugin' );
		$this->messages['outdated_php'] = __( 'Sample Plugin cannot be initialized because your setup uses a PHP version older than %s.', 'sample-plugin' );
		$this->messages['outdated_wp']  = __( 'Sample Plugin cannot be initialized because your setup uses a WordPress version older than %s.', 'sample-plugin' );
	}

	protected function instantiate_classes() {
		$this->options = $this->instantiate_library_class( 'Options', $this->prefix );
	}

	protected function add_hooks() {
		add_filter( 'populate_network_meta', array( $this->options, 'migrate_to_network' ), 10, 1 );
	}
}
