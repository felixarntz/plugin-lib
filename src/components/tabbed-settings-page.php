<?php
/**
 * Tabbed settings page class
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Components;

use Leaves_And_Love\Plugin_Lib\Fields\Field_Manager;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Components\Tabbed_Settings_Page' ) ) :

/**
 * Class for a tabbed settings page
 *
 * This class represents a settings menu page with tabs in the admin.
 *
 * @since 1.0.0
 */
abstract class Tabbed_Settings_Page extends Settings_Page {
	/**
	 * Array of tabs as `$id => $args` pairs.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $tabs = array();

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

		/* The default field manager for the entire page is not required. */
		$this->field_manager = null;
	}

	/**
	 * Adds a tab to the settings page.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $id   tab identifier.
	 * @param array  $args {
	 *     Optional. tab arguments.
	 *
	 *     @type string $title       tab title.
	 *     @type string $description tab description. Default empty.
	 * }
	 */
	public function add_tab( $id, $args = array() ) {
		$this->tabs[ $id ] = wp_parse_args( $args, array(
			'title'       => '',
			'description' => '',
		) );

		$this->tabs[ $id ]['field_manager'] = new Field_Manager( $this->manager->get_prefix(), array(
			'assets'        => $this->manager->assets(),
			'error_handler' => $this->manager->error_handler(),
		), array(
			'get_value_callback_args'    => array( $id ),
			'update_value_callback_args' => array( $id, '{value}' ),
			'name_prefix'                => $id,
		) );
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
	 *     @type string $tab         Identifier of the tab this section should belong to.
	 * }
	 */
	public function add_section( $id, $args = array() ) {
		$this->sections[ $id ] = wp_parse_args( $args, array(
			'title'       => '',
			'description' => '',
			'tab'         => '',
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
		if ( ! isset( $args['section'] ) ) {
			return;
		}

		if ( ! isset( $this->sections[ $args['section'] ] ) ) {
			return;
		}

		if ( ! isset( $this->tabs[ $this->sections[ $args['section'] ]['tab'] ] ) ) {
			return;
		}

		$tab_args = $this->tabs[ $this->sections[ $args['section'] ]['tab'] ];
		$tab_args['field_manager']->add( $id, $type, $args );
	}

	/**
	 * Enqueues assets to load on the page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue_assets() {
		$current_tab_id = $this->get_current_tab();

		$this->tabs[ $current_tab_id ]['field_manager']->enqueue();
	}

	/**
	 * Renders the settings page content.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function render() {
		$current_tab_id = $this->get_current_tab();

		$this->current_values = $this->tabs[ $current_tab_id ]['field_manager']->get_values();

		?>
		<div class="wrap">
			<?php $this->render_header(); ?>

			<?php $this->render_tab_navigation( $current_tab_id ); ?>

			<?php $this->render_tab_header( $current_tab_id ); ?>

			<?php $this->render_form( $current_tab_id ); ?>
		</div>
		<?php
	}

	/**
	 * Registers the settings, tabs, sections and fields for this page in WordPress.
	 *
	 * This method is only meant for internal usage.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register() {
		foreach ( $this->tabs as $id => $tab_args ) {
			register_setting( $id, $id, array( $this, 'validate' ) );

			foreach ( $tab_args['field_manager']->get_fields() as $field ) {
				add_settings_field( $field->id, $field->label, array( $this, 'render_field' ), $id, $field->section, array(
					'label_for'      => $this->field_manager->make_id( $field->id ),
					'field_instance' => $field,
				) );
			}
		}

		foreach ( $this->sections as $id => $section_args ) {
			add_settings_section( $id, $section_args['title'], array( $this, 'render_section_description' ), $section_args['tab'] );
		}
	}

	/**
	 * Validates the settings for the current tab.
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
		if ( ! isset( $this->tabs[ $option ] ) ) {
			return null;
		}

		return $this->validate_values( $values, $option, $this->tabs[ $option ]['field_manager']->get_fields() );
	}

	/**
	 * Renders the tab navigation.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $current_tab_id Identifier of the current tab.
	 */
	protected function render_tab_navigation( $current_tab_id ) {
		?>
		<h2 class="nav-tab-wrapper">
			<?php foreach ( $this->tabs as $tab_id => $tab_args ) : ?>
				<a class="nav-tab<?php echo $tab_id === $current_tab_id ? ' nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( 'tab', $tab_id ); ?>">
					<?php echo $tab_args['title']; ?>
				</a>
			<?php endforeach; ?>
		</h2>
		<?php
	}

	/**
	 * Renders the tab header.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $current_tab_id Identifier of the current tab.
	 */
	protected function render_tab_header( $current_tab_id ) {
		if ( ! empty( $this->tabs[ $current_tab_id ]['description'] ) ) : ?>
			<p class="description">
				<?php echo $this->tabs[ $current_tab_id ]['description']; ?>
			</p>
		<?php endif;
	}

	/**
	 * Returns the identifier of the current tab.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string Identifier of the current tab.
	 */
	protected function get_current_tab() {
		if ( isset( $_GET['tab'] ) && isset( $this->tabs[ $_GET['tab'] ] ) ) {
			return $_GET['tab']
		}

		return key( $this->tabs );
	}

	/**
	 * Adds tabs, sections and fields to this page.
	 *
	 * This method should call the methods `add_tab()`, `add_section()` and `add_field()` to
	 * populate the page.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected abstract function add_page_content;
}

endif;
