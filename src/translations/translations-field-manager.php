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
			'field_empty_required'           => $this->__translate( 'No value was given for the required field &#8220;%s&#8221;.', 'textdomain' ),
			/* translators: %s: field label */
			'field_repeatable_not_array'     => $this->__translate( 'The value for the repeatable field &#8220;%s&#8221; is not an array.', 'textdomain' ),
			/* translators: %s: field label */
			'field_repeatable_has_errors'    => $this->__translate( 'One or more errors occurred for the repeatable field &#8220;%s&#8221;.', 'textdomain' ),
			/* translators: %s: field label */
			'field_repeatable_add_button'    => $this->__translate( 'Add<span class="screen-reader-text"> another item to the &#8220;%s&#8221; list</span>', 'textdomain' ),
			/* translators: %s: field label */
			'field_repeatable_remove_button' => $this->__translate( 'Remove<span class="screen-reader-text"> this item from the &#8220;%s&#8221; list</span>', 'textdomain' ),
		);
	}
}

endif;
