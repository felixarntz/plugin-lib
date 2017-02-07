<?php
/**
 * REST controller class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

use WP_REST_Server;
use WP_REST_Controller;
use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\REST_Models_Controller' ) ) :

/**
 * Class to access models via the REST API.
 *
 * @since 1.0.0
 */
abstract class REST_Models_Controller extends WP_REST_Controller {
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
	 * @param Leaves_And_Love\Plugin_Lib\DB_Objects\Manager $manager The manager instance.
	 */
	public function __construct( $manager ) {
		$this->manager = $manager;
	}

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			'args' => array(
				'id' => array(
					'description' => $this->manager->get_message( 'rest_id_description' ),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => array(
					'context' => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				'args'                => array(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Checks if a given request has access to read models.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		$capabilities = $this->manager->capabilities();

		if ( 'edit' === $request['context'] ) {
			if ( ! $capabilities || ! $capabilities->user_can_edit() ) {
				return new WP_Error( 'rest_forbidden_context', $this->manager->get_message( 'rest_cannot_edit_items' ), array( 'status' => rest_authorization_required_code() ) );
			}

			return true;
		}

		if ( ! $capabilities || ! $capabilities->user_can_read() ) {
			return new WP_Error( 'rest_cannot_read_items', $this->manager->get_message( 'rest_cannot_read_items' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Retrieves a collection of models.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {

	}

	/**
	 * Checks if a given request has access to read a model.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if the request has read access for the model, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		$model = $this->manager->get( $request['id'] );
		if ( ! $model ) {
			return new WP_Error( 'rest_invalid_id', $this->manager->get_message( 'rest_invalid_id' ), array( 'status' => 404 ) );
		}

		$capabilities = $this->manager->capabilities();

		if ( 'edit' === $request['context'] ) {
			if ( ! $capabilities || ! $capabilities->user_can_edit( null, $request['id'] ) ) {
				return new WP_Error( 'rest_forbidden_context', $this->manager->get_message( 'rest_cannot_edit_item' ), array( 'status' => rest_authorization_required_code() ) );
			}

			return true;
		}

		if ( ! $capabilities || ! $capabilities->user_can_read( null, $request['id'] ) ) {
			return new WP_Error( 'rest_cannot_read_item', $this->manager->get_message( 'rest_cannot_read_item' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Retrieves a single model.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {

	}

	/**
	 * Checks if a given request has access to create a model.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has access to create models, WP_Error object otherwise.
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! empty( $request['id'] ) ) {
			return new WP_Error( 'rest_item_exists', $this->manager->get_message( 'rest_item_exists' ), array( 'status' => 400 ) );
		}

		$capabilities = $this->manager->capabilities();

		if ( ! $capabilities || ! $capabilities->user_can_create() ) {
			return new WP_Error( 'rest_cannot_create_item', $this->manager->get_message( 'rest_cannot_create_item' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Creates a single model.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {

	}

	/**
	 * Checks if a given request has access to update a model.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has access to update the item, WP_Error object otherwise.
	 */
	public function update_item_permissions_check( $request ) {
		$model = $this->manager->get( $request['id'] );
		if ( ! $model ) {
			return new WP_Error( 'rest_invalid_id', $this->manager->get_message( 'rest_invalid_id' ), array( 'status' => 404 ) );
		}

		$capabilities = $this->manager->capabilities();

		if ( ! $capabilities || ! $capabilities->user_can_edit( null, $request['id'] ) ) {
			return new WP_Error( 'rest_cannot_edit_item', $this->manager->get_message( 'rest_cannot_edit_item' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Updates a single model.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {

	}

	/**
	 * Checks if a given request has access to delete a model.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has access to delete the item, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		$model = $this->manager->get( $request['id'] );
		if ( ! $model ) {
			return new WP_Error( 'rest_invalid_id', $this->manager->get_message( 'rest_invalid_id' ), array( 'status' => 404 ) );
		}

		$capabilities = $this->manager->capabilities();

		if ( ! $capabilities || ! $capabilities->user_can_delete( null, $request['id'] ) ) {
			return new WP_Error( 'rest_cannot_delete_item', $this->manager->get_message( 'rest_cannot_delete_item' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Deletes a single model.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {

	}
}

endif;
