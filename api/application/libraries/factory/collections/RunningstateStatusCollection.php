<?php

class RunningstateStatusCollection extends NagiosCollection
{


	protected $_type = 'runningstatestatus';

	protected $_index = array(
		'host_name' => array(),
		'service_description' => array(),
		'current_state' => array(),
		);

}
