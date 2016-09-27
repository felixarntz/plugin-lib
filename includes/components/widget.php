<?php
/**
 * Widget class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Components;

use WP_Widget;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Components\Widget' ) ) :

/**
 * Class for a widget
 *
 * This class represents a widget.
 *
 * @since 1.0.0
 */
final class Widget extends WP_Widget {
	/**
	 * Widget slug.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string
	 */
	private $slug;

	/**
	 * Hook to run for the widget.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var callable
	 */
	private $func;

	/**
	 * Additional arguments for this widget.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var array
	 */
	private $args;

	/**
	 * Widget manager instance.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var Leaves_And_Love\Plugin_Lib\Components\Widgets
	 */
	private $manager;

	/**
	 * Whether assets for this widget have been enqueued.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var bool
	 */
	private $enqueued = false;

	/**
	 * Constructor.
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
	 * @param Leaves_And_Love\Plugin_Lib\Components\Widgets $manager The widget manager instance.
	 */
	public function __construct( $slug, $func, $args, $manager ) {
		$this->slug = $slug;
		$this->func = $func;
		$this->manager = $manager;

		$args = wp_parse_args( $args, array(
			'enqueue_callback' => null,
			'defaults'         => false,
			'cache'            => false,
			'cache_expiration' => DAY_IN_SECONDS,
		) );

		if ( ! empty( $args['name'] ) ) {
			$name = $args['name'];
			unset( $args['name'] );
		} else {
			$name = ucwords( str_replace( '_', ' ', $this->slug ) );
		}

		$widget_options = array(
			'customize_selective_refresh' => true,
		);
		if ( ! empty( $args['description'] ) ) {
			$widget_options['description'] = $args['description'];
			unset( $args['description'] );
		}

		$this->args = $args;

		parent::__construct( $this->slug, $name, $widget_options );
	}

	/**
	 * Runs the widget hook for given instance settings and display arguments.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 * @return string The rendered shortcode output.
	 */
	public function widget( $args, $instance ) {
		$cache_key = false;

		if ( $this->args['cache'] ) {
			$cache_key = $this->get_cache_key( $instance, $args );

			if ( false !== ( $cached = $this->manager->cache()->get( $cache_key, 'widgets' ) ) ) {
				return $cached;
			}
		}

		if ( is_array( $this->args['defaults'] ) ) {
			$instance = wp_parse_args( $instance, $this->args['defaults'] );
		}

		$output = call_user_func( $this->func, $instance, $args, $this->slug, $this->manager->template() );

		if ( $cache_key ) {
			$this->manager->cache()->set( $cache_key, $output, 'widgets', absint( $this->args['cache_expiration'] ) );
		}

		echo $output;
	}

	/**
	 * Updates a particular instance of a widget.
	 *
	 * The Fields API is used to handle this functionality.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {
		return apply_filters( 'lal_update_widget_settings_' . $this->slug, $new_instance, $old_instance );
	}

	/**
	 * Outputs the settings update form.
	 *
	 * The Fields API is used to handle this functionality.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		do_action( 'lal_render_widget_settings_' . $this->slug, $instance );
	}

	/**
	 * Runs the enqueue callback if one exists.
	 *
	 * This method will ensure that the callback will only be called once per script lifetime.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue_assets() {
		if ( $this->enqueued ) {
			return;
		}

		$this->enqueued = true;

		if ( ! $this->has_enqueue_callback() ) {
			return;
		}

		call_user_func( $this->args['enqueue_callback'] );
	}

	/**
	 * Checks whether an enqueue callback exists for this shortcode.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool True if an enqueue callback exists, otherwise false.
	 */
	public function has_enqueue_callback() {
		return null !== $this->args['enqueue_callback'] && is_callable( $this->args['enqueue_callback'] );
	}

	/**
	 * Creates a cache key from given attributes and content input.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $instance The settings for the particular instance of the widget.
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @return string The cache key created from the input.
	 */
	private function get_cache_key( $instance, $args ) {
		$instance['__args'] = $args;

		return $this->slug . ':' . md5( serialize( $instance ) );
	}
}

endif;
