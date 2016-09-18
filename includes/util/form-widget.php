<?php
/**
 * Widget form class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Util;

use Leaves_And_Love\Plugin_Lib\Traits\Actions;
use Leaves_And_Love\Plugin_Lib\Traits\Filters;
use WP_Fields_API_Form;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Util\Form_Widget' ) ) :

/**
 * Class for a shortcode
 *
 * This class represents a shortcode.
 *
 * @since 1.0.0
 */
class Form_Widget extends WP_Fields_API_Form {
	use Actions, Filters;

	protected $widget;
	protected $widget_instance = array();
	protected $widget_instance_validated = array();
	protected $messages = array();
	protected $hooks_added = false;

	public function set_messages( $messages ) {
		$this->messages = $messages;
	}

	public function set_widget( $widget ) {
		global $wp_fields;

		if ( $this->widget === $widget ) {
			return;
		}

		$this->widget = $widget;

		$this->item_id = 0;
		$this->object_subtype = $this->widget->id_base;

		$control = $wp_fields->get_control( $this->object_type, $this->id . '-title' );
		$control->input_attrs['id']   = $this->widget->get_field_id( 'title' );
		$control->input_attrs['name'] = $this->widget->get_field_name( 'title' );
	}

	public function set_widget_instance( $instance ) {
		$this->widget_instance = $instance;
	}

	public function register_fields( $wp_fields ) {
		$this->set_messages( array(
			'widget'            => __( 'Widget', 'content-organizer' ),
			'title_label'       => __( 'Title', 'content-organizer' ),
			'title_description' => __( 'The title will be shown above the widget.', 'content-organizer' ),
		) );

		$section_id   = $this->id . '-main';
		$section_args = array(
			'label'         => $this->messages['widget'],
			'display_label' => false,
			'controls'      => array(),
		);

		$section_args['controls'][ $this->id . '-title' ] = array(
			'type'        => 'text',
			'label'       => $this->messages['title_label'],
			'description' => $this->messages['title_description'],
			'input_attrs'  => array(
				'id'    => '',
				'name'  => '',
				'class' => 'widefat',
			),
			'field'       => 'title',
			'internal'    => true,
		);

		$this->add_section( $section_id, $section_args );

		parent::register_fields( $wp_fields );
	}

	public function add_hooks() {
		if ( $this->hooks_added ) {
			return;
		}

		$this->add_action( 'fields_update_widget', array( $this, 'update_field' ), 10, 3 );
		//TODO: $this->add_filter( 'fields_get_widget', array( $this, 'get_field' ), 10, 3 );

		//TODO: $this->add_action( 'lal_render_widget_settings', array( $this, 'render_settings' ), 10, 2 );
		//TODO: $this->add_filter( 'lal_update_widget_settings', array( $this, 'update_settings' ), 10, 3 );

		$this->hooks_added = true;
	}

	protected function render_settings( $instance, $widget ) {
		$this->set_widget( $widget );
		$this->set_widget_instance( $instance );

		$this->maybe_render();

		$this->widget_instance = array();
	}

	protected function update_settings( $new_instance, $old_instance, $widget ) {
		$this->set_widget( $widget );
		$this->set_widget_instance( $new_instance );

		$this->save_fields();

		$new_instance = array_merge( $old_instance, $this->widget_instance_validated );

		$this->widget_instance = array();
		$this->widget_instance_validated = array();

		return $new_instance;
	}

	protected function update_field( $value, $item_id, $field ) {
		$field_slug = $field->id; //TODO: extract actual field slug

		$this->widget_instance_validated[ $field_slug ] = $value;
	}

	protected function get_field( $default, $item_id, $field ) {
		$field_slug = $field->id; //TODO: extract actual field slug

		if ( ! isset( $this->widget_instance[ $field_slug ] ) ) {
			return $default;
		}

		return $this->widget_instance[ $field_slug ];
	}
}

endif;
