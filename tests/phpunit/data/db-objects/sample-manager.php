<?php

namespace Leaves_And_Love\Sample_DB_Objects;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Manager;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Meta_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Title_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Type_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Status_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Author_Manager_Trait;

class Sample_Manager extends Manager {
	use Meta_Manager_Trait, Type_Manager_Trait, Status_Manager_Trait, Author_Manager_Trait;

	protected $name = '';

	public function __construct( $prefix, $services, $translations, $name = '' ) {
		parent::__construct( $prefix, $services, $translations );

		$this->name = $name;

		$this->class_name            = 'Leaves_And_Love\Sample_DB_Objects\Sample';
		$this->collection_class_name = 'Leaves_And_Love\Sample_DB_Objects\Sample_Collection';
		$this->query_class_name      = 'Leaves_And_Love\Sample_DB_Objects\Sample_Query';

		$this->singular_slug = $name;
		$this->plural_slug   = 'y' === substr( $name, -1 ) ? substr( $name, 0, -1 ) . 'ies' : $name . 's';

		$this->table_name       = $this->plural_slug;
		$this->cache_group      = $this->plural_slug;
		$this->meta_type        = $this->singular_slug;
		$this->primary_property = 'id';
		$this->title_property   = 'title';
		$this->type_property    = 'type';
		$this->status_property  = 'status';
		$this->author_property  = 'author_id';
	}

	public function get_sample_name() {
		return $this->name;
	}
}
