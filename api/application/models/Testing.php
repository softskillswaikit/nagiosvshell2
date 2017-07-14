<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Testing extends CI_Model
{
	protected $_nagios_log = array();
	protected $event_data = array();
	protected $test_string = '10';

	protected $properties = array(
		'date_time' => '',
		'logtype' => '',
		'hostname' => '',
		'servicename' => '',
		'state' => '',
		'state_type' => '',
		'retry_count' => '',
		'messages' => ''
	);

	protected $properties_array = array();

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

		//properties_array counter
		$k = 0;

		for($j = 0; $j < count($this->_nagios_log); $j++)
		{
			$event_data = explode(' ', $_nagios_log[$j], 2);
			$unixtime = $event_data[0];
			$messages = $event_data[1];
			$properties['date_time'] = unixtime_convert($unixtime);
			$properties['messages'] = $messages;
			$properties_array[$k] = json_encode($properties);
			$k++
		}
	}

	private function unixtime_convert($unixtime)
	{
		//remove any non-numeric character
		$new_unixtime = preg_replace('/\D/', '', $unixtime);

		return date('M d Y H:i:s', $new_unixtime);
	}

	public function testing_1()
	{
		return $this->test_string;
	}

	public function testing_2($data_input)
	{
		return $data_input;
	}

	public function testing_3()
	{
		return $properties['date_time'];
	}

	public function testing_4()
	{
		return $properties['messages'];
	}

	public function testing_5()
	{
		return $properties_array[0];
	}
}

?>
