<?php

class HostresourceStatusCollection extends NagiosCollection
{


	protected $_type = 'hostresourcestatus';

	protected $_index = array(
		'host_name' => array(),
		'service_description' => array(),
		'current_state' => array()
		);

}
