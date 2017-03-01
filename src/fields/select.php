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
	 * Option groups with choices to select from, if necessary.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $optgroups = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Leaves_And_Love\Plugin_Lib\Fields\Field_Manager $manager Field manager instance.
	 * @param string                                          $id      Field identifier.
	 * @param array                                           $args    {
	 *     Optional. Field arguments. Anything you pass in addition to the default supported arguments
	 *     will be used as an attribute on the input. Default empty array.
	 *
	 *     @type string          $section       Section identifier this field belongs to. Default empty.
	 *     @type string          $label         Field label. Default empty.
	 *     @type string          $description   Field description. Default empty.
	 *     @type mixed           $default       Default value for the field. Default null.
	 *     @type bool|int        $repeatable    Whether this should be a repeatable field. An integer can also
	 *                                          be passed to set the limit of repetitions allowed. Default false.
	 *     @type array           $input_classes Array of CSS classes for the field input. Default empty array.
	 *     @type array           $label_classes Array of CSS classes for the field label. Default empty array.
	 *     @type callable        $validate      Custom validation callback. Will be executed after doing the regular
	 *                                          validation if no errors occurred in the meantime. Default none.
	 *     @type callable|string $before        Callback or string that should be used to generate output that will
	 *                                          be printed before the field. Default none.
	 *     @type callable|string $after         Callback or string that should be used to generate output that will
	 *                                          be printed after the field. Default none.
	 * }
	 */
	public function __construct( $manager, $id, $args = array() ) {
		parent::__construct( $manager, $id, $args );

		if ( ! empty( $this->optgroups ) ) {
			if ( ! empty( $this->choices ) ) {
				array_unshift( $this->optgroups, array(
					'label'   => '',
					'choices' => $this->choices,
				) );
			}

			$this->choices = array();

			foreach ( $this->optgroups as $optgroup ) {
				$this->choices = array_merge( $this->choices, $optgroup['choices'] );
			}
		}
	}

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
			<?php if ( ! empty( $this->optgroups ) ) : ?>
				<?php foreach ( $this->optgroups as $optgroup ) : ?>
					<?php if ( ! empty( $optgroup['label'] ) ) : ?>
						<optgroup label="<?php echo esc_attr( $optgroup['label'] ); ?>">
					<?php endif; ?>

					<?php foreach ( $optgroup['choices'] as $value => $label ) :
						$option_attrs = array(
							'value'    => $value,
							'selected' => in_array( $value, $current_value, true ),
						);
						?>
						<option<?php echo $this->attrs( $option_attrs ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>

					<?php if ( ! empty( $optgroup['label'] ) ) : ?>
						</optgroup>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php else : ?>
				<?php foreach ( $this->choices as $value => $label ) :
					$option_attrs = array(
						'value'    => $value,
						'selected' => in_array( $value, $current_value, true ),
					);
					?>
					<option<?php echo $this->attrs( $option_attrs ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			<?php endif; ?>
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
			<# if ( ! _.isEmpty( data.optgroups ) ) { #>
				<# _.each( data.optgroups, function( optgroup ) { #>
					<# if ( ! _.isEmpty( optgroup.label ) ) { #>
						<optgroup label="{{ optgroup.label }}">
					<# } #>

					<# _.each( optgroup.choices, function( label, value ) { #>
						<option value="{{ value }}"<?php echo $selected; ?>>{{ label }}</option>
					<# } ) #>

					<# if ( ! _.isEmpty( optgroup.label ) ) { #>
						</optgroup>
					<# } #>
				<# }) #>
			<# } else { #>
				<# _.each( data.choices, function( label, value ) { #>
					<option value="{{ value }}"<?php echo $selected; ?>>{{ label }}</option>
				<# } ) #>
			<# } #>
		</select>
		<?php
		$this->print_repeatable_remove_button_template();
	}

	/**
	 * Transforms single field data into an array to be passed to JavaScript applications.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param mixed $current_value Current value of the field.
	 * @return array Field data to be JSON-encoded.
	 */
	protected function single_to_json( $current_value ) {
		$data = parent::single_to_json( $current_value );
		$data['optgroups'] = $this->optgroups;

		return $data;
	}
}

endif;
