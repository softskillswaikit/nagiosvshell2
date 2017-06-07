<?php

class Hostresourcestatus extends NagiosObject
{

	protected static $_count;

	protected $_type = 'hostresourcestatus';

	protected $_index = array(
		'host_name' => array(),
		'service_description' => array(),
		'current_state' => array()
		);

}
