<?php
/**
 * Translations abstraction trait
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Traits;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\Traits\Translations_Trait' ) ) :

/**
 * Trait for library classes that need to use translatable strings.
 *
 * @since 1.0.0
 */
trait Translations_Trait {
	/**
	 * Translations to print to the user.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $translations = array();

	/**
	 * Returns a translated message for a specific identifier.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $identifier Translated message identifier.
	 * @return string Translated message or empty string if invalid identifier.
	 */
	protected function get_translation( $identifier ) {
		return $this->translations->get( $identifier );
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
