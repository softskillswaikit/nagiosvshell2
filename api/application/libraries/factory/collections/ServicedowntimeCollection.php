<?php

class ServicedowntimeCollection extends NagiosCollection
{


	protected $_type = 'servicedowntime';

	protected $_index = array(
		'host_name'	=> array(),
		'service_description' => array(),
		);


}