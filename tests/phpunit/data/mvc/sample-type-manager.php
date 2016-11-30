<?php

namespace Leaves_And_Love\Sample_MVC;

use Leaves_And_Love\Plugin_Lib\MVC\Model_Type_Manager;

class Sample_Type_Manager extends Model_Type_Manager {
	public function __construct( $prefix ) {
		parent::__construct( $prefix );

		$this->model_type_class_name = 'Leaves_And_Love\Sample_MVC\Sample_Type';
	}
}
