<?php

class SP_Main extends Leaves_And_Love_Plugin {
	protected $error_handler;
	protected $options;
	protected $template;
	protected $actions;
	protected $filters;

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
		$this->minimum_wp = '4.7';
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
		$this->error_handler = $this->instantiate_library_class( 'Error_Handler', $this->instantiate_library_class( 'Translations\Translations_Error_Handler' ) );
		$this->options       = $this->instantiate_library_class( 'Options', $this->prefix );
		$this->template      = $this->instantiate_library_class( 'Template', $this->prefix, array(
			'default_location' => $this->path( 'templates/' ),
		) );

		$this->actions = $this->instantiate_plugin_class( 'Actions' );
		$this->filters = $this->instantiate_plugin_class( 'Filters' );
	}

	protected function add_hooks() {

	}
}

function sp_create_output() {
	echo 'func';
}

function sp_get_string() {
	return 'func';
}
