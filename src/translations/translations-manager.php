<?php
/**
 * Translations for the Manager class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Translations;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Translations\Translations_Manager' ) ) :

/**
 * Translations for the Manager class.
 *
 * @since 1.0.0
 */
class Translations_Manager extends Translations {
	/**
	 * Initializes the translation strings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function init() {
		$this->translations = array(
			'db_insert_error'                        => $this->__translate( 'Could not insert model into the database.', 'textdomain' ),
			'db_update_error'                        => $this->__translate( 'Could not update model in the database.', 'textdomain' ),
			'meta_delete_error'                      => $this->__translate( 'Could not delete model metadata for key %s.', 'textdomain' ),
			'meta_update_error'                      => $this->__translate( 'Could not update model metadata for key %s.', 'textdomain' ),
			'db_fetch_error_missing_id'              => $this->__translate( 'Could not fetch model from the database because it is missing an ID.', 'textdomain' ),
			'db_fetch_error'                         => $this->__translate( 'Could not fetch model from the database.', 'textdomain' ),
			'db_delete_error_missing_id'             => $this->__translate( 'Could not delete model from the database because it is missing an ID.', 'textdomain' ),
			'db_delete_error'                        => $this->__translate( 'Could not delete model from the database.', 'textdomain' ),
			'meta_delete_all_error'                  => $this->__translate( 'Could not delete the model metadata. The model itself was deleted successfully though.', 'textdomain' ),
			'list_page_items'                        => $this->__translate( 'Models', 'textdomain' ),
			'list_page_cannot_edit_items'            => $this->__translate( 'You are not allowed to edit models.', 'textdomain' ),
			'list_page_add_new'                      => $this->_xtranslate( 'Add New', 'models button', 'textdomain' ),
			'list_page_search_results_for'           => $this->_xtranslate( 'Search results for &#8220;%s&#8221;', 'models', 'textdomain' ),
			'list_page_search_items'                 => $this->__translate( 'Search models', 'textdomain' ),
			'list_page_filter_items_list'            => $this->__translate( 'Filter models list', 'textdomain' ),
			'list_page_items_list_navigation'        => $this->__translate( 'Models list navigation', 'textdomain' ),
			'list_page_items_list'                   => $this->__translate( 'Models list', 'textdomain' ),
			'bulk_action_cannot_delete_item'         => $this->__translate( 'You are not allowed to delete the model %s.', 'textdomain' ),
			'bulk_action_delete_item_internal_error' => $this->__translate( 'An internal error occurred while trying to delete the model %s.', 'textdomain' ),
			'bulk_action_delete_has_errors'          => $this->_n_nooptranslate( '%s model could not be deleted as errors occurred:', '%s models could not be deleted as errors occurred:', 'textdomain' ),
			'bulk_action_delete_other_items_success' => $this->_n_nooptranslate( 'The other %s model was deleted successfully.', 'The other %s models were deleted successfully.', 'textdomain' ),
			'bulk_action_delete_success'             => $this->_n_nooptranslate( '%s model successfully deleted.', '%s models successfully deleted.', 'textdomain' ),
			'edit_page_item'                         => $this->__translate( 'Edit Model', 'textdomain' ),
			'edit_page_add_new'                      => $this->_xtranslate( 'Add New', 'model button', 'textdomain' ),
			'edit_page_invalid_id'                   => $this->__translate( 'Invalid model ID.', 'textdomain' ),
			'edit_page_cannot_edit_item'             => $this->__translate( 'Sorry, you are not allowed this model.', 'textdomain' ),
			'edit_page_cannot_create_item'           => $this->__translate( 'Sorry, you are not allowed to create a new model.', 'textdomain' ),
			'edit_page_title_label'                  => $this->__translate( 'Enter model title here', 'textdomain' ),
			'edit_page_title_placeholder'            => $this->__translate( 'Enter model title here', 'textdomain' ),
			'edit_page_create'                       => $this->_xtranslate( 'Create', 'model button', 'textdomain' ),
			'edit_page_update'                       => $this->_xtranslate( 'Update', 'model button', 'textdomain' ),
			'edit_page_delete'                       => $this->_xtranslate( 'Delete', 'model button', 'textdomain' ),
			'edit_page_submit_box_title'             => $this->_xtranslate( 'Publish', 'model submit box title', 'textdomain' ),
			'edit_page_status_label'                 => $this->_xtranslate( 'Status', 'model status label', 'textdomain' ),
			'action_edit_item_invalid_type'          => $this->__translate( 'The model type is invalid.', 'textdomain' ),
			'action_edit_item_invalid_status'        => $this->__translate( 'The model status is invalid.', 'textdomain' ),
			'action_edit_item_cannot_publish'        => $this->__translate( 'You are not allowed to publish this model.', 'textdomain' ),
			'action_edit_item_internal_error'        => $this->__translate( 'An internal error occurred while trying to save the model.', 'textdomain' ),
			'action_edit_item_has_errors'            => $this->__translate( 'Some errors occurred while trying to save the model:', 'textdomain' ),
			'action_edit_item_other_fields_success'  => $this->__translate( 'All other model data was saved successfully.', 'textdomain' ),
			'action_edit_item_success'               => $this->__translate( 'Model successfully saved.', 'textdomain' ),
			'action_delete_item_cannot_delete'       => $this->__translate( 'You are not allowed to delete the model %s.', 'textdomain' ),
			'action_delete_item_internal_error'      => $this->__translate( 'An internal error occurred while trying to delete the model %s.', 'textdomain' ),
			'action_delete_item_success'             => $this->__translate( 'Model %s successfully deleted.', 'textdomain' ),
		);
	}
}

endif;
