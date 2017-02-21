<?php
/**
 * Translations service trait
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Traits;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\Traits\Translations_Service_Trait' ) ) :

/**
 * Trait for services that need to use translatable strings.
 *
 * @since 1.0.0
 */
trait Translations_Service_Trait {
	/**
	 * Translations to print to the user.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Translations\Translations
	 */
	protected $translations;

	/**
	 * Returns a translated message for a specific identifier.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $identifier Translated message identifier.
	 * @param bool   $noop       Optional. Whether this is a noop message. Default false.
	 * @return string|array Translated message, or array if $noop, or empty string if
	 *                      invalid identifier.
	 */
	protected function get_translation( $identifier, $noop = false ) {
		return $this->translations->get( $identifier, $noop );
	}

	/**
	 * Sets the translations instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Leaves_And_Love\Plugin_Lib\Translations\Translations $translations Translations instance.
	 */
	protected function set_translations( $translations ) {
		$this->translations = $translations;
	}
}

endif;
