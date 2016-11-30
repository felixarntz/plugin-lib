<?php

namespace Leaves_And_Love\Sample_MVC;

use Leaves_And_Love\Plugin_Lib\MVC\Model;
use Leaves_And_Love\Plugin_Lib\Traits\Sitewide_Model;
use Leaves_And_Love\Plugin_Lib\Traits\Type_Model;

class Sample extends Model {
	use Sitewide_Model, Type_Model;

	protected $id = 0;

	protected $type = '';

	protected $title = '';

	protected $content = '';

	protected $parent_id = 0;

	public function __construct( $manager, $db_obj = null ) {
		parent::__construct( $manager, $db_obj );

		$this->primary_property = 'id';
		$this->type_property    = 'type';
	}
}
