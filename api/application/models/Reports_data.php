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
	protected $return_array = array();
	protected $temp_array = array();
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
	//Availability section
	public function get_availability($input_period, $input_date, $input_host_service, $assume_initial_state, $assume_state_retention, $assume_state_during_downtime, $include_soft_state, $first_assume_host_state, $first_assume_service_state, $backtrack_archive)
	{
		/*
		//array counter 
		$i = 0;
		$this->temp_array = $this->parse_log($this->_alert_array, 'alert');
		//filter the data into $return_array based on request
		if(is_array($input_host_service))
		{
			for($j = 0; $j < count($input_host_service); $j++)
			{
				//filter the array based on request
				foreach($this->temp_array as $items)
				{
					//custom report option
					//compare date
					if($this->compare_date($input_period, $input_date, $items->datetime))
					{
						$this->return_array[$i] = $items;
						$i++;
					}
				}
			}
		}
		else
		{
			//filter the array based on request
			foreach($this->temp_array as $items)
			{
				//custom report option
				//compare date
				if($this->compare_date($input_period, $input_date, $items->datetime))
				{
					$this->return_array[$i] = $items;
					$i++;
				}
			}
		}
		if(is_array($input_date))
		{
			$start_date = date('Y-m-d', (int)$input_date[0]);
		}
		else
		{
			$start_date = $this->get_date($input_period, $input_date);
		}
		$current_state = NULL;
		foreach($return_array as $data)
		{
			if($this->compare_date('TODAY', $start_date, $data->datetime))
			{
				$current_state = $data->state;
			}
		}
		foreach($this->return_array as $alerts)
		{
			//encode the $this->return_array after filter into JSON format
			$alerts = json_encode($alerts);
		}
		return $this->return_array;
		*/
	}
	//Trends section
	public function get_trend()
	{
		$this->return_array = $this->parse_log($this->_alert_array, 'alert');
		//encode the data into JSON format
		foreach($this->return_array as $items)
		{
			$items = json_encode($items);
		}
		return $this->return_array;
	}
	//Alert History section
	public function get_history_data($input_date)
	{
		//array counter
		$i = 0;
		$this->return_array = $this->parse_log($this->_alert_array, 'alert');
		//encode the data into JSON format
		//also filter the data by date
		foreach($this->return_array as $items)
		{
			if($this->compare_date('TODAY', $input_date, $items->datetime))
			{
				$this->temp_array[$i] = $items;
				$items = json_encode($items);
				$i++;
			}
		}
		$this->return_array = $this->temp_array;
		return $this->return_array;
	}
	//Alert Summary section
	public function get_alert_summary($return_type, $input_period, $input_date, $input_host_service, $input_logtype, $input_state_type, $input_state)
	{
		//array counter
		$i = 0;
		$this->temp_array = $this->parse_log($this->_alert_array, 'alert');
		//filter the data into $return_array based on request
		if(is_array($input_host_service))
		{
			for($j = 0; $j < count($input_host_service); $j++)
			{
				//filter the array based on request
				foreach($this->temp_array as $items)
				{
					//custom report option
					//compare date
					if($this->compare_date($input_period, $input_date, $items->datetime))
					{
						//compare hostname or servicename based on different input
						if($this->compare_string($input_host_service[$j], $items->hostname) or $this->compare_string($input_host_service[$j], $items->servicename))
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
										$this->return_array[$i] = $items;
										$i++;	
									}
								}
							}
						}
					}
				}
			}
		}
		else
		{
			//filter the array based on request
			foreach($this->temp_array as $items)
			{
				//custom report option
				//compare date
				if($this->compare_date($input_period, $input_date, $items->datetime))
				{
					//compare hostname or servicename based on different input
					if($this->compare_string($input_host_service, $items->hostname) or $this->compare_string($input_host_service, $items->servicename))
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
									$this->return_array[$i] = $items;
									$i++;	
								}
							}
						}
					}
				}
			}
		}
		//$return_type = 'TOP_PRODUCER' section
		if($this->compare_string($return_type, 'TOP_PRODUCER'))
		{
			$producer_array = array();
			$producer_obj = new StdCLass();
			//array counter
			$i = 0;
			foreach($this->return_array as $alert_producer)
			{
				if(!empty($producer_array))
				{
					for($k = 0; $k < count($producer_array); $k++)
					{
						if($this->compare_string($alert_producer->hostname, $producer->hostname) && $this->compare_string($alert_producer->servicename, $producer->servicename))
						{
							$producer->total_alert++;
						}
						else
						{
							$producer_obj->logtype = $alert_producer->logtype;
							$producer_obj->hostname = $alert_producer->hostname;
							$producer_obj->servicename = $alert_producer->servicename;
							$producer_obj->total_alert = 1;
							$producer_array[$i] = $producer_obj;
							$i++;
							unset($producer_obj);
						}
					}
				}
				else
				{
					$producer_obj->logtype = $alert_producer->logtype;
					$producer_obj->hostname = $alert_producer->hostname;
					$producer_obj->servicename = $alert_producer->servicename;
					$producer_obj->total_alert = 1;
					$producer_array[$i] = $producer_obj;
					$i++;
					unset($producer_obj);
				}
			}
			foreach($producer_array as $producers)
			{
				$producers = json_encode($producers);
			}
			return $producer_array;
		}
		//$return_type = 'ALERT_TOTAL' section
		//find alert totals for alert summary section
		else if($this->compare_string($return_type, 'ALERT_TOTAL'))
		{
			$alert_totals = 0;
			$alert_host_up_total = 0;
			$alert_host_up_soft = 0;
			$alert_host_up_hard = 0;
			$alert_host_down_total = 0;
			$alert_host_down_soft = 0;
			$alert_host_down_hard = 0;
			$alert_host_unreachable_total = 0;
			$alert_host_unreachable_soft = 0;
			$alert_host_unreachable_hard = 0;
			$alert_host_pending_total = 0;
			$alert_host_pending_soft = 0;
			$alert_host_pending_hard = 0;
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
			$alert_service_pending_total = 0;
			$alert_service_pending_soft = 0;
			$alert_service_pending_hard = 0;
			$alert_obj = new StdCLass();
			$alert_obj_array = array();
			if(is_array($input_host_service))
			{
				for($i = 0; $i < count($input_host_service); $i++)
				{
					foreach($this->return_array as $alerts)
					{
						if($this->compare_string($input_host_service[$i], $alerts->hostname) or $this->compare_string($input_host_service[$i], $alerts->servicename))
						{
							$alert_totals++;
							if($this->compare_string($alerts->state_type, 'SOFT'))
							{
								if($this->compare_string($alerts->state, 'UP'))
								{
									$alert_host_up_total++;
									$alert_host_up_soft++;
								}
								else if($this->compare_string($alerts->state, 'DOWN'))
								{
									$alert_host_down_total++;
									$alert_host_down_soft++;
								}
								else if($this->compare_string($alerts->state, 'UNREACHABLE'))
								{
									$alert_host_unreachable_total++;
									$alert_host_unreachable_soft++;
								}
								else if($this->compare_string($alerts->state, 'OK'))
								{
									$alert_service_ok_total++;
									$alert_service_ok_soft++;
								}
								else if($this->compare_string($alerts->state, 'WARNING'))
								{
									$alert_service_warning_total++;
									$alert_service_warning_soft++;
								}
								else if($this->compare_string($alerts->state, 'UNKNOWN'))
								{
									$alert_service_unknown_total++;
									$alert_service_unknown_soft++;
								}
								else if($this->compare_string($alerts->state, 'CRITICAL'))
								{
									$alert_service_critical_total++;
									$alert_service_critical_soft++;
								}
								//$alert->state = PENDING
								else 
								{
									//for HOST ALERT, the servicename is NULL
									if(empty($alerts->servicename))
									{
										$alert_host_pending_total++;
										$alert_host_pending_soft++;
									}
									else
									{
										$alert_service_pending_total++;
										$alert_service_pending_soft++;
									}
								}
							}
							//$this->compare_string($alerts->state_type, 'HARD') = true
							else
							{
								if($this->compare_string($alerts->state, 'UP'))
								{
									$alert_host_up_total++;
									$alert_host_up_hard++;
								}
								else if($this->compare_string($alerts->state, 'DOWN'))
								{
									$alert_host_down_total++;
									$alert_host_down_hard++;
								}
								else if($this->compare_string($alerts->state, 'UNREACHABLE'))
								{
									$alert_host_unreachable_total++;
									$alert_host_unreachable_hard++;
								}
								else if($this->compare_string($alerts->state, 'OK'))
								{
									$alert_service_ok_total++;
									$alert_service_ok_hard++;
								}
								else if($this->compare_string($alerts->state, 'WARNING'))
								{
									$alert_service_warning_total++;
									$alert_service_warning_hard++;
								}
								else if($this->compare_string($alerts->state, 'UNKNOWN'))
								{
									$alert_service_unknown_total++;
									$alert_service_unknown_hard++;
								}
								else if($this->compare_string($alerts->state, 'CRITICAL'))
								{
									$alert_service_critical_total++;
									$alert_service_critical_hard++;
								}
								//$alert->state = PENDING
								else 
								{
									//for HOST ALERT, the servicename is NULL
									if(empty($alerts->servicename))
									{
										$alert_host_pending_total++;
										$alert_host_pending_hard++;
									}
									else
									{
										$alert_service_pending_total++;
										$alert_service_pending_hard++;
									}
								}
							}
							$alert_obj->hostname = $alerts->hostname;
							$alert_obj->servicename = $alerts->servicename;
							$alert_obj->alert_totals = $alert_totals;
							$alert_obj->alert_host_up_total = $alert_host_up_total;
							$alert_obj->alert_host_up_soft = $alert_host_up_soft;
							$alert_obj->alert_host_up_hard = $alert_host_up_hard;
							$alert_obj->alert_host_down_total = $alert_host_down_total;
							$alert_obj->alert_host_down_soft = $alert_host_down_soft;
							$alert_obj->alert_host_down_hard = $alert_host_down_hard;
							$alert_obj->alert_host_unreachable_total = $alert_host_unreachable_total;
							$alert_obj->alert_host_unreachable_soft = $alert_host_unreachable_soft;
							$alert_obj->alert_host_unreachable_hard = $alert_host_unreachable_hard;
							$alert_obj->alert_host_pending_total = $alert_host_pending_total;
							$alert_obj->alert_host_pending_soft = $alert_host_pending_soft;
							$alert_obj->alert_host_pending_hard = $alert_host_pending_hard;
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
							$alert_obj->alert_service_pending_total = $alert_service_pending_total;
							$alert_obj->alert_service_pending_soft = $alert_service_pending_soft;
							$alert_obj->alert_service_pending_hard = $alert_service_pending_hard;
							$alert_obj_array[$i] = $alert_obj;
							unset($alert_obj);
						}
					}
				}
			}
			else
			{
				foreach($this->return_array as $alerts)
				{			
					$alert_totals++;
					if($this->compare_string($alerts->state_type, 'SOFT'))
					{
						if($this->compare_string($alerts->state, 'UP'))
						{
							$alert_host_up_total++;
							$alert_host_up_soft++;
						}
						else if($this->compare_string($alerts->state, 'DOWN'))
						{
							$alert_host_down_total++;
							$alert_host_down_soft++;
						}
						else if($this->compare_string($alerts->state, 'UNREACHABLE'))
						{
							$alert_host_unreachable_total++;
							$alert_host_unreachable_soft++;
						}
						else if($this->compare_string($alerts->state, 'OK'))
						{
							$alert_service_ok_total++;
							$alert_service_ok_soft++;
						}
						else if($this->compare_string($alerts->state, 'WARNING'))
						{
							$alert_service_warning_total++;
							$alert_service_warning_soft++;
						}
						else if($this->compare_string($alerts->state, 'UNKNOWN'))
						{
							$alert_service_unknown_total++;
							$alert_service_unknown_soft++;
						}
						else if($this->compare_string($alerts->state, 'CRITICAL'))
						{
							$alert_service_critical_total++;
							$alert_service_critical_soft++;
						}
						//$alert->state = PENDING
						else 
						{
							//for HOST ALERT, the servicename is NULL
							if(empty($alerts->servicename))
							{
								$alert_host_pending_total++;
								$alert_host_pending_soft++;
							}
							else
							{
								$alert_service_pending_total++;
								$alert_service_pending_soft++;
							}
						}
					}
					//$this->compare_string($alerts->state_type, 'HARD') = true
					else
					{
						if($this->compare_string($alerts->state, 'UP'))
						{
							$alert_host_up_total++;
							$alert_host_up_hard++;
						}
						else if($this->compare_string($alerts->state, 'DOWN'))
						{
							$alert_host_down_total++;
							$alert_host_down_hard++;
						}
						else if($this->compare_string($alerts->state, 'UNREACHABLE'))
						{
							$alert_host_unreachable_total++;
							$alert_host_unreachable_hard++;
						}
						else if($this->compare_string($alerts->state, 'OK'))
						{
							$alert_service_ok_total++;
							$alert_service_ok_hard++;
						}
						else if($this->compare_string($alerts->state, 'WARNING'))
						{
							$alert_service_warning_total++;
							$alert_service_warning_hard++;
						}
						else if($this->compare_string($alerts->state, 'UNKNOWN'))
						{
							$alert_service_unknown_total++;
							$alert_service_unknown_hard++;
						}
						else if($this->compare_string($alerts->state, 'CRITICAL'))
						{
							$alert_service_critical_total++;
							$alert_service_critical_hard++;
						}
						//$alert->state = PENDING
						else 
						{
							//for HOST ALERT, the servicename is NULL
							if(empty($alerts->servicename))
							{
								$alert_host_pending_total++;
								$alert_host_pending_hard++;
							}
							else
							{
								$alert_service_pending_total++;
								$alert_service_pending_hard++;
							}
						}
					}
					$alert_obj->hostname = $alerts->hostname;
					$alert_obj->servicename = $alerts->servicename;
					$alert_obj->alert_totals = $alert_totals;
					$alert_obj->alert_host_up_total = $alert_host_up_total;
					$alert_obj->alert_host_up_soft = $alert_host_up_soft;
					$alert_obj->alert_host_up_hard = $alert_host_up_hard;
					$alert_obj->alert_host_down_total = $alert_host_down_total;
					$alert_obj->alert_host_down_soft = $alert_host_down_soft;
					$alert_obj->alert_host_down_hard = $alert_host_down_hard;
					$alert_obj->alert_host_unreachable_total = $alert_host_unreachable_total;
					$alert_obj->alert_host_unreachable_soft = $alert_host_unreachable_soft;
					$alert_obj->alert_host_unreachable_hard = $alert_host_unreachable_hard;
					$alert_obj->alert_host_pending_total = $alert_host_pending_total;
					$alert_obj->alert_host_pending_soft = $alert_host_pending_soft;
					$alert_obj->alert_host_pending_hard = $alert_host_pending_hard;
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
					$alert_obj->alert_service_pending_total = $alert_service_pending_total;
					$alert_obj->alert_service_pending_soft = $alert_service_pending_soft;
					$alert_obj->alert_service_pending_hard = $alert_service_pending_hard;
					$alert_obj_array[0] = $alert_obj;
					unset($alert_obj);
				}
			}
			foreach($alert_obj_array as $items)
			{
				$items = json_encode($items);
			}
				
			return $alert_obj_array;
		}
		//if($this->compare_string($return_type, 'NORMAL'))
		else
		{
			foreach($this->return_array as $alerts)
			{
				//encode the $this->return_array after filter into JSON format
				$alerts = json_encode($alerts);
			}
			return $this->return_array;
		}
	}
	//Alert Histogram section
	public function get_alert_histogram()
	{
		$this->return_array = $this->parse_log($this->_alert_array, 'alert');
		//encode the data into JSON format
		foreach($this->return_array as $items)
		{
			$items = json_encode($items);
		}
		return $this->return_array;
	}
	//Notifications section
	public function get_notification($input_date)
	{
		//array_counter
		$i = 0;
		$this->return_array = $this->parse_log($this->_notifications_array, 'notification');
		//encode the data into JSON format
		//also filter the data by date
		foreach($this->return_array as $items)
		{
			if($this->compare_date('TODAY', $input_date, $items->datetime))
			{
				$this->temp_array[$i] = $items;
				$items = json_encode($items);
				$i++;
			}
		}
		$this->return_array = $this->temp_array;
		return $this->return_array;
	}
	//Event Log section
	public function get_event_log($input_date)
	{
		//array counter
		$i = 0;
		$this->return_array = $this->parse_log($this->_data_array, 'event');
		//encode the data into JSON format
		//also filter the data by date
		foreach($this->return_array as $items)
		{
			if($this->compare_date('TODAY', $input_date, $items->datetime))
			{
				$this->temp_array[$i] = $items;
				$items = json_encode($items);
				$i++;
			}
		}
		$this->return_array = $this->temp_array;
		return $this->return_array;
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
		$sorted_obj = new StdCLass();
		foreach($input_array as $logs)
		{
			if($this->compare_string($_type, 'notification'))
			{
				if(strpos($logs, 'HOST NOTIFICATION') !== false)
				{
					list($input_time, $other_message) = explode(' ', $logs, 2);
					list($logtype, $information) = explode(':', $other_message, 2);
					list($contact, $host, $state, $notificationcommand, $detail_message) = explode(';', $information, 5);
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
			else if($this->compare_string($_type, 'alert'))
			{
				if(strpos($logs, 'HOST ALERT') !== false)
				{
					list($input_time, $event_message) = explode(' ', $logs, 2);
					list($logtype, $information) = explode(':', $event_message, 2);
					list($hostname, $state, $state_type, $retry_count, $detail_message) = explode(';', $information, 5);
		
					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = $logtype;
					$sorted_obj->hostname = $hostname;
					$sorted_obj->servicename = NULL;
					$sorted_obj->state = $state;
					$sorted_obj->state_type = $state_type;
					$sorted_obj->retry_count = $retry_count;
					$sorted_obj->messages = $detail_message;
				}
				else if(strpos($logs, 'SERVICE ALERT') !== false)
				{
					list($input_time, $event_message) = explode(' ', $logs, 2);
					list($logtype, $information) = explode(':', $event_message, 2);
					list($hostname, $servicename, $state, $state_type, $retry_count, $detail_message) = explode(';', $information, 6);
		
					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = $logtype;
					$sorted_obj->hostname = $hostname;
					$sorted_obj->servicename = $servicename;
					$sorted_obj->state = $state;
					$sorted_obj->state_type = $state_type;
					$sorted_obj->retry_count = $retry_count;
					$sorted_obj->messages = $detail_message;
				}
				//strpos($logs, 'SERVICE FLAPPING ALERT') !== false
				else
				{
					list($input_time, $event_message) = explode(' ', $logs, 2);
					list($logtype, $information) = explode(':', $event_message, 2);
					list($hostname, $servicename, $state, $detail_message) = explode(';', $information, 4);
					$sorted_obj->datetime = trim($input_time, '[]');
					$sorted_obj->logtype = $logtype;
					$sorted_obj->hostname = $hostname;
					$sorted_obj->servicename = $servicename;
					$sorted_obj->state = $state;
					$sorted_obj->state_type = NULL;
					$sorted_obj->retry_count = NULL;
					$sorted_obj->messages = $detail_message;
				}
			}
			//$_type = 'event'
			else
			{
				list($input_time, $event_message) = explode(' ', $logs, 2);
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
	//Adapted from : http://php.net/manual/en/datetime.formats.relative.php
	private function compare_date($input_period, $input_date, $data_date)
	{
		if($this->compare_string($input_period, 'TODAY'))
		{
			$modify_input_date = date('Y-m-d', (int)$input_date);
			$modify_data_date = date('Y-m-d', (int)$data_date);
		
			if($this->compare_string($modify_input_date, $modify_data_date))
			{
				return true;
			}
			//the date is not same
			else
			{
				return false;
			}
		}
		else if($this->compare_string($input_period, 'LAST 24 HOURS'))
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
		else if($this->compare_string($input_period, 'YESTERDAY'))
		{
			$modify_input_date = date('Y-m-d', ( ((int)$input_date) - 86400) );
			$modify_data_date = date('Y-m-d', (int)$data_date);
			if($this->compare_string($modify_input_date, $modify_data_date))
			{
				return true;
			}
			//the date is not same
			else
			{
				return false;
			}
		}
		else if($this->compare_string($input_period, 'THIS WEEK'))
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
				if($this->compare_string( (date('Y-m-d', (int)$data_date)), ($sunday->format('Y-m-d')) ))
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		else if($this->compare_string($input_period, 'LAST 7 DAYS'))
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
		else if($this->compare_string($input_period, 'LAST WEEK'))
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
				if($this->compare_string( (date('Y-m-d', (int)$data_date)), ($sunday->format('Y-m-d')) ))
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		else if($this->compare_string($input_period, 'THIS MONTH'))
		{
			$modify_input_month = date('Y-m', (int)$input_date);
			$modify_data_month = date('Y-m', (int)$data_date);
			if($this->compare_string($modify_input_month, $modify_data_month))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else if($this->compare_string($input_period, 'LAST 31 DAYS'))
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
		else if($this->compare_string($input_period, 'LAST MONTH'))
		{
			$modify_input_month = date('Y-m-d', (int)$input_date);
			$modify_data_month = date('Y-m', (int)$data_date);
			$last_month_string = $modify_input_month.' -1 month';
			$last_month = date('Y-m', (strtotime($last_month_string)));
			if($this->compare_string($last_month, $modify_data_month))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else if($this->compare_string($input_period, 'THIS YEAR'))
		{
			$modify_input_year = date('Y', (int)$input_date);
			$modify_data_year = date('Y', (int)$data_date);
			if($this->compare_string($modify_input_year, $modify_data_year))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else if($this->compare_string($input_period, 'LAST YEAR'))
		{
			$modify_input_year = date('Y', (int)$input_date) - 1;
			$modify_data_year = date('Y', (int)$data_date);
			if($this->compare_string($modify_input_year, $modify_data_year))
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
					if($this->compare_string( (date('Y-m-d', (int)$data_date)), (date('Y-m-d', (int)$input_date[1])) ))
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
	//get the start date 
	//Adapted from : http://php.net/manual/en/datetime.formats.relative.php
	private function get_date($input_period, $input_date)
	{
		if($this->compare_string($input_period, 'TODAY'))
		{
			$modify_input_date = date('Y-m-d', (int)$input_date);
		
			return $modify_input_date;
		}
		else if($this->compare_string($input_period, 'LAST 24 HOURS'))
		{
			$modify_input_date = new DateTime();
			$modify_input_date->setTimestamp( (int)$input_date );
			$modify_input_date->modify('-1 day');
			
			return $modify_input_date->format('Y-m-d');
		}
		//1 day = 24 hour * 60 min * 60 sec = 86400 sec
		else if($this->compare_string($input_period, 'YESTERDAY'))
		{
			$monday = new DateTime();
			$monday->setTimestamp( (int)$input_date );
			$monday->modify('yesterday');
			return $monday->format('Y-m-d');
		}
		else if($this->compare_string($input_period, 'THIS WEEK'))
		{
			$monday = new DateTime();
			$monday->setTimestamp( (int)$input_date );
			$monday->modify('Monday this week');
			return $monday->format('Y-m-d');
		}
		else if($this->compare_string($input_period, 'LAST 7 DAYS'))
		{
			$modify_input_date = new DateTime();
			$modify_input_date->setTimestamp( (int)$input_date );
		
			$modify_input_date->modify('-7 days');
			
			return $modify_input_date->format('Y-m-d');
		}
		else if($this->compare_string($input_period, 'LAST WEEK'))
		{
			$monday = new DateTime();
			$monday->setTimestamp( (int)$input_date );
			$monday->modify('Monday last week');
			return $monday->format('Y-m-d');
		}
		else if($this->compare_string($input_period, 'THIS MONTH'))
		{
			//Adapted from : https://stackoverflow.com/questions/2094797/the-first-day-of-the-current-month-in-php-using-date-modify-as-datetime-object
			$modify_input_month = new DateTime();
			$modify_input_month->setTimestamp( (int)$input_date );
			$modify_input_month->modify('first day of this month');
			return $modify_input_month->format('Y-m-d');
		}
		else if($this->compare_string($input_period, 'LAST 31 DAYS'))
		{
			$modify_input_date = new DateTime();
			$modify_input_date->setTimestamp( (int)$input_date );
			$modify_input_date->modify('-31 days');
			
			return $modify_input_date->format('Y-m-d');
		}
		else if($this->compare_string($input_period, 'LAST MONTH'))
		{
			$modify_input_month = new DateTime();
			$modify_input_month->setTimestamp( (int)$input_date );
			$modify_input_month->modify('first day of last month');
			return $modify_input_month->format('Y-m-d');
		}
		else if($this->compare_string($input_period, 'THIS YEAR'))
		{
			$input_year = date('Y', (int)$input_date);
			
			$modify_input_year = $input_year.'-01-01';
			return $modify_input_year;
		}
		//if($this->compare_string($input_period, 'LAST YEAR'))
		else 
		{
			$input_year = date('Y', (int)$input_date) - 1;
			
			$modify_input_year = $input_year.'-01-01';
			return $modify_input_year;
		}
	}
	private function compare_string($input_string, $data_string)
	{
		if(strcmp($input_string, 'ALL') == 0)
		{
			return true;
		}
		//compare host
		if(strcmp($input_string, 'ALL HOST') == 0)
		{
			return true;
		}
		//compare logtype
		if(strcmp($input_string, 'ALL ALERT') == 0)
		{
			return true;
		}
		//compare state_type
		if(strcmp($input_string, 'ALL STATE TYPE') == 0)
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
}
?>
