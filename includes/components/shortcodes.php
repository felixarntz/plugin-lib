<?php
/**
 * Shortcodes abstraction class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Components;

use Leaves_And_Love\Plugin_Lib\Service;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Components\Shortcodes' ) ) :

/**
 * Class for Shortcodes API
 *
 * The class is a wrapper for the WordPress Shortcodes API.
 *
 * @since 1.0.0
 *
 * @method string                              get_prefix()
 * @method Leaves_And_Love\Plugin_Lib\Cache    cache()
 * @method Leaves_And_Love\Plugin_Lib\Template template()
 */
class Shortcodes extends Service {
	protected $cache;

	protected $template;

	protected $shortcode_tags = array();

	public function __construct( $prefix, $cache, $template ) {
		$this->prefix = $prefix;
		$this->cache = $cache;
		$this->template = $template;

		$this->set_services( array( 'cache', 'template' ) );
	}

	public function add( $tag, $func, $args = array() ) {
		if ( empty( $tag ) ) {
			return false;
		}

		$tag = $this->prefix . $tag;

		$this->shortcode_tags[ $tag ] = new Shortcode( $tag, $func, $args, $this );
		add_shortcode( $tag, array( $this->shortcode_tags[ $tag ], 'run' ) );

		return true;
	}

	public function has( $tag ) {
		$tag = $this->prefix . $tag;

		return isset( $this->shortcode_tags[ $tag ] ) && shortcode_exists( $tag );
	}

	public function get( $tag ) {
		if ( ! $this->has( $tag ) ) {
			return null;
		}

		$tag = $this->prefix . $tag;

		return $this->shortcode_tags[ $tag ];
	}

	public function remove( $tag ) {
		$tag = $this->prefix . $tag;

		if ( ! isset( $this->shortcode_tags[ $tag ] ) ) {
			return false;
		}

		remove_shortcode( $tag );
		unset( $this->shortcode_tags[ $tag ] );

		return true;
	}
}

endif;
