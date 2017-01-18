<?php

namespace Leaves_And_Love\Sample_DB_Objects;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Manager;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Meta_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Type_Manager_Trait;

class Sample_Manager extends Manager {
	use Meta_Manager_Trait, Type_Manager_Trait;

	protected $name = '';

	public function __construct( $prefix, $services, $translations, $name = '' ) {
		parent::__construct( $prefix, $services, $translations );

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
