<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
class Reports_data extends CI_Model
{
	//array of data taken from nagios log file
	protected $_nagios_log = array();
	//variable used to store data from nagios log file
	//adapted from: nagios_data.php
	//location: ./application/models/nagios_data.php
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
	//array for properties array
	protected $properties_array = array();
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
			$_nagios_log[$i] = fgets($logfile);
			$i++;
		}
		fclose($logfile);
	}
	public function get_events_log($date_selected)
	{
		//arrays for each line of data
		//$event_data[0] is the time
		//$event_data[1] is the messages
		$event_data = array();
		//properties_array counter
		$k = 0;
		for($j = 0; $j < count($this->_nagios_log); $j++)
		{
			$event_data = explode(' ', $_nagios_log[$j], 2);
			$unixtime = $event_data[0];
			$messages = $event_data[1];
			$properties['time'] = unixtime_convert($unixtime);
			if(strcmp($date_selected, $properties['time']) === 0)
			{
				$properties['messages'] = $messages;
				$properties_array[$k] = json_encode($properties);
				$k++;
			}
		}
		return $properties_array;
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