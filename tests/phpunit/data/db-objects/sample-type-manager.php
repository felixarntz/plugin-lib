<?php

namespace Leaves_And_Love\Sample_DB_Objects;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Manager;

class Sample_Type_Manager extends Model_Type_Manager {
	public function __construct( $prefix, $services ) {
		parent::__construct( $prefix, $services );

		$this->model_type_class_name = 'Leaves_And_Love\Sample_DB_Objects\Sample_Type';
	}
}
