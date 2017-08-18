<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Testing extends CI_Model
{
	//array of data taken from nagios log file
	protected $_data_array = array();
	protected $_host_service_notification_array = array();
	protected $_host_service_alert_array = array();
	protected $_backtrack_array = array();

	//array that store return data
	protected $_availability_array = array();
	protected $_trend_array = array();
	protected $_alert_history_array = array();
	protected $_alert_summary_array = array();
	protected $_alert_histogram_array = array();
	protected $_notification_array = array();
	protected $_event_array = array();

	//array that store file that are failed to open
	protected $_problem_array = array();

	//array counter
	protected $_counter;
	protected $_problem;

	//constructor
	public function __construct()
	{
		parent::__construct();

		date_default_timezone_set('UTC');

		$this->_counter = 0;
		$this->_problem = 0;
	}

	//Written by Low Zhi Jian
	//Functions to be called by web service
	//Availability section
	public function get_availability($return_type, $input_period, $input_date, $input_host, $input_service, $assume_initial_state, $assume_state_retention, $assume_state_downtime, $include_soft, $first_assume_host_state, $first_assume_service_state, $backtrack_archive)
	{
		$this->_get_data($input_date, $input_period);
		$this->_insert_data();

		$temp_array = array();
		$temp_array = $this->_parse_log($this->_host_service_alert_array, 'trend');

		if($include_soft)
		{
			$this->_availability_array = $this->_get_availability_host_service($temp_array, $input_host, $input_service, 'ALL');
		}
		else
		{
			$this->_availability_array = $this->_get_availability_host_service($temp_array, $input_host, $input_service, 'HARD');
		}

		$output_array = array();
		$return_array = array();

		//array counter
		$i = 0;

		//$return_type = 'HOSTGROUP'
		if($return_type === 1)
		{
			if(is_array($input_host))
			{
				foreach($input_host as $hosts)
				{
					$output_array = $this->_get_return_host($assume_state_downtime, $this->_availability_array, $hosts, $input_period, $input_date, $backtrack_archive, $assume_initial_state, $first_assume_host_state);
					$return_array[$i] = $this->_get_state_total_host_availability($output_array, $hosts);

					$i++;
				}
			}
			else
			{
				$output_array = $this->_get_return_host($assume_state_downtime, $this->_availability_array, $input_host, $input_period, $input_date, $backtrack_archive, $assume_initial_state, $first_assume_host_state);
				$return_array[0] = $this->_get_state_total_host_availability($output_array, $input_host);
			}
		}
		//$return_type = 'SERVICEGROUP'
		else if($return_type === 2)
		{
			//get unique host and service pair
			$key_obj = new StdClass();
			$unique_service_obj = new StdClass();
			$unique_service_array = array();

			foreach($this->_availability_array as $services)
			{
				$keys = $services->hostname.' '.$services->servicename;

				$key_obj->$keys += 1;
			}
			
			//array counter 
			$j = 0;

			foreach($key_obj as $key => $value)
			{
				list($hostname, $servicename) = explode(' ', $key, 2);

				$unique_service_obj->hostname = $hostname;
				$unique_service_obj->servicename = $servicename;

				$unique_service_array[$j] = $unique_service_obj;
				unset($unique_service_obj);

				$j++;
			}

			if(is_array($input_host))
			{
				foreach($input_host as $hosts)
				{
					$output_array = $this->_get_return_host($assume_state_downtime, $this->_availability_array, $hosts, $input_period, $input_date, $backtrack_archive, $assume_initial_state, $first_assume_service_state);
					$return_array[$i] = $this->_get_state_total_host_availability($output_array, $hosts);

					$i++;	

					$service_array = $this->_get_return_service($assume_state_downtime, $this->_availability_array, $hosts, $input_service, $input_period, $input_date, $backtrack_archive, $assume_initial_state, $first_assume_service_state);

					foreach($unique_service_array as $unique_services)
					{
						$return_array[$i] = $this->_get_state_total_service_availability($service_array, $hosts, $unique_services->servicename);

						$i++;
					}
				}
			}
			else
			{
				$output_array = $this->_get_return_host($assume_state_downtime, $this->_availability_array, $input_host, $input_period, $input_date, $backtrack_archive, $assume_initial_state, $first_assume_host_state);

				$return_array[$i] = $this->_get_state_total_host_availability($output_array, $input_host);

				$i++;

				$service_array = $this->_get_return_service($assume_state_downtime, $this->_availability_array, $input_host, $input_service, $input_period, $input_date, $backtrack_archive, $assume_initial_state, $first_assume_service_state);

				foreach($unique_service_array as $unique_services)
				{
					$return_array[$i] = $this->_get_state_total_service_availability($service_array, $input_host, $unique_services->servicename);

					$i++;
				}
			}
		}
		//$return_type = 'HOST'
		else if($return_type === 3)
		{
			if($this->_compare_string($input_host, 'ALL'))
			{
				$host_collection = $this->nagios_data->get_collection('host');
				$host_array = array();

				//array counter
				$h = 0;

				foreach($host_collection as $host)
				{
					$host_array[$h] = $host->host_name;

					$h++;
				}

				foreach($host_array as $hosts)
				{
					$output_array = $this->_get_return_host($assume_state_downtime, $this->_availability_array, $hosts, $input_period, $input_date, $backtrack_archive, $assume_initial_state, $first_assume_host_state);
					$return_array[$i] = $this->_get_state_total_host_availability($output_array, $hosts);

					$i++;
				}
			}
			else 
			{
				$output_array = $this->_get_return_host($assume_state_downtime, $this->_availability_array, $input_host, $input_period, $input_date, $backtrack_archive, $assume_initial_state, $first_assume_host_state);
				$return_array[$i] = $this->_get_state_total_host_availability($output_array, $input_host);

				$i++;

				//get unique host and service pair
				$key_obj = new StdClass();
				$unique_service_obj = new StdClass();
				$unique_service_array = array();

				foreach($this->_availability_array as $services)
				{
					$keys = $services->hostname.' '.$services->servicename;

					$key_obj->$keys += 1;
				}
				
				//array counter 
				$j = 0;

				foreach($key_obj as $key => $value)
				{
					list($hostname, $servicename) = explode(' ', $key, 2);

					$unique_service_obj->hostname = $hostname;
					$unique_service_obj->servicename = $servicename;

					$unique_service_array[$j] = $unique_service_obj;
					unset($unique_service_obj);

					$j++;
				}

				$state_breakdown_array = array();

				//array counter
				$s = 0;

				$service_array = $this->_get_return_service($assume_state_downtime, $this->_availability_array, $input_host, $input_service, $input_period, $input_date, $backtrack_archive, $assume_initial_state, $first_assume_service_state);

				foreach($unique_service_array as $unique_services)
				{
					$state_breakdown_array[$s] = $this->_get_state_total_service_availability($service_array, $input_host, $unique_services->servicename);

					$s++;
				}

				$return_array[$i] = $state_breakdown_array;

				$i++;

				$host_log_array = array();
				$host_log_obj = new StdClass();

				//array_counter
				$a = 0;

				if(is_array($input_date))
				{
					$now = (int)$input_date[1];
				}
				else
				{
					$now = (int)$input_date;
				}

				$temp_array = array_reverse($temp_array);

				foreach($temp_array as $alerts)
				{
					if($this->_compare_string($alerts->hostname, $input_host))
					{
						if($this->_compare_string($alerts->logtype, 'HOST ALERT'))
						{
							$duration = $now - (int)$alerts->datetime;
							$host_log_obj->hostname = $alerts->hostname;
							$host_log_obj->duration = abs($duration);
							$host_log_obj->start_time = $now - $duration;
							$host_log_obj->end_time = $now;
							$host_log_obj->state = $this->_get_state_num($alerts->state);
							$host_log_obj->state_type = $alerts->state_type;
							$host_log_obj->messages = $alerts->messages;

							$host_log_array[$a] = $host_log_obj;
							$a++;

							$now = $now - $duration;
												
							unset($host_log_obj);
						}
					}
				}

				$return_array[$i] = $host_log_array;

				$i++;
			}
		}
		//$return_type = 'SERVICE'
		else if($return_type === 4)
		{
			if($this->_compare_string($input_service, 'ALL'))
			{
				$host_collection = $this->nagios_data->get_collection('host');
				$host_array = array();

				//array counter
				$i = 0;
				$h = 0;

				foreach($host_collection as $host)
				{
					$host_array[$h] = $host->host_name;

					$h++;
				}

				//get unique host and service pair
				$key_obj = new StdClass();
				$unique_service_obj = new StdClass();
				$unique_service_array = array();

				foreach($this->_availability_array as $services)
				{
					$keys = $services->hostname.' '.$services->servicename;

					$key_obj->$keys += 1;
				}
				
				//array counter 
				$j = 0;

				foreach($key_obj as $key => $value)
				{
					list($hostname, $servicename) = explode(' ', $key, 2);

					$unique_service_obj->hostname = $hostname;
					$unique_service_obj->servicename = $servicename;

					$unique_service_array[$j] = $unique_service_obj;
					unset($unique_service_obj);

					$j++;
				}

				foreach($unique_service_array as $unique_services)
				{
					$service_array = $this->_get_return_service($assume_state_downtime, $this->_availability_array, $unique_services->hostname, $unique_services->servicename, $input_period, $input_date, $backtrack_archive, $assume_initial_state, $first_assume_service_state);

					$return_array[$i] = $this->_get_state_total_service_availability($service_array, $unique_services->hostname, $unique_services->servicename);

					$i++;
				}
			}
			else
			{
				$output_array = $this->_get_return_service($assume_state_downtime, $this->_availability_array, $input_host, $input_service, $input_period, $input_date, $backtrack_archive, $assume_initial_state, $first_assume_service_state);

				$return_array[$i] = $this->_get_state_total_service_availability($output_array, $input_host, $input_service);

				$i++;

				$service_log_array = array();
				$service_log_obj = new StdClass();

				//array_counter
				$c = 0;

				if(is_array($input_date))
				{
					$now = (int)$input_date[1];
				}
				else
				{
					$now = (int)$input_date;
				}

				$temp_array = array_reverse($temp_array);

				foreach($temp_array as $alerts)
				{
					if($this->_compare_string($alerts->hostname, $input_host))
					{
						if($this->_compare_string($alerts->servicename, $input_service))
						{
							if($this->_compare_string($alerts->logtype, 'SERVICE ALERT'))
							{
								$duration = $now - (int)$alerts->datetime;
								$service_log_obj->hostname = $alerts->hostname;
								$service_log_obj->servicename = $alerts->servicename;
								$service_log_obj->duration = abs($duration);
								$service_log_obj->start_time = $now - $duration;
								$service_log_obj->end_time = $now;
								$service_log_obj->state = $this->_get_state_num($alerts->state);
								$service_log_obj->state_type = $alerts->state_type;
								$service_log_obj->messages = $alerts->messages;

								$service_log_array[$c] = $service_log_obj;
								$c++;

								$now = $now - $duration;
													
								unset($service_log_obj);
							}
						}
					}
				}

				$return_array[$i] = $service_log_array;

				$i++;
			}
		}
		//$return_type = 'HOST RESOURCE'
		else if($return_type === 5)
		{
			if($this->_compare_string($input_host, 'ALL'))
			{
				$host_resource_collection = $this->nagios_data->get_collection('hostresource');
				$host_resource_array = array();

				//array counter
				$i = 0;

				foreach($host_resource_collection as $resources)
				{
					$host_resource_array[$i] = $resources->service_description;

					$i++;
				}

				$resource_array = array();
				$resource_obj = new StdClass();

				//array counter 
				$j = 0;

				foreach($this->_data_array as $data)
				{
					foreach($host_resource_array as $resources)
					{
						if(strpos($data, $resources))
						{
							list($input_time, $event_message) = explode(' ', $data, 2);
							list($logtype, $information) = explode(':', $event_message, 2);
							list($hostname, $servicename, $state, $state_type, $retry_count, $detail_message) = explode(';', $information, 6);
				
							$resource_obj->datetime = trim($input_time, '[]');
							$resource_obj->logtype = 'SERVICE ALERT';
							$resource_obj->hostname = trim($hostname);
							$resource_obj->servicename = trim($servicename);
							$resource_obj->state = trim($state);
							$resource_obj->state_type = trim($state_type);
							$resource_obj->retry_count = trim($retry_count);
							$resource_obj->messages = trim($detail_message);
					
							$resource_array[$j] = $resource_obj;
							$j++;

							unset($input_time, $event_message, $logtype, $information, $hostname, $servicename, $state, $state_type, $retry_count, $detail_message, $resource_obj);
						}
					}
				}

				//get unique host and service pair
				$key_obj = new StdClass();
				$unique_service_obj = new StdClass();
				$unique_service_array = array();

				foreach($resource_array as $services)
				{
					$keys = $services->hostname.' '.$services->servicename;

					$key_obj->$keys += 1;
				}
				
				//array counter 
				$j = 0;

				foreach($key_obj as $key => $value)
				{
					list($hostname, $servicename) = explode(' ', $key, 2);

					$unique_service_obj->hostname = $hostname;
					$unique_service_obj->servicename = $servicename;

					$unique_service_array[$j] = $unique_service_obj;
					unset($unique_service_obj);

					$j++;
				}

				//array counter 
				$d = 0;

				foreach($unique_service_array as $unique_services)
				{
					$output_array = $this->_get_return_service($assume_state_downtime, $resource_array, $unique_services->hostname, $unique_services->servicename, $input_period, $input_date, $backtrack_archive, $assume_initial_state, $first_assume_service_state);
					$return_array[$d] = $this->_get_state_total_service_availability($output_array, $unique_services->hostname, $unique_services->servicename);

					$d++;
				}
			}
			else
			{
				$output_array = $this->_get_return_service($assume_state_downtime, $this->_availability_array, $input_host, $input_service, $input_period, $input_date, $backtrack_archive, $assume_initial_state, $first_assume_service_state);

				$return_array[$i] = $this->_get_state_total_service_availability($output_array, $input_host, $input_service);

				$i++;

				$service_log_array = array();
				$service_log_obj = new StdClass();

				//array_counter
				$c = 0;

				if(is_array($input_date))
				{
					$now = (int)$input_date[1];
				}
				else
				{
					$now = (int)$input_date;
				}

				$temp_array = array_reverse($temp_array);

				foreach($temp_array as $alerts)
				{
					if($this->_compare_string($alerts->hostname, $input_host))
					{
						if($this->_compare_string($alerts->servicename, $input_service))
						{
							if($this->_compare_string($alerts->logtype, 'SERVICE ALERT'))
							{
								$duration = $now - (int)$alerts->datetime;
								$service_log_obj->hostname = $alerts->hostname;
								$service_log_obj->servicename = $alerts->servicename;
								$service_log_obj->duration = abs($duration);
								$service_log_obj->start_time = $now - $duration;
								$service_log_obj->end_time = $now;
								$service_log_obj->state = $this->_get_state_num($alerts->state);
								$service_log_obj->state_type = $alerts->state_type;
								$service_log_obj->messages = $alerts->messages;

								$service_log_array[$c] = $service_log_obj;
								$c++;

								$now = $now - $duration;
													
								unset($service_log_obj);
							}
						}
					}
				}

				$return_array[$i] = $service_log_array;

				$i++;
			}
		}
		//$return_type = 'SERVICE RUNNING STATE'
		else if($return_type === 6)
		{
			if($this->_compare_string($input_service, 'ALL'))
			{
				$running_state_array = array();
				$running_state_obj = new StdClass();

				//array counter 
				$i = 0;

				foreach($this->_data_array as $data)
				{
					if(strpos($data, '_running_state'))
					{
						list($input_time, $event_message) = explode(' ', $data, 2);
						list($logtype, $information) = explode(':', $event_message, 2);
						list($hostname, $servicename, $state, $state_type, $retry_count, $detail_message) = explode(';', $information, 6);

						$running_state_obj->datetime = trim($input_time, '[]');
						$running_state_obj->logtype = 'SERVICE ALERT';
						$running_state_obj->hostname = trim($hostname);
						$running_state_obj->servicename = trim($servicename);
						$running_state_obj->state = trim($state);
						$running_state_obj->state_type = trim($state_type);
						$running_state_obj->retry_count = trim($retry_count);
						$running_state_obj->messages = trim($detail_message);
					
						$running_state_array[$i] = $running_state_obj;
						$i++;

						unset($input_time, $event_message, $logtype, $information, $hostname, $servicename, $state, $state_type, $retry_count, $detail_message, $running_state_obj);
					}
				}

				//get unique host and service pair
				$key_obj = new StdClass();
				$unique_service_obj = new StdClass();
				$unique_service_array = array();

				foreach($running_state_array as $services)
				{
					$keys = $services->hostname.' '.$services->servicename;

					$key_obj->$keys += 1;
				}
				
				//array counter 
				$j = 0;

				foreach($key_obj as $key => $value)
				{
					list($hostname, $servicename) = explode(' ', $key, 2);

					$unique_service_obj->hostname = $hostname;
					$unique_service_obj->servicename = $servicename;

					$unique_service_array[$j] = $unique_service_obj;
					unset($unique_service_obj);

					$j++;
				}

				//array counter 
				$r = 0;

				foreach($unique_service_array as $unique_services)
				{
					$output_array = $this->_get_return_service($assume_state_downtime, $running_state_array, $unique_services->hostname, $unique_services->servicename, $input_period, $input_date, $backtrack_archive, $assume_initial_state, $first_assume_service_state);
					$return_array[$r] = $this->_get_state_total_service_availability($output_array, $unique_services->hostname, $unique_services->servicename);

					$r++;
				}
			}
			else
			{
				$output_array = $this->_get_return_service($assume_state_downtime, $this->_availability_array, $input_host, $input_service, $input_period, $input_date, $backtrack_archive, $assume_initial_state, $first_assume_service_state);

				$return_array[$i] = $this->_get_state_total_service_availability($output_array, $input_host, $input_service);

				$i++;

				$service_log_array = array();
				$service_log_obj = new StdClass();

				//array_counter
				$c = 0;

				if(is_array($input_date))
				{
					$now = (int)$input_date[1];
				}
				else
				{
					$now = (int)$input_date;
				}

				$temp_array = array_reverse($temp_array);

				foreach($temp_array as $alerts)
				{
					if($this->_compare_string($alerts->hostname, $input_host))
					{
						if($this->_compare_string($alerts->servicename, $input_service))
						{
							if($this->_compare_string($alerts->logtype, 'SERVICE ALERT'))
							{
								$duration = $now - (int)$alerts->datetime;
								$service_log_obj->hostname = $alerts->hostname;
								$service_log_obj->servicename = $alerts->servicename;
								$service_log_obj->duration = abs($duration);
								$service_log_obj->start_time = $now - $duration;
								$service_log_obj->end_time = $now;
								$service_log_obj->state = $this->_get_state_num($alerts->state);
								$service_log_obj->state_type = $alerts->state_type;
								$service_log_obj->messages = $alerts->messages;

								$service_log_array[$c] = $service_log_obj;
								$c++;

								$now = $now - $duration;
													
								unset($service_log_obj);
							}
						}
					}
				}

				$return_array[$i] = $service_log_array;

				$i++;
			}
		}

		//encode the data into JSON format
		foreach($return_array as $items)
		{
			$items = json_encode($items);
		}

		return $return_array;
	}

	//Trends section
	public function get_trend($return_type, $input_period, $input_date, $input_host, $input_service, $assume_initial_state, $assume_state_retention, $assume_state_downtime, $include_soft, $first_assume_service_state, $backtrack_archive)
	{
		$this->_get_data($input_date, $input_period);
		$this->_insert_data();

		$temp_array = array();
		$temp_array = $this->_parse_log($this->_host_service_alert_array, 'trend');

		//filter the data into $this->_trend_array based on request
		if($include_soft)
		{
			$this->_trend_array = $this->_get_trend_host_service($temp_array, $input_host, $input_service, 'ALL');
		}
		else
		{
			$this->_trend_array = $this->_get_trend_host_service($temp_array, $input_host, $input_service, 'HARD');
		}

		$return_array = array();

		//$return_type = 'HOST'
		if($return_type === 1)
		{
			$return_array[0] = $this->_get_return_host($assume_state_downtime, $this->_trend_array, $input_host, $input_period, $input_date, $backtrack_archive, $assume_initial_state, $first_assume_service_state);
			$return_array[1] = $this->_get_state_total_host($return_array[0]);
		}
		//$return_type = 'SERVICE'
		else if($return_type === 2)
		{
			$return_array[0] = $this->_get_return_service($assume_state_downtime, $this->_trend_array, $input_host, $input_service, $input_period, $input_date, $backtrack_archive, $assume_initial_state, $first_assume_service_state);
			$return_array[1] = $this->_get_state_total_service($return_array[0]);
		}
		//$return_type = 'HOST RESOURCE'
		else if($return_type === 3)
		{
			$host_resource_collection = $this->nagios_data->get_collection('hostresource');
			$host_resource_array = array();

			//array counter
			$i = 0;

			foreach($host_resource_collection as $resources)
			{
				$host_resource_array[$i] = $resources->service_description;

				$i++;
			}

			$resource_array = array();
			$resource_obj = new StdClass();

			//array counter 
			$j = 0;

			foreach($this->_data_array as $data)
			{
				foreach($host_resource_array as $resources)
				{
					if(strpos($data, $resources))
					{
						list($input_time, $event_message) = explode(' ', $data, 2);
						list($logtype, $information) = explode(':', $event_message, 2);
						list($hostname, $servicename, $state, $state_type, $retry_count, $detail_message) = explode(';', $information, 6);
			
						$resource_obj->datetime = trim($input_time, '[]');
						$resource_obj->logtype = 'SERVICE ALERT';
						$resource_obj->hostname = trim($hostname);
						$resource_obj->servicename = trim($servicename);
						$resource_obj->state = trim($state);
						$resource_obj->state_type = trim($state_type);
						$resource_obj->retry_count = trim($retry_count);
						$resource_obj->messages = trim($detail_message);
				
						$resource_array[$j] = $resource_obj;
						$j++;

						unset($input_time, $event_message, $logtype, $information, $hostname, $servicename, $state, $state_type, $retry_count, $detail_message, $resource_obj);
					}
				}
			}

			$return_array[0] = $this->_get_return_service($assume_state_downtime, $resource_array, $input_host, $input_service, $input_period, $input_date, $backtrack_archive, $assume_initial_state, $first_assume_service_state);
			$return_array[1] = $this->_get_state_total_service($return_array[0]);
		}
		//$return_type = 'SERVICE RUNNING STATE'
		else if($return_type === 4)
		{	
			$running_state_array = array();
			$running_state_obj = new StdClass();

			//array counter 
			$i = 0;

			foreach($this->_data_array as $data)
			{
				if(strpos($data, '_running_state'))
				{
					list($input_time, $event_message) = explode(' ', $data, 2);
					list($logtype, $information) = explode(':', $event_message, 2);
					list($hostname, $servicename, $state, $state_type, $retry_count, $detail_message) = explode(';', $information, 6);

					$running_state_obj->datetime = trim($input_time, '[]');
					$running_state_obj->logtype = 'SERVICE ALERT';
					$running_state_obj->hostname = trim($hostname);
					$running_state_obj->servicename = trim($servicename);
					$running_state_obj->state = trim($state);
					$running_state_obj->state_type = trim($state_type);
					$running_state_obj->retry_count = trim($retry_count);
					$running_state_obj->messages = trim($detail_message);
				
					$running_state_array[$i] = $running_state_obj;
					$i++;

					unset($input_time, $event_message, $logtype, $information, $hostname, $servicename, $state, $state_type, $retry_count, $detail_message, $running_state_obj);
				}
			}

			$return_array[0] = $this->_get_return_service($assume_state_downtime, $running_state_array, $input_host, $input_service, $input_period, $input_date, $backtrack_archive, $assume_initial_state, $first_assume_service_state);
			$return_array[1] = $this->_get_state_total_service($return_array[0]);
		}
		
		//encode the data into JSON format
		foreach($return_array as $items)
		{
			$items = json_encode($items);
		}

		return $return_array;
	}

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
	public function get_alert_summary($return_type, $input_period, $input_date, $input_host, $input_service, $input_logtype, $input_state_type, $input_state)
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
		if($return_type === 1)
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
		else if($return_type === 2)
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
		else if($return_type === 3)
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
		else if($return_type === 4)
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
		else if($return_type === 5)
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
		//$return_type = 'MOST RECENT ALERTS'
		else
		{	
			//reverse the array order
			$reverse_array = array_reverse($this->_alert_summary_array);

			foreach($reverse_array as $items)
			{
				$items = json_encode($items);
			}

			return $reverse_array;
		}
	}

	public function get_alert_histogram($return_type, $input_host, $input_service, $input_period, $input_date, $statistic_breakdown, $event_graph, $state_type_graph, $assume_state_retention, $initial_state_logged, $ignore_repeated_state)
	{
		$this->_get_data($input_date, $input_period);
		$this->_insert_data();

		$temp_array = array();
		$temp_array = $this->_parse_log($this->_host_service_alert_array, 'alert');

		//filter the data into $this->_alert_summary_array based on request
		$this->_alert_histogram_array = $this->_get_alert_histogram_host_service($temp_array, $input_host, $input_service, $event_graph, $state_type_graph);

		$return_obj = new StdClass();

		//$return_type = 'HOST'
		if($return_type === 1)
		{
			//$statistic_breakdown option : Month
			if($statistic_breakdown === 1)
			{
				$return_obj->up_count = $this->_get_alert_month($this->_alert_histogram_array, 'UP', false);
				$return_obj->down_count = $this->_get_alert_month($this->_alert_histogram_array, 'DOWN', false);
				$return_obj->unreachable_count = $this->_get_alert_month($this->_alert_histogram_array, 'UNREACHABLE', false);
			}
			//$statistic_breakdown option : Day of the Month
			else if($statistic_breakdown === 2)
			{
				$return_obj->up_count = $this->_get_alert_day_of_month($this->_alert_histogram_array, 'UP', false);
				$return_obj->down_count = $this->_get_alert_day_of_month($this->_alert_histogram_array, 'DOWN', false);
				$return_obj->unreachable_count = $this->_get_alert_day_of_month($this->_alert_histogram_array, 'UNREACHABLE', false);
			}
			//$statistic_breakdown option : Day of the Week
			else if($statistic_breakdown === 3)
			{
				$return_obj->up_count = $this->_get_alert_day_of_week($this->_alert_histogram_array, 'UP', false);
				$return_obj->down_count = $this->_get_alert_day_of_week($this->_alert_histogram_array, 'DOWN', false);
				$return_obj->unreachable_count = $this->_get_alert_day_of_week($this->_alert_histogram_array, 'UNREACHABLE', false);
			}
			//$statistic_breakdown option : Hour of the Day
			else
			{
				$return_obj->up_count = $this->_get_alert_hour($this->_alert_histogram_array, 'UP', false);
				$return_obj->down_count = $this->_get_alert_hour($this->_alert_histogram_array, 'DOWN', false);
				$return_obj->unreachable_count = $this->_get_alert_hour($this->_alert_histogram_array, 'UNREACHABLE', false);
			}
		}
		//$return_type = 'SERVICE'
		else if($return_type === 2)
		{
			//$statistic_breakdown option : Month
			if($statistic_breakdown === 1)
			{
				$return_obj->ok_count = $this->_get_alert_month($this->_alert_histogram_array, 'OK', false);
				$return_obj->warning_count = $this->_get_alert_month($this->_alert_histogram_array, 'WARNING', false);
				$return_obj->unknown_count = $this->_get_alert_month($this->_alert_histogram_array, 'UNKNOWN', false);
				$return_obj->critical_count = $this->_get_alert_month($this->_alert_histogram_array, 'CRITICAL', false);
			}
			//$statistic_breakdown option : Day of the Month
			else if($statistic_breakdown === 2)
			{
				$return_obj->ok_count = $this->_get_alert_day_of_month($this->_alert_histogram_array, 'OK', false);
				$return_obj->warning_count = $this->_get_alert_day_of_month($this->_alert_histogram_array, 'WARNING', false);
				$return_obj->unknown_count = $this->_get_alert_day_of_month($this->_alert_histogram_array, 'UNKNOWN', false);
				$return_obj->critical_count = $this->_get_alert_day_of_month($this->_alert_histogram_array, 'CRITICAL', false);
			}
			//$statistic_breakdown option : Day of the Week
			else if($statistic_breakdown === 3)
			{
				$return_obj->ok_count = $this->_get_alert_day_of_week($this->_alert_histogram_array, 'OK', false);
				$return_obj->warning_count = $this->_get_alert_day_of_week($this->_alert_histogram_array, 'WARNING', false);
				$return_obj->unknown_count = $this->_get_alert_day_of_week($this->_alert_histogram_array, 'UNKNOWN', false);
				$return_obj->critical_count = $this->_get_alert_day_of_week($this->_alert_histogram_array, 'CRITICAL', false);
			}
			//$statistic_breakdown option : Hour of the Day
			else
			{
				$return_obj->ok_count = $this->_get_alert_hour($this->_alert_histogram_array, 'OK', false);
				$return_obj->warning_count = $this->_get_alert_hour($this->_alert_histogram_array, 'WARNING', false);
				$return_obj->unknown_count = $this->_get_alert_hour($this->_alert_histogram_array, 'UNKNOWN', false);
				$return_obj->critical_count = $this->_get_alert_hour($this->_alert_histogram_array, 'CRITICAL', false);
			}
		}
		//$return_type= 'HOST RESOURCE'
		else if($return_type === 3)
		{
			$host_resource_collection = $this->nagios_data->get_collection('hostresource');
			$host_resource_array = array();

			//array counter
			$i = 0;

			foreach($host_resource_collection as $resources)
			{
				$host_resource_array[$i] = $resources->service_description;

				$i++;
			}

			$resource_array = array();
			$resource_obj = new StdClass();

			//array counter 
			$j = 0;

			foreach($this->_data_array as $data)
			{
				foreach($host_resource_array as $resources)
				{
					if(strpos($data, $resources))
					{
						list($input_time, $event_message) = explode(' ', $data, 2);

						$resource_obj->datetime = trim($input_time, '[]');
						$resource_obj->servicename = $resources;
						
						if(strpos($event_message, 'OK') !== false)
						{
							$resource_obj->state = 'OK';
						}
						else if(strpos($event_message, 'WARNING') !== false) 
						{
							$resource_obj->state = 'WARNING';
						}
						else if(strpos($event_message, 'UNKNOWN') !== false)
						{
							$resource_obj->state = 'UNKNOWN';
						}
						else if(strpos($event_message, 'CRITICAL') !== false)
						{
							$resource_obj->state = 'CRITICAL';
						}
				
						$resource_array[$j] = $resource_obj;
						$j++;

						unset($input_time, $event_message, $resource_obj);
					}
				}
			}

			//$statistic_breakdown option : Month
			if($statistic_breakdown === 1)
			{
				$return_obj->ok_count = $this->_get_alert_month($resource_array, 'OK', true);
				$return_obj->warning_count = $this->_get_alert_month($resource_array, 'WARNING', true);
				$return_obj->unknown_count = $this->_get_alert_month($resource_array, 'UNKNOWN', true);
				$return_obj->critical_count = $this->_get_alert_month($resource_array, 'CRITICAL', true);
			}
			//$statistic_breakdown option : Day of the Month
			else if($statistic_breakdown === 2)
			{
				$return_obj->ok_count = $this->_get_alert_day_of_month($resource_array, 'OK', true);
				$return_obj->warning_count = $this->_get_alert_day_of_month($resource_array, 'WARNING', true);
				$return_obj->unknown_count = $this->_get_alert_day_of_month($resource_array, 'UNKNOWN', true);
				$return_obj->critical_count = $this->_get_alert_day_of_month($resource_array, 'CRITICAL', true);
			}
			//$statistic_breakdown option : Day of the Week
			else if($statistic_breakdown === 3)
			{
				$return_obj->ok_count = $this->_get_alert_day_of_week($resource_array, 'OK', true);
				$return_obj->warning_count = $this->_get_alert_day_of_week($resource_array, 'WARNING', true);
				$return_obj->unknown_count = $this->_get_alert_day_of_week($resource_array, 'UNKNOWN', true);
				$return_obj->critical_count = $this->_get_alert_day_of_week($resource_array, 'CRITICAL', true);
			}
			//$statistic_breakdown option : Hour of the Day
			else
			{
				$return_obj->ok_count = $this->_get_alert_hour($resource_array, 'OK', true);
				$return_obj->warning_count = $this->_get_alert_hour($resource_array, 'WARNING', true);
				$return_obj->unknown_count = $this->_get_alert_hour($resource_array, 'UNKNOWN', true);
				$return_obj->critical_count = $this->_get_alert_hour($resource_array, 'CRITICAL', true);
			}
		}
		//$return_type= 'SERVICE RUNNING STATE'
		else
		{
			$running_state_array = array();
			$running_state_obj = new StdClass();

			//array counter 
			$i = 0;

			foreach($this->_data_array as $data)
			{
				if(strpos($data, '_running_state'))
				{
					list($input_time, $event_message) = explode(' ', $data, 2);

					$running_state_obj->datetime = trim($input_time, '[]');
						
					if(strpos($event_message, 'OK') !== false)
					{
						$running_state_obj->state = 'OK';
					}
					else if(strpos($event_message, 'WARNING') !== false) 
					{
						$running_state_obj->state = 'WARNING';
					}
					else if(strpos($event_message, 'UNKNOWN') !== false)
					{
						$running_state_obj->state = 'UNKNOWN';
					}
					else if(strpos($event_message, 'CRITICAL') !== false)
					{
						$running_state_obj->state = 'CRITICAL';
					}
				
					$running_state_array[$i] = $running_state_obj;
					$i++;

					unset($input_time, $event_message, $running_state_obj);
				}
			}

			//$statistic_breakdown option : Month
			if($statistic_breakdown === 1)
			{
				$return_obj->ok_count = $this->_get_alert_month($running_state_array, 'OK', true);
				$return_obj->warning_count = $this->_get_alert_month($running_state_array, 'WARNING', true);
				$return_obj->unknown_count = $this->_get_alert_month($running_state_array, 'UNKNOWN', true);
				$return_obj->critical_count = $this->_get_alert_month($running_state_array, 'CRITICAL', true);
			}
			//$statistic_breakdown option : Day of the Month
			else if($statistic_breakdown === 2)
			{
				$return_obj->ok_count = $this->_get_alert_day_of_month($running_state_array, 'OK', true);
				$return_obj->warning_count = $this->_get_alert_day_of_month($running_state_array, 'WARNING', true);
				$return_obj->unknown_count = $this->_get_alert_day_of_month($running_state_array, 'UNKNOWN', true);
				$return_obj->critical_count = $this->_get_alert_day_of_month($running_state_array, 'CRITICAL', true);
			}
			//$statistic_breakdown option : Day of the Week
			else if($statistic_breakdown === 3)
			{
				$return_obj->ok_count = $this->_get_alert_day_of_week($running_state_array, 'OK', true);
				$return_obj->warning_count = $this->_get_alert_day_of_week($running_state_array, 'WARNING', true);
				$return_obj->unknown_count = $this->_get_alert_day_of_week($running_state_array, 'UNKNOWN', true);
				$return_obj->critical_count = $this->_get_alert_day_of_week($running_state_array, 'CRITICAL', true);
			}
			//$statistic_breakdown option : Hour of the Day
			else
			{
				$return_obj->ok_count = $this->_get_alert_hour($running_state_array, 'OK', true);
				$return_obj->warning_count = $this->_get_alert_hour($running_state_array, 'WARNING', true);
				$return_obj->unknown_count = $this->_get_alert_hour($running_state_array, 'UNKNOWN', true);
				$return_obj->critical_count = $this->_get_alert_hour($running_state_array, 'CRITICAL', true);
			}
		}

		if($return_type === 1)
		{
			$up_min = $return_obj->up_count[0];
			$up_max = 0;
			$up_sum = 0;

			$down_min = $return_obj->down_count[0];
			$down_max = 0;
			$down_sum = 0;

			$unreachable_min = $return_obj->unreachable_count[0];
			$unreachable_max = 0;
			$unreachable_sum = 0;

			foreach($return_obj->up_count as $items)
			{
				$up_sum += $items;

				if($up_min > $items)
				{
					$up_min = $items;
				}
				else if($up_max < $items)
				{
					$up_max = $items;
				}
			}

			foreach($return_obj->down_count as $items)
			{
				$down_sum += $items;

				if($down_min > $items)
				{
					$down_min = $items;
				}
				else if($down_max < $items)
				{
					$down_max = $items;
				}
			}

			foreach($return_obj->unreachable_count as $items)
			{
				$unreachable_sum += $items;

				if($unreachable_min > $items)
				{
					$unreachable_min = $items;
				}
				else if($unreachable_max < $items)
				{
					$unreachable_max = $items;
				}
			}

			$return_obj->up_min = $up_min;
			$return_obj->up_max = $up_max;
			$return_obj->up_sum = $up_sum;
			$return_obj->up_avg = $up_sum / ( count($return_obj->up_count) );
			$return_obj->down_min = $down_min;
			$return_obj->down_max = $down_max;
			$return_obj->down_sum = $down_sum;
			$return_obj->down_avg = $down_sum / ( count($return_obj->down_count) );
			$return_obj->unreachable_min = $unreachable_min;
			$return_obj->unreachable_max = $unreachable_max;
			$return_obj->unreachable_sum = $unreachable_sum;
			$return_obj->unreachable_avg = $unreachable_sum / ( count($return_obj->unreachable_count) );
		}
		else
		{
			$ok_min = $return_obj->ok_count[0];
			$ok_max = 0;
			$ok_sum = 0;

			$warning_min = $return_obj->warning_count[0];
			$warning_max = 0;
			$warning_sum = 0;

			$unknown_min = $return_obj->unknown_count[0];
			$unknown_max = 0;
			$unknown_sum = 0;

			$critical_min = $return_obj->critical_count[0];
			$critical_max = 0;
			$critical_sum = 0;

			foreach($return_obj->ok_count as $items)
			{
				$ok_sum += $items;

				if($ok_min > $items)
				{
					$ok_min = $items;
				}
				else if($ok_max < $items)
				{
					$ok_max = $items;
				}
			}

			foreach($return_obj->warning_count as $items)
			{
				$warning_sum += $items;

				if($warning_min > $items)
				{
					$warning_min = $items;
				}
				else if($warning_max < $items)
				{
					$warning_max = $items;
				}
			}

			foreach($return_obj->unknown_count as $items)
			{
				$unknown_sum += $items;

				if($unknown_min > $items)
				{
					$unknown_min = $items;
				}
				else if($unknown_max < $items)
				{
					$unknown_max = $items;
				}
			}

			foreach($return_obj->critical_count as $items)
			{
				$critical_sum += $items;

				if($critical_min > $items)
				{
					$critical_min = $items;
				}
				else if($critical_max < $items)
				{
					$critical_max = $items;
				}
			}

			$return_obj->ok_min = $ok_min;
			$return_obj->ok_max = $ok_max;
			$return_obj->ok_sum = $ok_sum;
			$return_obj->ok_avg = $ok_sum / ( count($return_obj->ok_count) );
			$return_obj->warning_min = $warning_min;
			$return_obj->warning_max = $warning_max;
			$return_obj->warning_sum = $warning_sum;
			$return_obj->warning_avg = $warning_sum / ( count($return_obj->warning_count) );
			$return_obj->unknown_min = $unknown_min;
			$return_obj->unknown_max = $unknown_max;
			$return_obj->unknown_sum = $unknown_sum;
			$return_obj->unknown_avg = $unknown_sum / ( count($return_obj->unknown_count) );
			$return_obj->critical_min = $critical_min;
			$return_obj->critical_max = $critical_max;
			$return_obj->critical_sum = $critical_sum;
			$return_obj->critical_avg = $critical_sum / ( count($return_obj->critical_count) );
		}

		$date_array = $this->_get_start_end_date($input_date, $input_period);
		
		$return_obj->start_date = $date_array[0];
		$return_obj->end_date = $date_array[1];

		//encode the data into JSON format
		foreach($return_obj as $items)
		{
			$items = json_encode($items);
		}

		return $return_obj;
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
			$this->_problem_array[$this->_problem] = 'File : nagios-'.$modify_date.'-00.log not found';

			$this->_problem++;
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
			if(strpos($alerts, 'HOST ALERT:') !== false or strpos($alerts, 'SERVICE ALERT:') !== false or strpos($alerts, 'HOST DOWNTIME ALERT:') !== false or strpos($alerts, 'SERVICE DOWNTIME ALERT:') !== false)
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
			$return_array[1] = (int)$input_date;
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
			$return_array[1] = (int)$input_date;
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
			$return_array[1] = (int)$input_date;	
		}
		//$input_period = 'LAST YEAR'
		else 
		{
			$return_array[0] = strtotime('Jan 1 last year');
			$return_array[1] = (strtotime('Dec 31 last year 23:59:59'));
		}

		if(is_array($input_date))
		{
			$return_array[0] = (int)$input_date[0];
			$return_array[1] = (int)$input_date[1];
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

		//compare host problem state		
		if(strcmp($input_string, 'HOST PROBLEM STATE') == 0)
		{
			if(strcmp($data_string, 'DOWN') == 0 or strcmp($data_string, 'UNREACHABLE') == 0 or strcmp($data_string, 'PENDING') == 0)
			{
				return true;
			}
		}

		//compare service problem state
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
				else if(strpos($logs, 'SERVICE NOTIFICATION:') !== false)
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
			else if($this->_compare_string($_type, 'trend'))
			{
				if(strpos($logs, 'CURRENT HOST STATE:') !== false)
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
				else if(strpos($logs, 'HOST ALERT:') !== false)
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

	//Function used to get the number for each host state and service state
	private function _get_state_num($input_state)
	{
		if($this->_compare_string($input_state, 'UP'))
		{
			return 2;
		}
		else if($this->_compare_string($input_state, 'DOWN'))
		{	
			return 1;
		}
		else if($this->_compare_string($input_state, 'UNREACHABLE'))
		{
			return 0;
		}
		else if($this->_compare_string($input_state, 'OK'))
		{
			return 3;
		}
		else if($this->_compare_string($input_state, 'WARNING'))
		{
			return 2;
		}
		else if($this->_compare_string($input_state, 'UNKNOWN'))
		{
			return 1;
		}
		else if($this->_compare_string($input_state, 'CRITICAL'))
		{
			return 0;
		}
		else if($this->_compare_string($input_state, 'STARTUP'))
		{
			return 6;
		}
		else if($this->_compare_string($Input_state, 'SHUTDOWN'))
		{
			return 7;
		}
		else if($this->_compare_string($input_state, 'STARTED'))
		{
			return 8;
		}
		else if($this->_compare_string($Input_state, 'STOPPED'))
		{
			return 9;
		}
	}

	//Functions used by availability section
	//Function used to filter data
	private function _get_availability_host_service($input_array, $input_host, $input_service, $state_type)
	{
		//array counte
		$i = 0;
		$return_array = array();

		//filter the array based on request
		foreach($input_array as $items)
		{
			if(is_array($input_host))
			{
				foreach($input_host as $hosts)
				{
					//custom report option
					//compare host name
					if($this->_compare_string($hosts, $items->hostname))
					{
						//compare service name
						if($this->_compare_string($input_service, $items->servicename))
						{	
							//compare state_type
							if($this->_compare_string($state_type, $items->state_type))
							{	
								$return_array[$i] = $items;

								$i++;	
							}			
						}
					}
				}
			}
			else
			{
				//custom report option
				//compare host name
				if($this->_compare_string($input_host, $items->hostname))
				{
					//compare service name
					if($this->_compare_string($input_service, $items->servicename))
					{	
						//compare state_type
						if($this->_compare_string($state_type, $items->state_type))
						{	
							$return_array[$i] = $items;

							$i++;	
						}			
					}
				}
			}
		}

		return $return_array;
	}

	//Function used to calculate total duration for each type of state for host
	private function _get_state_total_host_availability($input_array, $input_host)
	{
		$all_total = 0;
		$up_total = 0;
		$down_total = 0;
		$unreachable_total = 0;
		$undetermined_total = 0;

		$up_schedule_total = 0;
		$down_schedule_total = 0;
		$unreachable_schedule_total = 0;
		$undetermined_not_run_total = 0;

		$up_unschedule_total = 0;
		$down_unschedule_total = 0;
		$unreachable_unschedule_total = 0;
		$undetermined_insufficient_total = 0;

		$check_array = array();
		$check_array = $this->_parse_log($this->_data_array, 'availability');

		$start = 0;
		$end = 0;
		$not_run_duration = 0;

		foreach($check_array as $check)
		{
			//$check->state = 'STRATUP'
			if($check->state === 6)
			{
				$start = $check->datetime;
			}
			//$check->state = 'SHUTDOWN'
			else if($check->state === 7)
			{
				$end = $check->datetime;	
			}

			if( empty($start) && empty($end) )
			{
				continue;
			}
			else
			{
				$not_run_duration = $end - $start;

				$start = 0;
				$end = 0;
			}
		}

		foreach($input_array as $alerts)
		{
			if($this->_compare_string($alerts->hostname, $input_host))
			{
				$all_total += $alerts->duration;

				//$alerts->state = 'UP'
				if($alerts->state === 3)
				{
					$up_total += $alerts->duration;

					if($alerts->schedule)
					{
						$up_schedule_total += $alerts->duration;
					}
					else
					{
						$up_unschedule_total += $alerts->duration;
					}
				}
				//$alerts->state = 'DOWN'
				else if($alerts->state === 2)
				{
					$down_total += $alerts->duration;

					if($alerts->schedule)
					{
						$down_schedule_total += $alerts->duration;
					}
					else
					{
						$down_unschedule_total += $alerts->duration;
					}
				}
				//$alerts->state = 'UNREACHABLE'
				else if($alerts->state === 1)
				{
					$unreachable_total += $alerts->duration;

					if($alerts->schedule)
					{
						$unreachable_schedule_total += $alerts->duration;
					}
					else
					{
						$unreachable_unschedule_total += $alerts->duration;
					}
				}
				//$alerts->state = 'UNDETERMINED'
				else if($alerts->state === 0)
				{
					$undetermined_total += $alerts->duration;

					$undetermined_not_run_total = $not_run_duration;
					$undetermined_insufficient_total = $undetermined_total - $not_run_duration;
				}
			}
		}

		$return_obj = new StdClass();
		$return_obj->hostname = $input_host;
		$return_obj->all_total = $all_total;
		$return_obj->up_total = $up_total;
		$return_obj->down_total = $down_total;
		$return_obj->unreachable_total = $unreachable_total;
		$return_obj->undetermined_total = $undetermined_total;
		$return_obj->up_schedule_total = $up_schedule_total;
		$return_obj->down_schedule_total = $down_schedule_total;
		$return_obj->unreachable_schedule_total = $unreachable_schedule_total;
		$return_obj->undetermined_not_run_total = $undetermined_not_run_total;
		$return_obj->up_unschedule_total = $up_unschedule_total;
		$return_obj->down_unschedule_total = $down_unschedule_total;
		$return_obj->unreachable_unschedule_total = $unreachable_unschedule_total;
		$return_obj->undetermined_insufficient_total = $undetermined_insufficient_total;

		return $return_obj;
	}

	//Function used to calculate total duration for each type of state for service
	private function _get_state_total_service_availability($input_array, $input_host, $input_service)
	{
		$all_total = 0;
		$ok_total = 0;
		$warning_total = 0;
		$unknown_total = 0;
		$critical_total = 0;
		$undetermined_total = 0;

		$ok_schedule_total = 0;
		$warning_schedule_total = 0;
		$unknown_schedule_total = 0;
		$critical_schedule_total = 0;
		$undetermined_not_run_total = 0;

		$ok_unschedule_total = 0;
		$warning_unschedule_total = 0;
		$unknown_unschedule_total = 0;
		$critical_unschedule_total = 0;
		$undetermined_insufficient_total = 0;

		foreach($check_array as $check)
		{
			//$check->state = 'STARTUP'
			if($check->state === 6)
			{
				$start = $check->datetime;
			}
			//$check->state = 'SHUTDOWN'
			else if($check->state === 7)
			{
				$end = $check->datetime;	
			}

			if( empty($start) && empty($end) )
			{
				continue;
			}
			else
			{
				$not_run_duration = $end - $start;

				$start = 0;
				$end = 0;
			}
		}

		foreach($input_array as $alerts)
		{
			if($this->_compare_string($alerts->hostname, $input_host))
			{
				if($this->_compare_string($alerts->servicename, $input_service))
				{
					$all_total += $alerts->duration;

					//$alerts->state = 'OK'
					if($alerts->state === 4)
					{
						$ok_total += $alerts->duration;

						if($alerts->schedule)
						{
							$ok_schedule_total += $alerts->duration;
						}
						else
						{
							$ok_unschedule_total += $alerts->duration;
						}
					}
					//$alerts->state = 'WARNING'
					else if($alerts->state === 3)
					{
						$warning_total += $alerts->duration;

						if($alerts->schedule)
						{
							$warning_schedule_total += $alerts->duration;
						}
						else
						{
							$warning_unschedule_total += $alerts->duration;
						}
					}
					//$alerts->state = 'UNKNOWN'
					else if($alerts->state === 2)
					{
						$unknown_total += $alerts->duration;

						if($alerts->schedule)
						{
							$unknown_schedule_total += $alerts->duration;
						}
						else
						{
							$unknown_unschedule_total += $alerts->duration;
						}
					}
					//$alerts->state = 'CRITICAL'
					else if($alerts->state === 1)
					{
						$critical_total += $alerts->duration;

						if($alerts->schedule)
						{
							$critical_schedule_total += $alerts->duration;
						}
						else
						{
							$critical_unschedule_total += $alerts->duration;
						}
					}
					//$alerts->state = 'UNDETERMINED'
					else if($alerts->state === 0)
					{
						$undetermined_total += $alerts->duration;

						$undetermined_not_run_total = $not_run_duration;
						$undetermined_insufficient_total = $undetermined_total - $not_run_duration;
					}
				}
			}
		}

		$return_obj = new StdClass();
		$return_obj->hostname = $input_host;
		$return_obj->servicename = $input_service;
		$return_obj->all_total = $all_total;
		$return_obj->ok_total = $ok_total;
		$return_obj->warning_total = $warning_total;
		$return_obj->unknown_total = $unknown_total;
		$return_obj->critical_total = $critical_total;
		$return_obj->undetermined_total = $undetermined_total;
		$return_obj->ok_schedule_total = $ok_schedule_total;
		$return_obj->warning_schedule_total = $warning_schedule_total;
		$return_obj->unknown_schedule_total = $unknown_schedule_total;
		$return_obj->critical_schedule_total = $critical_schedule_total;
		$return_obj->undetermined_not_run_total = $undetermined_not_run_total;
		$return_obj->ok_unschedule_total = $ok_unschedule_total;
		$return_obj->warning_unschedule_total = $warning_unschedule_total;
		$return_obj->unknown_unschedule_total = $unknown_unschedule_total;
		$return_obj->critical_unschedule_total = $critical_unschedule_total;
		$return_obj->undetermined_insufficient_total = $undetermined_insufficient_total;

		return $return_obj;
	}

	//Functions used by trend section
	//Function used to filter data
	private function _get_trend_host_service($input_array, $input_host, $input_service, $state_type)
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
					//compare state_type
					if($this->_compare_string($state_type, $items->state_type))
					{	
						$return_array[$i] = $items;

						$i++;	
					}			
				}
			}
		}

		return $return_array;
	}

	//Function used to get return data for host
	private function _get_return_host($assume_state_downtime, $input_array, $input_host, $input_period, $input_date, $backtrack_archive, $assume_initial_state, $first_assume_host_state)
	{
		//$initial_state = 'UNDETERMINED'
		$initial_state = 0;

		if(is_array($input_date))
		{
			$start_time = $input_date[0];
			$end_time = $input[1];
		}
		else
		{
			if($this->_is_today($input_date))
			{
				$start_time = strtotime('today midnight');
			}
			else
			{
				$date_array = array();
				$date_array = $this->_get_start_end_date($input_date, $input_period);

				$start_time = $date_array[0];
				$end_time = (int)$input_date;
			}
		}

		$initial_state = $this->_is_detected($input_array, $start_time, $input_host, 'ALL');

		if($initial_state === 0)
		{
			//counter 
			$num = 0;
			$z = 0;

			while($num < $backtrack_archive)
			{
				$backtrack_duration = 86400 * (++$num);
				$temp_time = $start_time - $backtrack_duration;

				$output = array();
					
				$modify_date = date('m-d-Y', (int)$temp_time);
				$filepath = '/usr/local/nagios/var/archives/nagios-'.$modify_date.'-00.log';

				if(file_exists($filepath))
				{
					//opening the nagios log file
					$logfile = fopen($filepath, 'r') or die('File not found !');

					while(! feof($logfile))
					{
						$output_array[$z] = trim(fgets($logfile));
						$z++;
					}

					fclose($logfile);

					$initial_state = $this->_is_detected($output_array, $temp_time, $input_host, 'ALL');
				}
				else
				{
					$initial_state = 0;
				}

				if($initial_state === 0)
				{
					$num++;
				}
				else
				{
					//end the loop
					$num = $backtrack_archive++;
				}
			}
		}

		if($assume_initial_state)
		{
			$initial_state = $this->_get_state_num($first_assume_host_state);
		}
		else
		{
			$initial_state = 0;
		}

		$now = $end_time;
		$input_array = array_reverse($input_array);

		$return_obj = new StdClass();
		$return_array = array();

		//array counter 
		$i = 0;

		if($assume_state_downtime)
		{
			foreach($input_array as $alerts)
			{
				if($this->_compare_string($alerts->logtype, 'HOST ALERT'))
				{
					$duration = $now - (int)$alerts->datetime;
					$return_obj->logtype = $alerts->logtype;
					$return_obj->hostname = $alerts->hostname;
					$return_obj->duration = abs($duration);
					$return_obj->start_time = $now - $duration;
					$return_obj->end_time = $now;
					$return_obj->state = $this->_get_state_num($alerts->state);
					$return_obj->schedule = false;

					if($this->_compare_string($alerts->logtype, 'HOST DOWNTIME ALERT'))
					{
						//$alerts->state = 'STARTED'
						if($alerts->state === 8)
						{
							$return_obj->schedule = true;
						}
					}

					$return_array[$i] = $return_obj;
					$i++;

					$now = $now - $duration;
										
					unset($return_obj);
				}
			}
		}
		else
		{
			foreach($input_array as $alerts)
			{
				if($this->_compare_string($alerts->logtype, 'HOST ALERT') or $this->_compare_string($alerts->logtype, 'HOST DOWNTIME ALERT'))
				{
					$duration = $now - (int)$alerts->datetime;
					$return_obj->logtype = $alerts->logtype;
					$return_obj->hostname = $alerts->hostname;
					$return_obj->duration = abs($duration);
					$return_obj->start_time = $now - $duration;
					$return_obj->end_time = $now;
					$return_obj->state = $this->_get_state_num($alerts->state);
					$return_obj->schedule = false;

					if($this->_compare_string($alerts->logtype, 'HOST DOWNTIME ALERT'))
					{
						//$alerts->state = 'STARTED'
						if($alerts->state === 8)
						{
							//$return_obj->state = 0;
							$return_obj->state = 0;
							$return_obj->schedule = true;
						}
						//$alerts->state = 'STOPPED'
						else if($alerts->state === 9)
						{
							$return_obj->state = 'NULL';
						}
					}

					$return_array[$i] = $return_obj;
					$i++;

					$now = $now - $duration;
										
					unset($return_obj);
				}
			}

			$reverse_array = array_reverse($return_array);

			foreach($reverse_array as $reverse)
			{
				if($this->_compare_string($reverse->logtype, 'HOST DOWNTIME ALERT'))
				{
					if($this->_compare_string($reverse->state, 'NULL'))
					{
						$reverse->state = $current_state;
					}
				}
				else
				{
					$current_state = $reverse->state;
				}
			}

			unset($return_array);

			$return_array = array_reverse($reverse_array);
		}

		$duration = $now - (int)$start_time;
		$return_obj->logtype = 'INITIAL STATE';
		$return_obj->hostname = $input_host;
		$return_obj->duration = abs($duration);
		$return_obj->start_time = $start_time;
		$return_obj->end_time = $now;
		$return_obj->state = $initial_state;

		$return_array[$i] = $return_obj;
		$i++;

		$return_array = array_reverse($return_array);

		return $return_array;
	}

	//Function used to get return data for service
	private function _get_return_service($assume_state_downtime, $input_array, $input_host, $input_service, $input_period, $input_date, $backtrack_archive, $assume_initial_state, $first_assume_service_state)
	{
		//$initial_state = 'UNDETERMINED'
		$initial_state = 0;

		if(is_array($input_date))
		{
			$start_time = $input_date[0];
			$end_time = $input[1];
		}
		else
		{
			if($this->_is_today($input_date))
			{
				$start_time = strtotime('today midnight');
			}
			else
			{
				$date_array = array();
				$date_array = $this->_get_start_end_date($input_date, $input_period);

				$start_time = $date_array[0];
				$end_time = (int)$input_date;
			}
		}

		$initial_state = $this->_is_detected($input_array, $start_time, $input_host, $input_service);

		if($initial_state === 0)
		{
			//counter 
			$num = 0;
			$z = 0;

			while($num < $backtrack_archive)
			{
				$backtrack_duration = 86400 * (++$num);
				$temp_time = $start_time - $backtrack_duration;

				$output = array();
					
				$modify_date = date('m-d-Y', (int)$temp_time);
				$filepath = '/usr/local/nagios/var/archives/nagios-'.$modify_date.'-00.log';

				if(file_exists($filepath))
				{
					//opening the nagios log file
					$logfile = fopen($filepath, 'r') or die('File not found !');

					while(! feof($logfile))
					{
						$output_array[$z] = trim(fgets($logfile));
						$z++;
					}

					fclose($logfile);

					$initial_state = $this->_is_detected($output_array, $temp_time, $input_host, $input_service);
				}
				else
				{
					$initial_state = 0;
				}

				if($initial_state === 0)
				{
					$num++;
				}
				else
				{
					//end the loop
					$num = $backtrack_archive++;
				}
			}
		}

		if($assume_initial_state)
		{
			$initial_state = $this->_get_state_num($first_assume_service_state);
		}
		else
		{
			$initial_state = 0;
		}

		$now = $end_time;
		$input_array = array_reverse($input_array);

		$return_obj = new StdClass();
		$return_array = array();

		//array counter 
		$i = 0;

		if($assume_state_downtime)
		{
			foreach($input_array as $alerts)
			{
				if($this->_compare_string($alerts->logtype, 'SERVICE ALERT'))
				{
					$duration = $now - (int)$alerts->datetime;
					$return_obj->logtype = $alerts->logtype;
					$return_obj->hostname = $alerts->hostname;
					$return_obj->servicename = $alerts->servicename;
					$return_obj->duration = abs($duration);
					$return_obj->start_time = $now - $duration;
					$return_obj->end_time = $now;
					$return_obj->state = $this->_get_state_num($alerts->state);
					$return_obj->schedule = false;

					if($this->_compare_string($alerts->logtype, 'SERVICE DOWNTIME ALERT'))
					{
						if($this->_compare_string($alerts->state, 'STARTED'))
						{
							$return_obj->schedule = true;
						}
					}

					$return_array[$i] = $return_obj;
					$i++;

					$now = $now - $duration;
									
					unset($return_obj);
				}
			}
		}
		else
		{
			foreach($input_array as $alerts)
			{
				if($this->_compare_string($alerts->logtype, 'SERVICE ALERT') or $this->_compare_string($alerts->logtype, 'SERVICE DOWNTIME ALERT'))
				{
					$duration = $now - (int)$alerts->datetime;
					$return_obj->logtype = $alerts->logtype;
					$return_obj->hostname = $alerts->hostname;
					$return_obj->servicename = $alerts->servicename;
					$return_obj->duration = abs($duration);
					$return_obj->start_time = $now - $duration;
					$return_obj->end_time = $now;
					$return_obj->state = $this->_get_state_num($alerts->state);
					$return_obj->schedule = false;

					if($this->_compare_string($alerts->logtype, 'SERVICE DOWNTIME ALERT'))
					{
						if($this->_compare_string($alerts->state, 'STARTED'))
						{
							$return_obj->state = 0;
							$return_obj->schedule = true;
						}
						else if($this->_compare_string($alerts->state, 'STOPPED'))
						{
							$return_obj->state = 'NULL';
						}
					}

					$return_array[$i] = $return_obj;
					$i++;

					$now = $now - $duration;
									
					unset($return_obj);
				}
			}

			$reverse_array = array_reverse($return_array);

			foreach($reverse_array as $reverse)
			{
				if($this->_compare_string($reverse->logtype, 'SERVICE DOWNTIME ALERT'))
				{
					if($this->_compare_string($reverse->state, 'NULL'))
					{
						$reverse->state = $current_state;
					}
				}
				else
				{
					$current_state = $reverse->state;
				}
			}

			unset($return_array);

			$return_array = array_reverse($reverse_array);
		}

		$duration = $now - (int)$start_time;
		$return_obj->logtype = 'INITIAL STATE';
		$return_obj->hostname = $input_host;
		$return_obj->servicename = $input_service;
		$return_obj->duration = abs($duration);
		$return_obj->start_time = $start_time;
		$return_obj->end_time = $now;
		$return_obj->state = $initial_state;

		$return_array[$i] = $return_obj;
		$i++;

		$return_array = array_reverse($return_array);

		return $return_array;
	}

	//Function used to detect whether the initial state is detected
	private function _is_detected($input_array, $start_time, $input_host, $input_service)
	{
		//$initial_state = 'UNDETERMINED'
		$initial_state = 0;

		$detect = false;

		foreach($input_array as $items)
		{
			if($this->_compare_date('TODAY', $items->datetime, $start_time))
			{
				if($detect)
				{
					continue;
				}
				else 
				{
					if($this->_compare_string($input_host, $items->hostname))
					{
						if($this->_compare_string($input_service, $items->servicename))
						{
							$detect = true;

							//$input_array[$count]->state = 'UP'
							if($this->_compare_string($items->state, 'UP'))
							{
								$initial_state = 3;
							}
							//$input_array[$count]->state = 'DOWN'
							else if($this->_compare_string($items->state, 'DOWN'))
							{
								$initial_state = 2;
							}
							//$input_array[$count]->state = 'UNREACHABLE'
							else if($this->_compare_string($items->state, 'UNREACHABLE'))
							{
								$initial_state = 1;
							}
							//$input_array[$count]->state = 'OK'
							else if($this->_compare_string($items->state, 'OK'))
							{
								$initial_state = 4;
							}
							//$input_array[$count]->state = 'WARNING'
							else if($this->_compare_string($items->state, 'WARNING'))
							{
								$initial_state = 3;
							}
							//$input_array[$count]->state = 'UNKNOWN'
							else if($this->_compare_string($items->state, 'UNKNOWN'))
							{
								$initial_state = 2;
							}
							//$input_array[$count]->state = 'CRITICAL'
							else if($this->_compare_string($items->state, 'CRITICAL'))
							{
								$initial_state = 1;
							}
							//$input_array[$count]->state = 'STARTED'
							else if($this->_compare_string($items->state, 'STARTED'))
							{
								$detect = false;
							}
							//$input_array[$count]->state = 'STOPPED'
							else if($this->_compare_string($items->state, 'STOPPED'))
							{
								$detect = false;
							}
						}
					}
				}
			}
		}

		return $initial_state;
	}

	//Function used to calculate total duration for each type of state for host
	private function _get_state_total_host($input_array)
	{
		$all_total = 0;
		$up_total = 0;
		$down_total = 0;
		$unreachable_total = 0;
		$undetermined_total = 0;

		foreach($input_array as $alerts)
		{
			$all_total += $alerts->duration;

			//$alerts->state = 'UP'
			if($alerts->state === 3)
			{
				$up_total += $alerts->duration;
			}
			//$alerts->state = 'DOWN'
			else if($alerts->state === 2)
			{
				$down_total += $alerts->duration;
			}
			//$alerts->state = 'UNREACHABLE'
			else if($alerts->state === 1)
			{
				$unreachable_total += $alerts->duration;
			}
			//$alerts->state = 'UNDETERMINED'
			else if($alerts->state === 0)
			{
				$undetermined_total += $alerts->duration;
			}
		}

		$return_obj = new StdClass();
		$return_obj->all_total = $all_total;
		$return_obj->up_total = $up_total;
		$return_obj->down_total = $down_total;
		$return_obj->unreachable_total = $unreachable_total;
		$return_obj->undetermined_total = $undetermined_total;

		return $return_obj;
	}

	//Function used to calculate total duration for each type of state for service
	private function _get_state_total_service($input_array)
	{
		$all_total = 0;
		$ok_total = 0;
		$warning_total = 0;
		$unknown_total = 0;
		$critical_total = 0;
		$undetermined_total = 0;

		foreach($input_array as $alerts)
		{
			$all_total += $alerts->duration;

			//$alerts->state = 'OK'
			if($alerts->state === 4)
			{
				$ok_total += $alerts->duration;
			}
			//$alerts->state = 'WARNING'
			else if($alerts->state === 3)
			{
				$warning_total += $alerts->duration;
			}
			//$alerts->state = 'UNKNOWN'
			else if($alerts->state === 2)
			{
				$unknown_total += $alerts->duration;
			}
			//$alerts->state = 'CRITICAL'
			else if($alerts->state === 1)
			{
				$critical_total += $alerts->duration;
			}
			//$alerts->state = 'UNDETERMINED'
			else if($alerts->state === 0)
			{
				$undetermined_total += $alerts->duration;
			}
		}

		$return_obj = new StdClass();
		$return_obj->all_total = $all_total;
		$return_obj->ok_total = $ok_total;
		$return_obj->warning_total = $warning_total;
		$return_obj->unknown_total = $unknown_total;
		$return_obj->critical_total = $critical_total;
		$return_obj->undetermined_total = $undetermined_total;

		return $return_obj;
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

	//Function used by alert histogram section
	//Function used to filter data based on request
	private function _get_alert_histogram_host_service($input_array, $input_host, $input_service, $input_state, $input_state_type)
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
					//compare state
					if($this->_compare_string($input_state, $items->state))
					{
						//compare state_type
						if($this->_compare_string($input_state_type, $items->state_type))
						{
							$return_array[$i] = $items;

							$i++;
						}
					}
				}
			}
		}

		return $return_array;
	}

	//Function used to check whether the service is host resource or service running state
	private function _is_host_resource($services)
	{
		$host_resource_collection = $this->nagios_data->get_collection('hostresource');
		$host_resource_array = array();

		//array counter
		$i = 0;

		foreach($host_resource_collection as $resources)
		{
			$host_resource_array[$i] = $resources->service_description;

			$i++;
		}

		foreach($host_resource_array as $host_resource)
		{
			if($this->_compare_string($services, $host_resource))
			{
				return true;
			}
		}

		if(strpos($services, '_running_state') !== false)
		{
			return true;
		}

		return false;
	}

	//Function used to get number of alerts categorized by month
	private function _get_alert_month($input_array, $input_state, $host)
	{
		$return_array = array();

		//populate the array
		for($i = 0; $i < 13; $i ++)
		{
			$return_array[$i] = 0;
		}

		if($host)
		{
			foreach($input_array as $alerts)
			{
				$month = (int)date('m', (int)$alerts->datetime);

				if($this->_compare_string($alerts->state, $input_state))
				{			
					$return_array[$month] += 1;			
				}
			}
		}
		else
		{
			foreach($input_array as $alerts)
			{
				$month = (int)date('m', (int)$alerts->datetime);

				if($this->_compare_string($alerts->state, $input_state))
				{
					if( !$this->_is_host_resource($alerts->servicename) )
					{
						$return_array[$month] += 1;
					}
				}
			}
		}

		//code used to remove the first element of the array 
		$throw = array_shift($return_array);

		return $return_array;
	}

	//Function used to get the number of alerts categorized by day of month
	private function _get_alert_day_of_month($input_array, $input_state, $host)
	{
		$return_array = array();

		//populate the array
		for($i = 0; $i < 32; $i ++)
		{
			$return_array[$i] = 0;
		}

		if($host)
		{
			foreach($input_array as $alerts)
			{
				$day = (int)date('j', (int)$alerts->datetime);

				if($this->_compare_string($alerts->state, $input_state))
				{			
					$return_array[$day] += 1;		
				}
			}
		}
		else
		{
			foreach($input_array as $alerts)
			{
				$day = (int)date('j', (int)$alerts->datetime);

				if($this->_compare_string($alerts->state, $input_state))
				{
					if( !$this->_is_host_resource($alerts->servicename) )
					{
						$return_array[$day] += 1;
					}
				}
			}
		}

		//code used to remove the first element of the array 
		$throw = array_shift($return_array);

		return $return_array;
	}

	//Function used to get the number of alerts categorized by day of week
	private function _get_alert_day_of_week($input_array, $input_state, $host)
	{
		$return_array = array();

		//populate the array
		for($i = 0; $i < 8; $i ++)
		{
			$return_array[$i] = 0;
		}

		if($host)
		{
			foreach($input_array as $alerts)
			{
				//1 for Monday -> 7 for Sunday
				$day = (int)date('N', (int)$alerts->datetime);

				if($this->_compare_string($alerts->state, $input_state))
				{			
					$return_array[$day] += 1;		
				}
			}
		}
		else
		{
			foreach($input_array as $alerts)
			{
				//1 for Monday -> 7 for Sunday
				$day = (int)date('N', (int)$alerts->datetime);

				if($this->_compare_string($alerts->state, $input_state))
				{
					if( !$this->_is_host_resource($alerts->servicename) )
					{
						$return_array[$day] += 1;
					}
				}
			}
		}

		//code used to remove the first element of the array 
		$throw = array_shift($return_array);

		return $return_array;
	}

	//Function used to get the number of alerts categorized by hour of the day
	private function _get_alert_hour($input_array, $input_state, $host)
	{
		$return_array = array();

		//populate the array
		for($i = 0; $i < 25; $i ++)
		{
			$return_array[$i] = 0;
		}

		if($host)
		{
			foreach($input_array as $alerts)
			{
				//1 for Monday -> 7 for Sunday
				$hour = (int)date('G', (int)$alerts->datetime);

				if($this->_compare_string($alerts->state, $input_state))
				{			
					$return_array[$hour] += 1;			
				}
			}
		}
		else
		{
			foreach($input_array as $alerts)
			{
				//1 for Monday -> 7 for Sunday
				$hour = (int)date('G', (int)$alerts->datetime);

				if($this->_compare_string($alerts->state, $input_state))
				{
					if( !$this->_is_host_resource($alerts->servicename) )
					{
						$return_array[$hour] += 1;
					}
				}
			}
		}

		//code used to remove the first element of the array 
		$throw = array_shift($return_array);

		return $return_array;
	}




}




?>
