<?php
/**
 * Widgets abstraction class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Components;

use Leaves_And_Love\Plugin_Lib\Service;
use Leaves_And_Love\Plugin_Lib\Traits\Actions;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Components\Widgets' ) ) :

/**
 * Class for Widgets API
 *
 * The class is a wrapper for the WordPress Widgets API.
 *
 * @since 1.0.0
 *
 * @method string                              get_prefix()
 * @method Leaves_And_Love\Plugin_Lib\Cache    cache()
 * @method Leaves_And_Love\Plugin_Lib\Template template()
 */
class Widgets extends Service {
	use Actions;

	/**
	 * Cache instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Cache
	 */
	protected $cache;

	/**
	 * Template instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Template
	 */
	protected $template;

	/**
	 * Registered widgets.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $widgets = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string                              $prefix   The prefix for all widgets.
	 * @param array                               $messages Messages to print to the user.
	 * @param Leaves_And_Love\Plugin_Lib\Cache    $cache    The Cache API instance.
	 * @param Leaves_And_Love\Plugin_Lib\Template $template The Template API instance.
	 */
	public function __construct( $prefix, $messages, $cache, $template ) {
		$this->prefix = $prefix;
		$this->cache = $cache;
		$this->template = $template;

		//TODO: These are the required messages. They belong to the plugin.
		$messages = array(
			'widget'            => __( 'Widget', 'content-organizer' ),
			'title_label'       => __( 'Title', 'content-organizer' ),
			'title_description' => __( 'The title will be shown above the widget.', 'content-organizer' ),
		);

		$this->set_messages( $messages );
		$this->set_services( array( 'cache', 'template' ) );
	}

	/**
	 * Registers a widget.
	 *
	 * Other than regular WordPress widgets, these widgets work similar like shortcodes. They simply consist
	 * of a callback that will receive the widget instance settings, display arguments, the widget slug and
	 * the Template API instance. The latter allows the widget to use it for rendering.
	 *
	 * The widget slug will automatically be prefixed with the plugin-wide prefix.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string                                           $slug    Unique widget slug.
	 * @param callable                                         $func    Hook to run for the widget.
	 * @param array|string                                     $args    {
	 *     Array or string of additional widget arguments.
	 *
	 *     @type string   $name             Name for the widget displayed on the configuration page. Default is
	 *                                      created from the slug.
	 *     @type string   $description      Description for the widget displayed on the configuration page. Default
	 *                                      empty.
	 *     @type callable $enqueue_callback Function to enqueue scripts and stylesheets this widget requires.
	 *                                      Default null.
	 *     @type array    $defaults         Array of default attribute values. If passed, the widget instance settings
	 *                                      will be parsed with these before executing the callback hook so that
	 *                                      you do not need to take care of that in the widget hook. Default
	 *                                      false.
	 *     @type bool     $cache            Whether to cache the output of this widget. Default false.
	 *     @type int      $cache_expiration Time in seconds for which the widget should be cached. This only
	 *                                      takes effect if $cache is true. Default is 86400 (one day).
	 * }
	 * @return bool True on success, false on failure.
	 */
	public function add( $slug, $func, $args = array() ) {
		if ( empty( $slug ) ) {
			return false;
		}

		$slug = $this->prefix . $slug;

		$this->widgets[ $slug ] = new Widget( $slug, $func, $args, $this );
		register_widget( $this->widgets[ $slug ] );

		return true;
	}

	/**
	 * Checks whether a specific widget is registered.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Widget slug to check for.
	 * @return bool True if the widget is registered, otherwise false.
	 */
	public function has( $slug ) {
		$slug = $this->prefix . $slug;

		return isset( $this->widgets[ $slug ] );
	}

	/**
	 * Retrieves a widget object.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Widget slug to retrieve object for.
	 * @return Leaves_And_Love\Plugin_Lib\Components\Widget|null Widget object, or null if not exists.
	 */
	public function get( $slug ) {
		if ( ! $this->has( $slug ) ) {
			return null;
		}

		$slug = $this->prefix . $slug;

		return $this->widgets[ $slug ];
	}

	/**
	 * Unregisters a widget.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Widget slug of the widget to remove.
	 * @return bool True on success, false on failure.
	 */
	public function remove( $slug ) {
		$slug = $this->prefix . $slug;

		if ( ! isset( $this->widgets[ $slug ] ) ) {
			return false;
		}

		unregister_widget( $this->widgets[ $slug ] );
		unset( $this->widgets[ $slug ] );

		return true;
	}

	/**
	 * Registers the Fields API forms for all registered widgets.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_widget_forms() {
		if ( ! isset( $GLOBALS['wp_fields'] ) ) {
			return;
		}

		foreach ( $this->widgets as $slug => $widget ) {
			$form = new \Leaves_And_Love\Plugin_Lib\Util\Form_Widget( 'widget', 'widget-form-' . $slug, array(
				'object_subtype' => $slug,
			), $widget, $this->messages );

			$GLOBALS['wp_fields']->add_form( $form->object_type, $form, $form->object_subtype );

			$form->register_fields( $GLOBALS['wp_fields'] );
			$form->add_hooks();
		}
	}

	/**
	 * Adds widget hooks.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function add_hooks() {
		$this->add_action( 'fields_register', array( $this, 'register_widget_forms' ), 10, 0 );
	}
}

endif;
