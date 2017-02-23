<?php
/**
 * Translations for the Field_Manager class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Translations;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Translations\Translations_Field_Manager' ) ) :

/**
 * Translations for the Field_Manager class.
 *
 * @since 1.0.0
 */
class Translations_Field_Manager extends Translations {
	/**
	 * Initializes the translation strings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function init() {
		$this->translations = array(
			/* translators: %s: field label */
			'field_empty_required' => $this->__translate( 'No value was given for the required field &#8220;%s&#8221;.', 'textdomain' ),
		);
	}
}

endif;
