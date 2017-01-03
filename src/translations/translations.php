<?php
/**
 * Translations class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Translations;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Translations\Translations' ) ) :

/**
 * Base class for a set of translations
 *
 * Translation strings cannot be bundled in a library, since WordPress
 * requires its plugins to handle those. The translations classes allow
 * to easily make the library strings translation-ready from inside a
 * specific plugin.
 *
 * @since 1.0.0
 */
abstract class Translations {
	/**
	 * All translation identifiers and their strings.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $translations = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Returns a string for a specific identifier.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $identifier Translation string identifier.
	 * @return string Translation string or empty string if invalid identifier.
	 */
	public function get( $identifier ) {
		if ( ! isset( $this->translations[ $identifier ] ) ) {
			return '';
		}

		return $this->translations[ $identifier ];
	}

	/**
	 * Returns all translation strings.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Array of all translation identifiers and their strings.
	 */
	public function get_all() {
		return $this->translations;
	}

	/**
	 * Initializes the translation strings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected abstract function init();

	/**
	 * Dummy method for __() translations.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $message    Untranslated string.
	 * @param string $textdomain Textdomain for the translation.
	 * @return string The unmodified string.
	 */
	protected function __translate( $message, $textdomain ) {
		return $message;
	}

	/**
	 * Dummy method for _n() translations.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $message    Untranslated string.
	 * @param string $textdomain Textdomain for the translation.
	 * @return string The unmodified string.
	 */
	protected function _ntranslate( $message, $textdomain ) {
		return $message;
	}

	/**
	 * Dummy method for _x() translations.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $message    Untranslated string.
	 * @param string $textdomain Textdomain for the translation.
	 * @return string The unmodified string.
	 */
	protected function _xtranslate( $message, $textdomain ) {
		return $message;
	}

	/**
	 * Dummy method for _nx() translations.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $message    Untranslated string.
	 * @param string $textdomain Textdomain for the translation.
	 * @return string The unmodified string.
	 */
	protected function _nxtranslate( $message, $textdomain ) {
		return $message;
	}

	/**
	 * Dummy method for _n_noop() translations.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $message    Untranslated string.
	 * @param string $textdomain Textdomain for the translation.
	 * @return string The unmodified string.
	 */
	protected function _n_nooptranslate( $message, $textdomain ) {
		return $message;
	}

	/**
	 * Dummy method for _nx_noop() translations.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $message    Untranslated string.
	 * @param string $textdomain Textdomain for the translation.
	 * @return string The unmodified string.
	 */
	protected function _nx_nooptranslate( $message, $textdomain ) {
		return $message;
	}
}

endif;
