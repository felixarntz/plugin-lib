<?php

namespace Leaves_And_Love\Sample_MVC;

use Leaves_And_Love\Plugin_Lib\MVC\Manager;
use Leaves_And_Love\Plugin_Lib\Traits\Sitewide_Manager;
use Leaves_And_Love\Plugin_Lib\Traits\Meta_Manager;
use Leaves_And_Love\Plugin_Lib\Traits\Type_Manager;

class Sample_Manager extends Manager {
	use Sitewide_Manager, Meta_Manager, Type_Manager;

	protected $name = '';

	public function __construct( $db, $cache, $messages, $meta = null, $type_manager = null, $name = '' ) {
		parent::__construct( $db, $cache, $messages, $meta, $type_manager );

		$this->name = $name;

		$this->class_name            = 'Leaves_And_Love\Sample_MVC\Sample';
		$this->collection_class_name = 'Leaves_And_Love\Sample_MVC\Sample_Collection';
		$this->query_class_name      = 'Leaves_And_Love\Sample_MVC\Sample_Query';

		$this->table_name  = $name . 's';
		$this->cache_group = $name . 's';
		$this->meta_type   = $name;
	}

	public function get_sample_name() {
		return $this->name;
	}
}
