<?php
/**
 * Field base class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Field' ) ) :

/**
 * Base class for a field
 *
 * @since 1.0.0
 */
abstract class Field {
	/**
	 * Field manager instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Leaves_And_Love\Plugin_Lib\Fields\Field_Manager
	 */
	protected $manager = null;

	/**
	 * Field identifier. Used to create the id and name attributes.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $id = '';

	/**
	 * Section identifier this field belongs to.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $section = '';

	/**
	 * Field label.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $label = '';

	/**
	 * Field description.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $description = '';

	/**
	 * Default value of the field.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var mixed
	 */
	protected $default = null;

	/**
	 * Array of CSS classes for the field input.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $input_classes = array();

	/**
	 * Array of CSS classes for the field label.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $label_classes = array();

	/**
	 * Array of additional input attributes as `$key => $value` pairs.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $input_attrs = array();

	/**
	 * Custom validation callback.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var callable|null
	 */
	protected $validate = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Leaves_And_Love\Plugin_Lib\Fields\Field_Manager $manager Field manager instance.
	 * @param string                                          $id      Field identifier.
	 * @param array                                           $args    {
	 *     Optional. Field arguments. Default empty array.
	 *
	 *     @type string   $section       Section identifier this field belongs to. Default empty.
	 *     @type string   $label         Field label. Default empty.
	 *     @type string   $description   Field description. Default empty.
	 *     @type mixed    $default       Default value for the field. Default null.
	 *     @type array    $input_classes Array of CSS classes for the field input. Default empty array.
	 *     @type array    $label_classes Array of CSS classes for the field label. Default empty array.
	 *     @type array    $input_attrs   Array of additional input attributes as `$key => $value` pairs.
	 *                                   Default empty array.
	 *     @type callable $validate      Custom validation callback. Will be executed after doing the regular
	 *                                   validation if no errors occurred in the meantime. Default none.
	 * }
	 */
	public function __construct( $manager, $id, $args = array() ) {
		$this->manager = $manager;
		$this->id = $id;

		$forbidden_keys = array( 'manager', 'id' );

		foreach ( $args as $key => $value ) {
			if ( in_array( $key, $forbidden_keys, true ) ) {
				continue;
			}

			if ( isset( $this->$key ) ) {
				$this->$key = $value;
			} else {
				$this->input_attrs[ $key ] = $value;
			}
		}
	}

	/**
	 * Magic isset-er.
	 *
	 * Checks whether a property is set.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $property Property to check for.
	 * @return bool True if the property is set, false otherwise.
	 */
	public function __isset( $property ) {
		if ( 'manager' === $property ) {
			return false;
		}

		return isset( $this->$property );
	}

	/**
	 * Magic getter.
	 *
	 * Returns a property value.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $property Property to get.
	 * @return mixed Property value, or null if property is not set.
	 */
	public function __get( $property ) {
		if ( 'manager' === $property ) {
			return null;
		}

		if ( ! isset( $this->$property ) ) {
			return null;
		}

		return $this->$property;
	}

	/**
	 * Enqueues the necessary assets for the field.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue() {
		// Empty method body.
	}

	/**
	 * Renders the field's label.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function render_label() {
		if ( empty( $this->label ) ) {
			return;
		}

		?>
		<label<?php echo $this->get_label_attrs(); ?>>
			<?php echo $this->label; ?>
		</label>
		<?php
	}

	/**
	 * Renders the field's main content including the input.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param mixed $current_value Current value of the field.
	 */
	public function render_content( $current_value ) {
		$this->render_input( $current_value );

		if ( ! empty( $this->description ) ) : ?>
		<p class="description">
			<?php echo $this->description; ?>
		</p>
		<?php endif;
	}

	/**
	 * Renders the field's input.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param mixed $current_value Current value of the field.
	 */
	public abstract function render_input( $current_value );

	/**
	 * Validates a value for the field.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param mixed $value Value to validate. When null is passed, the method
	 *                     assumes no value was sent.
	 * @return mixed|WP_Error The validated value on success, or an error
	 *                        object on failure.
	 */
	public function validate( $value = null ) {
		$this->pre_validate( $value );
		$this->post_validate( $value );
	}

	/**
	 * Handles pre-validation of a value.
	 *
	 * This method returns an error if the value of a required field is empty.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param mixed $value Vaule to handle pre-validation for.
	 * @return mixed|WP_Error The value on success, or an error object on failure.
	 */
	protected function pre_validate( $value ) {
		if ( isset( $this->input_attrs['required'] ) && $this->input_attrs['required'] && $this->is_value_empty( $value ) ) {
			return new WP_Error( 'field_empty_required', sprintf( $this->manager->get_message( 'field_empty_required' ), $this->label ) );
		}

		return $value;
	}

	/**
	 * Handles post-validation of a value.
	 *
	 * This method checks whether a custom validation callback is set and executes it.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param mixed $value Vaule to handle post-validation for.
	 * @return mixed|WP_Error The value on success, or an error object on failure.
	 */
	protected function post_validate( $value ) {
		if ( $this->validate && is_callable( $this->validate ) ) {
			return call_user_func( $this->validate, $value );
		}

		return $value;
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
		return empty( $value );
	}

	/**
	 * Returns the attributes for the field's label.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $label_attrs Array of custom label attributes.
	 * @param bool  $as_string   Optional. Whether to return them as an attribute
	 *                           string. Default true.
	 * @return array|string Either an array of `$key => $value` pairs, or an
	 *                      attribute string if `$as_string` is true.
	 */
	protected function get_label_attrs( $label_attrs = array(), $as_string = true ) {
		$base_label_attrs = array(
			'for' => $this->manager->make_id( $this->id ),
		);

		if ( ! empty( $this->label_classes ) ) {
			$base_label_attrs['class'] = implode( ' ', $this->label_classes );
		}

		$all_label_attrs = array_merge( $base_label_attrs, $label_attrs );

		if ( $as_string ) {
			return $this->attrs( $all_label_attrs );
		}

		return $all_label_attrs;
	}

	/**
	 * Returns the attributes for the field's input.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $input_attrs Array of custom input attributes.
	 * @param bool  $as_string   Optional. Whether to return them as an attribute
	 *                           string. Default true.
	 * @return array|string Either an array of `$key => $value` pairs, or an
	 *                      attribute string if `$as_string` is true.
	 */
	protected function get_input_attrs( $input_attrs = array(), $as_string = true ) {
		$base_input_attrs = array(
			'id' => $this->manager->make_id( $this->id ),
			'name' => $this->manager->make_name( $this->id ),
		);

		if ( ! empty( $this->input_classes ) ) {
			$base_input_attrs['class'] = implode( ' ', $this->input_classes );
		}

		$all_input_attrs = array_merge( $base_input_attrs, $input_attrs, $this->input_attrs );

		if ( $as_string ) {
			return $this->attrs( $all_input_attrs );
		}

		return $all_input_attrs;
	}

	/**
	 * Transforms an array of attributes into an attribute string.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $attrs Array of `$key => $value` pairs.
	 * @return string Attribute string.
	 */
	protected function attrs( $attrs ) {
		$output = '';

		foreach ( $attrs as $attr => $value ) {
			if ( is_bool( $value ) && $value ) {
				$output .= ' ' . $attr;
			} else {
				$output .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
			}
		}

		return $output;
	}
}

endif;
