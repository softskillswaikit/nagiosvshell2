<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Testing extends CI_Model

{
	protected $test_data;

	//array of data taken from nagios log file
	protected $_nagios_log = array();
	protected $_archives_log = array();

	//array contains data from $_nagios_log array and $_archives_log array
	protected $_data_array = array();

	//array contains only lines with 'HOST ALERT:' and 'SERVICE ALERT:'
	protected $_alert_array = array();

	//array contains only lines with 'HOST NOTIFICATION:' and 'SERVICE NOTIFICATION:'
	protected $_notifications_array = array();

	//array that store return data for each component under Reports section
	protected $availability_array = array();
	protected $trends_array = array();
	protected $alert_history_array = array();
	protected $alert_summary_array = array();
	protected $alert_histogram_array = array();
	protected $notification_array = array();
	protected $event_array = array();

	//constructor
	public function __construct()
	{
		parent::__construct();

		$this->test_data = '30';
		$this->_get_nagios_log();
		$this->_get_archives_log();
		$this->_insert_data();
	}

	//Written by Low Zhi Jian
	//get log file data
	private function _get_nagios_log()
	{
		//opening the nagios log file
		$logfile = fopen("/usr/local/nagios/var/nagios.log", "r") or die("Unable to open file!");
		
		//array counter
		$i = 0;
		while(! feof($logfile) )
		{
			$this->_nagios_log[$i] = trim(fgets($logfile));
			$i++;
		}

		fclose($logfile);
	}

	//get archives log file data
	//adapted from https://stackoverflow.com/questions/18558445/read-multiple-files-with-php-from-directory
	private function _get_archives_log()
	{
		//array counter
		$i = 0;

		//opening the files in archives folder
		foreach (glob("/usr/local/nagios/var/archives/*.log") as $file)
		{
			$file_handle = fopen($file, "r");

			while(! feof($file_handle) )
			{
				$this->_archives_log[$i] = trim(fgets($file_handle));
				$i++;
			}

			fclose($file_handle);
		}
	}

	private function _insert_data()
	{
		//_data_array counter
		$i = 0;

		foreach($this->_nagios_log as $logs)
		{
			$this->_data_array[$i] = $logs;
			$i++;
		}


		foreach($this->_archives_log as $logs)
		{
			$this->_data_array[$i] = $logs;
			$i++;
		}


		//_alert_array counter
		$j = 0;
		foreach($this->_data_array as $logs)
		{
			if(strpos($logs, 'HOST ALERT:') !== false or strpos($logs, 'SERVICE ALERT:') !== false)
			{
				$this->_alert_array[$j] = $logs;
				$j++;
			}
		}

		//_notifications_array counter
		$k = 0;

		foreach($this->_data_array as $logs)
		{
			if(strpos($logs, 'HOST NOTIFICATION:') !== false or strpos($logs, 'SERVICE NOTIFICATION:') !== false)
			{
				$this->_notifications_array[$k] = $logs;
				$k++;
			}
		}
	}

	public function testing_1()
	{
		//return $this->test_data;
		//return $this->_data_array;
		//return $this->_alert_array;
		//return $this->_notifications_array;
		//return $this->_nagios_log;
		//return $this->_archives_log;
	}

	public function get_availability()
	{
		$this->availability_array = $this->parse_log($this->_alert_array, 'alert');

		//encode the data into JSON format
		foreach($this->availability_array as $items)
		{
			$items = json_encode($items);
		}

		return $this->availability_array;
	}

	public function get_trend()
	{
		$this->trends_array = $this->parse_log($this->_alert_array, 'alert');

		//encode the data into JSON format
		foreach($this->trends_array as $items)
		{
			$items = json_encode($items);
		}

		return $this->trends_array;
	}

	public function get_history_data()
	{
		$this->alert_history_array = $this->parse_log($this->_alert_array, 'alert');

		//encode the data into JSON format
		foreach($this->alert_history_array as $items)
		{
			$items = json_encode($items);
		}

		return $this->alert_history_array;
	}

	public function get_alert_summary()
	{
		$this->alert_summary_array = $this->parse_log($this->_alert_array, 'alert');

		//encode the data into JSON format
		foreach($this->alert_summary_array as $items)
		{
			$items = json_encode($items);
		}

		return $this->alert_summary_array;
	}

	public function get_alert_histogram()
	{
		$this->alert_histogram_array = $this->parse_log($this->_alert_array, 'alert');

		//encode the data into JSON format
		foreach($this->alert_histogram_array as $items)
		{
			$items = json_encode($items);
		}

		return $this->alert_histogram_array;
	}

	public function get_notification()
	{
		$this->notification_array = $this->parse_log($this->_notifications_array, 'notification');

		//encode the data into JSON format
		foreach($this->notification_array as $items)
		{
			$items = json_encode($items);
		}

		return $this->notification_array;
	}

	public function get_event_log()
	{
		$this->event_array = $this->parse_log($this->_data_array, 'event');

		//encode the data into JSON format
		foreach($this->event_array as $items)
		{
			$items = json_encode($items);
		}

		return $this->event_array;
	}

	//function to parse the log 
	private function parse_log($input_array, $_type)
	{
		//array that store the $sorted_obj
		$sorted_array = array();	

		//counter for $this->return_array;
		$i = 0;

		foreach($input_array as $logs)
		{
			if(strcmp($_type, 'notification') == 0)
			{
				list($input_time, $other_message) = explode(' ', $logs, 2);
				list($logtype, $information) = explode(':', $other_message, 2);
				list($contact, $host, $service, $state, $notificationcommand, $detail_message) = explode(';', $information, 6);

				$sorted_obj = new StdCLass();
				$sorted_obj->datetime = $this->unixtime_convert($input_time);
				$sorted_obj->logtype = $logtype;
				$sorted_obj->contact = $contact;
				$sorted_obj->host = $host;
				$sorted_obj->service = $service;
				$sorted_obj->state = $state;
				$sorted_obj->notificationcommand = $notificationcommand;
				$sorted_obj->messages = $detail_message;
			}
			else
			{
				list($input_time, $event_message) = explode(' ', $logs, 2);
				list($logtype, $information) = explode(':', $event_message, 2);
				list($hostname, $servicename, $state, $state_type, $retry_count, $detail_message) = explode(';', $information, 6);

				if(strcmp($_type, 'alert') == 0)
				{
					$sorted_obj = new StdCLass();
					$sorted_obj->datetime = $this->unixtime_convert($input_time);
					$sorted_obj->logtype = $logtype;
					$sorted_obj->hostname = $hostname;
					$sorted_obj->servicename = $servicename;
					$sorted_obj->state = $state;
					$sorted_obj->state_type = $state_type;
					$sorted_obj->retry_count = $retry_count;
					$sorted_obj->messages = $detail_message;
				}
				else if(strcmp($_type, 'event') == 0)
				{
					$sorted_obj = new StdCLass();
					$sorted_obj->datetime = $this->unixtime_convert($input_time);
					$sorted_obj->messages = $event_message;

					if(strcmp($logtype, 'HOST ALERT') == 0)
					{
						$sorted_obj->logtype = $state;
					}
					else if(strcmp($logtype, 'SERVICE ALERT') == 0)
					{
						$sorted_obj->logtype = $state;
					}
					else if(strcmp($logtype, 'HOST NOTIFICATION') == 0)
					{
						$sorted_obj->logtype = $logtype;
					}
					else if(strcmp($logtype, 'SERVICE NOTIFICATION') == 0)
					{
						$sorted_obj->logtype = $logtype;
					}
					else if(strcmp($logtype, 'SERVICE FLAPPING ALERT') == 0)
					{
						$sorted_obj->logtype = 'FLAPPING';
					}
					else
					{
						$sorted_obj->logtype = 'INFORMATION';
					}
				}
			}

			$sorted_array[$i] = $sorted_obj;

			$i++;

			//clear the data
			unset($input_time, $event_message, $logtype, $information, $hostname, $servicename, $state, $state_type, $retry_count, $detail_message);
			unset($sorted_obj);
		}

		$i = 0;

		return $sorted_array;
	}

	//function to convert unix timestamp to localtime
	private function unixtime_convert($unixtime)
	{
		//remove any non-numeric character
		$old_unixtime = trim($unixtime, '[]');
		$new_unixtime = (int)$old_unixtime;

		return date('Y-m-d H:i:s', $new_unixtime);
	}
}

?>