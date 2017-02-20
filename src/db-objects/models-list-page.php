<?php
/**
 * List page class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

use Leaves_And_Love\Plugin_Lib\Components\Admin_Page;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models_List_Page' ) ) :

/**
 * Class for a models list page.
 *
 * @since 1.0.0
 */
abstract class Models_List_Page extends Admin_Page {
	/**
	 * The manager instance for the models.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\DB_Objects\Manager
	 */
	protected $model_manager;

	/**
	 * The list table.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\DB_Objects\Models_List_Table
	 */
	protected $list_table;

	/**
	 * The list table class name.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $list_table_class_name = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models_List_Table';

	/**
	 * The slug of the admin page to create or edit a model.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $edit_page_slug = '';

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
		parent::__construct( $slug, $manager );

		$this->model_manager = $model_manager;

		if ( empty( $this->title ) ) {
			$this->title = $this->model_manager->get_message( 'list_page_items' );
		}

		if ( empty( $this->menu_title ) ) {
			$this->menu_title = $this->model_manager->get_message( 'list_page_items' );
		}

		if ( empty( $this->capability ) ) {
			$capabilities = $this->model_manager->capabilities();
			if ( $capabilities ) {
				$base_capabilities = $capabilities->get_capabilities( 'base' );

				$this->capability = $base_capabilities['edit_items'];
			}
		}

		if ( empty( $this->edit_page_slug ) ) {
			$this->edit_page_slug = $this->manager->get_prefix() . 'edit_' . $this->model_manager->get_singular_slug();
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
		if ( ! $capabilities || ! $capabilities->user_can_edit() ) {
			wp_die( $this->model_manager->get_message( 'list_page_cannot_edit' ), 403 );
		}

		$this->setup_list_table();
		$this->handle_bulk_actions();
		$this->clean_referer();
		$this->prepare_list_table();
		$this->setup_screen();
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
	 * Renders the list page content.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function render() {
		?>
		<div class="wrap">
			<?php $this->render_header(); ?>

			<?php $this->render_form(); ?>
		</div>
		<?php
	}

	/**
	 * Renders the list page header.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render_header() {
		$capabilities = $this->model_manager->capabilities();

		$edit_page_url = '';
		if ( ! empty( $this->edit_page_slug ) ) {
			$edit_page_url = add_query_arg( 'page', $this->edit_page_slug, $this->url );
		}

		?>
		<h1 class="wp-heading-inline">
			<?php echo $this->title; ?>
		</h1>

		<?php if ( ! empty( $edit_page_url ) && $capabilities && $capabilities->user_can_create() ) : ?>
			<a href="<?php echo esc_url( $edit_page_url ); ?>" class="page-title-action"><?php echo $this->model_manager->get_message( 'list_page_add_new' ); ?></a>
		<?php endif; ?>

		<?php if ( isset( $_REQUEST['s'] ) && strlen( $_REQUEST['s'] ) ) : ?>
			<span class="subtitle"><?php printf( $this->model_manager->get_message( 'list_page_search_results_for' ), esc_attr( wp_unslash( $_REQUEST['s'] ) ) ); ?></span>
		<?php endif; ?>

		<hr class="wp-header-end">

		<?php

		if ( isset( $_REQUEST['bulk_action_result'] ) ) {
			$transient_name = $this->model_manager->get_prefix() . $this->model_manager->get_plural_slug() . '_bulk_action_result';
			$message = get_transient( $transient_name );
			if ( false !== $message ) {
				delete_transient( $transient_name );

				$class = 'true' === $_REQUEST['bulk_action_result'] ? 'notice-success' : 'notice-error';

				echo '<div id="message" class="notice ' . $class . ' is-dismissible">' . wpautop( $message ) . '</div>';
			}

			$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'bulk_action_result' ), $_SERVER['REQUEST_URI'] );
		}
	}

	/**
	 * Renders the list page form.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render_form() {
		$this->list_table->views();

		?>
		<form id="<?php echo $this->model_manager->get_plural_slug(); ?>-filter" method="get">

			<?php $this->list_table->search_box( $this->model_manager->get_message( 'list_page_search_items' ), $this->model_manager->get_singular_slug() ); ?>

			<input type="hidden" name="page" value="<?php echo $this->slug; ?>" />

			<?php if ( method_exists( $this->model_manager, 'get_author_property' ) && ( $author_property = $this->model_manager->get_author_property() ) && ! empty( $_REQUEST[ $author_property ] ) ) : ?>
				<input type="hidden" name="<?php echo $author_property; ?>" value="<?php echo esc_attr( $_REQUEST[ $author_property ] ); ?>" />
			<?php endif; ?>

			<?php $this->list_table->display(); ?>
		</form>
		<?php
	}

	/**
	 * Sets up the list table instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function setup_list_table() {
		$class_name = $this->list_table_class_name;

		$edit_page_url = '';
		if ( ! empty( $this->edit_page_slug ) ) {
			$edit_page_url = add_query_arg( 'page', $this->edit_page_slug, $this->url );
		}

		$this->list_table = new $class_name( $this->model_manager, array(
			'screen'      => $this->hook_suffix,
			'models_page' => $this->url,
			'model_page'  => $edit_page_url,
		) );
	}

	/**
	 * Handles bulk actions when necessary.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function handle_bulk_actions() {
		$doaction = $this->list_table->current_action();

		if ( ! $doaction ) {
			return;
		}

		$prefix      = $this->model_manager->get_prefix();
		$plural_slug = $this->model_manager->get_plural_slug();

		check_admin_referer( 'bulk-' . $prefix . $plural_slug );

		$sendback = wp_get_referer();
		if ( ! $sendback ) {
			$sendback = $this->url;
		}

		$sendback = add_query_arg( 'paged', $this->list_table->get_pagenum(), $sendback );

		$ids = array();
		if ( isset( $_REQUEST[ $plural_slug ] ) ) {
			$ids = array_map( 'absint', $_REQUEST[ $plural_slug ] );
		}

		if ( empty( $ids ) ) {
			wp_redirect( $sendback );
			exit;
		}

		$message = '';
		if ( method_exists( $this, 'bulk_action_' . $doaction ) ) {
			$message = call_user_func( array( $this, 'bulk_action_' . $doaction ), $ids );
		} else {
			/**
			 * Fires when a custom bulk action should be handled.
			 *
			 * The hook callback should return a success message or an error object which
			 * will then be used to display feedback to the user.
			 *
			 * The dynamic parts of the hook name refer to the manager's prefix, its plural slug
			 * and the slug of the action to handle respectively.
			 *
			 * @since 1.0.0
			 *
			 * @param string                                        $message Empty message to be modified.
			 * @param array                                         $ids     Array of model IDs.
			 * @param Leaves_And_Love\Plugin_Lib\DB_Objects\Manager $manager The manager instance.
			 */
			$message = apply_filters( "{$prefix}{$plural_slug}_handle_bulk_action_{$doaction}", $message, $ids, $this->model_manager );
		}

		$sendback = remove_query_arg( array( 'action', 'action2', $plural_slug ), $sendback );

		if ( $message ) {
			$result = 'true';
			if ( is_wp_error( $message ) ) {
				$result = 'false';
				$message = $message->get_error_message();
			}

			$transient_name = $prefix . $plural_slug . '_bulk_action_result';

			set_transient( $transient_name, $message, 30 );

			$sendback = add_query_arg( 'bulk_action_result', $result, $sendback );
		}

		wp_redirect( $sendback );
		exit;
	}

	/**
	 * Redirects to a clean URL if the referer is part of the current URL.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function clean_referer() {
		if ( empty( $_REQUEST['_wp_http_referer'] ) ) {
			return;
		}

		wp_redirect( remove_query_arg( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );
		exit;
	}

	/**
	 * Prepares the models in the list table.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function prepare_list_table() {
		$this->list_table->prepare_items();
	}

	/**
	 * Sets up the screen with screen reader content, options and help tabs.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function setup_screen() {
		$screen = get_current_screen();

		$screen->set_screen_reader_content( array(
			'heading_views'      => $this->model_manager->get_message( 'list_page_filter_items_list' ),
			'heading_pagination' => $this->model_manager->get_message( 'list_page_items_list_navigation' ),
			'heading_list'       => $this->model_manager->get_message( 'list_page_items_list' ),
		) );

		add_screen_option( 'per_page', array(
			'default' => 20,
			'option'  => 'list_' . $this->model_manager->get_prefix() . $this->model_manager->get_plural_slug() . '_per_page',
		) );
	}
}

endif;
