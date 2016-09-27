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
 * Class for a widget form.
 *
 * This represents a specific widget's admin form.
 *
 * @since 1.0.0
 */
class Form_Widget extends WP_Fields_API_Form {
	use Actions, Filters;

	/**
	 * Messages to print to the user.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $messages = array();

	/**
	 * The widget this form belongs to.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Components\Widget
	 */
	protected $widget;

	/**
	 * The current widget instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $widget_instance = array();

	/**
	 * The current validated widget instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $widget_instance_validated = array();

	/**
	 * Whether the hooks for this form have been added.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var bool
	 */
	protected $hooks_added = false;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string                                       $object_type Object type for this form. Should be 'widget'.
	 * @param string                                       $form_id     ID for this form.
	 * @param array                                        $args        Additional arguments for this form.
	 * @param Leaves_And_Love\Plugin_Lib\Components\Widget $widget      The widget this form belongs to.
	 * @param array                                        $messages    Default widget messages printed to the user.
	 */
	public function __construct( $object_type, $form_id, $args, $widget, $messages ) {
		parent::__construct( $object_type, $form_id, $args );

		$this->messages = $messages;
		$this->widget = $widget;
	}

	/**
	 * Sets the currently active widget instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $instance Widget instance.
	 */
	public function set_widget_instance( $instance ) {
		$this->widget_instance = $instance;
		$this->widget_instance_validated = array();
	}

	/**
	 * Registers all fields for this widget form.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_Fields_API $wp_fields The Fields API instance.
	 */
	public function register_fields( $wp_fields ) {
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
				'id'    => $this->widget->get_field_id( 'title' ),
				'name'  => $this->widget->get_field_name( 'title' ),
				'class' => 'widefat',
			),
			'field'       => 'title',
			'internal'    => true,
		);

		$this->add_section( $section_id, $section_args );

		//TODO: register widget-specific fields

		parent::register_fields( $wp_fields );
	}

	/**
	 * Adds the necessary hooks in order for this form to work.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function add_hooks() {
		if ( $this->hooks_added ) {
			return;
		}

		$this->add_action( 'fields_update_widget', array( $this, 'update_field' ), 10, 3 );
		$this->add_filter( 'fields_value_widget', array( $this, 'get_field' ), 10, 3 );

		$this->add_action( 'lal_render_widget_settings_' . $this->object_subtype, array( $this, 'render_settings' ), 10, 1 );
		$this->add_filter( 'lal_update_widget_settings_' . $this->object_subtype, array( $this, 'update_settings' ), 10, 2 );

		$this->hooks_added = true;
	}

	/**
	 * Renders the widget form for a given instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $instance A widget instance.
	 */
	protected function render_settings( $instance ) {
		$this->set_widget_instance( $instance );

		$this->maybe_render();

		$this->set_widget_instance( array() );
	}

	/**
	 * Validates the settings for a given instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $new_instance New settings for a widget instance.
	 * @param array $old_instance Previous settings for that widget instance.
	 * @return array Validated settings.
	 */
	protected function update_settings( $new_instance, $old_instance ) {
		$this->set_widget_instance( $new_instance );

		$this->save_fields();

		$new_instance = array_merge( $old_instance, $this->widget_instance_validated );

		$this->set_widget_instance( array() );

		return $new_instance;
	}

	/**
	 * Updates a given field within the instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param mixed               $value   The value to set.
	 * @param int                 $item_id The object ID. Can be ignored here.
	 * @param WP_Fields_API_Field $field   The field object.
	 */
	protected function update_field( $value, $item_id, $field ) {
		if ( $field->object_subtype !== $this->object_subtype ) {
			return;
		}

		$field_slug = $field->id; //TODO: extract actual field slug

		$this->widget_instance_validated[ $field_slug ] = $value;
	}

	/**
	 * Retrieves the value for a given field within the instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param mixed               $default The default value.
	 * @param int                 $item_id The object ID. Can be ignored here.
	 * @param WP_Fields_API_Field $field   The field object.
	 * @return mixed The current value of the given field.
	 */
	protected function get_field( $default, $item_id, $field ) {
		if ( $field->object_subtype !== $this->object_subtype ) {
			return $default;
		}

		$field_slug = $field->id; //TODO: extract actual field slug

		if ( ! isset( $this->widget_instance[ $field_slug ] ) ) {
			return $default;
		}

		return $this->widget_instance[ $field_slug ];
	}
}

endif;
