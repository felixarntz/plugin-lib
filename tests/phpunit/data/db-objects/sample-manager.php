<?php

namespace Leaves_And_Love\Sample_DB_Objects;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Manager;
use Leaves_And_Love\Plugin_Lib\Traits\Sitewide_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\Traits\Meta_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\Traits\Type_Manager_Trait;

class Sample_Manager extends Manager {
	use Sitewide_Manager_Trait, Meta_Manager_Trait, Type_Manager_Trait;

	protected $name = '';

	public function __construct( $db, $cache, $messages, $additional_services = array(), $name = '' ) {
		parent::__construct( $db, $cache, $messages, $additional_services );

		$this->name = $name;

		$this->class_name            = 'Leaves_And_Love\Sample_DB_Objects\Sample';
		$this->collection_class_name = 'Leaves_And_Love\Sample_DB_Objects\Sample_Collection';
		$this->query_class_name      = 'Leaves_And_Love\Sample_DB_Objects\Sample_Query';

		$this->table_name  = $name . 's';
		$this->cache_group = $name . 's';
		$this->meta_type   = $name;
	}

	public function get_sample_name() {
		return $this->name;
	}
}
