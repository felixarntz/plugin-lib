<?php
/**
 * Radio field class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

use Leaves_And_Love\Plugin_Lib\Fields\Select_Base;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Radio' ) ) :

/**
 * Class for a radio field.
 *
 * @since 1.0.0
 */
class Radio extends Select_Base {
	/**
	 * Field type identifier.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $slug = 'radio';

	/**
	 * Label mode for this field's label.
	 *
	 * Accepts values 'explicit', 'implicit', 'no_assoc', 'aria_hidden' and 'skip'.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $label_mode = 'aria_hidden';

	/**
	 * Renders a single input for the field.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param mixed $current_value Current field value.
	 */
	protected function render_single_input( $current_value ) {
		$input_attrs = $this->get_input_attrs( array(
			'type' => 'radio',
		), false );

		if ( ! $this->multi ) {
			$current_value = (array) $current_value;
		} else {
			$input_attrs['type'] = 'checkbox';
			$input_attrs['name'] .= '[]';
		}

		$base_id = $input_attrs['id'];

		$count = 0;

		?>
		<fieldset>
			<legend class="screen-reader-text"><?php echo $this->label; ?></legend>

			<?php foreach ( $this->choices as $value => $label ) :
				$count++;

				$input_attrs['id']      = $base_id . '-' . $count;
				$input_attrs['value']   = $value;
				$input_attrs['checked'] = in_array( $value, $current_value, true );
				?>
				<input<?php echo $this->attrs( $input_attrs ); ?>>
				<label for="<?php echo esc_attr( $input_attrs['id'] ); ?>"><?php echo $label; ?></label>
			<?php endforeach; ?>
		</fieldset>
		<?php
	}

	/**
	 * Prints a single input template.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function print_single_input_template() {
		if ( $this->multi ) {
			$type = 'checkbox';
			$name_suffix = '[]';
			$checked = '<# if ( _.contains( data.currentValue, value ) ) { #> checked<# } #>';
		} else {
			$type = 'radio';
			$name_suffix = '';
			$checked = '<# if ( data.currentValue === value ) { #> checked<# } #>';
		}

		?>
		<fieldset>
			<legend class="screen-reader-text">{{ data.label }}</legend>

			<# _.each( data.choices, function( label, value, obj ) { #>
				<input type="<?php echo $type; ?>"{{ _.attrs( _.extend( {}, data.inputAttrs, {
					id: data.inputAttrs.id + _.indexOf( _.keys( obj ), value ),
					name: data.inputAttrs.name + name_suffix
				} ) ) }} value="{{ value }}"<?php echo $checked; ?>>
				<label for="{{ data.inputAttrs.id + _.indexOf( _.keys( obj ), value ) }}">{{ label }}</label>
			<# } ) #>
		</fieldset>
		<?php
		$this->print_repeatable_remove_button_template();
	}
}

endif;
