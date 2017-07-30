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
	protected $properties = array(
		'date_time' => '',
		'logtype' => '',
		'hostname' => '',
		'servicename' => '',
		'state' => '',
		'state_type' => '',
		'retry_count' => '',
		'messages' => '',
	);
	protected $properties_array = array();
	//variable used to store data from nagios log file
	//adapted from: nagios_data.php
	//location: ./application/models/nagios_data.php
	protected $_AvailabilityCollection;
	protected $_TrendCollection;
	protected $_Alert_historyCollection;
	protected $_Alert_summaryCollection;
	protected $_Alert_histogramCollection;
	protected $_Events_logCollection;
	protected $_NotificationsCollection;
	protected $report_type = array(
		'availability',
		'trend',
		'alert_history',
		'alert_summary',
		'alert_histogram' ,
		'events_log',
		'notifications',
	);
	private $_map = array();
	//constructor
	public function __construct()
	{
		parent::__construct();
		
		$this->_map_collections();
		$this->_get_nagios_log();
		$this->_get_archives_log();
		$this->_insert_data();
	}
	private function _map_collections()
	{
		$this->_map = array(
			'availability' => &$this->_AvailabilityCollection,
			'trend' =>&$this->_TrendCollection,
			'alert_history' => &$this->_Alert_historyCollection,
			'alert_summary' => &$this->_Alert_summaryCollection,
			'alert_histogram' => &$this->_Alert_histogramCollection,
			'events_log' => &$this->_Events_logCollection,
			'notifications' => &$this->_NotificationsCollection,
		);
/** TEMPORARY MAP*/
		$this->report_type['availability'] = &$this->_AvailabilityCollection;
		$this->report_type['trend'] = &$this->_TrendCollection;
		$this->report_type['alert_history'] = &$this->_Alert_historyCollection;
		$this->report_type['alert_summary'] = &$this->_Alert_summaryCollection;
		$this->report_type['alert_histogram'] = &$this->_Alert_histogramCollection;
		$this->report_type['events_log'] = &$this->_Events_logCollection;
		$this->report_type['notifications'] = &$this->_NotificationsCollection;
	}
	public function get_collection($type)
	{
		$collection = '_'.ucfirst($type).'Collection';
		if(isset($this->_map[$type]))
		{
			return $this->_map[$type];
		} 
		else if(isset($this->$collection))
		{
			return $this->$collection;
		}
		else
		{
			throw new Exception(get_class($this).': Unable to retrieve collection of type: '.$type);
		}
	}
	private function _add($type, $Data)
	{
		$Collection = $this->_map[$type];
		$Collection->add($Data);
	}
	//Written by Low Zhi Jian
	//get log file data
	private function _get_nagios_log()
	{
		//opening the nagios log file
		$logfile = fopen("../../../../nagios/var/nagios.log", "r") or die("Unable to open file!");
		
		//array counter
		$i = 0;
		while(! feof($logfile) )
		{
			$this->_nagios_log[$i] = fgets($logfile);
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
				$this->_archives_log[$i] = fgets($file_handle);
				$i++;
			}
			fclose($file_handle);
		}
	}
	private function _insert_data()
	{
		//data_array counter
		$i = 0;
		foreach($this->_nagios_log as $logs)
		{
			$this->_data_array[$i] = $logs;
		}
		foreach($this->_archives_log as $logs)
		{
			$this->_data_array[$i] = $logs;
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
	public function get_events_log($date_required)
	{
		//arrays for each line of data
		//$event_data[0] is the time
		//$event_data[1] is the messages
		$event_data = array();
		//properties_array counter
		$k = 0;
		foreach($this->_data_array as $logs)
		{
			$event_data = explode(' ', $logs, 2);
			$unixtime = $event_data[0];
			$messages = $event_data[1];
			$this->properties['date_time'] = unixtime_convert($unixtime);
			if(compare_date($this->properties['date_time'], $date_required))
			{
				$this->properties['messages'] = $messages;
				$this->properties_array[$k] = json_encode($this->properties);
				$k++;
			}
		}
		return $this->properties_array;
	}
	public function get_notification()
	{
		
	}
	//function to convert unix timestamp to localtime
	private function unixtime_convert($unixtime)
	{
		//remove any non-numeric character
		$new_unixtime = trim($unixtime, '[]');
		return date('M d Y H:i:s', $new_unixtime);
	}
	//function to compare date
	private function compare_date($unix_date, $date_required)
	{
		//$unix_array[0] = month
		//$unix_array[1] = day
		//$unix_array[2] = year
		$unix_array = array();
		$unix_array = explode(' ', $unix_date, 4);
		$month_same = false;
		$day_same = false;
		$year_same = false;
		$unix_month = date_parse($unix_array[0]);
		//$date_required is yyyy-mm-dd format
		//$rdate_array[0] = year
		//$rdate_array[1] = month
		//$rdate_array[2] = day
		$rdate_array = array();
		$rdate_array = explode('-', $date_required, 3);
		if($unix_month === $rdate_array[1])
		{
			$month_same = true;
		}
		if($unix_array[1] === $rdate_array[2])
		{
			$day_same = true;
		}
		if($unix_array[2] === $rdate_array[0])
		{
			$year_same = true;
		}
		if($month_same && $day_same && $year_same)
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
