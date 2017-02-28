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
	 * Field type identifier.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $slug = '';

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
	 * Whether this is a repeatable field.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var bool
	 */
	protected $repeatable = false;

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
	 * Callback or string for output to print before the field.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var callable|string|null
	 */
	protected $before = null;

	/**
	 * Callback or string for output to print after the field.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var callable|string|null
	 */
	protected $after = null;

	/**
	 * Internal index counter for repeatable fields.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int|null
	 */
	protected $index = null;

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
	 *     @type string          $section       Section identifier this field belongs to. Default empty.
	 *     @type string          $label         Field label. Default empty.
	 *     @type string          $description   Field description. Default empty.
	 *     @type mixed           $default       Default value for the field. Default null.
	 *     @type bool|int        $repeatable    Whether this should be a repeatable field. An integer can also
	 *                                          be passed to set the limit of repetitions allowed. Default false.
	 *     @type array           $input_classes Array of CSS classes for the field input. Default empty array.
	 *     @type array           $label_classes Array of CSS classes for the field label. Default empty array.
	 *     @type array           $input_attrs   Array of additional input attributes as `$key => $value` pairs.
	 *                                          Default empty array.
	 *     @type callable        $validate      Custom validation callback. Will be executed after doing the regular
	 *                                          validation if no errors occurred in the meantime. Default none.
	 *     @type callable|string $before        Callback or string that should be used to generate output that will
	 *                                          be printed before the field. Default none.
	 *     @type callable|string $after         Callback or string that should be used to generate output that will
	 *                                          be printed after the field. Default none.
	 * }
	 */
	public function __construct( $manager, $id, $args = array() ) {
		$this->manager = $manager;
		$this->id = $id;

		$forbidden_keys = array( 'manager', 'id', 'slug', 'index' );

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

		if ( $this->is_repeatable() && null === $this->index ) {
			?>
			<span<?php echo $this->get_label_attrs(); ?>>
				<?php echo $this->label; ?>
			</span>
			<?php
		} else {
			?>
			<label<?php echo $this->get_label_attrs(); ?>>
				<?php echo $this->label; ?>
			</label>
			<?php
		}
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
		if ( ! empty( $this->before ) ) {
			if ( is_callable( $this->before ) ) {
				call_user_func( $this->before );
			} elseif ( is_string( $this->before ) ) {
				echo $this->before;
			}
		}

		$this->render_input( $current_value );

		if ( ! empty( $this->description ) ) : ?>
		<p class="description">
			<?php echo $this->description; ?>
		</p>
		<?php endif;

		if ( ! empty( $this->after ) ) {
			if ( is_callable( $this->after ) ) {
				call_user_func( $this->after );
			} elseif ( is_string( $this->after ) ) {
				echo $this->after;
			}
		}
	}

	/**
	 * Renders the field's input.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param mixed $current_value Current value of the field.
	 */
	public final function render_input( $current_value ) {
		if ( $this->is_repeatable() ) {
			$current_value = (array) $current_value;

			$limit = $this->get_repeatable_limit();
			$hide_button = $limit && count( $current_value ) === $limit;

			$this->open_repeatable_wrap();

			$srt_added = false;
			if ( ! in_array( 'screen-reader-text', $this->label_classes, true ) ) {
				$this->label_classes[] = 'screen-reader-text';
				$srt_added = true;
			}

			$this->index = 0;
			foreach ( $current_value as $single_value ) {
				$this->open_repeatable_item_wrap();

				$this->render_label();
				$this->render_single_input( $single_value );

				$this->close_repeatable_item_wrap();

				$this->index++;
			}
			$this->index = null;

			if ( $srt_added ) {
				$key = array_search( 'screen-reader-text', $this->label_classes, true );
				unset( $this->label_classes[ $key ] );
			}

			$this->close_repeatable_wrap();

			$this->render_repeatable_add_button( $hide_button );
		} else {
			$this->render_single_input( $current_value );
		}
	}

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
	public final function validate( $value = null ) {
		if ( $this->is_repeatable() ) {
			if ( empty( $value ) ) {
				return array();
			}

			if ( ! is_array( $value ) ) {
				return new WP_Error( 'field_repeatable_not_array', sprintf( $this->manager->get_message( 'field_repeatable_not_array' ), $this->label ) );
			}

			$validated = array();
			$errors = new WP_Error();
			foreach ( $value as $single_value ) {
				$validated_single = $this->validate_single( $single_value );
				if ( is_wp_error( $validated_single ) ) {
					$errors->add( $validated_single->get_error_code(), $validated_single->get_error_message(), $validated_single->get_error_data() );
					continue;
				}

				$validated[] = $validated_single;
			}

			if ( ! empty( $errors->errors ) ) {
				$error_data = array( 'errors' => $errors->errors );
				if ( ! empty( $validated ) ) {
					$error_data['validated'] = $validated;
				}

				return new WP_Error( 'field_repeatable_has_errors', sprintf( $this->manager->get_message( 'field_repeatable_has_errors' ), $this->label ), $error_data );
			}

			return $validated;
		}

		return $this->validate_single( $value );
	}

	/**
	 * Renders a single input for the field.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param mixed $current_value Current field value.
	 */
	protected abstract function render_single_input( $current_value );

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
		$value = $this->pre_validate_single( $value );
		if ( is_wp_error( $value ) ) {
			return $value;
		}

		return $this->post_validate_single( $value );
	}

	/**
	 * Handles pre-validation of a single value.
	 *
	 * This method returns an error if the value of a required field is empty.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param mixed $value Vaule to handle pre-validation for.
	 * @return mixed|WP_Error The value on success, or an error object on failure.
	 */
	protected function pre_validate_single( $value ) {
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
	protected function post_validate_single( $value ) {
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
	 * Returns the `id` attribute for the field.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string `id` attribute value.
	 */
	protected function get_id_attribute() {
		$id = $this->manager->make_id( $this->id );

		if ( null !== $this->index ) {
			$id .= '-' . ( $this->index + 1 );
		}

		return $id;
	}

	/**
	 * Returns the `name` attribute for the field.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string `name` attribute value.
	 */
	protected function get_name_attribute() {
		$name = $this->manager->make_name( $this->id );

		if ( null !== $this->index ) {
			$name .= '[' . $this->index . ']';
		}

		return $name;
	}

	/**
	 * Opens the wrap for a repeatable field list.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected final function open_repeatable_wrap() {
		if ( ! $this->is_repeatable() ) {
			return;
		}

		$id = $this->get_id_attribute();

		$wrap_attrs = array(
			'id'           => $id . '-repeatable-wrap',
			'class'        => 'plugin-lib-repeatable-wrap plugin-lib-repeatable-'. $this->slug . '-wrap',
			'data-limit'   => $this->get_repeatable_limit(),
		);

		echo '<div' . $this->attrs( $wrap_attrs ) . '>';
	}

	/**
	 * Closes the wrap for a repeatable field list.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected final function close_repeatable_wrap() {
		if ( ! $this->is_repeatable() ) {
			return;
		}

		echo '</div>';
	}

	/**
	 * Opens the wrap for a repeatable field list item.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected final function open_repeatable_item_wrap() {
		if ( ! $this->is_repeatable() ) {
			return;
		}

		$id = $this->get_id_attribute();

		$wrap_attrs = array(
			'id'           => $id . '-repeatable-item',
			'class'        => 'plugin-lib-repeatable-item',
		);

		echo '<div' . $this->attrs( $wrap_attrs ) . '>';
	}

	/**
	 * Closes the wrap for a repeatable field list item.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected final function close_repeatable_item_wrap() {
		if ( ! $this->is_repeatable() ) {
			return;
		}

		echo '</div>';
	}

	/**
	 * Renders a button to add a new item to a repeatable field list.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param bool $hide_button Optional. Whether to initially hide the 'Add' button.
	 *                          Default false.
	 */
	protected final function render_repeatable_add_button( $hide_button = false ) {
		$this->render_repeatable_button( 'add', sprintf( $this->manager->get_message( 'field_repeatable_add_button' ) $this->label ), $hide_button );
	}

	/**
	 * Renders a button to remove an existing item from a repeatable field list.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected final function render_repeatable_remove_button() {
		$this->render_repeatable_button( 'remove', sprintf( $this->manager->get_message( 'field_repeatable_remove_button' ), $this->label ) );
	}

	/**
	 * Renders an add or remove button for a repeatable field.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $mode        Either 'add' or 'remove'.
	 * @param string $message     The message to display on the button.
	 * @param bool   $hide_button Optional. Whether to initially hide the button.
	 *                            Default false.
	 */
	protected final function render_repeatable_button( $mode, $message, $hide_button = false ) {
		if ( ! $this->is_repeatable() ) {
			return;
		}

		$id = $this->get_id_attribute();

		if ( 'remove' === $mode ) {
			$core_class  = 'button-link-delete';
			$target_mode = 'item';
		} else {
			$mode        = 'add';
			$core_class  = 'button';
			$target_mode = 'wrap';
		}

		$button_attrs = array(
			'id'          => $id . '-repeatable-' . $mode . '-button',
			'class'       => 'plugin-lib-repeatable-' . $mode . '-button ' . $core_class,
			'data-target' => $id . '-repeatable-' . $target_mode,
		);
		if ( $hide_button ) {
			$button_attrs['style'] = 'display:none;';
		}

		echo '<button' . $this->attrs( $button_attrs ) . '>' . $message . '</button>';
	}

	/**
	 * Returns whether this field is repeatable.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return bool True if the field is repeatable, false otherwise.
	 */
	protected function is_repeatable() {
		return (bool) $this->repeatable;
	}

	/**
	 * Returns the amount of times the field can be repeated.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return int Repeat limit. Equals 0 if the field is not repeatable or
	 *             if there is no limit set.
	 */
	protected function get_repeatable_limit() {
		if ( is_numeric( $this->repeatable ) ) {
			return absint( $this->repeatable );
		}

		return 0;
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
		$base_label_attrs = array();

		if ( ! $this->is_repeatable() || null !== $this->index ) {
			$base_label_attrs['for'] = $this->get_id_attribute();
		}

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
			'id'   => $this->get_id_attribute(),
			'name' => $this->get_name_attribute(),
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
