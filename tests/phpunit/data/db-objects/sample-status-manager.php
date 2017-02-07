<?php

namespace Leaves_And_Love\Sample_DB_Objects;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Status_Manager;

class Sample_Status_Manager extends Model_Status_Manager {
	public function __construct( $prefix ) {
		parent::__construct( $prefix );

		$this->model_status_class_name = 'Leaves_And_Love\Sample_DB_Objects\Sample_Status';
	}
}
