<?php
/**
 * Translations for the Error_Handler class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Translations;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Translations\Translations_Error_Handler' ) ) :

/**
 * Translations for the Error_Handler class.
 *
 * @since 1.0.0
 */
class Translations_Error_Handler extends Translations {
	/**
	 * Initializes the translation strings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function init() {
		$this->translations = array(
			'called_incorrectly'         => $this->__translate( '%1$s was called <strong>incorrectly</strong>. %2$s', 'textdomain' ),
			'added_in_version'           => $this->__translate( '(This message was added in Plugin Name version %s.)', 'textdomain' ),
			'deprecated_function'        => $this->__translate( '%1$s is <strong>deprecated</strong> since Plugin Name version %2$s. Use %3$s instead.', 'textdomain' ),
			'deprecated_function_no_alt' => $this->__translate( '%1$s is <strong>deprecated</strong> since Plugin Name version %2$s with no alternative available.', 'textdomain' ),
			'deprecated_argument'        => $this->__translate( '%1$s was called with an argument that is <strong>deprecated</strong> since Plugin Name version %2$s. %3$s', 'textdomain' ),
			'deprecated_argument_no_alt' => $this->__translate( '%1$s was called with an argument that is <strong>deprecated</strong> since Plugin Name version %2$s with no alternative available.', 'textdomain' ),
			'deprecated_hook'            => $this->__translate( '%1$s is <strong>deprecated</strong> since Plugin Name version %2$s. Use %3$s instead.', 'textdomain' ),
			'deprecated_hook_no_alt'     => $this->__translate( '%1$s is <strong>deprecated</strong> since Plugin Name version %2$s with no alternative available.', 'textdomain' ),
		);
	}
}

endif;
