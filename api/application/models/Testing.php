<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Testing extends CI_Model
{
	//array of data taken from nagios log file
	protected $_data_array = array();
	protected $_host_service_notification_array = array();
	protected $_host_service_alert_array = array();

	//array counter
	protected $_counter;

	//array that store return data
	protected $_availability_array = array();
	protected $_alert_history_array = array();
	protected $_alert_summary_array = array();
	protected $_notification_array = array();
	protected $_event_array = array();

	//constructor
	public function __construct()
	{
		parent::__construct();

		date_default_timezone_set('UTC');

		$this->_counter = 0;
		$this->_get_nagios_log();
		$this->_get_archive_log();
		$this->_insert_data();
	}

	//Written by Low Zhi Jian
	//Functions to be called by web service
	//Alert history section
	public function get_history_data($input_date)
	{
		//array counter
		$i = 0;
		$temp_array = array();

		$this->_alert_history_array = $this->_parse_log($this->_host_service_alert_array, 'alert');

		//encode the data into JSON format
		//also filter the data by date
		foreach($this->_alert_history_array as $items)
		{
			if($this->_compare_date('TODAY', $input_date, $items->datetime))
			{
				$temp_array[$i] = $items;
				$items = json_encode($items);

				$i++;
			}
		}

		$this->_alert_history_array = $temp_array;

		return $this->_alert_history_array;
	}

	//Alert summary section
	public function get_alert_summary($return_type, $input_period, $input_date, $input_host, $input_service, $input_logtype, $input_state_type, $input_state)
	{
		$temp_array = array();
		$temp_array = $this->_parse_log($this->_host_service_alert_array, 'alert');

		//filter the data into $this->_alert_summary_array based on request
		//the hosts inside a hostgroup is passed in form of array
		//the services inside a servicegroup is passed in form of array
		if(is_array($input_host))
		{
			foreach($input_host as $hosts)
			{
				if(is_array($input_service))
				{
					foreach($input_service as $services)
					{
						$this->_alert_summary_array = array_merge($this->_alert_summary_array, $this->_get_alert_summary_host_service($temp_array, $input_period, $input_date, $hosts, $services, $input_logtype, $input_state_type, $input_state));
					}
				}
				else
				{
					$this->_alert_summary_array = array_merge($this->_alert_summary_array, $this->_get_alert_summary_host_service($temp_array, $input_period, $input_date, $hosts, $input_service, $input_logtype, $input_state_type, $input_state));
				}
			}
		}
		else
		{
			if(is_array($input_service))
			{
				foreach($input_service as $services)
				{
					$this->_alert_summary_array = array_merge($this->_alert_summary_array, $this->_get_alert_summary_host_service($temp_array, $input_period, $input_date, $input_host, $services, $input_logtype, $input_state_type, $input_state));	
				}
			}
			else
			{
				$this->_alert_summary_array = array_merge($this->_alert_summary_array, $this->_get_alert_summary_host_service($temp_array, $input_period, $input_date, $input_host, $input_service, $input_logtype, $input_state_type, $input_state));
			}
		}

		//$return_type = 'TOP_PRODUCER'
		if($this->_compare_string($return_type, 'TOP_PRODUCER'))
		{
			$producer_array = array();
			$producer_obj = new StdClass();
			$key_obj = new StdClass();

			//get unique host and service pair
			foreach($this->_alert_summary_array as $alert_producer)
			{
				$keys = $alert_producer->logtype.' '.$alert_producer->hostname.' '.$alert_producer->servicename;

				$key_obj->$keys += 1;
			}

			//array counter
			$i = 0;

			foreach($key_obj as $key => $value) 
			{
				list($logtypes_1, $logtypes_2, $hostnames, $servicenames) = explode(' ', $key, 4);

				$producer_obj->logtype = $logtypes_1.' '.$logtypes_2;
				$producer_obj->hostname = $hostnames;
				$producer_obj->servicename = $servicenames;
				$producer_obj->total_alert = $value;

				$producer_array[$i] = $producer_obj;
				unset($producer_obj);

				$i++;
			}

			foreach($producer_array as $items)
			{
				$items = json_encode($items);
			}

			return $producer_array;
		}
		//$return_type = 'ALERT_TOTAL_HOST'
		//find alert totals for host
		else if($this->_compare_string($return_type, 'ALERT_TOTAL_HOST'))
		{

		}
		//$return_type = 'NORMAL'
		else
		{	
			foreach($this->_alert_summary_array as $items)
			{
				$items = json_encode($items);
			}

			return $this->_alert_summary_array;
		}
	}

	//Notifications section
	public function get_notification($input_date)
	{
		//array counter
		$i = 0;
		$temp_array = array();

		$this->_notification_array = $this->_parse_log($this->_host_service_notification_array, 'notification');

		//encode the data into JSON format
		//also filter the data by date
		foreach($this->_notification_array as $items)
		{
			if($this->_compare_date('TODAY', $input_date, $items->datetime))
			{
				$temp_array[$i] = $items;
				$items = json_encode($items);

				$i++;
			}
		}

		$this->_notification_array = $temp_array;

		return $this->_notification_array;
	}

	//Event log section
	public function get_event_log($input_date)
	{	
		//array counter
		$i = 0;
		$temp_array = array();

		$this->_event_array = $this->_parse_log($this->_data_array, 'event');

		//encode the data into JSON format
		//also filter the data by date
		foreach($this->_event_array as $items)
		{
			if($this->_compare_date('TODAY', $input_date, $items->datetime))
			{
				$temp_array[$i] = $items;
				$items = json_encode($items);

				$i++;
			}
		}

		$this->_event_array = $temp_array;

		return $this->_event_array;
	}

	//UTILITY FUNCTION
	//Get log file data
	private function _get_nagios_log()
	{
		//opening the nagios log file
		$logfile = fopen('/usr/local/nagios/var/nagios.log', 'r') or die('Unable to open file');

		while(! feof($logfile) )
		{
			$this->_data_array[$this->_counter] = trim(fgets($logfile));
			$this->_counter++;
		}

		fclose($logfile);
	}

	//Get archive log file data
	//Adapted from https://stackoverflow.com/questions/18558445/read-multiple-files-with-php-from-directory
	private function _get_archive_log()
	{
		//opening the files in archives folder
		foreach (glob("/usr/local/nagios/var/archives/*.log") as $file)
		{
			$file_handle = fopen($file, "r");

			while(! feof($file_handle) )
			{
				$this->_data_array[$this->_counter] = trim(fgets($file_handle));
				$this->_counter++;
			}

			fclose($file_handle);
		}
	}

	//Function used to insert data into corresponding array
	private function _insert_data()
	{
		//array counter
		$i = 0;

		foreach($this->_data_array as $notifications)
		{
			if(strpos($notifications, 'HOST NOTIFICATION:') !== false or strpos($notifications, 'SERVICE NOTIFICATION:') !== false)
			{
				$this->_host_service_notification_array[$i] = $notifications;

				$i++;
			}
		}

		//array counter
		$j = 0;

		foreach($this->_data_array as $alerts)
		{
			if(strpos($alerts, 'HOST ALERT:') !== false or strpos($alerts, 'SERVICE ALERT:') !== false or strpos($alerts, 'SERVICE FLAPPING ALERT:') !== false)
			{
				$this->_host_service_alert_array[$j] = $alerts;

				$j++;
			}
		}
	}

	//Function used to compare string
	private function _compare_string($input_string, $data_string)
	{
		if(strcmp($input_string, 'ALL') == 0)
		{
			return true;
		}

		//compare host state
		if(strcmp($input_string, 'ALL HOST STATE') == 0)
		{
			if(strcmp($data_string, 'UP') == 0 or strcmp($data_string, 'DOWN') == 0 or strcmp($data_string, 'UNREACHABLE') == 0 or strcmp($data_string, 'PENDING') == 0)
			{
				return true;
			}
		}
		
		if(strcmp($input_string, 'HOST PROBLEM STATE') == 0)
		{
			if(strcmp($data_string, 'DOWN') == 0 or strcmp($data_string, 'UNREACHABLE') == 0 or strcmp($data_string, 'PENDING') == 0)
			{
				return true;
			}
		}

		//compare service state
		if(strcmp($input_string, 'ALL SERVICE STATE') == 0)
		{
			if(strcmp($data_string, 'OK') == 0 or strcmp($data_string, 'WARNING') == 0 or strcmp($data_string, 'UNKNOWN') == 0 or strcmp($data_string, 'CRITICAL') == 0 or strcmp($data_string, 'PENDING') == 0)
			{
				return true;
			}
		}
		
		if(strcmp($input_string, 'SERVICE PROBLEM STATE') == 0)
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
		//the data is not same
		else
		{
			return false;
		}
	}

	//Function used to compare date
	//Adapted from : http://php.net/manual/en/datetime.formats.relative.php
	private function _compare_date($input_period, $input_date, $data_date)
	{
		if($this->_compare_string($input_period, 'TODAY'))
		{
			$modify_input_date = date('Y-m-d', (int)$input_date);
			$modify_data_date = date('Y-m-d', (int)$data_date);
		
			if($this->_compare_string($modify_input_date, $modify_data_date))
			{
				return true;
			}
			//the date is not same
			else
			{
				return false;
			}
		}
		else if($this->_compare_string($input_period, 'LAST 24 HOURS'))
		{
			$modify_input_date = new DateTime();
			$modify_data_date = new DateTime();

			//Adapted from : http://php.net/manual/en/datetime.settimestamp.php
			$modify_input_date->setTimestamp( (int)$input_date );
			$modify_data_date->setTimestamp( (int)$data_date );

			//Adapted from : https://stackoverflow.com/questions/17718107/how-do-i-subtract-24-hour-from-date-time-object-in-php
			//Adapted from : https://stackoverflow.com/questions/15911312/how-to-check-if-time-is-between-two-times-in-php
			if( ($modify_data_date <= $modify_input_date) && ($modify_data_date >= ($modify_input_date->modify('-1 day'))) )
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		//1 day = 24 hour * 60 min * 60 sec = 86400 sec
		else if($this->_compare_string($input_period, 'YESTERDAY'))
		{
			$modify_input_date = date('Y-m-d', ( ((int)$input_date) - 86400) );
			$modify_data_date = date('Y-m-d', (int)$data_date);

			if($this->_compare_string($modify_input_date, $modify_data_date))
			{
				return true;
			}
			//the date is not same
			else
			{
				return false;
			}
		}
		else if($this->_compare_string($input_period, 'THIS WEEK'))
		{
			$monday = new DateTime();
			$monday->setTimestamp( (int)$input_date );
			$monday->modify('Monday this week');

			$sunday = new DateTime();
			$sunday->setTimestamp( (int)$input_date );
			$sunday->modify('Sunday this week');

			$modify_data_date = new DateTime();
			$modify_data_date->setTimestamp( (int)$data_date );

			if( ($modify_data_date <= $sunday) && ($modify_data_date >= $monday) )
			{
				return true;
			}
			else
			{
				if($this->_compare_string( (date('Y-m-d', (int)$data_date)), ($sunday->format('Y-m-d')) ))
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		else if($this->_compare_string($input_period, 'LAST 7 DAYS'))
		{
			$modify_input_date = new DateTime();
			$modify_data_date = new DateTime();

			$modify_input_date->setTimestamp( (int)$input_date );
			$modify_data_date->setTimestamp( (int)$data_date );

			if( ($modify_data_date <= $modify_input_date) && ($modify_data_date >= ($modify_input_date->modify('-7 days'))) )
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else if($this->_compare_string($input_period, 'LAST WEEK'))
		{
			$monday = new DateTime();
			$monday->setTimestamp( (int)$input_date );
			$monday->modify('Monday last week');

			$sunday = new DateTime();
			$sunday->setTimestamp( (int)$input_date );
			$sunday->modify('Sunday last week');

			$modify_data_date = new DateTime();
			$modify_data_date->setTimestamp( (int)$data_date );

			if( ($modify_data_date <= $sunday) && ($modify_data_date >= $monday) )
			{
				return true;
			}
			else
			{
				if($this->_compare_string( (date('Y-m-d', (int)$data_date)), ($sunday->format('Y-m-d')) ))
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		else if($this->_compare_string($input_period, 'THIS MONTH'))
		{
			$modify_input_month = date('Y-m', (int)$input_date);
			$modify_data_month = date('Y-m', (int)$data_date);

			if($this->_compare_string($modify_input_month, $modify_data_month))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else if($this->_compare_string($input_period, 'LAST 31 DAYS'))
		{
			$modify_input_date = new DateTime();
			$modify_data_date = new DateTime();

			$modify_input_date->setTimestamp( (int)$input_date );
			$modify_data_date->setTimestamp( (int)$data_date );

			if( ($modify_data_date <= $modify_input_date) && ($modify_data_date >= ($modify_input_date->modify('-31 days'))) )
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else if($this->_compare_string($input_period, 'LAST MONTH'))
		{
			$modify_input_month = date('Y-m-d', (int)$input_date);
			$modify_data_month = date('Y-m', (int)$data_date);

			$last_month_string = $modify_input_month.' -1 month';
			$last_month = date('Y-m', (strtotime($last_month_string)));

			if($this->_compare_string($last_month, $modify_data_month))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else if($this->_compare_string($input_period, 'THIS YEAR'))
		{
			$modify_input_year = date('Y', (int)$input_date);
			$modify_data_year = date('Y', (int)$data_date);

			if($this->_compare_string($modify_input_year, $modify_data_year))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else if($this->_compare_string($input_period, 'LAST YEAR'))
		{
			$modify_input_year = date('Y', (int)$input_date) - 1;
			$modify_data_year = date('Y', (int)$data_date);

			if($this->_compare_string($modify_input_year, $modify_data_year))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		//if($this->compare_string($input_period, 'CUSTOM'))
		else
		{
			if(is_array($input_date))
			{
				$start_date = new DateTime();
				$end_date = new DateTime();

				$start_date->setTimestamp( (int)$input_date[0] );
				$end_date->setTimestamp( (int)$input_date[1] );

				$modify_data_date = new DateTime();
				$modify_data_date->setTimestamp( (int)$data_date );

				if( ($modify_data_date <= $end_date) && ($modify_data_date >= $start_date) )
				{
					return true;
				}
				else
				{
					if($this->_compare_string( (date('Y-m-d', (int)$data_date)), (date('Y-m-d', (int)$input_date[1])) ))
					{
						return true;
					}
					else
					{
						return false;
					}
				}
			}
		}
	}

	//Function used to filter time period
	private function _compare_time_period($input_time_period, $input_date)
	{
		if($this->_compare_string($input_time_period, '24x7'))
		{
			return true;
		}
		else if($this->_compare_string($input_time_period, '24x7_sans_holidays'))
		{
			$modify_input_date = new DateTime();
			$modify_input_date->setTimestamp( (int)$input_date );

			$modify_input_date_string = $modify_input_date->format('m-d');

			if($this->_compare_string($modify_input_date_string, '12-25'))
			{
				return false;
			}
			else if($this->_compare_string($modify_input_date_string, '07-04'))
			{
				return false;
			}
			else if($this->_compare_string($modify_input_date_string, '01-01'))
			{
				return false;
			}
			else if($this->_compare_string($modify_input_date_string, '11-04'))
			{
				$day = $modify_input_date->format('l');

				if($this->_compare_string($day, 'Thursday'))
				{
					return false;
				}
				else
				{
					return true;
				}
			}
			else if($this->_compare_string($modify_input_date_string, '09-01'))
			{
				$day = $modify_input_date->format('l');

				if($this->_compare_string($day, 'Monday'))
				{
					return false;
				}
				else
				{
					return true;
				}
			}
			else if($this->_compare_string($modify_input_date_string, '05-01'))
			{
				$day = $modify_input_date->format('l');

				if($this->_compare_string($day, 'Monday'))
				{
					return false;
				}
				else
				{
					return true;
				}
			}
			else
			{
				return true;
			}
		}
		else if($this->_compare_string($input_time_period, 'none'))
		{
			return false;
		}
		else if($this->_compare_string($input_time_period, 'us-holidays'))
		{
			$modify_input_date = new DateTime();
			$modify_input_date->setTimestamp( (int)$input_date );

			$modify_input_date_string = $modify_input_date->format('m-d');

			if($this->_compare_string($modify_input_date_string, '01-01'))
			{
				return false;
			}
			else if($this->_compare_string($modify_input_date_string, '07-04'))
			{
				return false;
			}
			else if($this->_compare_string($modify_input_date_string, '12-25'))
			{
				return false;
			}
			else if($this->_compare_string($modify_input_date_string, '05-01'))
			{
				$day = $modify_input_date->format('l');

				if($this->_compare_string($day, 'Monday'))
				{
					return false;
				}
				else
				{
					return true;
				}
			}
			else if($this->_compare_string($modify_input_date_string, '09-01'))
			{
				$day = $modify_input_date->format('l');

				if($this->_compare_string($day, 'Monday'))
				{
					return false;
				}
				else
				{
					return true;
				}
			}
			else if($this->_compare_string($modify_input_date_string, '11-04'))
			{
				$day = $modify_input_date->format('l');

				if($this->_compare_string($day, 'Thursday'))
				{
					return false;
				}
				else
				{
					return true;
				}
			}
			else
			{
				return true;
			}
		}
		else if($this->_compare_string($input_time_period, 'workhours'))
		{
			$modify_input_date = new DateTime();
			$modify_input_date->setTimestamp( (int)$input_date );

			$modify_input_date_string = $modify_input_date->format('H:i');
			$modify_input_time_string = $modify_input_date->format('H');

			$day = $modify_input_date->format('l');

			if($this->_compare_string($day, 'Monday') or $this->_compare_string($day, 'Tuesday') or $this->_compare_string($day, 'Wednesday') or $this->_compare_string($day, 'Thursday') or $this->_compare_string($day, 'Friday'))
			{
				if($this->_compare_string($modify_input_date_string, '10:00'))
				{
					return true;
				}
				else if( (int)$modify_input_time_string >= 1 && (int)$modify_input_time_string <=9 )
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		//$input_time_period = 'None'
		else
		{
			return true;
		}
	}

	//Function to parse the log 
	private function _parse_log($input_array, $_type)
	{
		//array that store the $sorted_obj
		$sorted_array = array();	

		//counter for $this->_return_array;
		$i = 0;

		$sorted_obj = new StdCLass();

		foreach($input_array as $logs)
		{
			if($this->_compare_string($_type, 'notification'))
			{
				if(strpos($logs, 'HOST NOTIFICATION:') !== false)
				{
					list($input_time, $other_message) = explode(' ', $logs, 2);
					list($logtype, $information) = explode(':', $other_message, 2);
					list($contact, $host, $state, $notification_command, $detail_message) = explode(';', $information, 5);

					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = trim($logtype);
					$sorted_obj->contact = trim($contact);
					$sorted_obj->host = trim($host);
					$sorted_obj->service = 'N/A';
					$sorted_obj->state = trim($state);
					$sorted_obj->notification_command = trim($notificationcommand);
					$sorted_obj->messages = trim($detail_message);
				}
				//strpos($logs, 'SERVICE NOTIFICATION:') !== false
				else
				{
					list($input_time, $other_message) = explode(' ', $logs, 2);
					list($logtype, $information) = explode(':', $other_message, 2);
					list($contact, $host, $service, $state, $notificationcommand, $detail_message) = explode(';', $information, 6);

					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = trim($logtype);
					$sorted_obj->contact = trim($contact);
					$sorted_obj->host = trim($host);
					$sorted_obj->service = trim($service);
					$sorted_obj->state = trim($state);
					$sorted_obj->notificationcommand = trim($notificationcommand);
					$sorted_obj->messages = trim($detail_message);
				}
			}
			else if($this->_compare_string($_type, 'alert'))
			{
				if(strpos($logs, 'HOST ALERT:') !== false)
				{
					list($input_time, $event_message) = explode(' ', $logs, 2);
					list($logtype, $information) = explode(':', $event_message, 2);
					list($hostname, $state, $state_type, $retry_count, $detail_message) = explode(';', $information, 5);
		
					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = trim($logtype);
					$sorted_obj->hostname = trim($hostname);
					$sorted_obj->servicename = 'N/A';
					$sorted_obj->state = trim($state);
					$sorted_obj->state_type = trim($state_type);
					$sorted_obj->retry_count = trim($retry_count);
					$sorted_obj->messages = trim($detail_message);
				}
				else if(strpos($logs, 'SERVICE ALERT:') !== false)
				{
					list($input_time, $event_message) = explode(' ', $logs, 2);
					list($logtype, $information) = explode(':', $event_message, 2);
					list($hostname, $servicename, $state, $state_type, $retry_count, $detail_message) = explode(';', $information, 6);
		
					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = trim($logtype);
					$sorted_obj->hostname = trim($hostname);
					$sorted_obj->servicename = trim($servicename);
					$sorted_obj->state = trim($state);
					$sorted_obj->state_type = trim($state_type);
					$sorted_obj->retry_count = trim($retry_count);
					$sorted_obj->messages = trim($detail_message);
				}
				else if(strpos($logs, 'HOST DOWNTIME ALERT:') !== false)
				{
					list($input_time, $event_message) = explode(' ', $logs, 2);
					list($logtype, $information) = explode(':', $event_message, 2);
					list($hostname, $state, $detail_message) = explode(';', $information, 3);
		
					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = trim($logtype);
					$sorted_obj->hostname = trim($hostname);
					$sorted_obj->servicename = 'N/A';
					$sorted_obj->state = trim($state);
					$sorted_obj->state_type = 'N/A';
					$sorted_obj->retry_count = 'N/A';
					$sorted_obj->messages = trim($detail_message);
				}
				else if(strpos($logs, 'SERVICE DOWNTIME ALERT:') !== false)
				{
					list($input_time, $event_message) = explode(' ', $logs, 2);
					list($logtype, $information) = explode(':', $event_message, 2);
					list($hostname, $servicename, $state, $detail_message) = explode(';', $information, 4);
		
					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = trim($logtype);
					$sorted_obj->hostname = trim($hostname);
					$sorted_obj->servicename = trim($servicename);
					$sorted_obj->state = trim($state);
					$sorted_obj->state_type = 'N/A';
					$sorted_obj->retry_count = 'N/A';
					$sorted_obj->messages = trim($detail_message);
				}
				//strpos($logs, 'SERVICE FLAPPING ALERT:') !== false
				else
				{
					list($input_time, $event_message) = explode(' ', $logs, 2);
					list($logtype, $information) = explode(':', $event_message, 2);
					list($hostname, $servicename, $state, $detail_message) = explode(';', $information, 4);

					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = trim($logtype);
					$sorted_obj->hostname = trim($hostname);
					$sorted_obj->servicename = trim($servicename);
					$sorted_obj->state = trim($state);
					$sorted_obj->state_type = 'N/A';
					$sorted_obj->retry_count = 'N/A';
					$sorted_obj->messages = trim($detail_message);
				}
			}
			else if($this->_compare_string($_type, 'event'))
			{
				list($input_time, $event_message) = explode(' ', $logs, 2);

				$sorted_obj->datetime = trim($input_time, '[]');
				$sorted_obj->messages = trim($event_message);

				if(strpos($logs, 'HOST NOTIFICATION') !== false)
				{
					list($logtype, $information) = explode(':', $event_message, 2);

					$sorted_obj->logtype = trim($logtype);
				}
				else if(strpos($logs, 'SERVICE NOTIFICATION') !== false)
				{
					list($logtype, $information) = explode(':', $event_message, 2);
					
					$sorted_obj->logtype = trim($logtype);
				}
				else if(strpos($logs, 'HOST ALERT') !== false)
				{
					list($logtype, $information) = explode(':', $event_message, 2);
					list($hostname, $state, $state_type, $retry_count, $detail_message) = explode(';', $information, 5);

					$sorted_obj->logtype = trim($state);
				}
				else if(strpos($logs, 'SERVICE ALERT') !== false)
				{
					list($logtype, $information) = explode(':', $event_message, 2);
					list($hostname, $servicename, $state, $state_type, $retry_count, $detail_message) = explode(';', $information, 6);

					$sorted_obj->logtype = trim($state);
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
			//$_type = 'availability'
			else
			{
				if(strpos($logs, 'HOST ALERT:') !== false)
				{
					list($input_time, $event_message) = explode(' ', $logs, 2);
					list($logtype, $information) = explode(':', $event_message, 2);
					list($hostname, $state, $state_type, $retry_count, $detail_message) = explode(';', $information, 5);
		
					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = trim($logtype);
					$sorted_obj->hostname = trim($hostname);
					$sorted_obj->servicename = 'N/A';
					$sorted_obj->state = trim($state);
					$sorted_obj->state_type = trim($state_type);
					$sorted_obj->retry_count = trim($retry_count);
					$sorted_obj->messages = trim($detail_message);
				}
				else if(strpos($logs, 'SERVICE ALERT:') !== false)
				{
					list($input_time, $event_message) = explode(' ', $logs, 2);
					list($logtype, $information) = explode(':', $event_message, 2);
					list($hostname, $servicename, $state, $state_type, $retry_count, $detail_message) = explode(';', $information, 6);
		
					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = trim($logtype);
					$sorted_obj->hostname = trim($hostname);
					$sorted_obj->servicename = trim($servicename);
					$sorted_obj->state = trim($state);
					$sorted_obj->state_type = trim($state_type);
					$sorted_obj->retry_count = trim($retry_count);
					$sorted_obj->messages = trim($detail_message);
				}
				else if(strpos($logs, 'HOST DOWNTIME ALERT:') !== false)
				{
					list($input_time, $event_message) = explode(' ', $logs, 2);
					list($logtype, $information) = explode(':', $event_message, 2);
					list($hostname, $state, $detail_message) = explode(';', $information, 3);
		
					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = trim($logtype);
					$sorted_obj->hostname = trim($hostname);
					$sorted_obj->servicename = 'N/A';
					$sorted_obj->state = trim($state);
					$sorted_obj->state_type = 'N/A';
					$sorted_obj->retry_count = 'N/A';
					$sorted_obj->messages = trim($detail_message);
				}
				else if(strpos($logs, 'SERVICE DOWNTIME ALERT:') !== false)
				{
					list($input_time, $event_message) = explode(' ', $logs, 2);
					list($logtype, $information) = explode(':', $event_message, 2);
					list($hostname, $servicename, $state, $detail_message) = explode(';', $information, 4);
		
					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = trim($logtype);
					$sorted_obj->hostname = trim($hostname);
					$sorted_obj->servicename = trim($servicename);
					$sorted_obj->state = trim($state);
					$sorted_obj->state_type = 'N/A';
					$sorted_obj->retry_count = 'N/A';
					$sorted_obj->messages = trim($detail_message);
				}
				else if(strpos($logs, 'CURRENT HOST STATE:') !== false)
				{
					list($input_time, $event_message) = explode(' ', $logs, 2);
					list($logtype, $information) = explode(':', $event_message, 2);
					list($hostname, $state, $state_type, $retry_count, $detail_message) = explode(';', $information, 5);
		
					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = trim($logtype);
					$sorted_obj->hostname = trim($hostname);
					$sorted_obj->servicename = 'N/A';
					$sorted_obj->state = trim($state);
					$sorted_obj->state_type = trim($state_type);
					$sorted_obj->retry_count = trim($retry_count);
					$sorted_obj->messages = trim($detail_message);
				}
				else if(strpos($logs, 'CURRENT SERVICE STATE:') !== false)
				{
					list($input_time, $event_message) = explode(' ', $logs, 2);
					list($logtype, $information) = explode(':', $event_message, 2);
					list($hostname, $servicename, $state, $state_type, $retry_count, $detail_message) = explode(';', $information, 6);
		
					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = trim($logtype);
					$sorted_obj->hostname = trim($hostname);
					$sorted_obj->servicename = trim($servicename);
					$sorted_obj->state = trim($state);
					$sorted_obj->state_type = trim($state_type);
					$sorted_obj->retry_count = trim($retry_count);
					$sorted_obj->messages = trim($detail_message);
				}
				else if(strpos($logs, 'Successfully shutdown...') !== false)
				{
					list($input_time, $event_message) = explode(' ', $logs, 2);

					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = 'NAGIOS STATUS';
					$sorted_obj->hostname = 'N/A';
					$sorted_obj->servicename = 'N/A';
					$sorted_obj->state = 'SHUTDOWN';
					$sorted_obj->state_type = 'N/A';
					$sorted_obj->retry_count = 'N/A';
					$sorted_obj->messages = trim($event_message);
				}
				else if(strpos($logs, 'starting...') !== false)
				{
					list($input_time, $event_message) = explode(' ', $logs, 2);

					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = 'NAGIOS STATUS';
					$sorted_obj->hostname = 'N/A';
					$sorted_obj->servicename = 'N/A';
					$sorted_obj->state = 'STARTUP';
					$sorted_obj->state_type = 'N/A';
					$sorted_obj->retry_count = 'N/A';
					$sorted_obj->messages = trim($event_message);
				}
				else
				{
					continue;
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

	//Functions used by alert summary section
	//Function used to filter data 
	private function _get_alert_summary_host_service($input_array, $input_period, $input_date, $input_host, $input_service, $input_logtype, $input_state_type, $input_state)
	{
		//array counter
		$i = 0;
		$return_array = array();

		//filter the array based on the request
		foreach($input_array as $items)
		{
			//custom report option
			//compare date
			if($this->_compare_date($input_period, $input_date, $items->datetime))
			{
				//compare host name
				if($this->_compare_string($input_host, $items->hostname))
				{
					//compare service name
					if($this->_compare_string($input_service, $items->servicename))
					{
						//compare logtype
						if($this->_compare_string($input_logtype, $items->logtype))
						{
							//compare state_type
							if($this->_compare_string($input_state_type, $items->state_type))
							{
								//compare state
								if($this->_compare_string($input_state, $items->state))
								{
									$return_array[$i] = $items;

									$i++;
								}
							}
						}
					}
				}
			}
		}

		return $return_array;
	}

	//Funtion used to get alert total for host and hostgroup
	private function _get_alert_total_host($input_host_name)
	{
		$alert_obj = new StdCLass();

		$alert_host_total = 0;
		$alert_host_soft_total = 0;
		$alert_host_hard_total = 0;

		$alert_host_up_total = 0;
		$alert_host_up_soft = 0;
		$alert_host_up_hard = 0;

		$alert_host_down_total = 0;
		$alert_host_down_soft = 0;
		$alert_host_down_hard = 0;

		$alert_host_unreachable_total = 0;
		$alert_host_unreachable_soft = 0;
		$alert_host_unreachable_hard = 0;

		$alert_service_total = 0;
		$alert_service_soft_total = 0;
		$alert_service_hard_total = 0;

		$alert_service_ok_total = 0;
		$alert_service_ok_soft = 0;
		$alert_service_ok_hard = 0;

		$alert_service_warning_total = 0;
		$alert_service_warning_soft = 0;
		$alert_service_warning_hard = 0;

		$alert_service_unknown_total = 0;
		$alert_service_unknown_soft = 0;
		$alert_service_unknown_hard = 0;

		$alert_service_critical_total = 0;
		$alert_service_critical_soft = 0;
		$alert_service_critical_hard = 0;

		foreach($this->_alert_summary_array as $alert_producer)
		{
			if($this->_compare_string($input_host_name, $alert_producer->hostname))
			{
				if($this->_compare_string($alert_producer->logtype, 'HOST ALERT'))
				{
					$alert_host_total++;

					if($this->_compare_string($alert_producer->state_type, 'SOFT'))
					{
						$alert_host_soft_total++;

						if($this->_compare_string($alert_producer->state, 'UP'))
						{
							$alert_host_up_total++;
							$alert_host_up_soft++;
						}
						else if($this->_compare_string($alert_producer->state, 'DOWN'))
						{
							$alert_host_down_total++;
							$alert_host_down_soft++;
						}
						//$alert_producer->state = 'UNREACHABLE'
						else
						{
							$alert_host_unreachable_total++;
							$alert_host_unreachable_soft++;
						}
					}
					//$alert_producer->state_type = 'HARD'
					else
					{
						$alert_host_hard_total++;

						if($this->_compare_string($alert_producer->state, 'UP'))
						{
							$alert_host_up_total++;
							$alert_host_up_hard++;
						}
						else if($this->_compare_string($alert_producer->state, 'DOWN'))
						{
							$alert_host_down_total++;
							$alert_host_down_hard++;
						}
						//$alert_producer->state = 'UNREACHABLE'
						else
						{
							$alert_host_unreachable_total++;
							$alert_host_unreachable_hard++;
						}
					}
				}
				//$alert_producer->logtype = 'SERVICE ALERT'
				else
				{
					$alert_service_total++;

					if($this->_compare_string($alert_producer->state_type, 'SOFT'))
					{
						$alert_service_soft_total++;

						if($this->_compare_string($alert_producer->state, 'OK'))
						{
							$alert_service_ok_total++;
							$alert_service_ok_soft++;
						}
						else if($this->_compare_string($alert_producer->state, 'WARNING'))
						{
							$alert_service_warning_total++;
							$alert_service_warning_soft++;
						}
						else if($this->_compare_string($alert_producer->state, 'UNKNOWN'))
						{
							$alert_service_unknown_total++;
							$alert_service_unknown_soft++;
						}
						//$alert_producer->state = 'CRITICAL'
						else
						{
							$alert_service_critical_total++;
							$alert_service_critical_soft++;
						}
					}
					//$alert_producer->state_type = 'HARD'
					else
					{
						$alert_service_hard_total++;

						if($this->_compare_string($alert_producer->state, 'OK'))
						{
							$alert_service_ok_total++;
							$alert_service_ok_hard++;
						}
						else if($this->_compare_string($alert_producer->state, 'WARNING'))
						{
							$alert_service_warning_total++;
							$alert_service_warning_hard++;
						}
						else if($this->_compare_string($alert_producer->state, 'UNKNOWN'))
						{
							$alert_service_unknown_total++;
							$alert_service_unknown_hard++;
						}
						//$alert_producer->state = 'CRITICAL'
						else
						{	
							$alert_service_critical_total++;
							$alert_service_critical_hard++;
						}
					}
				}
			}
		}

		$alert_obj->hostname = $input_host_name;

		$alert_obj->alert_host_total = $alert_host_total;
		$alert_obj->alert_host_soft_total = $alert_host_soft_total;
		$alert_obj->alert_host_hard_total = $alert_host_up_total;

		$alert_obj->alert_host_up_total = $alert_host_up_total;
		$alert_obj->alert_host_up_soft = $alert_host_up_soft;
		$alert_obj->alert_host_up_hard = $alert_host_up_hard;

		$alert_obj->alert_host_down_total = $alert_host_down_total;
		$alert_obj->alert_host_down_soft = $alert_host_down_soft;
		$alert_obj->alert_host_down_hard = $alert_host_down_hard;

		$alert_obj->alert_host_unreachable_total = $alert_host_unreachable_total;
		$alert_obj->alert_host_unreachable_soft = $alert_host_unreachable_soft;
		$alert_obj->alert_host_unreachable_hard = $alert_host_unreachable_hard;

		$alert_obj->alert_service_total = $alert_service_total;
		$alert_obj->alert_service_soft_total = $alert_service_soft_total;
		$alert_obj->alert_service_hard_total = $alert_service_hard_total;

		$alert_obj->alert_service_ok_total = $alert_service_ok_total;
		$alert_obj->alert_service_ok_soft = $alert_service_ok_soft;
		$alert_obj->alert_service_ok_hard = $alert_service_ok_hard;

		$alert_obj->alert_service_warning_total = $alert_service_warning_total;
		$alert_obj->alert_service_warning_soft = $alert_service_warning_soft;
		$alert_obj->alert_service_warning_hard = $alert_service_warning_hard;

		$alert_obj->alert_service_unknown_total = $alert_service_unknown_total;
		$alert_obj->alert_service_unknown_soft = $alert_service_unknown_soft;
		$alert_obj->alert_service_unknown_hard = $alert_service_unknown_hard;

		$alert_obj->alert_service_critical_total = $alert_service_critical_total;
		$alert_obj->alert_service_critical_soft = $alert_service_critical_soft;
		$alert_obj->alert_service_critical_hard = $alert_service_critical_hard;

		return $alert_obj;
	}




}




?>