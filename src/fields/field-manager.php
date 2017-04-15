<?php
/**
 * Field_Manager class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

use Leaves_And_Love\Plugin_Lib\Service;
use Leaves_And_Love\Plugin_Lib\Assets;
use Leaves_And_Love\Plugin_Lib\Fields\Interfaces\Field_Manager_Interface;
use Leaves_And_Love\Plugin_Lib\Traits\Container_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Traits\Args_Service_Trait;
use WP_Error;
use Exception;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Field_Manager' ) ) :

/**
 * Manager class for fields
 *
 * @since 1.0.0
 */
class Field_Manager extends Service implements Field_Manager_Interface {
	use Container_Service_Trait, Args_Service_Trait;

	/**
	 * Instance ID of this field manager. Used internally.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $instance_id = '';

	/**
	 * Array of fields that are part of this manager, grouped by their `$section`.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $field_instances = array();

	/**
	 * Section lookup map for field identifiers.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $section_lookup = array();

	/**
	 * Array of current values.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $current_values = array();

	/**
	 * Field manager instances.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * Instance count of field managers per prefix.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var array
	 */
	protected static $prefix_count = array();

	/**
	 * Array of registered field types, as `$type => $class_name` pairs.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var array
	 */
	protected static $field_types = array();

	/**
	 * Internal flag whether default types have been registered.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var bool
	 */
	protected static $defaults_registered = false;

	/**
	 * Internal flags for enqueueing field assets.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var array
	 */
	protected static $enqueued = array();

	/**
	 * Internal flags for printing JS templates.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var array
	 */
	protected static $templates_printed = array();

	/**
	 * The AJAX API service definition.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var string
	 */
	protected static $service_ajax = 'Leaves_And_Love\Plugin_Lib\AJAX';

	/**
	 * The Assets API service definition.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var string
	 */
	protected static $service_assets = 'Leaves_And_Love\Plugin_Lib\Assets';

	/**
	 * The Assets API service definition for the library itself.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var string
	 */
	protected static $service_library_assets = 'Leaves_And_Love\Plugin_Lib\Assets';


	/**
	 * Translations to print to the user.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var Leaves_And_Love\Plugin_Lib\Translations\Translations
	 */
	protected static $translations;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string                                                             $prefix       Prefix.
	 * @param array                                                              $services     {
	 *     Array of service instances.
	 *
	 *     @type Leaves_And_Love\Plugin_Lib\AJAX          $ajax          The AJAX API class instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Assets        $assets        The Assets API class instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Error_Handler $error_handler The error handler instance.
	 * }
	 * @param array                                                              $args         {
	 *     Array of arguments.
	 *
	 *     @type callable $get_value_callback         Callback to get current values.
	 *     @type array    $get_value_callback_args    Arguments to pass to the `$get_value_callback`.
	 *                                                A placeholder `{id}` can be used to indicate that
	 *                                                this argument should be replaced by the field ID.
	 *     @type callable $update_value_callback      Callback to update the current values with new ones.
	 *     @type array    $update_value_callback_args Arguments to pass to the `$update_value_callback`.
	 *                                                One of these arguments must be a placeholder `{value}`.
	 *                                                Another placeholder `{id}` can also be used to indicate
	 *                                                that this argument should be replaced by the field ID.
	 *     @type string   $name_prefix                The name prefix to create name attributes for fields.
	 *     @type string   $render_mode                Render mode. Default 'form-table'.
	 * }
	 */
	public function __construct( $prefix, $services, $args ) {
		if ( ! isset( $services['library_assets'] ) ) {
			$services['library_assets'] = Assets::get_library_instance();
		}

		$this->set_prefix( $prefix );
		$this->set_services( $services );
		$this->set_args( $args );

		if ( ! isset( self::$prefix_count[ $prefix ] ) ) {
			self::$prefix_count[ $prefix ] = 1;
		} else {
			self::$prefix_count[ $prefix ]++;
		}

		$this->instance_id = $prefix . self::$prefix_count[ $prefix ];

		self::$instances[ $this->instance_id ] = $this;

		self::register_default_field_types();
	}

	/**
	 * Adds a new field.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $id   Field identifier. Must be unique for this field manager.
	 * @param string $type Identifier of the type.
	 * @param array  $args Optional. Field arguments. See the field class constructor for supported
	 *                     arguments. Default empty array.
	 * @return bool True on success, false on failure.
	 */
	public function add( $id, $type, $args = array() ) {
		if ( ! self::is_field_type_registered( $type ) ) {
			return false;
		}

		if ( isset( $this->section_lookup[ $id ] ) ) {
			return false;
		}

		$section = isset( $args['section'] ) ? $args['section'] : '';

		$class_name = self::get_registered_field_type( $type );
		$field_instance = new $class_name( $this, $id, $args );

		$this->section_lookup[ $id ] = $section;

		if ( ! isset( $this->field_instances[ $section ] ) ) {
			$this->field_instances[ $section ] = array();
		}

		$this->field_instances[ $section ][ $id ] = $field_instance;

		return true;
	}

	/**
	 * Gets a specific field.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $id Field identifier.
	 * @return Leaves_And_Love\Plugin_Lib\Fields\Field|null Field instance, or null if it does not exist.
	 */
	public function get( $id ) {
		if ( ! $this->exists( $id ) ) {
			return null;
		}

		return $this->field_instances[ $this->section_lookup[ $id ] ][ $id ];
	}

	/**
	 * Checks whether a specific field exists.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $id Field identifier.
	 * @return bool True if the field exists, false otherwise.
	 */
	public function exists( $id ) {
		if ( ! isset( $this->section_lookup[ $id ] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Removes an existing field.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $id Field identifier.
	 * @return bool True on success, false on failure.
	 */
	public function remove( $id ) {
		if ( ! $this->exists( $id ) ) {
			return false;
		}

		unset( $this->field_instances[ $this->section_lookup[ $id ] ][ $id ] );
		unset( $this->section_lookup[ $id ] );

		return true;
	}

	/**
	 * Enqueues the necessary assets for a list of fields.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue() {
		if ( $this->enqueued( '_core' ) ) {
			return;
		}

		$main_dependencies = array( 'jquery', 'underscore', 'backbone', 'wp-util' );
		$localize_data     = array(
			'field_managers' => array(),
		);

		$values = $this->get_values();

		$localize_data['field_managers'][ $this->instance_id ] = array(
			'fields' => array(),
		);

		$field_instances = $this->get_fields();

		/** This is run to verify there are no circular dependencies. */
		$this->resolve_dependency_order( $field_instances );

		foreach ( $field_instances as $id => $field_instance ) {
			$type = $field_instance->slug;

			if ( ! $this->enqueued( $type ) ) {
				list( $new_dependencies, $new_localize_data ) = $field_instance->enqueue();

				if ( ! empty( $new_dependencies ) ) {
					$main_dependencies = array_merge( $main_dependencies, $new_dependencies );
				}

				if ( ! empty( $new_localize_data ) ) {
					$localize_data = array_merge_recursive( $localize_data, $new_localize_data );
				}

				$this->enqueued( $type, true );
			}

			$value = isset( $values[ $id ] ) ? $values[ $id ] : $field_instance->default;

			$localize_data['field_managers'][ $this->instance_id ]['fields'][ $id ] = $field_instance->to_json( $value );
		}

		$this->library_assets()->register_style( 'fields', 'assets/dist/css/fields.css', array(
			'ver'     => \Leaves_And_Love_Plugin_Loader::VERSION,
			'enqueue' => true,
		) );

		$this->library_assets()->register_script( 'fields', 'assets/dist/js/fields.js', array(
			'deps'          => $main_dependencies,
			'ver'           => \Leaves_And_Love_Plugin_Loader::VERSION,
			'in_footer'     => true,
			'enqueue'       => true,
			'localize_name' => 'pluginLibFieldsAPIData',
			'localize_data' => $localize_data,
		) );

		if ( is_admin() ) {
			add_action( 'admin_footer', array( $this, 'print_templates' ), 1, 0 );
		} else {
			add_action( 'wp_footer', array( $this, 'print_templates' ), 1, 0 );
		}

		$this->enqueued( '_core', true );
	}

	/**
	 * Checks whether dependencies for a specific type have been enqueued.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string    $type Type to check for.
	 * @param bool|null $set  Optional. A boolean in case the value should be set. Default null.
	 * @return bool True if the dependencies have been enqueued at the time of calling the function,
	 *              false otherwise.
	 */
	public function enqueued( $type, $set = null ) {
		$result = isset( self::$enqueued[ $type ] ) && self::$enqueued[ $type ];
		if ( null !== $set ) {
			self::$enqueued[ $type ] = (bool) $set;
		}

		return $result;
	}

	/**
	 * Prints field templates for JavaScript.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function print_templates() {
		if ( isset( self::$templates_printed['_core'] ) && self::$templates_printed['_core'] ) {
			return;
		}

		foreach ( $this->get_fields() as $id => $field_instance ) {
			$type = $field_instance->slug;

			if ( isset( self::$templates_printed[ $type ] ) && self::$templates_printed[ $type ] ) {
				continue;
			}

			?>
			<script type="text/html" id="tmpl-plugin-lib-field-<?php echo $field_instance->slug; ?>-label">
				<?php echo $field_instance->print_label_template(); ?>
			</script>
			<script type="text/html" id="tmpl-plugin-lib-field-<?php echo $field_instance->slug; ?>-content">
				<?php echo $field_instance->print_content_template(); ?>
			</script>
			<script type="text/html" id="tmpl-plugin-lib-field-<?php echo $field_instance->slug; ?>-repeatable-item">
				<?php echo $field_instance->print_repeatable_item_template(); ?>
			</script>
			<?php

			self::$templates_printed[ $type ] = true;
		}

		self::$templates_printed['_core'] = true;
	}

	/**
	 * Renders a list of fields.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string|array|null $sections        Optional. Section identifier(s), to only render
	 *                                           fields that belong to this section. Default null.
	 * @param callable|null     $render_callback Optional. Callback to use for rendering a single
	 *                                           field. It will be passed the field instance and
	 *                                           the field's current value. Default is the callback
	 *                                           specified through the class' $render_mode argument.
	 */
	public function render( $sections = null, $render_callback = null ) {
		$field_instances = $this->get_fields( $sections );

		if ( ! $render_callback || ! is_callable( $render_callback ) ) {
			switch ( $this->render_mode ) {
				case 'form-table':
				default:
					$render_callback = array( $this, 'render_form_table_row' );
			}
		}

		$values = $this->get_values();

		foreach ( $field_instances as $id => $field_instance ) {
			$value = isset( $values[ $id ] ) ? $values[ $id ] : $field_instance->default;

			call_user_func( $render_callback, $field_instance, $value );
		}
	}

	/**
	 * Gets the current values for all fields of this manager.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Array of values as `$id => $current_value` pairs.
	 */
	public function get_values() {
		if ( empty( $this->current_values ) ) {
			$field_instances = $this->get_fields();

			$id_key = array_search( '{id}', $this->get_value_callback_args, true );
			if ( false !== $id_key ) {
				$values = array();

				foreach ( $field_instances as $id => $field_instance ) {
					$args = $this->get_value_callback_args;
					$args[ $id_key ] = $id;

					$values[ $id ] = call_user_func_array( $this->get_value_callback, $args );
				}

				$this->current_values = $values;
			} else {
				$this->current_values = call_user_func_array( $this->get_value_callback, $this->get_value_callback_args );
			}
		}

		return $this->current_values;
	}

	/**
	 * Updates the current values for a list of fields.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array             $values   New values to validate and store, as `$id => $new_value` pairs.
	 * @param string|array|null $sections Optional. Section identifier(s), to only update values for
	 *                                    fields that belong to this section. Default null.
	 * @return bool|WP_Error True on success, or an error object if some fields produced validation errors.
	 *                       All fields that are not part of this error object have been updated successfully.
	 */
	public function update_values( $values, $sections = null ) {
		$field_instances = $this->get_fields( $sections );
		$field_instances = $this->resolve_dependency_order( $field_instances );

		$validated_values = $this->get_values();

		$errors = new WP_Error();

		$value_key = array_search( '{value}', $this->update_value_callback_args, true );

		$id_key = array_search( '{id}', $this->update_value_callback_args, true );
		if ( false !== $id_key ) {
			foreach ( $field_instances as $id => $field_instance ) {
				$validated_value = $this->validate_value( $field_instance, $values, $errors );
				if ( is_wp_error( $validated_value ) ) {
					continue;
				}

				$this->current_values[ $id ] = $validated_value;

				$args = $this->update_value_callback_args;
				$args[ $id_key ] = $id;
				$args[ $value_key ] = $validated_value;

				call_user_func_array( $this->update_value_callback, $args );
			}
		} else {
			foreach ( $field_instances as $id => $field_instance ) {
				$validated_value = $this->validate_value( $field_instance, $values, $errors );
				if ( is_wp_error( $validated_value ) ) {
					continue;
				}

				$this->current_values[ $id ] = $validated_value;

				$validated_values[ $id ] = $validated_value;
			}

			$args = $this->update_value_callback_args;
			$args[ $value_key ] = $validated_values;

			call_user_func_array( $this->update_value_callback, $args );
		}

		if ( ! empty( $errors->errors ) ) {
			return $errors;
		}

		return true;
	}

	/**
	 * Returns an array of fields that are part of this field manager.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string|array|null $sections Optional. Section identifier(s), to only return
	 *                                    fields that belong to this section. Default null.
	 * @return array Array of fields as `$id => $instance` pairs.
	 */
	public function get_fields( $sections = null ) {
		if ( null !== $sections ) {
			$sections = (array) $sections;
		} else {
			$sections = array_keys( $this->field_instances );
		}

		$all_field_instances = array();
		foreach ( $this->field_instances as $section => $field_instances ) {
			if ( ! in_array( $section, $sections, true ) ) {
				continue;
			}

			$all_field_instances = array_merge( $all_field_instances, $field_instances );
		}

		return $all_field_instances;
	}

	/**
	 * Creates the id attribute for a given field identifier.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string          $id    Field identifier.
	 * @param int|string|null $index Optional. Index of the field, in case it is a repeatable field.
	 *                               Default null.
	 * @return string Field id attribute.
	 */
	public function make_id( $id, $index = null ) {
		$field_id = str_replace( '_', '-', $id );

		$instance_id = $this->get_instance_id();
		if ( $instance_id ) {
			$field_id = $instance_id . '_' . $field_id;
		}

		if ( null !== $index ) {
			if ( '%index%' === $index ) {
				$field_id .= '-%indexPlus1%';
			} else {
				$field_id .= '-' . ( $index + 1 );
			}
		}

		return $field_id;
	}

	/**
	 * Creates the name attribute for a given field identifier.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string          $id    Field identifier.
	 * @param int|string|null $index Optional. Index of the field, in case it is a repeatable field.
	 *                               Default null.
	 * @return string Field name attribute.
	 */
	public function make_name( $id, $index ) {
		$name_prefix = $this->name_prefix;

		$field_name = $id;
		if ( ! empty( $this->name_prefix ) ) {
			$field_name = $this->name_prefix . '[' . $field_name . ']';
		}

		if ( null !== $this->index ) {
			$field_name .= '[' . $this->index . ']';
		}

		return $field_name;
	}

	/**
	 * Returns the ID of this instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string|null Instance ID.
	 */
	public function get_instance_id() {
		return $this->instance_id;
	}

	/**
	 * Returns a specific manager message.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $identifier Identifier for the message.
	 * @param bool   $noop       Optional. Whether this is a noop message. Default false.
	 * @return string|array Translated message, or array if $noop, or empty string if
	 *                      invalid identifier.
	 */
	public function get_message( $identifier, $noop = false ) {
		return self::$translations->get( $identifier, $noop );
	}

	/**
	 * Renders a field in form table mode.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Leaves_And_Love\Plugin_Lib\Fields\Field $field Field instance.
	 * @param mixed                                   $value Current field value.
	 */
	protected function render_form_table_row( $field, $value ) {
		?>
		<tr<?php echo $field->get_wrap_attrs(); ?>>
			<th scope="row">
				<?php $field->render_label(); ?>
			</th>
			<td>
				<?php $field->render_content( $value ); ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Validates a value.
	 *
	 * The $errors object passed will automatically receive any occurring errors.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Leaves_And_Love\Plugin_Lib\Fields\Field $field  Field instance.
	 * @param array                                   $values Array of all values to validate.
	 * @param WP_Error                                $errors Error object to possibly fill.
	 * @return mixed|WP_Error Validated value on success, error object on failure.
	 */
	protected function validate_value( $field, $values, $errors ) {
		$value = isset( $values[ $field->id ] ) ? $values[ $field->id ] : null;

		$validated_value = $field->validate( $value );
		if ( is_wp_error( $validated_value ) ) {
			$error = $validated_value;
			$error_data = $error->get_error_data();
			if ( isset( $error_data['validated'] ) ) {
				$validated_value = $error_data['validated'];
			}

			$errors->add( $error->get_error_code(), $error->get_error_message() );
		}

		return $validated_value;
	}

	/**
	 * Sorts field instances by their dependencies so that those can be resolved in the correct order.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $field_instances Array of field instances.
	 * @return array Array of field instances sorted by their dependencies.
	 */
	protected function resolve_dependency_order( $field_instances ) {
		$resolved = array();

		foreach ( $field_instances as $id => $field_instance ) {
			$resolved = $this->resolve_dependency_order_for_instance( $field_instance, $field_instances, $resolved, array() );
		}

		return $resolved;
	}

	/**
	 * Recursive helper method for sorting field instances by their dependencies.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Leaves_And_Love\Plugin_Lib\Fields\Field $field_instance Field instance to recursively add its dependencies and itself.
	 * @param array                                   $all_instances  All field instances in the collection to sort.
	 * @param array                                   $resolved       Results array to append to.
	 * @param array                                   $queued_ids     Array of field identifiers that are currently queued for appending.
	 *                                                                This allows to detect circular dependencies.
	 * @return array Modified results array.
	 */
	protected function resolve_dependency_order_for_instance( $field_instance, $all_instances, $resolved, $queued_ids ) {
		if ( isset( $resolved[ $field_instance->id ] ) ) {
			return $resolved;
		}

		$dependency_resolver = $field_instance->dependency_resolver;
		if ( ! $dependency_resolver ) {
			$resolved[ $field_instance->id ] = $field_instance;
			return $resolved;
		}

		$dependency_ids = $dependency_resolver->get_dependency_field_identifiers();
		if ( empty( $dependency_ids ) ) {
			$resolved[ $field_instance->id ] = $field_instance;
			return $resolved;
		}

		$queued_ids[] = $field_instance->id;

		foreach ( $dependency_ids as $dependency_id ) {
			if ( ! isset( $all_instances[ $dependency_id ] ) ) {
				continue;
			}

			if ( in_array( $dependency_id, $queued_ids, true ) ) {
				throw new Exception( sprintf( 'Circular dependency detected in plugin-lib between fields &#8220;%1$s&#8221; and &#8220;%2$s&#8221;!', $field_instance->id, $dependency_id ) );
			}

			$resolved = $this->resolve_dependency_order_for_instance( $all_instances[ $dependency_id ], $all_instances, $resolved, $queued_ids );
		}

		$resolved[ $field_instance->id ] = $field_instance;

		return $resolved;
	}

	/**
	 * Registers a field type.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param string $type       Identifier of the type.
	 * @param string $class_name Name of the field type class.
	 * @return bool True on success, false on failure.
	 */
	public static function register_field_type( $type, $class_name ) {
		self::register_default_field_types();

		if ( self::is_field_type_registered( $type ) ) {
			return false;
		}

		// Do not allow registration of an existing class as a different type.
		if ( in_array( $class_name, self::$field_types, true ) ) {
			return false;
		}

		self::$field_types[ $type ] = $class_name;

		return true;
	}

	/**
	 * Retrieves the class name for a registered field type.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param string $type Identifier of the type.
	 * @return string Class name, or empty string if the type is not registered.
	 */
	public static function get_registered_field_type( $type ) {
		self::register_default_field_types();

		if ( ! self::is_field_type_registered( $type ) ) {
			return '';
		}

		return self::$field_types[ $type ];
	}

	/**
	 * Checks whether a field type is registered.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param string $type Identifier of the type.
	 * @return bool True if the type is registered, false otherwise.
	 */
	public static function is_field_type_registered( $type ) {
		self::register_default_field_types();

		return isset( self::$field_types[ $type ] );
	}

	/**
	 * Unregisters a field type.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param string $type Identifier of the type.
	 * @return bool True on success, false on failure.
	 */
	public static function unregister_field_type( $type ) {
		self::register_default_field_types();

		if ( ! self::is_field_type_registered( $type ) ) {
			return false;
		}

		unset( self::$field_types[ $type ] );

		return true;
	}

	/**
	 * Registers the default field types.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 */
	protected static function register_default_field_types() {
		if ( self::$defaults_registered ) {
			return;
		}

		self::$defaults_registered = true;

		$default_field_types = array(
			'text'        => 'Leaves_And_Love\Plugin_Lib\Fields\Text',
			'email'       => 'Leaves_And_Love\Plugin_Lib\Fields\Email',
			'url'         => 'Leaves_And_Love\Plugin_Lib\Fields\URL',
			'textarea'    => 'Leaves_And_Love\Plugin_Lib\Fields\Textarea',
			'wysiwyg'     => 'Leaves_And_Love\Plugin_Lib\Fields\WYSIWYG',
			'number'      => 'Leaves_And_Love\Plugin_Lib\Fields\Number',
			'range'       => 'Leaves_And_Love\Plugin_Lib\Fields\Range',
			'checkbox'    => 'Leaves_And_Love\Plugin_Lib\Fields\Checkbox',
			'select'      => 'Leaves_And_Love\Plugin_Lib\Fields\Select',
			'multiselect' => 'Leaves_And_Love\Plugin_Lib\Fields\Multiselect',
			'radio'       => 'Leaves_And_Love\Plugin_Lib\Fields\Radio',
			'multibox'    => 'Leaves_And_Love\Plugin_Lib\Fields\Multibox',
			'datetime'    => 'Leaves_And_Love\Plugin_Lib\Fields\Datetime',
			'color'       => 'Leaves_And_Love\Plugin_Lib\Fields\Color',
			'media'       => 'Leaves_And_Love\Plugin_Lib\Fields\Media',
			'map'         => 'Leaves_And_Love\Plugin_Lib\Fields\Map',
			'group'       => 'Leaves_And_Love\Plugin_Lib\Fields\Group',
		);

		foreach ( $default_field_types as $type => $class_name ) {
			self::register_field_type( $type, $class_name );
		}
	}

	/**
	 * Sets the translations instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Leaves_And_Love\Plugin_Lib\Translations\Translations $translations Translations instance.
	 */
	public static function set_translations( $translations ) {
		self::$translations = $translations;
	}

	/**
	 * Gets an option.
	 *
	 * Default callback used for the `$get_value_callback` argument.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 *
	 * @param string $id Field identifier.
	 * @return mixed Current value, or null if not set.
	 */
	protected static function get_option( $id ) {
		return get_option( $id, null );
	}

	/**
	 * Updates an option.
	 *
	 * Default callback used for the `$update_value_callback` argument.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 *
	 * @param string $id    Field identifier.
	 * @param mixed  $value New value to set.
	 * @return bool True on success, false on failure.
	 */
	protected static function update_option( $id, $value ) {
		return update_option( $id, $value );
	}

	/**
	 * Parses the get value callback.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 *
	 * @param mixed $value The input value.
	 * @return callable The parsed value.
	 */
	protected static function parse_arg_get_value_callback( $value ) {
		if ( ! is_callable( $value ) ) {
			return array( __CLASS__, 'get_option' );
		}

		return $value;
	}

	/**
	 * Parses the get value callback args.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 *
	 * @param mixed $value The input value.
	 * @return array The parsed value.
	 */
	protected static function parse_arg_get_value_callback_args( $value ) {
		if ( ! is_array( $value ) ) {
			return array( '{id}' );
		}

		return $value;
	}

	/**
	 * Parses the update value callback.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 *
	 * @param mixed $value The input value.
	 * @return callable The parsed value.
	 */
	protected static function parse_arg_update_value_callback( $value ) {
		if ( ! is_callable( $value ) ) {
			return array( __CLASS__, 'update_option' );
		}

		return $value;
	}

	/**
	 * Parses the update value callback args.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 *
	 * @param mixed $value The input value.
	 * @return array The parsed value.
	 */
	protected static function parse_arg_update_value_callback_args( $value ) {
		if ( ! is_array( $value ) ) {
			return array( '{id}', '{value}' );
		}

		/* A '{value}' element must always be present. This is the worst way to verify it. */
		if ( ! in_array( '{value}', $value, true ) ) {
			$value[] = '{value}';
		}

		return $value;
	}

	/**
	 * Parses the name prefix.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 *
	 * @param mixed $value The input value.
	 * @return string The parsed value.
	 */
	protected static function parse_arg_name_prefix( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		return $value;
	}

	/**
	 * Parses the render mode.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 *
	 * @param mixed $value The input value.
	 * @return string The parsed value.
	 */
	protected static function parse_arg_render_mode( $value ) {
		$valid_modes = array( 'form-table' );

		if ( ! is_string( $value ) || ! in_array( $value, $valid_modes, true ) ) {
			return 'form-table';
		}

		return $value;
	}
}

endif;
