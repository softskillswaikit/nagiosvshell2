<?php

class HostresourceCollection extends NagiosCollection
{


	protected $_type = 'hostresource';

	protected $_index = array(
		'host_name' => array(),
		'service_description' => array()
		);

}
