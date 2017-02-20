<?php
/**
 * Trait for managers that support dates
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Traits;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Date_Manager_Trait' ) ) :

/**
 * Trait for managers.
 *
 * Include this trait for managers that support dates.
 *
 * @since 1.0.0
 */
trait Date_Manager_Trait {
	/**
	 * The date property of the model.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $date_property = 'date';

	/**
	 * Array of secondary date properties in the model.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $secondary_date_properties = array();

	/**
	 * Returns the name of the date property in a model.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Name of the date property.
	 */
	public function get_date_property() {
		return $this->date_property;
	}

	/**
	 * Returns the names for any secondary date properties, if any.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Array of secondary date properties.
	 */
	public function get_secondary_date_properties() {
		return $this->secondary_date_properties;
	}
}

endif;
