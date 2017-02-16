<?php
/**
 * List table class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models_List_Table' ) ) :

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class to list models in a WordPress admin list table.
 *
 * @since 1.0.0
 */
abstract class Models_List_Table extends \WP_List_Table {
	/**
	 * The manager instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\DB_Objects\Manager
	 */
	protected $manager;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @see WP_List_Table::__construct() for more information on default arguments.
	 *
	 * @param Leaves_And_Love\Plugin_Lib\DB_Objects\Manager $manager The manager instance.
	 * @param array                                         $args    An associative array of arguments.
	 */
	public function __construct( $manager, $args = array() ) {
		$this->manager = $manager;

		$new_args = array(
			'singular' => $this->manager->get_prefix() . $this->manager->get_singular_slug(),
			'plural'   => $this->manager->get_prefix() . $this->manager->get_plural_slug(),
			'ajax'     => false,
			'screen'   => isset( $args['screen'] ) ? $args['screen'] : null,
		);

		parent::__construct( $new_args );

		if ( ! isset( $this->_args['models_page'] ) ) {
			$this->_args['models_page'] = add_query_arg( 'page', $_GET['page'], self_admin_url( $this->screen->parent_file ) );
		}

		if ( ! isset( $this->_args['model_page'] ) ) {
			if ( false !== strpos( $this->_args['models_page'], $this->manager->get_plural_slug() ) ) {
				$this->_args['model_page'] = str_replace( $this->manager->get_plural_slug(), $this->manager->get_singular_slug(), $this->_args['models_page'] );
			} else {
				$this->_args['model_page'] = '';
			}
		}
	}

	/**
	 * Checks the current user's permissions.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool Whether the current user can edit items.
	 */
	public function ajax_user_can() {
		$capabilities = $this->manager->capabilities();

		return ( $capabilities && $capabilities->user_can_edit() );
	}

	/**
	 * Prepares the list of items for displaying.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function prepare_items() {
		$capabilities = $this->manager->capabilities();

		$per_page = $this->get_items_per_page( 'list_table_' . $this->_args['plural'] . '_per_page' );

		$paged = $this->get_pagenum();

		$query_params = array(
			'number' => $per_page,
			'offset' => ( $paged - 1 ) * $per_page,
		);

		if ( method_exists( $this->manager, 'get_type_property' ) ) {
			$type_property = $this->manager->get_type_property();

			if ( isset( $_REQUEST[ $type_property ] ) ) {
				$query_params[ $type_property ] = $_REQUEST[ $type_property ];
			}
		}

		if ( method_exists( $this->manager, 'get_status_property' ) ) {
			$status_property = $this->manager->get_status_property();

			$internal_statuses = $this->manager->statuses()->query( array( 'internal' => true ) );

			if ( isset( $_REQUEST[ $status_property ] ) ) {
				$query_params[ $status_property ] = (array) $_REQUEST[ $status_property ];
			}

			if ( ! empty( $internal_statuses ) ) {
				if ( isset( $query_params[ $status_property ] ) ) {
					$query_params[ $status_property ] = array_diff( $query_params[ $status_property ], array_keys( $internal_statuses ) );
				} else {
					$query_params[ $status_property ] = array_diff( array_keys( $this->manager->statuses()->query() ), array_keys( $internal_statuses ) );
				}
			}
		}

		if ( method_exists( $this->manager, 'get_author_property' ) ) {
			$author_property = $this->manager->get_author_property();

			if ( ! $capabilities || ! $capabilities->current_user_can( 'edit_others_items' ) ) {
				$query_params[ $author_property ] = get_current_user_id();
			} elseif ( isset( $_REQUEST[ $author_property ] ) ) {
				$query_params[ $author_property ] = $_REQUEST[ $author_property ];
			}
		}

		if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
			$query_params['orderby'] = array( $_REQUEST['orderby'] => $_REQUEST['order'] );
		} elseif ( isset( $_REQUEST['orderby'] ) ) {
			$query_params['orderby'] = array( $_REQUEST['orderby'] => 'ASC' );
		} elseif ( isset( $_REQUEST['order'] ) ) {
			$query_params['orderby'] = array( $this->manager->get_primary_property() => $_REQUEST['order'] );
		}

		$query_object = $this->manager->create_query_object();

		if ( ! empty( $query_object->get_search_fields() ) && isset( $_REQUEST['s'] ) ) {
			$query_params['search'] = wp_unslash( trim( $_REQUEST['s'] ) );
		}

		$collection = $query_object->query( $query_params );

		$total = $collection->get_total();

		$this->items = $collection->get_raw();

		$this->set_pagination_args( array(
			'total_items' => $total,
			'per_page'    => $per_page,
		) );
	}

	/**
	 * Displays a message when there are no items.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function no_items() {
		echo $this->manager->get_message( 'list_table_no_items' );
	}

	/**
	 * Gets a list of columns.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Columns as `$slug => $label` pairs.
	 */
	public function get_columns() {
		$columns = array();
		$columns['cb'] = '<input type="checkbox" />';

		if ( method_exists( $this->manager, 'get_title_property' ) ) {
			$title_property = $this->manager->get_title_property();

			$columns[ $title_property ] = $this->manager->get_message( 'list_table_column_label_title' );
		}

		if ( method_exists( $this->manager, 'get_author_property' ) ) {
			$author_property = $this->manager->get_author_property();

			$columns[ $author_property ] = $this->manager->get_message( 'list_table_column_label_author' );
		}

		if ( method_exists( $this->manager, 'get_date_property' ) ) {
			$date_property = $this->manager->get_date_property();

			$columns[ $date_property ] = $this->manager->get_message( 'list_table_column_label_date' );
		}

		return $columns;
	}

	/**
	 * Gets a list of sortable columns.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Sortable columns as `$slug => $orderby` pairs. $orderby
	 *               can be a plain string or an array with the first element
	 *               being the field slug and the second being true to make the
	 *               initial sorting order descending.
	 */
	protected function get_sortable_columns() {
		$sortable_columns = array();

		if ( method_exists( $this->manager, 'get_title_property' ) ) {
			$title_property = $this->manager->get_title_property();

			$sortable_columns[ $title_property ] = $title_property;
		}

		if ( method_exists( $this->manager, 'get_date_property' ) ) {
			$date_property = $this->manager->get_date_property();

			$sortable_columns[ $date_property ] = array( $date_property, true );
		}

		return $sortable_columns;
	}

	/**
	 * Generates and display row actions links for the list table.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Leaves_And_Love\Plugin_Lib\DB_Objects\Model $model       The model being acted upon.
	 * @param string                                      $column_name Current column name.
	 * @param string                                      $primary     Primary column name.
	 * @return string The row actions HTML, or an empty string if the current column is not the primary column.
	 */
	protected function handle_row_actions( $model, $column_name, $primary ) {
		if ( $column_name !== $primary ) {
			return '';
		}

		$primary_property = $this->manager->get_primary_property();
		$model_id = $model->$primary_property;

		$view_url = '';

		$edit_url = '';
		if ( ! empty( $this->_args['model_page'] ) ) {
			$edit_url = add_query_arg( 'id', $model_id, $this->_args['model_page'] );
		}

		$actions = $this->build_row_actions( $model, $model_id, $view_url, $edit_url, $this->_args['models_page'] );

		$links = array();

		foreach ( $actions as $slug => $data ) {
			$class = ! empty( $data['class'] ) ? ' class="' . esc_attr( $data['class'] ) . '"' : '';
			$aria_label = ! empty( $data['aria_label'] ) ? ' aria-label="' . esc_attr( $data['aria_label'] ) . '"' : '';

			$links[ $slug ] = sprintf( '<a href="%1$s"%2$s%3$s>%4$s</a>', esc_url( $data['url'] ), $class, $aria_label, $data['label'] );
		}

		return $this->row_actions( $links );
	}

	/**
	 * Gets an associative array with the list of views available on
	 * this table.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Views as `$slug => $link` pairs.
	 */
	protected function get_views() {
		$current = '';

		$views = $this->build_views( $current, $this->_args['models_page'], $this->_args['model_page'] );

		$links = array();

		foreach ( $views as $slug => $data ) {
			if ( $current === $slug ) {
				if ( ! empty( $data['class'] ) ) {
					$data['class'] .= ' current';
				} else {
					$data['class'] = 'current';
				}
			}

			$class = ! empty( $data['class'] ) ? ' class="' . esc_attr( $data['class'] ) . '"' : '';
			$aria_label = ! empty( $data['aria_label'] ) ? ' aria-label="' . esc_attr( $data['aria_label'] ) . '"' : '';

			$links[ $slug ] = sprintf( '<a href="%1$s"%2$s%3$s>%4$s</a>', esc_url( $data['url'] ), $class, $aria_label, $data['label'] );
		}

		return $links;
	}

	/**
	 * Gets an associative array with the list of bulk actions available
	 * on this table.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Bulk actions as `$slug => $label` pairs.
	 */
	protected function get_bulk_actions() {
		$actions = $this->build_bulk_actions();

		$options = array();

		foreach ( $actions as $slug => $data ) {
			$options[ $slug ] = $data['label'];
		}

		return $options;
	}

	/**
	 * Returns the available views for the list table.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string &$current Slug of the current view, passed by reference. Should be
	 *                         set properly in the method.
	 * @param string $list_url Optional. The URL to the list page. Default empty.
	 * @param string $edit_url Optional. The base URL to the edit page. Default empty.
	 * @return array Views as `$slug => $data` pairs. The $data array must have keys 'url'
	 *               and 'label' and may additionally have 'class' and 'aria_label'.
	 */
	protected function build_views( &$current, $list_url = '', $edit_url = '' ) {
		$capabilities = $this->manager->capabilities();

		$current = 'all';
		$total = 0;

		$views = array();

		$user_id = 0;
		if ( method_exists( $this->manager, 'get_author_property' ) ) {
			$author_property = $this->manager->get_author_property();

			if ( ! $capabilities || ! $capabilities->current_user_can( 'edit_others_items' ) ) {
				$user_id = get_current_user_id();
			} else {
				$user_counts = $this->manager->count( get_current_user_id() );

				if ( isset( $_REQUEST[ $author_property ] ) && get_current_user_id() === absint( $_REQUEST[ $author_property ] ) ) {
					$current = 'mine'
				}

				$views['mine'] = array(
					'url'   => add_query_arg( $author_property, get_current_user_id(), $list_url ),
					'label' => sprintf( translate_nooped_plural( $this->manager->get_message( 'list_table_view_mine' ), $user_counts['_total'] ), number_format_i18n( $user_counts['_total'] ) ),
				);
			}
		}

		$counts = $this->manager->count( $user_id );

		if ( method_exists( $this->manager, 'get_status_property' ) ) {
			$status_property = $this->manager->get_status_property();

			$internal_statuses = array_keys( $this->manager->statuses()->query( array( 'internal' => true ) ) );
			foreach ( $counts as $status => $number ) {
				if ( '_total' === $status ) {
					continue;
				}

				if ( in_array( $status, $internal_statuses, true ) ) {
					continue;
				}

				$views[ $status ] = array(
					'url'   => add_query_arg( $status_property, $status, $list_url ),
					'label' => sprintf( translate_nooped_plural( $this->manager->get_message( 'list_table_view_status_' . $status ), $number ), number_format_i18n( $number ) ),
				);

				$total += $number;
			}

			if ( isset( $_REQUEST[ $status_property ] ) ) {
				$current = $_REQUEST[ $status_property ];
			}
		} else {
			$total = $counts['_total'];
		}

		if ( isset( $user_counts ) && absint( $user_counts['_total'] ) === absint( $total ) ) {
			unset( $views['mine'] );
		}

		if ( ! empty( $views ) ) {
			$views = array_merge( array(
				'all' => array(
					'url'   => $list_url,
					'label' => sprintf( translate_nooped_plural( $this->manager->get_message( 'list_table_view_all' ), $total ), number_format_i18n( $total ) ),
				),
			), $views );
		}

		return $views;
	}

	/**
	 * Returns the available bulk actions for the list table.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Actions as `$slug => $data` pairs. The $data array must have the key
	 *               'label'.
	 */
	protected function build_bulk_actions() {
		$actions = array();

		$capabilities = $this->manager->capabilities();
		if ( $capabilities && $capabilities->user_can_delete() ) {
			$actions['delete'] = array(
				'label' => $this->manager->get_message( 'list_table_bulk_action_delete' ),
			);
		}

		return $actions;
	}

	/**
	 * Returns the available row actions for a given item in the list table.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Leaves_And_Love\Plugin_Lib\DB_Objects\Model $model The model for which to return row actions.
	 * @param int                                         $model_id The model ID.
	 * @param string                                      $view_url Optional. The URL to view the model in the
	 *                                                              frontend. Default empty.
	 * @param string                                      $edit_url Optional. The URL to edit the model in the
	 *                                                              backend. Default empty.
	 * @param string                                      $list_url Optional. The URL to the list page. Default
	 *                                                              empty.
	 * @return array Row actions as `$id => $link` pairs.
	 */
	protected function build_row_actions( $model, $model_id, $view_url = '', $edit_url = '', $list_url = '' ) {
		$actions = array();

		$title = null;
		if ( method_exists( $this->manager, 'get_title_property' ) ) {
			$title_property = $this->manager->get_title_property();
			$title = $model->$title_property;
		}

		$capabilities = $this->manager->capabilities();
		if ( ! empty( $edit_url ) && $capabilities ) {
			if ( $capabilities->user_can_edit( null, $model_id ) ) {
				$aria_label = $this->manager->get_message( 'list_table_row_action_edit_item' );
				if ( null !== $title ) {
					$aria_label = sprintf( $this->manager->get_message( 'list_table_row_action_edit_item_title' ), $title );
				}

				$actions['edit'] = array(
					'url'        => $edit_url,
					'label'      => $this->manager->get_message( 'list_table_row_action_edit' ),
					'aria_label' => $aria_label,
				);
			}

			if ( $capabilities->user_can_delete( null, $model_id ) ) {
				$aria_label = $this->manager->get_message( 'list_table_row_action_delete_item' );
				if ( null !== $title ) {
					$aria_label = sprintf( $this->manager->get_message( 'list_table_row_action_delete_item_title' ), $title );
				}

				$actions['delete'] = array(
					'url'        => add_query_arg( 'action', 'delete', $edit_url ),
					'label'      => $this->manager->get_message( 'list_table_row_action_delete' ),
					'aria_label' => $aria_label,
					'class'      => 'submitdelete',
				);
			}
		}

		if ( ! empty( $view_url ) ) {
			$show_view = true;

			if ( method_exists( $this->manager, 'get_status_property' ) ) {
				$status_property = $this->manager->get_status_property();

				$public_statuses = $this->manager->statuses()->get_public();

				if ( ! in_array( $model->$status_property, $public_statuses, true ) && ( ! $capabilities || ! $capabilities->user_can_edit( null, $model_id ) ) ) {
					$show_view = false;
				}
			}

			if ( $show_view ) {
				$aria_label = $this->manager->get_message( 'list_table_row_action_view_item' );
				if ( null !== $title ) {
					$aria_label = sprintf( $this->manager->get_message( 'list_table_row_action_view_item_title' ), $title );
				}

				$actions['view'] = array(
					'url'        => $view_url,
					'label'      => $this->manager->get_message( 'list_table_row_action_view' ),
					'aria_label' => $aria_label,
				);
			}
		}

		return $actions;
	}
}

endif;
