<?php
/**
 * Edit page class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Edit_Page' ) ) :

/**
 * Class for a model edit page.
 *
 * @since 1.0.0
 */
abstract class Model_Edit_Page extends Manager_Page {
	/**
	 * The current model.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\DB_Objects\Model
	 */
	protected $model;

	/**
	 * Whether the page is currently in update scope.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var bool
	 */
	protected $is_update = false;

	/**
	 * The slug of the admin page to list models.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $list_page_slug = '';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string                                            $slug          Page slug.
	 * @param Leaves_And_Love\Plugin_Lib\Components\Admin_Pages $manager       Admin page manager instance.
	 * @param Leaves_And_Love\Plugin_Lib\DB_Objects\Manager     $model_manager Model manager instance.
	 */
	public function __construct( $slug, $manager, $model_manager ) {
		parent::__construct( $slug, $manager, $model_manager );

		if ( empty( $this->title ) ) {
			$this->title = $this->model_manager->get_message( 'edit_page_item' );
		}

		if ( empty( $this->menu_title ) ) {
			$this->menu_title = $this->model_manager->get_message( 'edit_page_add_new' );
		}

		if ( empty( $this->capability ) ) {
			$capabilities = $this->model_manager->capabilities();
			if ( $capabilities ) {
				$base_capabilities = $capabilities->get_capabilities( 'base' );

				$this->capability = $base_capabilities['create_items'];
			}
		}

		if ( empty( $this->list_page_slug ) ) {
			$this->list_page_slug = $this->manager->get_prefix() . 'list_' . $this->model_manager->get_plural_slug();
		}
	}

	/**
	 * Handles a request to the page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function handle_request() {
		$capabilities = $this->model_manager->capabilities();
		$primary_property = $this->model_manager->get_primary_property();

		if ( isset( $_REQUEST[ $primary_property ] ) ) {
			$id = absint( $_REQUEST[ $primary_property ] );

			$this->model = $this->model_manager->get( $id );
			if ( null === $this->model ) {
				wp_die( $this->model_manager->get_message( 'edit_page_invalid_id' ), 400 );
			}

			if ( ! $capabilities || ! $capabilities->user_can_edit( null, $id ) ) {
				wp_die( $this->model_manager->get_message( 'edit_page_cannot_edit_item' ), 403 );
			}

			$this->is_update = true;
		} else {
			if ( ! $capabilities || ! $capabilities->user_can_create() ) {
				wp_die( $this->model_manager->get_message( 'edit_page_cannot_create_item' ), 403 );
			}

			$this->model = $this->model_manager->create();

			if ( method_exists( $this->model_manager, 'get_type_property' ) ) {
				$type_property = $this->model_manager->get_type_property();
				$this->model->$type_property = $this->model_manager->types()->get_default();
			}

			if ( method_exists( $this->model_manager, 'get_status_property' ) ) {
				$status_property = $this->model_manager->get_status_property();
				$this->model->$status_property = $this->model_manager->statuses()->get_default();
			}
		}

		$this->handle_actions();
		$this->clean_referer();
		$this->setup_screen( get_current_screen() );
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
	 * Renders the edit page header.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render_header() {
		$capabilities = $this->model_manager->capabilities();

		$new_page_url = '';
		if ( $this->is_update ) {
			$new_page_url = $this->url;
		}

		?>
		<h1 class="wp-heading-inline">
			<?php echo $this->title; ?>
		</h1>

		<?php if ( ! empty( $new_page_url ) && $capabilities && $capabilities->user_can_create() ) : ?>
			<a href="<?php echo esc_url( $new_page_url ); ?>" class="page-title-action"><?php echo $this->model_manager->get_message( 'edit_page_add_new' ); ?></a>
		<?php endif; ?>

		<hr class="wp-header-end">

		<?php

		$this->print_current_message( 'action' );
	}

	/**
	 * Renders the edit page form.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render_form() {
		$id = null;
		if ( $this->is_update ) {
			$primary_property = $this->model_manager->get_primary_property();
			$id = $this->model->$primary_property;
		}

		?>
		<form id="post" action="<?php echo esc_url( $this->get_model_edit_url() ); ?>" method="post">
			<?php wp_nonce_field( $this->get_nonce_action( 'action', $id ) ); ?>
			<input type="hidden" name="action" value="edit" />

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
					<div id="post-body-content">
						<?php $this->render_form_content(); ?>
					</div>
					<div id="postbox-container-1" class="postbox-container">
						<?php $this->render_submit_box(); ?>
					</div>
					<div id="postbox-container-2" class="postbox-container">
						<?php $this->render_advanced_form_content(); ?>
					</div>
				</div>
				<br class="clear" />
			</div>
		</form>
		<?php
	}

	/**
	 * Renders the edit page main form content.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render_form_content() {
		if ( method_exists( $this->model_manager, 'get_title_property' ) ) {
			$title_property = $this->model_manager->get_title_property();

			?>
			<div id="titlediv">
				<div id="titlewrap">
					<label id="title-prompt-text" class="screen-reader-text" for="title"><?php echo $this->model_manager->get_message( 'edit_page_title_label' ); ?></label>
					<input type="text" id="title" name="post_title" value="<?php echo esc_attr( $this->model->$title_property ); ?>" placeholder="<?php echo esc_attr( $this->model_manager->get_message( 'edit_page_title_placeholder' ) ); ?>" size="30" />
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Renders the edit page advanced form content.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render_advanced_form_content() {
		// Empty method body.
	}

	/**
	 * Renders the edit page submit box.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render_submit_box() {
		$capabilities = $this->model_manager->capabilities();

		$id = null;
		$update_text = $this->model_manager->get_message( 'edit_page_create' );
		$delete_url = '';

		if ( $this->is_update ) {
			$primary_property = $this->model_manager->get_primary_property();
			$id = $this->model->$primary_property;

			$update_text = $this->model_manager->get_message( 'edit_page_update' );

			if ( $capabilities && $capabilities->user_can_delete( null, $id ) ) {
				$delete_url = add_query_arg( array(
					'action' => 'delete',
					'nonce'  => wp_create_nonce( $this->get_nonce_action( 'action', $id ) ),
				), $this->get_model_edit_url() );
			}
		}

		$statuses = array();
		$status_property = '';
		$current_status = '';
		if ( method_exists( $this->model_manager, 'get_status_property' ) ) {
			$status_property = $this->model_manager->get_status_property();
			$current_status = $this->model->$status_property;

			if ( $capabilities && $capabilities->user_can_publish( null, $id ) ) {
				$statuses = $this->model_manager->statuses()->query();
			} else {
				$statuses = $this->model_manager->statuses()->query( array(
					'public'   => false,
					'slug'     => $current_status,
					'operator' => 'OR',
				) );
			}
		}

		?>
		<div id="submitdiv" class="postbox">
			<h2 class="hndle">
				<span><?php echo $this->model_manager->get_message( 'edit_page_submit_box_title' ); ?></span>
			</h2>
			<div class="inside">
				<div id="submitpost" class="submitbox">
					<div id="minor-publishing">
						<div id="minor-publishing-actions">
							<!-- TODO -->
							<div class="clear"></div>
						</div>
						<div id="misc-publishing-actions">
							<?php if ( ! empty( $status_property ) && ! empty( $statuses ) ) : ?>
								<div id="post-status-select">
									<label for="post-status"><?php echo $this->model_manager->get_message( 'edit_page_status_label' ); ?></label>
									<select id="post-status" name="<?php echo esc_attr( $status_property ); ?>">
										<?php foreach ( $statuses as $status ) : ?>
											<option value="<?php echo esc_attr( $status->slug ); ?>"<?php selected( $current_status, $status->slug ); ?>><?php echo esc_html( $status->label ); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							<?php endif; ?>
						</div>
						<div class="clear"></div>
					</div>
					<div id="major-publishing-actions">
						<div id="delete-action">
							<?php if ( ! empty( $delete_url ) ) : ?>
								<a class="submitdelete deletion" href="<?php echo esc_url( $delete_url ); ?>"><?php echo $this->model_manager->get_message( 'edit_page_delete' ); ?></a>
							<?php endif; ?>
						</div>
						<div id="publishing-action">
							<?php submit_button( $update_text, 'primary large', 'publish', false ); ?>
						</div>
						<div class="clear"></div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Handles actions when necessary.
	 *
	 * These actions are usually row actions from the models list page.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function handle_actions() {
		if ( ! isset( $_REQUEST['action'] ) ) {
			return;
		}

		$doaction = wp_unslash( $_REQUEST['action'] );

		$primary_property = $this->model_manager->get_primary_property();
		$id = $this->model->$primary_property;
		if ( ! $id ) {
			$id = null;
		}

		$sendback = $this->get_referer();
		if ( false !== strpos( $sendback, $this->slug ) ) {
			$action_type = 'action';
		} else {
			$action_type = 'row_action';

			$sendback_query = parse_url( $sendback, PHP_URL_QUERY );
			if ( ! empty( $sendback_query ) ) {
				parse_str( $sendback_query, $sendback_query_args );
				if ( ! empty( $sendback_query_args ) && ! empty( $sendback_query_args['paged'] ) ) {
					$sendback = add_query_arg( 'paged', (int) $sendback_query_args['paged'], $sendback );
				}
			}
		}

		$message = '';

		if ( $id || ( 'action' === $action_type && 'edit' === $doaction ) ) {
			check_admin_referer( $action_type, $id );

			if ( method_exists( $this, $action_type . '_' . $doaction ) ) {
				$message = call_user_func( array( $this, $action_type . '_' . $doaction ), $id );
			} else {
				$prefix        = $this->model_manager->get_prefix();
				$singular_slug = $this->model_manager->get_singular_slug();

				/**
				 * Fires when a custom action should be handled.
				 *
				 * This is usually one of the row actions from the models list page.
				 *
				 * The hook callback should return a success message or an error object which
				 * will then be used to display feedback to the user.
				 *
				 * The dynamic parts of the hook name refer to the manager's prefix, its singular slug,
				 * one of the terms 'action' or 'row_action', and the slug of the action to handle respectively.
				 *
				 * @since 1.0.0
				 *
				 * @param string                                        $message Empty message to be modified.
				 * @param int                                           $id      Model ID.
				 * @param Leaves_And_Love\Plugin_Lib\DB_Objects\Manager $manager The manager instance.
				 */
				$message = apply_filters( "{$prefix}{$singular_slug}_handle_{$action_type}_{$doaction}", $message, $id, $this->model_manager );
			}
		}

		if ( 'action' === $action_type ) {
			$sendback = add_query_arg( $primary_property, $this->model->$primary_property, $sendback );
		}

		$sendback = remove_query_arg( array( 'action' ), $sendback );

		if ( $message ) {
			$sendback = $this->redirect_with_message( $sendback, $message, $action_type );
		}

		wp_redirect( $sendback );
		exit;
	}

	/**
	 * Sets up the screen with screen reader content, options and help tabs.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param WP_Screen Current screen.
	 */
	protected function setup_screen( $screen ) {
		add_screen_option( 'layout_columns', array(
			'max'     => 2,
			'default' => 2,
		) );
	}

	/**
	 * Returns the URL to edit the current model.
	 *
	 * This method basically returns the default admin page URL with the
	 * ID of the current model appended in the query string.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string Model edit URL.
	 */
	protected function get_model_edit_url() {
		if ( $this->is_update ) {
			$primary_property = $this->model_manager->get_primary_property();

			return add_query_arg( $primary_property, $this->model->$primary_property, $this->url );
		}

		return $this->url;
	}

	/**
	 * Handles the 'edit' action.
	 *
	 * This is the general action to update a model.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int $id ID of the model to update.
	 * @return string|WP_Error Feedback message, or error object on failure.
	 */
	protected function action_edit( $id ) {

	}
}

endif;
