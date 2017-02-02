<?php
/**
 * Settings page class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Components;

use Leaves_And_Love\Plugin_Lib\Fields\Field_Manager;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Components\Settings_Page' ) ) :

/**
 * Class for a settings page
 *
 * This class represents a settings menu page in the admin.
 *
 * @since 1.0.0
 */
abstract class Settings_Page extends Admin_Page {
	/**
	 * Page description.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $description = '';

	/**
	 * Array of sections as `$id => $args` pairs.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $sections = array();

	/**
	 * Array of current values.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $current_values = array();

	/**
	 * Field manager instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Fields\Field_Manager
	 */
	protected $field_manager = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string                                            $slug    Page slug.
	 * @param Leaves_And_Love\Plugin_Lib\Components\Admin_Pages $manager Admin page manager instance.
	 */
	public function __construct( $slug, $manager ) {
		parent::__construct( $slug, $manager );

		$this->field_manager = new Field_Manager( $this->manager->get_prefix(), array(
			'assets'        => $this->manager->assets(),
			'error_handler' => $this->manager->error_handler(),
		), array(
			'get_value_callback_args'    => array( $this->slug ),
			'update_value_callback_args' => array( $this->slug, '{value}' ),
			'name_prefix'                => $this->slug,
		) );

		$this->add_page_content();
	}

	/**
	 * Adds a section to the settings page.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $id   Section identifier.
	 * @param array  $args {
	 *     Optional. Section arguments.
	 *
	 *     @type string $title       Section title.
	 *     @type string $description Section description. Default empty.
	 * }
	 */
	public function add_section( $id, $args = array() ) {
		$this->sections[ $id ] = wp_parse_args( $args, array(
			'title'       => '',
			'description' => '',
		) );
	}

	/**
	 * Adds a field to the settings page.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $id      Field identifier.
	 * @param string $type    Identifier of the type.
	 * @param array  $args    {
	 *     Optional. Field arguments. See the field class constructor for further arguments.
	 *
	 *     @type string $section       Section identifier this field belongs to. Default empty.
	 *     @type string $label         Field label. Default empty.
	 *     @type string $description   Field description. Default empty.
	 *     @type mixed  $default       Default value for the field. Default null.
	 *     @type array  $input_classes Array of CSS classes for the field input. Default empty array.
	 *     @type array  $label_classes Array of CSS classes for the field label. Default empty array.
	 *     @type array  $input_attrs   Array of additional input attributes as `$key => $value` pairs.
	 *                                 Default empty array.
	 * }
	 */
	public function add_field( $id, $type, $args = array() ) {
		$this->field_manager->add( $id, $type, $args );
	}

	/**
	 * Enqueues assets to load on the page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue_assets() {
		$this->field_manager->enqueue();
	}

	/**
	 * Renders the settings page content.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function render() {
		$this->current_values = $this->field_manager->get_values();

		?>
		<div class="wrap">
			<?php $this->render_header(); ?>

			<?php $this->render_form( $this->slug ); ?>
		</div>
		<?php
	}

	/**
	 * Registers the setting, sections and fields for this page in WordPress.
	 *
	 * This method is only meant for internal usage.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register() {
		register_setting( $this->slug, $this->slug, array( $this, 'validate' ) );

		foreach ( $this->sections as $id => $section_args ) {
			add_settings_section( $id, $section_args['title'], array( $this, 'render_section_description' ), $this->slug );
		}

		foreach ( $this->field_manager->get_fields() as $id => $field ) {
			add_settings_field( $id, $field->label, array( $this, 'render_field' ), $this->slug, $field->section, array(
				'label_for'      => $this->field_manager->make_id( $id ),
				'field_instance' => $field,
			) );
		}
	}

	/**
	 * Validates the settings for the page.
	 *
	 * This method is only meant for internal usage.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array  $values Array of values.
	 * @param string $option Option name.
	 * @return array Array of validated values.
	 */
	public function validate( $values, $option ) {
		/* Perform a minimal sanity check. */
		if ( $this->slug !== $option ) {
			return null;
		}

		return $this->validate_values( $values, $option, $this->field_manager->get_fields() );
	}

	/**
	 * Renders a section description.
	 *
	 * This method is only meant for internal usage.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $section_args Array of section arguments.
	 */
	public function render_section_description( $section_args ) {
		if ( ! isset( $this->sections[ $section_args['id'] ] ) ) {
			return;
		}

		if ( empty( $this->sections[ $section_args['id'] ]['description'] ) ) {
			return;
		}

		?>
		<p class="description">
			<?php echo $this->sections[ $section_args['id'] ]['description']; ?>
		</p>
		<?php
	}

	/**
	 * Renders a field.
	 *
	 * This method is only meant for internal usage.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $field_args Array of field arguments.
	 */
	public function render_field( $field_args ) {
		$field = $field_args['field_instance'];

		$value = isset( $this->current_values[ $field->id ] ) ? $this->current_values[ $field->id ] : $field->default;

		$field->render_content( $value );
	}

	/**
	 * Renders the settings page header.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render_header() {
		?>
		<h1>
			<?php echo $this->title; ?>
		</h1>

		<?php if ( ! empty( $this->description ) ) : ?>
			<p class="description">
				<?php echo $this->description; ?>
			</p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Renders the settings page form.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $option Option name.
	 */
	protected function render_form( $option ) {
		?>
		<form action="options.php" method="post" novalidate="novalidate">
			<?php settings_fields( $option ); ?>
			<?php do_settings_sections( $option ); ?>
			<?php submit_button(); ?>
		</form>
		<?php
	}

	/**
	 * Validates field values for an array of fields.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array  $values Array of values.
	 * @param string $option Option name.
	 * @param array  $fields Array of field instances.
	 * @return array Array of validated values.
	 */
	protected function validate_values( $values, $option, $fields ) {
		$validated_values = get_option( $option, array() );

		foreach ( $fields as $id => $field ) {
			$value = isset( $values[ $id ] ) ? $values[ $id ] : null;

			$validated_value = $field->validate( $value );
			if ( is_wp_error( $validated_value ) ) {
				add_settings_error( $option, $validated_value->get_error_code(), $validated_value->get_error_message(), 'error' );
				continue;
			}

			$validated_values[ $id ] = $validated_value;
		}

		return $validated_values;
	}

	/**
	 * Adds sections and fields to this page.
	 *
	 * This method should call the methods `add_section()` and `add_field()` to populate the page.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected abstract function add_page_content();
}

endif;
