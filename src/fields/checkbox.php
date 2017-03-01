<?php
/**
 * Checkbox field class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

use Leaves_And_Love\Plugin_Lib\Fields\Field;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Checkbox' ) ) :

/**
 * Class for a checkbox field.
 *
 * @since 1.0.0
 */
class Checkbox extends Field {
	/**
	 * Field type identifier.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $slug = 'checkbox';

	/**
	 * Label mode for this field's label.
	 *
	 * Accepts values 'explicit', 'implicit', 'no_assoc', 'aria_hidden' and 'skip'.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $label_mode = 'skip';

	/**
	 * Renders a single input for the field.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param mixed $current_value Current field value.
	 */
	protected function render_single_input( $current_value ) {
		$input_attrs = array(
			'type'    => 'checkbox',
			'value'   => '1',
			'checked' => (bool) $current_value,
		);
		?>
		<input<?php echo $this->get_input_attrs( $input_attrs ); ?>>
		<label for="<?php echo esc_attr( $this->get_id_attribute() ); ?>"><?php echo $this->label; ?></label>
		<?php
		$this->render_repeatable_remove_button();
	}

	/**
	 * Prints a single input template.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render_single_input_template() {
		?>
		<input type="checkbox" value="1"{{ _.attrs( data.input_attrs ) }}<# if ( data.current_value ) { #> checked<# } #>>
		<label for="{{ data.id }}">{{ data.label }}</label>
		<?php
		$this->print_repeatable_remove_button_template();
	}

	/**
	 * Validates a single value for the field.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param mixed $value Value to validate. When null is passed, the method
	 *                     assumes no value was sent.
	 * @return mixed|WP_Error The validated value on success, or an error
	 *                        object on failure.
	 */
	protected function validate_single( $value = null ) {
		if ( ! $value ) {
			return false;
		}

		if ( is_string( $value ) && strtolower( $value ) === 'false' ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks whether a value is considered empty.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param mixed $value Value to check whether its empty.
	 * @return bool True if the value is considered empty, false otherwise.
	 */
	protected function is_value_empty( $value ) {
		return false;
	}
}

endif;
