<?php

class Test_Service_Class extends Leaves_And_Love\Plugin_Lib\Service {
	protected $service_cache;
	protected $service_options;

	public function __construct( $prefix, $services ) {
		$this->set_prefix( $prefix );
		$this->set_services( $services );
	}
}
