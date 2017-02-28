<?php
/**
 * Select field class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

use Leaves_And_Love\Plugin_Lib\Fields\Select_Base;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Select' ) ) :

/**
 * Class for a select field.
 *
 * @since 1.0.0
 */
class Select extends Select_Base {
	/**
	 * Field type identifier.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $slug = 'select';

	/**
	 * Renders a single input for the field.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param mixed $current_value Current field value.
	 */
	protected function render_single_input( $current_value ) {
		if ( ! $this->multi ) {
			$current_value = (array) $current_value;
		}

		$input_attrs = array( 'multiple' => $this->multi );

		?>
		<select<?php echo $this->get_input_attrs( $input_attrs ); ?>>
			<?php foreach ( $this->choices as $value => $label ) :
				$option_attrs = array(
					'value'    => $value,
					'selected' => in_array( $value, $current_value, true ),
				);
				?>
				<option<?php echo $this->attrs( $option_attrs ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
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
		if ( $this->multi ) {
			$multiple = ' multiple';
			$selected = '<# if ( _.contains( data.current_value, value ) ) { #> selected<# } #>';
		} else {
			$multiple = '';
			$selected = '<# if ( data.current_value === value ) { #> selected<# } #>';
		}

		?>
		<select{{ _.attrs( data.input_attrs ) }}<?php echo $multiple; ?>>
			<# _.each( data.choices, function( label, value ) { #>
				<option value="{{ value }}"<?php echo $selected; ?>>{{ label }}</option>
			<# } ) #>
		</select>
		<?php
		$this->print_repeatable_remove_button_template();
	}
}

endif;
