<?php
/**
 * Trait for models that support types
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Traits;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\Traits\Type_Model' ) ) :

/**
 * Trait for models.
 *
 * Include this trait for models that support types.
 *
 * @since 1.0.0
 */
trait Type_Model {
	/**
	 * The type property of the model.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $type_property = 'type';

	/**
	 * Returns the name of the type property that identifies the model.
	 *
	 * This is usually the unique type identifier.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Name of the primary property.
	 */
	public function get_type_property() {
		return $this->type_property;
	}

	/**
	 * Returns the type object for the model's type.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Leaves_And_Love\Plugin_Lib\MVC\Model_Type|null Type object, or null it it does not exist.
	 */
	public function get_type_object() {
		$type_property = $this->type_property;

		return $this->manager->get_type( $this->$type_property );
	}
}

endif;
