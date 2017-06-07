<?php

class Heartbeatstatus extends NagiosObject
{

	protected static $_count;

	protected $_type = 'heartbeatstatus';

	protected $_index = array(
		'host_name' => array(),
		'service_description' => array(),
		'current_state' => array(),
		);



}
