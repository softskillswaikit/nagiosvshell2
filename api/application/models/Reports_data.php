<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Reports_data extends CI_Model
{
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

		$this->_get_nagios_log();
		$this->_get_archives_log();
		$this->_insert_data();
	}

	//Written by Low Zhi Jian
	//Functions to be called by web service
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

	public function get_alert_summary($summary_type = 'NORMAL', $input_date, $input_host, $input_logtype, $input_state_type, $input_state, $maxitem=NULL)
	{
		//temporary array
		$temp_array = array();
		$i = 0;

		$this->alert_summary_array = $this->parse_log($this->_alert_array, 'alert');

		//filter the array based on request
		foreach($this->alert_summary_array as $items)
		{
			//custom report option
			//compare date
			if($this->compare_date($input_date, $items->datetime))
			{
				//compare hostname
				if($this->compare_string($input_host, $items->hostname))
				{
					//compare logtype
					if($this->compare_string($input_logtype, $items->logtype))
					{
						//compare state_type
						if($this->compare_string($input_state_type, $items->state_type))
						{
							//compare state
							if($this->compare_string($input_state, $items->state))
							{		
								if(!empty($maxitem))
								{				
									if($i === $maxitem)
									{
										break;
									}
								}

								$temp_array[$i] = $items;

								$i++;	
							}
						}
					}
				}
			}
		}

		$this->alert_summary_array = $temp_array;

		//find alert totals for alert summary section
		$alert_totals = 0;
		$alert_host_up = 0;
		$alert_host_down = 0;
		$alert_host_unreachable = 0;
		$alert_host_pending = 0;
		$alert_service_ok = 0;
		$alert_service_warning = 0;
		$alert_service_unknown = 0;
		$alert_service_critical = 0;
		$alert_service_pending = 0;

		$alert_obj = new StdCLass();

		foreach($this->alert_summary_array as $alerts)
		{
			$alert_totals++;

			if(strcmp($alerts->state, 'UP') == 0)
			{
				$alert_host_up++;
			}
			else if(strcmp($alerts->state, 'DOWN') == 0)
			{
				$alert_host_down++;
			}
			else if(strcmp($alerts->state, 'UNREACHABLE') == 0)
			{
				$alert_host_unreachable++;
			}
			else if(strcmp($alerts->state, 'OK') == 0)
			{
				$alert_service_ok++;
			}
			else if(strcmp($alerts->state, 'WARNING') == 0)
			{
				$alert_service_warning++;
			}
			else if(strcmp($alerts->state, 'UNKNOWN') == 0)
			{
				$alert_service_unknown++;
			}
			else if(strcmp($alerts->state, 'CRITICAL') == 0)
			{
				$alert_service_critical++;
			}
			//$alert->state = PENDING
			else 
			{
				//for HOST ALERT, the servicename is NULL
				if(empty($alerts->servicename))
				{
					$alert_host_pending++;
				}
				else
				{
					$alert_service_pending++;
				}
			}

			//encode the $this->alert_summary_array after filter into JSON format
			$alerts = json_encode($alerts);
		}

		$alert_obj->alert_totals = $alert_totals;
		$alert_obj->alert_host_up = $alert_host_up;
		$alert_obj->alert_host_down = $alert_host_down;
		$alert_obj->alert_host_unreachable = $alert_host_unreachable;
		$alert_obj->alert_host_pending = $alert_host_pending;
		$alert_obj->alert_service_ok = $alert_service_ok;
		$alert_obj->alert_service_warning = $alert_service_warning;
		$alert_obj->alert_service_unknown = $alert_service_unknown;
		$alert_obj->alert_service_critical = $alert_service_critical;
		$alert_obj->alert_service_pending = $alert_service_pending;

		$json_alert_obj = json_encode($alert_obj);
		unset($alert_obj);

		if($this->compare_string($summary_type, 'NORMAL'))
		{
			return $this->alert_summary_array;
		}
		//$summary_type = 'ALERT_TOTAL'
		else 
		{
			return $json_alert_obj;
		}
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

	public function get_notification($input_date)
	{
		//temporary array
		$temp_array = array();
		$i = 0;

		$this->notification_array = $this->parse_log($this->_notifications_array, 'notification');

		//encode the data into JSON format
		//also filter the data by date
		foreach($this->notification_array as $items)
		{
			if($this->compare_date($input_date, $items->datetime))
			{
				$temp_array[$i] = $items;
				$items = json_encode($items);

				$i++;
			}
		}

		$this->notification_array = $temp_array;

		return $this->notification_array;
	}

	public function get_event_log($input_date)
	{
		//temporary array
		$temp_array = array();
		$i = 0;

		$this->event_array = $this->parse_log($this->_data_array, 'event');

		//encode the data into JSON format
		//also filter the data by date
		foreach($this->event_array as $items)
		{
			if($this->compare_date($input_date, $items->datetime))
			{
				$temp_array[$i] = $items;
				$items = json_encode($items);

				$i++;
			}
		}

		$this->event_array = $temp_array;

		return $this->event_array;
	}

	//UTILITY FUNCTION
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

		foreach($this->_archives_log as $logs)
		{
			$this->_data_array[$i] = $logs;
			$i++;
		}

		foreach($this->_nagios_log as $logs)
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
				if(strpos($logs, 'HOST NOTIFICATION') !== false)
				{
					list($input_time, $other_message) = explode(' ', $logs, 2);
					list($logtype, $information) = explode(':', $other_message, 2);
					list($contact, $host, $state, $notificationcommand, $detail_message) = explode(';', $information, 5);

					$sorted_obj = new StdCLass();
					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = $logtype;
					$sorted_obj->contact = $contact;
					$sorted_obj->host = $host;
					$sorted_obj->service = NULL;
					$sorted_obj->state = $state;
					$sorted_obj->notificationcommand = $notificationcommand;
					$sorted_obj->messages = $detail_message;
				}
				//strpos($logs, 'SERVICE NOTIFICATION') !== false
				else
				{
					list($input_time, $other_message) = explode(' ', $logs, 2);
					list($logtype, $information) = explode(':', $other_message, 2);
					list($contact, $host, $service, $state, $notificationcommand, $detail_message) = explode(';', $information, 6);

					$sorted_obj = new StdClass();
					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = $logtype;
					$sorted_obj->contact = $contact;
					$sorted_obj->host = $host;
					$sorted_obj->service = $service;
					$sorted_obj->state = $state;
					$sorted_obj->notificationcommand = $notificationcommand;
					$sorted_obj->messages = $detail_message;
				}
			}
			else if(strcmp($_type, 'alert') == 0)
			{
				if(strpos($logs, 'HOST ALERT') !== false)
				{
					list($input_time, $event_message) = explode(' ', $logs, 2);
					list($logtype, $information) = explode(':', $event_message, 2);
					list($hostname, $state, $state_type, $retry_count, $detail_message) = explode(';', $information, 5);
		
					$sorted_obj = new StdCLass();
					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = $logtype;
					$sorted_obj->hostname = $hostname;
					$sorted_obj->servicename = NULL;
					$sorted_obj->state = $state;
					$sorted_obj->state_type = $state_type;
					$sorted_obj->retry_count = $retry_count;
					$sorted_obj->messages = $detail_message;
				}
				//strpos($logs, 'SERVICE ALERT') !== false
				else
				{
					list($input_time, $event_message) = explode(' ', $logs, 2);
					list($logtype, $information) = explode(':', $event_message, 2);
					list($hostname, $servicename, $state, $state_type, $retry_count, $detail_message) = explode(';', $information, 6);
		
					$sorted_obj = new StdCLass();
					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = $logtype;
					$sorted_obj->hostname = $hostname;
					$sorted_obj->servicename = $servicename;
					$sorted_obj->state = $state;
					$sorted_obj->state_type = $state_type;
					$sorted_obj->retry_count = $retry_count;
					$sorted_obj->messages = $detail_message;
				}
			}
			//$_type = 'event'
			else
			{
				list($input_time, $event_message) = explode(' ', $logs, 2);

				$sorted_obj = new StdCLass();
				$sorted_obj->datetime = trim($input_time, '[]');
				$sorted_obj->messages = $event_message;

				if(strpos($logs, 'HOST NOTIFICATION') !== false)
				{
					list($logtype, $information) = explode(':', $event_message, 2);

					$sorted_obj->logtype = $logtype;
				}
				else if(strpos($logs, 'SERVICE NOTIFICATION') !== false)
				{
					list($logtype, $information) = explode(':', $event_message, 2);
					
					$sorted_obj->logtype = $logtype;
				}
				else if(strpos($logs, 'HOST ALERT') !== false)
				{
					list($logtype, $information) = explode(':', $event_message, 2);
					list($hostname, $state, $state_type, $retry_count, $detail_message) = explode(';', $information, 5);

					$sorted_obj->logtype = $state;
				}
				else if(strpos($logs, 'SERVICE ALERT') !== false)
				{
					list($logtype, $information) = explode(':', $event_message, 2);
					list($hostname, $servicename, $state, $state_type, $retry_count, $detail_message) = explode(';', $information, 6);

					$sorted_obj->logtype = $state;
				}
				else if(strpos($logs, 'SERVICE FLAPPING ALERT') !== false)
				{
					$sorted_obj->logtype = 'FLAPPING';
				}
				else if(strpos($logs, 'LOG ROTATION') !== false)
				{
					$sorted_obj->logtype = 'LOG ROTATION';
				}
				//other messages
				else
				{
					$sorted_obj->logtype = 'INFORMATION';
				}
			}

			$sorted_array[$i] = $sorted_obj;

			$i++;

			//clear the data
			unset($input_time, $event_message, $logtype, $information, $hostname, $servicename, $state, $state_type, $retry_count, $detail_message, $contact, $host, $service, $notificationcommand, $other_message);
			unset($sorted_obj);
		}

		$i = 0;

		return $sorted_array;
	}

	//function used to compare date
	private function compare_date($input_date, $data_date)
	{
		$modify_input_date = date('Y-m-d', (int)$input_date);
		$modify_data_date = date('Y-m-d', (int)$data_date);
	
		if(strcmp($modify_input_date, $modify_data_date) == 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	private function compare_string($input_string, $data_string)
	{
		//compare host
		if(strcmp($input_string, 'ALL_HOST') == 0)
		{
			return true;
		}

		//compare logtype
		if(strcmp($input_string, 'ALL_ALERT') == 0)
		{
			return true;
		}

		//compare state_type
		if(strcmp($input_string, 'ALL_STATE_TYPE') == 0)
		{
			return true;
		}

		//compare host state
		if(strcmp($input_string, 'ALL_HOST_STATE') == 0)
		{
			if(strcmp($data_string, 'UP') == 0 or strcmp($data_string, 'DOWN') == 0 or strcmp($data_string, 'UNREACHABLE') == 0 or strcmp($data_string, 'PENDING') == 0)
			{
				return true;
			}
		}
		else if(strcmp($input_string, 'HOST_PROBLEM_STATE') == 0)
		{
			if(strcmp($data_string, 'DOWN') == 0 or strcmp($data_string, 'UNREACHABLE') == 0 or strcmp($data_string, 'PENDING') == 0)
			{
				return true;
			}
		}

		//compare service state
		if(strcmp($input_string, 'ALL_SERVICE_STATE') == 0)
		{
			if(strcmp($data_string, 'OK') == 0 or strcmp($data_string, 'WARNING') == 0 or strcmp($data_string, 'UNKNOWN') == 0 or strcmp($data_string, 'CRITICAL') == 0 or strcmp($data_string, 'PENDING') == 0)
			{
				return true;
			}
		}
		else if(strcmp($input_string, 'SERVICE_PROBLEM_STATE') == 0)
		{
			if(strcmp($data_string, 'WARNING') == 0 or strcmp($data_string, 'UNKNOWN') == 0 or strcmp($data_string, 'CRITICAL') == 0 or strcmp($data_string, 'PENDING') == 0)
			{
				return true;
			}
		}

		if(strcmp($input_string, $data_string) == 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}


}

?>