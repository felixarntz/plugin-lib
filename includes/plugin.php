<?php
/**
 * Plugin main file
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

if ( ! class_exists( 'Leaves_And_Love_Plugin' ) ) :

/**
 * Main plugin class.
 *
 * Takes care of initializing the plugin.
 *
 * @since 1.0.0
 */
abstract class Leaves_And_Love_Plugin {
	/**
	 * The plugin version.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $version;

	/**
	 * The plugin prefix.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $prefix;

	/**
	 * The plugin vendor name.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $vendor_name;

	/**
	 * The plugin project name.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $project_name;

	/**
	 * The minimum required PHP version.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $minimum_php;

	/**
	 * The minimum required WordPress version.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $minimum_wp;

	/**
	 * Path to the plugin's main file.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $main_file;

	/**
	 * Relative base path to the other files of this plugin.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $basedir_relative;

	/**
	 * Messages printed to the user.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $messages = array();

	/**
	 * Error object if the class cannot be initialized.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var WP_Error|null
	 */
	protected $error;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $main file        Path to the plugin's main file.
	 * @param string $basedir_relative The relative base path to the other files of this plugin.
	 *
	 * @codeCoverageIgnore
	 */
	public function __construct( $main_file, $basedir_relative = '' ) {
		$this->main_file = $main_file;
		$this->basedir_relative = $basedir_relative;
		$this->minimum_php = '5.4';
		$this->minimum_wp = '4.5';

		$this->load_base_properties();
		$this->load_textdomain();
		$this->load_messages();

		$this->error = $this->check();
	}

	/**
	 * Dummy magic method to prevent Content Organizer from being cloned.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @codeCoverageIgnore
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, $this->messages['cheatin_huh'], '1.0.0' );
	}

	/**
	 * Dummy magic method to prevent Content Organizer from being unserialized.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @codeCoverageIgnore
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, $this->messages['cheatin_huh'], '1.0.0' );
	}

	/**
	 * Returns class instances for the plugin.
	 *
	 * This magic method allows you to call methods with the name of a class property, which will
	 * then return the respective instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $method_name Name of the method to call.
	 * @param array  $args        Method arguments.
	 * @return object|null Either the class instance denoted by the method name, or null if it doesn't exist.
	 */
	public function __call( $method_name, $args ) {
		if ( in_array( $method_name, array(
			'get_activation_hook',
			'get_deactivation_hook',
			'get_uninstall_hook',
		), true ) ) {
			return false;
		}

		if ( isset( $this->$method_name ) && is_object( $this->$method_name ) ) {
			return $this->$method_name;
		}

		return null;
	}

	/**
	 * Returns the full path to a relative path for a plugin file or directory.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $rel_path Relative path.
	 * @return string Full path.
	 */
	public function path( $rel_path ) {
		return plugin_dir_path( $this->main_file ) . $this->basedir_relative . ltrim( $rel_path, '/' );
	}

	/**
	 * Returns the full URL to a relative path for a plugin file or directory.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $rel_path Relative path.
	 * @return string Full URL.
	 */
	public function url( $rel_path ) {
		return plugin_dir_url( $this->main_file ) . $this->basedir_relative . ltrim( $rel_path, '/' );
	}

	/**
	 * Loads the plugin by registering the autoloader and instantiating the general classes.
	 *
	 * This method can only be executed once.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function load() {
		if ( did_action( $this->prefix . 'loaded' ) ) {
			return;
		}

		spl_autoload_register( array( $this, 'autoload' ) );

		$this->instantiate_classes();

		/**
		 * Fires after the plugin has loaded.
		 *
		 * @since 1.0.0
		 *
		 * @param Leaves_And_Love_Plugin $plugin The plugin instance.
		 */
		do_action( $this->prefix . 'loaded', $this );
	}

	/**
	 * Starts the plugin by adding the necessary hooks.
	 *
	 * This method can only be executed once.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function start() {
		if ( did_action( $this->prefix . 'started' ) ) {
			return;
		}

		$this->add_hooks();

		/**
		 * Fires after the plugin has started.
		 *
		 * @since 1.0.0
		 *
		 * @param Leaves_And_Love_Plugin $plugin The plugin instance.
		 */
		do_action( $this->prefix . 'started', $this );
	}

	/**
	 * Autoloader.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $class_name The class to load.
	 *
	 * @codeCoverageIgnore
	 */
	public function autoload( $class_name ) {
		$parts = explode( '\\', $class_name );

		$vendor = array_shift( $parts );
		if ( $this->vendor_name !== $vendor ) {
			return;
		}

		$project = array_shift( $parts );
		if ( $this->project_name !== $project ) {
			return;
		}

		$path = $this->path( 'includes/' . strtolower( str_replace( '_', '-', implode( '/', $parts ) ) ) . '.php' );
		if ( ! file_exists( $path ) ) {
			return;
		}

		require_once $path;
	}

	/**
	 * Checks whether the plugin can run on this setup.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return WP_Error|null Error object if the plugin cannot run on this setup, null otherwise.
	 */
	protected function check() {
		if ( version_compare( phpversion(), $this->minimum_php, '<' ) ) {
			return new WP_Error( $this->prefix . 'outdated_php', sprintf( $this->messages['outdated_php'], $this->minimum_php ) );
		}

		if ( version_compare( get_bloginfo( 'version' ), $this->minimum_wp, '<' ) ) {
			return new WP_Error( $this->prefix . 'outdated_wordpress', sprintf( $this->messages['outdated_wp'], $this->minimum_wp ) );
		}

		return null;
	}

	/**
	 * Instantiates a specific plugin class.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $class_name The class name, without basic namespace.
	 * @param mixed  $args,...   Optional arguments to pass to the constructor.
	 *
	 * @return mixed The plugin class instance.
	 */
	protected function instantiate_plugin_class( $class_name ) {
		$class_name = $this->vendor_name . '\\' . $this->project_name . '\\' . $class_name;

		if ( func_num_args() === 1 ) {
			return new $class_name();
		}

		$args = array_slice( func_get_args(), 1 );

		$generator = new ReflectionClass( $class_name );
		return $generator->newInstanceArgs( $args );
	}

	/**
	 * Instantiates a specific library class.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $class_name The class name, without basic namespace.
	 * @param mixed  $args,...   Optional arguments to pass to the constructor.
	 *
	 * @return mixed The library class instance.
	 */
	protected function instantiate_library_class( $class_name ) {
		$class_name = 'Leaves_And_Love\\Plugin_Lib\\' . $class_name;

		if ( func_num_args() === 1 ) {
			return new $class_name();
		}

		$args = array_slice( func_get_args(), 1 );

		$generator = new ReflectionClass( $class_name );
		return $generator->newInstanceArgs( $args );
	}

	/**
	 * Loads the base properties of the class.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected abstract function load_base_properties();

	/**
	 * Loads the plugin's textdomain.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected abstract function load_textdomain();

	/**
	 * Loads the class messages.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected abstract function load_messages();

	/**
	 * Instantiates the general plugin classes.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected abstract function instantiate_classes();

	/**
	 * Adds the necessary plugin hooks.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected abstract function add_hooks();
}

endif;
