<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class System_commands extends CI_Model

{
	protected $output;
	protected $return_value;

	//constructor
	public function __construct()
	{
		parent::__construct();

		$this->return_value = "";
	}

	//Written by : Low Zhi Jian (UTAR)
	//The way to run shell script from php
	//Adapted from : https://stackoverflow.com/questions/7397672/how-to-run-a-sh-file-from-php

	//The way to pass variables into shell script
	//Adapted from : https://stackoverflow.com/questions/16932113/passing-variables-to-shell-exec

	//The way to use if-else method in shell script
	//Adapted from : http://codewiki.wikidot.com/shell-script:if-else

	//The way to use switch case method in shell script
	//Adapted from : https://www.tutorialspoint.com/unix/case-esac-statement.htm

	public function add_comment($input_host_name, $input_service_description, $input_persistent, $input_author, $input_comments, $input_type)
	{
		$commands = '';

		//$input_type = 'host';
		if($this->_compare_string($input_type, 'host'))
		{
			$commands = 'ADD_HOST_COMMENT;'.$input_host_name.';'.$input_persistent.';'.$input_author.';'.$input_comments;
		}
		//$input_type = 'svc';
		else
		{
			$commands = 'ADD_SVC_COMMENT;'.$input_host_name.';'.$input_service_description.';'.$input_persistent.';'.$input_author.';'.$input_comments; 
		}

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
		}
	}

	public function delete_comment($input_comment_id, $input_type)
	{	
		$commands = '';

		//$type = 'host';
		if($this->_compare_string($input_type, 'host'))
		{
			$commands = 'DEL_HOST_COMMENT;'.$input_comment_id;
		}
		//$type = 'svc';
		else
		{
			$commands = 'DEL_SVC_COMMENT;'.$input_comment_id;
		}

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
		}
	}

	public function schedule_downtime($input_host_name, $input_start_time, $input_end_time, $input_fixed, $input_trigger_id, $input_duration, $input_author, $input_comments, $input_type)
	{
		$commands = '';

		//$type = 'host';
		if($this->_compare_string($input_type, 'host'))
		{
			$commands = 'SCHEDULE_HOST_DOWNTIME;'.$input_host_name.';'.$input_start_time.';'.$input_end_time.';'.$input_fixed.';'.$input_trigger_id.';'.$input_duration.';'.$input_author.';'.$input_comments;
		}
		//$type = 'svc';
		else
		{
			$commands = 'SCHEDULE_HOST_SVC_DOWNTIME;'.$input_host_name.';'.$input_start_time.';'.$input_end_time.';'.$input_fixed.';'.$input_trigger_id.';'.$input_duration.';'.$input_author.';'.$input_comments;
		}

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
		}
	}

	public function modify_process_info($input_type)
	{
		$commands = '';

		//$type = 'shutdown_nagios';
		if($this->_compare_string($input_type, 'shutdown_nagios'))
		{
			$commands = 'SHUTDOWN_PROGRAM';
		}
		//$type = 'restart_nagios';
		else if($this->_compare_string($input_type, 'restart_nagios'))
		{
			$commands = 'RESTART_PROGRAM';
		}
		//$type = 'enable_notification';
		else if($this->_compare_string($input_type, 'enable_notification'))
		{
			$commands = 'ENABLE_NOTIFICATIONS';
		}
		//$type = 'disable_notification';
		else if($this->_compare_string($input_type, 'disable_notification'))
		{
			$commands = 'DISABLE_NOTIFICATIONS';
		}
		//$type = 'start_service_check';
		else if($this->_compare_string($input_type, 'start_service_check'))
		{
			$commands = 'START_EXECUTING_SVC_CHECKS';
		}
		//$type = 'stop_service_check';
		else if($this->_compare_string($input_type, 'stop_service_check'))
		{
			$commands = 'STOP_EXECUTING_SVC_CHECKS';
		}
		//$type = 'start_passive_service_check';
		else if($this->_compare_string($input_type, 'start_passive_service_check'))
		{
			$commands = 'START_ACCEPTING_PASSIVE_SVC_CHECKS';
		}	
		//$type = 'stop_passive_service_check';
		else if($this->_compare_string($input_type, 'stop_passive_service_check'))
		{
			$commands = 'STOP_ACCEPTING_PASSIVE_SVC_CHECKS';
		}
		//$type = 'enable_event_handler';
		else if($this->_compare_string($input_type, 'enable_event_handler'))
		{
			$commands = 'ENABLE_EVENT_HANDLERS';
		}
		//$type = 'disable_event_handler';
		else if($this->_compare_string($input_type, 'disable_event_handler'))
		{
			$commands = 'DISABLE_EVENT_HANDLERS';
		}
		//$type = 'start_obsess_over_svc';
		else if($this->_compare_string($input_type, 'start_obsess_over_svc'))
		{
			$commands = 'START_OBSESSING_OVER_SVC_CHECKS';
		}
		//$type = 'stop_obsess_over_svc';
		else if($this->_compare_string($input_type, 'stop_obsess_over_svc'))
		{
			$commands = 'STOP_OBSESSING_OVER_SVC_CHECKS';
		}
		//$type = 'start_obsess_over_host';
		else if($this->_compare_string($input_type, 'start_obsess_over_host'))
		{
			$commands = 'START_OBSESSING_OVER_HOST_CHECKS';
		}
		//$type = 'stop_obsess_over_host';
		else if($this->_compare_string($input_type, 'stop_obsess_over_host'))
		{
			$commands = 'STOP_OBSESSING_OVER_HOST_CHECKS';
		}
		//$type = 'enable_performance';
		else if($this->_compare_string($input_type, 'enable_performance'))
		{
			$commands = 'ENABLE_PERFORMANCE_DATA';
		}
		//$type = 'disable_performance';
		else if($this->_compare_string($input_type, 'disable_performance'))
		{
			$commands = 'DISABLE_PERFORMANCE_DATA';
		}
		//$type = 'start_host_check';
		else if($this->_compare_string($input_type, 'start_host_check'))
		{
			$commands = 'START_EXECUTING_HOST_CHECKS';
		}
		//$type = 'stop_host_check';
		else if($this->_compare_string($input_type, 'stop_host_check'))
		{
			$commands = 'STOP_EXECUTING_HOST_CHECKS';
		}
		//$type = 'start_passive_host_check';
		else if($this->_compare_string($input_type, 'start_passive_host_check'))
		{	
			$commands = 'START_ACCEPTING_PASSIVE_HOST_CHECKS';
		}
		//$type = 'stop_passive_host_check';
		else if($this->_compare_string($input_type, 'stop_passive_host_check'))
		{
			$commands = 'STOP_ACCEPTING_PASSIVE_HOST_CHECKS';
		}
		//$type = 'enable_flap';
		else if($this->_compare_string($input_type, 'enable_flap'))
		{
			$commands = 'ENABLE_FLAP_DETECTION';
		}
		//$type = 'disable_flap';
		else
		{
			$commands = 'DISABLE_FLAP_DETECTION';
		}

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
		}
	}

	public function performance_info_commands()
	{
		$this->return_value = shell_exec("/usr/local/nagios/bin/nagiostats -c /usr/local/nagios/etc/nagios.cfg");

		$this->return_value = trim($this->return_value);

		//split using newline character
		$return_array = preg_split("/\\r\\n|\\r|\\n/", $this->return_value);

		return $return_array;
	}

	public function scheduling_queue($input_host_name, $input_service_description, $input_checktime, $input_type)
	{
		$commands = '';

		//$type = 'enable_svc_check';
		if($this->_compare_string($input_type, 'enable_svc_check'))
		{
			$commands = 'ENABLE_SVC_CHECK;'.$input_host_name.';'.$input_service_description;
		}
		//$type = 'disable_svc_check';
		else if($this->_compare_string($input_type, 'disable_svc_check'))
		{
			$commands = 'DISABLE_SVC_CHECK;'.$input_host_name.';'.$input_service_description;
		}
		//$type = 'schedule_svc_check';
		else
		{
			$commands = 'SCHEDULE_SVC_CHECK;'.$input_host_name.';'.$input_service_description.';'.$input_checktime;
		}

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
		}
	}

	private function _compare_string($input_string, $data_string)
	{
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