<?php

class Hostdowntime extends NagiosObject
{

	protected static $_count;

	protected $_type = 'hostdowntime';

	protected $_index = array(
		'host_name'	=> array(),
		);


}