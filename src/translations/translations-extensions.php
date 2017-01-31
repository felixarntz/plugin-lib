<?php
/**
 * Translations for the extensions class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Translations;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Translations\Translations_Extensions' ) ) :

/**
 * Translations for the extensions class.
 *
 * @since 1.0.0
 */
class Translations_Extensions extends Translations {
	/**
	 * Initializes the translation strings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function init() {
		$this->translations = array(
			'extension_class_not_exist'    => $this->__translate( 'The extension class %s does not exist.', 'textdomain' ),
			'extension_class_invalid'      => $this->__translate( 'The extension class %1$s is invalid, as it does not inherit the %2$s class.', 'textdomain' ),
			'extension_already_registered' => $this->__translate( 'An extension with the name %s is already registered.', 'textdomain' ),
		);
	}
}

endif;
