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
		$host_name = $input_host_name;
		$service_description = $input_service_description;
		$persistent = $input_persistent;
		$author = $input_author;
		$comments = $input_comments;
		$type = $input_type;
		//$type = 'host';
		//$type = 'svc';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/add_comment.sh ".escapeshellarg($host_name)." ".escapeshellarg($service_description)." ".escapeshellarg($persistent)." ".escapeshellarg($author)." ".escapeshellarg($comments)." ".escapeshellarg($type));

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
		$comment_id = $input_comment_id;
		$type = $input_type;
		//$type = 'host';
		//$type = 'svc';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/delete_comment.sh ".escapeshellarg($comment_id)." ".escapeshellarg($type));

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
		$host_name = $input_host_name;
		$start_time = $input_start_time;
		$end_time = $input_end_time;
		$fixed = $input_fixed;
		$trigger_id = $input_trigger_id;
		$duration = $input_duration;
		$author = $input_author;
		$comments = $input_comments;
		$type = $input_type;
		//$type = 'host';
		//$type = 'svc';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/schedule_downtime.sh ".escapeshellarg($host_name)." ".escapeshellarg($start_time)." ".escapeshellarg($end_time)." ".escapeshellarg($fixed)." ".escapeshellarg($trigger_id)." ".escapeshellarg($duration)." ".escapeshellarg($author)." ".escapeshellarg($comments)." ".escapeshellarg($type));

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
		$type = $input_type;
		//$type = 'shutdown_nagios';
		//$type = 'restart_nagios';
		//$type = 'enable_notification';
		//$type = 'disable_notification';
		//$type = 'start_service_check';
		//$type = 'stop_service_check';
		//$type = 'start_passive_service_check';
		//$type = 'stop_passive_service_check';
		//$type = 'enable_event_handler';
		//$type = 'disable_event_handler';
		//$type = 'start_obsess_over_svc';
		//$type = 'stop_obsess_over_svc';
		//$type = 'start_obsess_over_host';
		//$type = 'stop_obsess_over_host';
		//$type = 'enable_performance';
		//$type = 'disable_performance';
		//$type = 'start_host_check';
		//$type = 'stop_host_check';
		//$type = 'start_passive_host_check';
		//$type = 'stop_passive_host_check';
		//$type = 'enable_flap';
		//$type = 'disable_flap';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/modify_process_info.sh ".escapeshellarg($type));

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
		$host_name = $input_host_name;
		$service_description = $input_service_description;
		$checktime = $input_checktime;
		$type = $input_type;
		//$type = 'enable_svc_check';
		//$type = 'disable_svc_check';
		//$type = 'schedule_svc_check';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/scheduling_queue.sh ".escapeshellarg($host_name)." ".escapeshellarg($service_description)." ".escapeshellarg($checktime)." ".escapeshellarg($type));

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

}

?>