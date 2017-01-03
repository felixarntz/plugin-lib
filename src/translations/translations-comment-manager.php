<?php
/**
 * Translations for the Comment_Manager class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Translations;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Translations\Translations_Comment_Manager' ) ) :

/**
 * Translations for the Comment_Manager class.
 *
 * @since 1.0.0
 */
class Translations_Comment_Manager extends Translations {
	/**
	 * Initializes the translation strings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function init() {
		$this->translations = array(
			'db_insert_error'            => $this->__translate( 'Could not insert comment into the database.', 'textdomain' ),
			'db_update_error'            => $this->__translate( 'Could not update comment in the database.', 'textdomain' ),
			'meta_delete_error'          => $this->__translate( 'Could not delete comment metadata for key %s.', 'textdomain' ),
			'meta_update_error'          => $this->__translate( 'Could not update comment metadata for key %s.', 'textdomain' ),
			'db_fetch_error_missing_id'  => $this->__translate( 'Could not fetch comment from the database because it is missing an ID.', 'textdomain' ),
			'db_fetch_error'             => $this->__translate( 'Could not fetch comment from the database.', 'textdomain' ),
			'db_delete_error_missing_id' => $this->__translate( 'Could not delete comment from the database because it is missing an ID.', 'textdomain' ),
			'db_delete_error'            => $this->__translate( 'Could not delete comment from the database.', 'textdomain' ),
			'meta_delete_all_error'      => $this->__translate( 'Could not delete the comment metadata. The comment itself was deleted successfully though.', 'textdomain' ),
		);
	}
}

endif;
