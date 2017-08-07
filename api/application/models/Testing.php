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
	protected $_alert_histogram_array = array();
	protected $_notification_array = array();
	protected $_event_array = array();

	//constructor
	public function __construct()
	{
		parent::__construct();

		date_default_timezone_set('UTC');

		$this->_counter = 0;
	}

	//Written by Low Zhi Jian
	//Functions to be called by web service
	//Alert history section
	public function get_alert_history($input_date)
	{
		$this->_get_data($input_date, 'TODAY');
		$this->_insert_data();

		//array counter
		$i = 0;
		$temp_array = array();

		$this->_alert_history_array = $this->_parse_log($this->_host_service_alert_array, 'alert');

		//encode the data into JSON format
		//also filter the data by date
		foreach($this->_alert_history_array as $items)
		{
			$temp_array[$i] = $items;
			$items = json_encode($items);

			$i++;
		}

		$this->_alert_history_array = $temp_array;

		return $this->_alert_history_array;
	}

	//Alert summary section
	public function get_alert_summary($return_type, $input_period, $input_date, $input_host, $input_service = 'ALL', $input_logtype, $input_state_type, $input_state)
	{
		$this->_get_data($input_date, $input_period);
		$this->_insert_data();

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
						$this->_alert_summary_array = array_merge($this->_alert_summary_array, $this->_get_alert_summary_host_service($temp_array, $hosts, $services, $input_logtype, $input_state_type, $input_state));
					}
				}
				else
				{
					$this->_alert_summary_array = array_merge($this->_alert_summary_array, $this->_get_alert_summary_host_service($temp_array, $hosts, $input_service, $input_logtype, $input_state_type, $input_state));
				}
			}
		}
		else
		{
			if(is_array($input_service))
			{
				foreach($input_service as $services)
				{
					$this->_alert_summary_array = array_merge($this->_alert_summary_array, $this->_get_alert_summary_host_service($temp_array, $input_host, $services, $input_logtype, $input_state_type, $input_state));	
				}
			}
			else
			{
				$this->_alert_summary_array = array_merge($this->_alert_summary_array, $this->_get_alert_summary_host_service($temp_array, $input_host, $input_service, $input_logtype, $input_state_type, $input_state));
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
			$host_obj = new StdClass();

			if(is_array($input_host))
			{
				//array coutner
				$h = 0;

				$host_obj_array = array();

				foreach($input_host as $hosts)
				{
					$host_obj = $this->_get_alert_total_host($hosts);

					$host_obj_array[$h] = $host_obj;
					$h++;

					unset($host_obj);
				}

				foreach($host_obj_array as $items)
				{
					$items = json_encode($items);
				}

				return $host_obj_array;
			}
			else
			{
				$host_obj = $this->_get_alert_total_host($input_host);

				foreach($host_obj as $items)
				{
					$items = json_encode($items);
				}

				return $host_obj;
			}
		}
		//$return_type = 'ALERT_TOTAL_HOSTGROUP'
		//find alert totals for hostgroup
		else if($this->_compare_string($return_type, 'ALERT_TOTAL_HOSTGROUP'))
		{
			$host_obj = new StdCLass();
			$hostgroup_obj =  new StdClass();

			if(is_array($input_host))
			{
				foreach($input_host as $hosts)
				{
					$host_obj = $this->_get_alert_total_host($hosts);

					$hostgroup_obj->alert_host_total += $host_obj->alert_host_total;
					$hostgroup_obj->alert_host_soft_total += $host_obj->alert_host_soft_total;
					$hostgroup_obj->alert_host_hard_total += $host_obj->alert_host_up_total;

					$hostgroup_obj->alert_host_up_total += $host_obj->alert_host_up_total;
					$hostgroup_obj->alert_host_up_soft += $host_obj->alert_host_up_soft;
					$hostgroup_obj->alert_host_up_hard += $host_obj->alert_host_up_hard;

					$hostgroup_obj->alert_host_down_total += $host_obj->alert_host_down_total;
					$hostgroup_obj->alert_host_down_soft += $host_obj->alert_host_down_soft;
					$hostgroup_obj->alert_host_down_hard += $host_obj->alert_host_down_hard;

					$hostgroup_obj->alert_host_unreachable_total += $host_obj->alert_host_unreachable_total;
					$hostgroup_obj->alert_host_unreachable_soft += $host_obj->alert_host_unreachable_soft;
					$hostgroup_obj->alert_host_unreachable_hard += $host_obj->alert_host_unreachable_hard;

					$hostgroup_obj->alert_service_total += $host_obj->alert_service_total;
					$hostgroup_obj->alert_service_soft_total += $host_obj->alert_service_soft_total;
					$hostgroup_obj->alert_service_hard_total += $host_obj->alert_service_hard_total;

					$hostgroup_obj->alert_service_ok_total += $host_obj->alert_service_ok_total;
					$hostgroup_obj->alert_service_ok_soft += $host_obj->alert_service_ok_soft;
					$hostgroup_obj->alert_service_ok_hard += $host_obj->alert_service_ok_hard;

					$hostgroup_obj->alert_service_warning_total += $host_obj->alert_service_warning_total;
					$hostgroup_obj->alert_service_warning_soft += $host_obj->alert_service_warning_soft;
					$hostgroup_obj->alert_service_warning_hard += $host_obj->alert_service_warning_hard;

					$hostgroup_obj->alert_service_unknown_total += $host_obj->alert_service_unknown_total;
					$hostgroup_obj->alert_service_unknown_soft += $host_obj->alert_service_unknown_soft;
					$hostgroup_obj->alert_service_unknown_hard += $host_obj->alert_service_unknown_hard;

					$hostgroup_obj->alert_service_critical_total += $host_obj->alert_service_critical_total;
					$hostgroup_obj->alert_service_critical_soft += $host_obj->alert_service_critical_soft;
					$hostgroup_obj->alert_service_critical_hard += $host_obj->alert_service_critical_hard;

					unset($host_obj);
				}
			}
			else
			{
				$hostgroup_obj = $this->_get_alert_total_host($input_host);
			}


			foreach($hostgroup_obj as $items)
			{
				$items = json_encode($items);
			}

			return $hostgroup_obj;
		}
		//$return_type = 'ALERT_TOTAL_SERVICE'
		//find alert totals for service
		else if($this->_compare_string($return_type, 'ALERT_TOTAL_SERVICE'))
		{
			$unique_service_array = array();
			$unique_service_obj = new StdClass();
			$key_obj = new StdClass();
			$return_array = array();

			//get unique host host and service pair
			foreach($this->_alert_summary_array as $alert_producer)
			{
				$keys = $alert_producer->hostname.' '.$alert_producer->servicename;

				$key_obj->$keys += 1;
			}

			//array counter
			$i = 0;

			foreach($key_obj as $key => $value) 
			{
				list($hostname, $servicename) = explode(' ', $key, 2);

				$unique_service_obj->hostname = $hostname;
				$unique_service_obj->servicename = $servicename;

				$unique_service_array[$i] = $unique_service_obj;
				unset($unique_service_obj);

				$i++;
			}

			//array counter
			$k = 0;

			foreach($unique_service_array as $services)
			{
				$return_array[$k] = $this->_get_alert_total_service($services->hostname, $services->servicename);

				$k++;
			}

			foreach($return_array as $items)
			{
				$items = json_encode($items);
			}

			return $return_array;
		}
		//$return_type = 'ALERT_TOTAL_SERVICEGROUP'
		//find alert total for servicegroup
		else if($this->_compare_string($return_type, 'ALERT_TOTAL_SERVICEGROUP'))
		{
			$unique_service_array = array();
			$unique_service_obj = new StdClass();
			$service_obj = new StdClass();
			$servicegroup_obj = new StdClass();
			$key_obj = new StdClass();
			$return_array = array();

			//get unique host host and service pair
			foreach($this->_alert_summary_array as $alert_producer)
			{
				$keys = $alert_producer->hostname.' '.$alert_producer->servicename;

				$key_obj->$keys += 1;
			}

			//array counter
			$i = 0;

			foreach($key_obj as $key => $value) 
			{
				list($hostname, $servicename) = explode(' ', $key, 2);

				$unique_service_obj->hostname = $hostname;
				$unique_service_obj->servicename = $servicename;

				$unique_service_array[$i] = $unique_service_obj;
				unset($unique_service_obj);

				$i++;
			}

			//array counter
			$s = 0;

			foreach($unique_service_array as $services)
			{
				$service_obj = $this->_get_alert_total_service($services->hostname, $services->servicename);

				$servicegroup_obj->alert_host_total += $service_obj->alert_host_total;
				$servicegroup_obj->alert_host_soft_total += $service_obj->alert_host_soft_total;
				$servicegroup_obj->alert_host_hard_total += $service_obj->alert_host_up_total;

				$servicegroup_obj->alert_host_up_total += $service_obj->alert_host_up_total;
				$servicegroup_obj->alert_host_up_soft += $service_obj->alert_host_up_soft;
				$servicegroup_obj->alert_host_up_hard += $service_obj->alert_host_up_hard;

				$servicegroup_obj->alert_host_down_total += $service_obj->alert_host_down_total;
				$servicegroup_obj->alert_host_down_soft += $service_obj->alert_host_down_soft;
				$servicegroup_obj->alert_host_down_hard += $service_obj->alert_host_down_hard;

				$servicegroup_obj->alert_host_unreachable_total += $service_obj->alert_host_unreachable_total;
				$servicegroup_obj->alert_host_unreachable_soft += $service_obj->alert_host_unreachable_soft;
				$servicegroup_obj->alert_host_unreachable_hard += $service_obj->alert_host_unreachable_hard;

				$servicegroup_obj->alert_service_total += $service_obj->alert_service_total;
				$servicegroup_obj->alert_service_soft_total += $service_obj->alert_service_soft_total;
				$servicegroup_obj->alert_service_hard_total += $service_obj->alert_service_hard_total;

				$servicegroup_obj->alert_service_ok_total += $service_obj->alert_service_ok_total;
				$servicegroup_obj->alert_service_ok_soft += $service_obj->alert_service_ok_soft;
				$servicegroup_obj->alert_service_ok_hard += $service_obj->alert_service_ok_hard;

				$servicegroup_obj->alert_service_warning_total += $service_obj->alert_service_warning_total;
				$servicegroup_obj->alert_service_warning_soft += $service_obj->alert_service_warning_soft;
				$servicegroup_obj->alert_service_warning_hard += $service_obj->alert_service_warning_hard;

				$servicegroup_obj->alert_service_unknown_total += $service_obj->alert_service_unknown_total;
				$servicegroup_obj->alert_service_unknown_soft += $service_obj->alert_service_unknown_soft;
				$servicegroup_obj->alert_service_unknown_hard += $service_obj->alert_service_unknown_hard;

				$servicegroup_obj->alert_service_critical_total += $service_obj->alert_service_critical_total;
				$servicegroup_obj->alert_service_critical_soft += $service_obj->alert_service_critical_soft;
				$servicegroup_obj->alert_service_critical_hard += $service_obj->alert_service_critical_hard;

				unset($service_obj);
			}

			foreach($servicegroup_obj as $items)
			{
				$items = json_encode($items);
			}

			return $servicegroup_obj;
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

	public function get_alert_histogram()
	{

	}

	//Notifications section
	public function get_notification($input_date)
	{
		$this->_get_data($input_date, 'TODAY');
		$this->_insert_data();

		//array counter
		$i = 0;
		$temp_array = array();

		$this->_notification_array = $this->_parse_log($this->_host_service_notification_array, 'notification');

		//encode the data into JSON format
		//also filter the data by date
		foreach($this->_notification_array as $items)
		{
			$temp_array[$i] = $items;
			$items = json_encode($items);

			$i++;
		}

		$this->_notification_array = $temp_array;

		return $this->_notification_array;
	}

	//Event log section
	public function get_event_log($input_date)
	{	
		$this->_get_data($input_date, 'TODAY');
		$this->_insert_data();

		//array counter
		$i = 0;
		$temp_array = array();

		$this->_event_array = $this->_parse_log($this->_data_array, 'event');

		//encode the data into JSON format
		//also filter the data by date
		foreach($this->_event_array as $items)
		{
			$temp_array[$i] = $items;
			$items = json_encode($items);

			$i++;
		}

		$this->_event_array = $temp_array;

		return $this->_event_array;
	}

	//UTILITY FUNCTION
	//Get log file data
	private function _get_nagios_log()
	{
		//opening the nagios log file
		$logfile = fopen('/usr/local/nagios/var/nagios.log', 'r') or die('File not found !');

		while(! feof($logfile) )
		{
			$this->_data_array[$this->_counter] = trim(fgets($logfile));
			$this->_counter++;
		}

		fclose($logfile);
	}

	//Get archive log file data
	//Adapted from https://stackoverflow.com/questions/18558445/read-multiple-files-with-php-from-directory
	private function _get_archive_log($input_date)
	{
		$modify_date = date('m-d-Y', (int)$input_date);

		$filepath = '/usr/local/nagios/var/archives/nagios-'.$modify_date.'-00.log';

		if(file_exists($filepath))
		{
			//opening the nagios log file
			$logfile = fopen($filepath, 'r') or die('File not found !');

			while(! feof($logfile))
			{
				$this->_data_array[$this->_counter] = trim(fgets($logfile));
				$this->_counter++;
			}

			fclose($logfile);
		}
		else
		{
			echo 'File : nagios-'.$modify_date.'-00.log not found';
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

	//Function used to get data from log file
	private function _get_data($input_date, $input_period)
	{
		$date_array = array();

		if($this->_compare_string($input_period, 'TODAY'))
		{
			//the date entered is today
			if($this->_is_today((int)$input_date))
			{
				$this->_get_nagios_log();
			}
			else
			{	
				$this->_get_archive_log($input_date);
			}
		}
		//$input_period = 'CUSTOM'
		else if(is_array($input_date))
		{
			$start_date = $input_date[0];
			$end_date = $input_date[1];

			//array counter
			$i = 0;

			while($start_date <= $end_date)
			{
				$date_array[$i] = $start_date;
				$start_date = strtotime('+1 day', $start_date);

				$i++;
			}

			foreach($date_array as $date)
			{
				$this->_get_archive_log($date);
			}
		}
		else
		{
			//the date entered is today
			if($this->_is_today($input_date))
			{
				$this->_get_nagios_log();
			}
			else
			{
				$date_array = $this->_get_date_range($input_date, $input_period);

				foreach($date_array as $date)
				{
					$this->_get_archive_log($date);
				}
			}
		}
	}

	//Function used to determine whether the input date is today
	private function _is_today($input_date)
	{
		if($this->_compare_date('TODAY', $input_date, time()))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	//Function used to get the date between a period
	private function _get_date_range($input_date, $input_period)
	{
		//Adapted from : https://stackoverflow.com/questions/4312439/php-return-all-dates-between-two-dates-in-an-array
		$date_array = array();
		$start_end_array = array();
		$start_end_array = $this->_get_start_end_date($input_date, $input_period);
		$current_date = $start_end_array[0];
		$end_date = $start_end_array[1];

		//array counter
		$i = 0;

		while($current_date <= $end_date)
		{
			$date_array[$i] = $current_date;
			$current_date = strtotime('+1 day', $current_date);

			$i++;
		}

		return $date_array;
	}

	//Function used to get the start date from end date and period
	private function _get_start_end_date($input_date, $input_period)
	{
		$return_array = array();

		if($this->_compare_string('LAST 24 HOURS'))
		{
			$return_array[0] = (int)$input_date - 86400;
			$return_array[1] = (int)$input_date;
		}
		else if($this->_compare_string($input_period, 'YESTERDAY'))
		{
			$yesterday = new DateTime();
			$yesterday->setTimestamp( (int)$input_date );
			$yesterday->modify('Yesterday');

			$return_array[0] = $yesterday->getTimestamp();
			$return_array[1] = (int)$input_date;
		}
		else if($this->_compare_string($input_period, 'THIS WEEK'))
		{
			$monday = new DateTime();
			$monday->setTimestamp( (int)$input_date );
			$monday->modify('Monday this week');

			$return_array[0] = $monday->getTimestamp();

			$sunday = new DateTime();
			$sunday->setTimestamp( (int)$input_date );
			$sunday->modify('Sunday this week');

			$return_array[1] = $sunday->getTimestamp();
		}
		else if($this->_compare_string($input_period, 'LAST 7 DAYS'))
		{
			$return_array[0] = (int)$input_date - ( 7 * 86400 );
			$return_array[1] = (int)$input_date;
		}
		else if($this->_compare_string($input_period, 'LAST WEEK'))
		{
			$monday = new DateTime();
			$monday->setTimestamp( (int)$input_date );
			$monday->modify('Monday last week');

			$return_array[0] = $monday->getTimestamp();

			$sunday = new DateTime();
			$sunday->setTimestamp( (int)$input_date );
			$sunday->modify('Sunday last week');

			$return_array[1] = $sunday->getTimestamp();
		}
		else if($this->_compare_string($input_period, 'THIS MONTH'))
		{
			$month_first = new DateTime();
			$month_first->setTimestamp( (int)$input_date );
			$month_first->modify('first day of this month');

			$return_array[0] = $month_first->getTimestamp();

			$month_last = new DateTime();
			$month_last->setTimestamp( (int)$input_date );
			$month_last->modify('last day of this month');

			$return_array[1] = $month_last->getTimestamp();
		}
		else if($this->_compare_string($input_period, 'LAST 31 DAYS'))
		{
			$return_array[0] = (int)$input_date - ( 31 * 86400 );
			$return_array[1] = (int)$input_date;
		}
		else if($this->_compare_string($input_period, 'LAST MONTH'))
		{
			$month_first = new DateTime();
			$month_first->setTimestamp( (int)$input_date );
			$month_first->modify('first day of last month');

			$return_array[0] = $month_first->getTimestamp();

			$month_last = new DateTime();
			$month_last->setTimestamp( (int)$input_date );
			$month_last->modify('last day of last month');

			$return_array[1] = $month_last->getTimestamp();
		}
		else if($this->_compare_string($input_period, 'THIS YEAR'))
		{
			$return_array[0] = strtotime('Jan 1');
			$return_array[1] = strtotime('Dec 31 23:59:59');	
		}
		//$input_period = 'LAST YEAR'
		else 
		{
			$return_array[0] = strtotime('Jan 1 last year');
			$return_array[1] = strtotime('Dec 31 last year 23:59:59');
		}

		return $return_array;
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
	private function _get_alert_summary_host_service($input_array, $input_host, $input_service, $input_logtype, $input_state_type, $input_state)
	{
		//array counter
		$i = 0;
		$return_array = array();

		//filter the array based on the request
		foreach($input_array as $items)
		{
			//custom report option	
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

	//Function used to get alert total for service and servicegroup
	private function _get_alert_total_service($input_host_name, $input_service_name)
	{
		$alert_obj = new StdClass();

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
			if($this->_compare_string($input_host_name, $alert_producer->hostname) && $this->_compare_string($input_service_name, $alert_producer->servicename))
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
		$alert_obj->servicename = $input_service_name;

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