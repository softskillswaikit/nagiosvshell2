<?php

class Servicedowntime extends NagiosObject
{

	protected static $_count;

	protected $_type = 'servicedowntime';

	protected $_index = array(
		'host_name'	=> array(),
		'service_description' => array(),
		);


}