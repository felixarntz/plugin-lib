<?php
/**
 * Admin page class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Components;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Components\Admin_Page' ) ) :

/**
 * Class for an admin page
 *
 * This class represents a menu page in the admin.
 *
 * @since 1.0.0
 *
 * @property string      $administration_panel Administration panel the page belongs to.
 * @property string|null $parent_slug          Parent page slug.
 * @property int         $position             Page position index.
 * @property string      $hook_suffix          Page hook suffix.
 *
 * @property-read string $slug       Page slug.
 * @property-read string $title      Page title.
 * @property-read string $capability Required capability to access the page.
 * @property-read string $icon_url   Icon URL for the page.
 */
abstract class Admin_Page {
	/**
	 * Page slug.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $slug = '';

	/**
	 * Page title.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $title = '';

	/**
	 * Required capability to access the page.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $capability = '';

	/**
	 * Icon URL for the page.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $icon_url = '';

	/**
	 * Administration panel the page belongs to.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $administration_panel = 'site';

	/**
	 * Parent page slug.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string|null
	 */
	protected $parent_slug = null;

	/**
	 * Page position index.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int
	 */
	protected $position = null;

	/**
	 * Page hook suffix.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $hook_suffix = '';

	/**
	 * Parent manager for admin pages.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Components\Admin_Pages
	 */
	protected $manager = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string                                            $slug    Page slug.
	 * @param Leaves_And_Love\Plugin_Lib\Components\Admin_Pages $manager Admin page manager instance.
	 */
	public function __construct( $slug, $manager ) {
		$this->slug = $slug;
		$this->manager = $manager;
	}

	/**
	 * Handles a request to the page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function handle_request() {
		// Empty method body.
	}

	/**
	 * Enqueues assets to load on the page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue_assets() {
		// Empty method body.
	}

	/**
	 * Renders the page content.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public abstract function render();

	/**
	 * Magic isset-er.
	 *
	 * Checks whether a property is set.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $property Property to check for.
	 * @return bool True if the property is set, false otherwise.
	 */
	public function __isset( $property ) {
		return in_array( $property, $this->get_read_properties(), true );
	}

	/**
	 * Magic getter.
	 *
	 * Returns a property value.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $property Property to get.
	 * @return mixed Property value, or null if property is not set.
	 */
	public function __get( $property ) {
		if ( ! in_array( $property, $this->get_read_properties(), true ) ) {
			return null;
		}

		return $this->$property;
	}

	/**
	 * Magic setter.
	 *
	 * Sets a property value.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $property Property to set.
	 * @param mixed  $value    Property value.
	 */
	public function __set( $property, $value ) {
		if ( ! in_array( $property, $this->get_write_properties(), true ) ) {
			return;
		}

		$this->$property = $value;
	}

	/**
	 * Gets the names of properties with read-access.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Array of property names.
	 */
	protected function get_read_properties() {
		return array( 'slug', 'title', 'capability', 'icon_url', 'administration_panel', 'parent_slug', 'hook_suffix' );
	}

	/**
	 * Gets the names of properties with write-access.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Array of property names.
	 */
	protected function get_write_properties() {
		return array( 'administration_panel', 'parent_slug', 'hook_suffix' );
	}
}

endif;
