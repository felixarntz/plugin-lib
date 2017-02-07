<?php
/**
 * Trait for managers that support authors
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Traits;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Author_Manager_Trait' ) ) :

/**
 * Trait for managers.
 *
 * Include this trait for managers that support authors.
 *
 * @since 1.0.0
 */
trait Author_Manager_Trait {
	/**
	 * The author property of the model.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $author_property = 'author';

	/**
	 * Returns the name of the author property in a model.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Name of the author property.
	 */
	public function get_author_property() {
		return $this->author_property;
	}
}

endif;
