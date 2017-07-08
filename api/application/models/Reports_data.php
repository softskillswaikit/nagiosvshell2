<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Reports_data extends CI_Model
{
	//array of data taken from nagios log file
	protected $_nagios_log = array();

	//variable used to store data from nagios log file
	//adapted from: nagios_data.php
	//location: ./application/models/nagios_data.php
	protected $_time_collection;
	protected $_logtype_collection;
	protected $_hostname_collection;
	protected $_servicename_collection;
	protected $_state_collection;
	protected $_state_type_collection;
	protected $_retry_count_collection;
	protected $_messages_collection;

	protected $properties = array(
		'time' => '',
		'logtype' => '',
		'hostname' => '',
		'servicename' => '',
		'state' => '',
		'state_type' => '',
		'retry_count' => '',
		'messages' => ''
	);

	//constructor
	public function __construct()
	{
		parent::__construct();
		
		//opening the nagios log file
		$logfile = fopen("../../../../nagios/var/nagios.log", "r") or die("Unable to open file!");
		
		//array counter
		$i = 0;
		while(! foef($logfile) )
		{
			$_nagios_log[i] = fgets($logfile);
			i++;
		}

		//calling the function _map_collections()
		$this->_map_collections();

		fclose($logfile);
	}

	public function get_events_log()
	{
		//array for properties array
		$properties_array = array();

		//arrays for each line of data
		//$report_data[0] is the time
		//$report_data[1] is the messages
		$report_data = array();

		for($j = 0; $j < count($this->_nagios_log); $j++)
		{
			$report_data = explode(' ', $_nagios_log[i], 2);
			$unixtime = $report_data[0];
			$messages = $report_data[1];
			$properties['time'] = unixtime_convert($unixtime);
			$properties['messages'] = $messages;
			$properties_array[j] = json_encode($properties);
		}

		return $properties_array;
	}

	//adapted from: nagios_data.php
	//location: ./application/models/nagios_data.php
	private function _map_collections() 
	{
		$this->properties['time'] => &$this->_time_collection;
		$this->properties['logtype'] => &$this->_logtype_collection;
		$this->properties['hostname'] => &$this->_hostname_collection;
		$this->properties['servicename'] => &$this->_servicename_collection;
		$this->properties['state'] => &$this->_state_collection;
		$this->properties['state_type'] => &$this->_state_type_collection;
		$this->properties['retry_count'] => &$this->_retry_count_collection;
		$this->properties['messages'] => &$this->_messages_collection;
	}

	//function to convert unix timestamp to localtime
	private function unixtime_convert($unixtime)
	{
		//remove any non-numeric character
		$new_unixtime = preg_replace('/\D/', '', $unixtime);

		return date('M d Y H:i:s', strtotime($new_unixtime));
	}
}

?>