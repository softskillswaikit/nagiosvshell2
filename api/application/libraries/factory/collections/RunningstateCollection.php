<?php

class RunningstateCollection extends NagiosCollection
{


	protected $_type = 'runningstate';

	protected $_index = array(
		'host_name' => array(),
		'service_description' => array()
		);

}
