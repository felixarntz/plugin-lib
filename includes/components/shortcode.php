<?php
/**
 * Shortcode class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Components;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Components\Shortcode' ) ) :

/**
 * Class for a shortcode
 *
 * This class represents a shortcode.
 *
 * @since 1.0.0
 */
final class Shortcode {
	private $tag;

	private $func;

	private $args;

	private $manager;

	public function __construct( $tag, $func, $args, $manager ) {
		$this->tag = $tag;
		$this->func = $func;
		$this->args = wp_parse_args( $args, array(
			'enqueue_callback' => null,
			'cache'            => false,
			'cache_expiration' => DAY_IN_SECONDS,
		) );
		$this->manager = $manager;
	}

	public function run( $atts, $content ) {
		$cache_key = false;

		if ( $this->args['cache'] ) {
			$cache_key = $this->get_cache_key( $atts, $content );

			if ( false !== ( $cached = $this->manager->cache()->get( $cache_key, 'shortcodes' ) ) ) {
				return $cached;
			}
		}

		$output = call_user_func( $this->func, $atts, $content, $this->tag, $this->manager->template() );

		if ( $cache_key ) {
			$this->manager->cache()->set( $cache_key, $output, 'shortcodes', absint( $this->args['cache_expiration'] ) );
		}

		return $output;
	}

	public function enqueue_assets() {
		if ( ! $this->has_enqueue_callback() ) {
			return;
		}

		call_user_func( $this->args['enqueue_callback'] );
	}

	public function has_enqueue_callback() {
		return null !== $this->args['enqueue_callback'] && is_callable( $this->args['enqueue_callback'] );
	}

	private function get_cache_key( $atts, $content ) {
		if ( null !== $content ) {
			$atts['__content'] = $content;
		}

		return $this->tag . ':' . md5( serialize( $atts ) );
	}
}

endif;
