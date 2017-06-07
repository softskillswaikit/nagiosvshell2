<?php

class HeartbeatCollection extends NagiosCollection
{


	protected $_type = 'heartbeat';

	protected $_index = array(
		'host_name' => array(),
		'service_description' => array()
		);

}
