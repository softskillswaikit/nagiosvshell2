<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class System_commands extends CI_Model
{
	protected $return_value;

	//constructor
	public function __construct()
	{
		parent::__construct();

		$this->return_value = "";

		date_default_timezone_set('UTC');
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

	//These commands are adapted from:
	//https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=?

	//command id = 1
	public function add_host_comment($input_host_name, $input_persistent, $input_author, $input_comments)
	{
		if($input_persistent)
		{
			$persistent = '1';
		}
		else
		{
			$persistent = '0';
		}

		$commands = 'ADD_HOST_COMMENT;'.$input_host_name.';'.$persistent.';'.$input_author.';'.$input_comments;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 2
	public function add_svc_comment($input_host_name, $input_service_description, $input_persistent, $input_author, $input_comments)
	{
		if($input_persistent)
		{
			$persistent = '1';
		}
		else
		{
			$persistent = '0';
		}

		$commands = 'ADD_SVC_COMMENT;'.$input_host_name.';'.$input_service_description.';'.$persistent.';'.$input_author.';'.$input_comments; 

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 3
	public function delete_host_comment($input_comment_id)
	{
		$commands = 'DEL_HOST_COMMENT;'.$input_comment_id;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 4
	public function delete_svc_comment($input_comment_id)
	{	
		$commands = 'DEL_SVC_COMMENT;'.$input_comment_id;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 5
	public function enable_svc_check($input_host_name, $input_service_description)
	{	
		$commands = 'ENABLE_SVC_CHECK;'.$input_host_name.';'.$input_service_description;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}	

	//command id = 6
	public function disable_svc_check($input_host_name, $input_service_description)
	{
		$commands = 'DISABLE_SVC_CHECK;'.$input_host_name.';'.$input_service_description;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 7
	public function disable_all_notification()
	{
		$commands = 'DISABLE_NOTIFICATIONS';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 8
	public function enable_all_notification()
	{
		$commands = 'ENABLE_NOTIFICATIONS';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 9
	public function restart_nagios()
	{
		$commands = 'RESTART_PROGRAM';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 10
	public function shutdown_nagios()
	{
		$commands = 'SHUTDOWN_PROGRAM';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 11
	public function enable_svc_notification($input_host_name, $input_service_description)
	{
		$commands = 'ENABLE_SVC_NOTIFICATIONS;'.$input_host_name.';'.$input_service_description;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 12
	public function disable_svc_notification($input_host_name, $input_service_description)
	{
		$commands = 'DISABLE_SVC_NOTIFICATIONS;'.$input_host_name.';'.$input_service_description;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 13
	public function delete_all_host_comment($input_host_name)
	{
		$commands = 'DEL_ALL_HOST_COMMENTS;'.$input_host_name;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 14
	public function delete_all_svc_comment($input_host_name, $input_service_description)
	{
		$commands = 'DEL_ALL_SVC_COMMENTS;'.$input_host_name.';'.$input_service_description;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 15
	public function enable_host_notification($input_host_name)
	{
		$commands = 'ENABLE_HOST_NOTIFICATIONS;'.$input_host_name;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 16
	public function disable_host_notification($input_host_name)
	{
		$commands = 'DISABLE_HOST_NOTIFICATIONS;'.$input_host_name;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 29
	//command id = 129 (force check)
	public function schedule_svc_check($input_host_name, $input_service_description, $input_checktime, $input_force_check)
	{
		if($input_force_check)
		{
			$commands = 'SCHEDULE_FORCED_SVC_CHECK;'.$input_host_name.';'.$input_service_description.';'.$input_checktime;
		}
		else
		{
			$commands = 'SCHEDULE_SVC_CHECK;'.$input_host_name.';'.$input_service_description.';'.$input_checktime;
		}

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 30
	//command id = 130 (force check)
	public function schedule_host_svc_check($input_host_name, $input_checktime, $input_force_check)
	{
		if($input_force_check)
		{
			$commands = 'SCHEDULE_FORCED_HOST_SVC_CHECKS;'.$input_host_name.';'.$input_checktime;
		}
		else
		{
			$commands = 'SCHEDULE_HOST_SVC_CHECKS;'.$input_host_name.';'.$input_checktime;
		}

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 33
	public function enable_host_svc_check($input_host_name)
	{
		$commands = 'ENABLE_HOST_SVC_CHECKS;'.$input_host_name;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 34
	public function disable_host_svc_check($input_host_name)
	{
		$commands = 'DISABLE_HOST_SVC_CHECKS;'.$input_host_name;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 35
	public function enable_host_svc_notification($input_host_name)
	{
		$commands = 'ENABLE_HOST_SVC_NOTIFICATIONS;'.$input_host_name;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 36
	public function disable_host_svc_notification($input_host_name)
	{
		$commands = 'DISABLE_HOST_SVC_NOTIFICATIONS;'.$input_host_name;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 39
	public function acknowledge_host_problem($input_host_name, $input_sticky, $input_notify, $input_persistent, $input_author, $input_comments)
	{
		if($input_sticky)
		{
			$sticky = '2';
		}
		else
		{
			$sticky = '0';
		}

		if($input_notify)
		{
			$notify = '1';
		}
		else
		{
			$notify = '0';
		}

		if($input_persistent)
		{
			$persistent = '1';
		}
		else
		{
			$persistent = '0';
		}

		$commands = 'ACKNOWLEDGE_HOST_PROBLEM;'.$input_host_name.';'.$sticky.';'.$notify.';'.$persistent.';'.$input_author.';'.$input_comments;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 40
	public function acknowledge_svc_problem($input_host_name, $input_service_description, $input_sticky, $input_notify, $input_persistent, $input_author, $input_comments)
	{
		if($input_sticky)
		{
			$sticky = '2';
		}
		else
		{
			$sticky = '0';
		}

		if($input_notify)
		{
			$notify = '1';
		}
		else
		{
			$notify = '0';
		}

		if($input_persistent)
		{
			$persistent = '1';
		}
		else
		{
			$persistent = '0';
		}

		$commands = 'ACKNOWLEDGE_SVC_PROBLEM;'.$input_host_name.';'.$input_service_description.';'.$sticky.';'.$notify.';'.$persistent.';'.$input_author.';'.$input_comments;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 41
	public function start_svc_check()
	{
		$commands = 'START_EXECUTING_SVC_CHECKS';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 42
	public function stop_svc_check()
	{
		$commands = 'STOP_EXECUTING_SVC_CHECKS';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 43
	public function start_passive_svc_check()
	{
		$commands = 'START_ACCEPTING_PASSIVE_SVC_CHECKS';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 44
	public function stop_passive_svc_check()
	{
		$commands = 'STOP_ACCEPTING_PASSIVE_SVC_CHECKS';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 45
	public function enable_passive_svc_check($input_host_name, $input_service_description)
	{
		$commands = 'ENABLE_PASSIVE_SVC_CHECKS;'.$input_host_name.';'.$input_service_description;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 46
	public function disable_passive_svc_check($input_host_name, $input_service_description)
	{
		$commands = 'DISABLE_PASSIVE_SVC_CHECKS;'.$input_host_name.';'.$input_service_description;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 47
	public function enable_event_handler()
	{
		$commands = 'ENABLE_EVENT_HANDLERS';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 48
	public function disable_event_handler()
	{
		$commands = 'DISABLE_EVENT_HANDLERS';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 53
	public function enable_host_check($input_host_name)
	{
		$commands = 'ENABLE_HOST_CHECK;'.$input_host_name;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 54
	public function disable_host_check($input_host_name)
	{
		$commands = 'DISABLE_HOST_CHECK;'.$input_host_name;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 55
	public function start_obsess_over_svc_check()
	{
		$commands = 'START_OBSESSING_OVER_SVC_CHECKS';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 56
	public function stop_obsess_over_svc_check()
	{
		$commands = 'STOP_OBSESSING_OVER_SVC_CHECKS';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 57
	public function start_obsess_over_host_check()
	{
		$commands = 'START_OBSESSING_OVER_HOST_CHECKS';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 58
	public function stop_obsess_over_host_check()
	{
		$commands = 'STOP_OBSESSING_OVER_HOST_CHECKS';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		///check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 59
	public function start_obsess_over_host($input_host_name)
	{
		$commands = 'START_OBSESSING_OVER_HOST;'.$input_host_name;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 60
	public function stop_obsess_over_host($input_host_name)
	{
		$commands = 'STOP_OBSESSING_OVER_HOST;'.$input_host_name;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 61
	public function start_obsess_over_svc($input_host_name, $input_service_description)
	{
		$commands = 'START_OBSESSING_OVER_SVC;'.$input_host_name.';'.$input_service_description;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 62
	public function stop_obsess_over_svc($input_host_name, $input_service_description)
	{
		$commands = 'STOP_OBSESSING_OVER_SVC;'.$input_host_name.';'.$input_service_description;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 65
	public function enable_performance_data()
	{
		$commands = 'ENABLE_PERFORMANCE_DATA';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 66
	public function disable_performance_data()
	{
		$commands = 'DISABLE_PERFORMANCE_DATA';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 67
	public function start_host_check()
	{
		$commands = 'START_EXECUTING_HOST_CHECKS';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 68
	public function stop_host_check()
	{
		$commands = 'STOP_EXECUTING_HOST_CHECKS';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 69
	public function start_passive_host_check()
	{
		$commands = 'START_ACCEPTING_PASSIVE_HOST_CHECKS';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 70
	public function stop_passive_host_check()
	{
		$commands = 'STOP_ACCEPTING_PASSIVE_HOST_CHECKS';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 71
	public function enable_passive_host_check($input_host_name)
	{
		$commands = 'ENABLE_PASSIVE_HOST_CHECKS;'.$input_host_name;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 72
	public function disable_passive_host_check($input_host_name)
	{
		$commands = 'DISABLE_PASSIVE_HOST_CHECKS;'.$input_host_name;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 73
	public function enable_flap_detection()
	{
		$commands = 'ENABLE_FLAP_DETECTION';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 74
	public function disable_flap_detection()
	{
		$commands = 'DISABLE_FLAP_DETECTION';

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 75
	public function enable_host_flap_detection($input_host_name)
	{
		$commands = 'ENABLE_HOST_FLAP_DETECTION;'.$input_host_name;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 76
	public function enable_svc_flap_detection($input_host_name, $input_service_description)
	{
		$commands = 'ENABLE_SVC_FLAP_DETECTION;'.$input_host_name.';'.$input_service_description;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 77
	public function disable_host_flap_detection($input_host_name)
	{
		$commands = 'DISABLE_HOST_FLAP_DETECTION;'.$input_host_name;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 118
	public function schedule_host_downtime($input_host_name, $input_start_time, $input_end_time, $input_fixed, $input_trigger_id='0', $input_duration, $input_author, $input_comments)
	{
		if($input_fixed)
		{
			$fixed = '1';
		}
		else
		{
			$fixed = '0';
		}

		$commands = 'SCHEDULE_HOST_DOWNTIME;'.$input_host_name.';'.$input_start_time.';'.$input_end_time.';'.$fixed.';'.$input_trigger_id.';'.$input_duration.';'.$input_author.';'.$input_comments;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 119
	public function schedule_svc_downtime($input_host_name, $input_service_description, $input_start_time, $input_end_time, $input_fixed, $input_trigger_id='0', $input_duration, $input_author, $input_comments)
	{
		if($input_fixed)
		{
			$fixed = '1';
		}
		else
		{
			$fixed = '0';
		}

		$commands = 'SCHEDULE_SVC_DOWNTIME;'.$input_host_name.';'.$input_service_description.';'.$input_start_time.';'.$input_end_time.';'.$fixed.';'.$input_trigger_id.';'.$input_duration.';'.$input_author.';'.$input_comments;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 122
	public function schedule_host_svc_downtime($input_host_name, $input_start_time, $input_end_time, $input_fixed, $input_trigger_id='0', $input_duration, $input_author, $input_comments)
	{
		if($input_fixed)
		{
			$fixed = '1';
		}
		else
		{
			$fixed = '0';
		}

		$commands = 'SCHEDULE_HOST_SVC_DOWNTIME;'.$input_host_name.';'.$input_start_time.';'.$input_end_time.';'.$fixed.';'.$input_trigger_id.';'.$input_duration.';'.$input_author.';'.$input_comments;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 127
	//command id = 128 (force check)
	public function schedule_host_check($input_host_name, $input_checktime, $input_force_check)
	{
		if($input_force_check)
		{
			$commands = 'SCHEDULE_FORCED_HOST_CHECK;'.$input_host_name.';'.$input_checktime;
		}
		else
		{
			$commands = 'SCHEDULE_HOST_CHECK;'.$input_host_name.';'.$input_checktime;
		}

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 134
	public function send_custom_host_notification($input_host_name, $input_force, $input_broadcast, $input_author, $input_comments)
	{
		if($input_force && $input_broadcast)
		{
			$option = '3';
		}
		else if($input_force)
		{
			$option = '2';
		}
		else if($input_broadcast)
		{	
			$option = '1';
		}
		else
		{
			$option = '0';
		}

		$commands = 'SEND_CUSTOM_HOST_NOTIFICATION;'.$input_host_name.';'.$option.';'.$input_author.';'.$input_comments;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 135
	public function send_custom_svc_notification($input_host_name, $input_service_description, $input_force, $input_broadcast, $input_author, $input_comments)
	{
		if($input_force && $input_broadcast)
		{
			$option = '3';
		}
		else if($input_force)
		{
			$option = '2';
		}
		else if($input_broadcast)
		{	
			$option = '1';
		}
		else
		{
			$option = '0';
		}

		$commands = 'SEND_CUSTOM_SVC_NOTIFICATION;'.$input_host_name.';'.$input_service_description.';'.$option.';'.$input_author.';'.$input_comments;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//command id = 150
	public function disable_svc_flap_detection($input_host_name, $input_service_description)
	{
		$commands = 'DISABLE_SVC_FLAP_DETECTION;'.$input_host_name.';'.$input_service_description;

		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/system_command.sh ".escapeshellarg($commands));

		//check that the command runs successfully
		if(empty($this->return_value))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	public function get_return_array($return_type)
	{
		//process info section
		$process_obj = new StdClass();
		$process_array = array();

		//performance info section
		$performance_obj = new StdClass();
		$parse_value = shell_exec("/usr/local/nagios/bin/nagiostats -c /usr/local/nagios/etc/nagios.cfg");

		$parse_value = trim($parse_value);

		//split using newline character
		$parse_array = preg_split("/\\r\\n|\\r|\\n/", $parse_value);

		if(strcmp($return_type, 'PROCESS') == 0)
		{
			//opening and get data from status.dat file
			$status_file = fopen("/usr/local/nagios/var/status.dat", "r") or die("Unable to open file!");

			//array counter 
			$i = 0;

			while(! feof($status_file) )
			{
				if($i >= 54)
				{
					break;
				}

				$process_array[$i] = trim(fgets($status_file));
				$i++;
			}

			fclose($status_file);

			//program veersion
			list($version, $version_no) = explode('=', $process_array[9], 2);
			$process_obj->$version = $version_no;

			//program start time
			list($start_time, $program_start_time) = explode('=', $process_array[21], 2);
			$process_obj->$start_time = $program_start_time;

			//total running time
			list($run_time, $program_run_time) = explode(':', $parse_array[11], 2);
			$process_obj->total_run_time = trim($program_run_time); 

			//last log rotation
			list($last_log, $last_log_rotation) = explode('=', $process_array[22], 2);
			$process_obj->$last_log = $last_log_rotation;

			//nagios PID
			list($nagios_id, $nagios_pid) = explode('=', $process_array[19], 2);
			$process_obj->$nagios_id = $nagios_pid;

			//enable notification?
			list($notification, $enable_notification) = explode('=', $process_array[23], 2);

			if((int)$enable_notification)
			{
				$process_obj->$notification = true;
			}
			else
			{
				$process_obj->$notification = false;
			}

			//service check being executed?
			list($service_check, $service_check_execute) = explode('=', $process_array[24], 2);

			if((int)$service_check_execute)
			{
				$process_obj->$service_check = true;
			}
			else
			{
				$process_obj->$service_check = false;
			}

			//passive service check being accepted?
			list($passive_service_check, $passive_service_check_accept) = explode('=', $process_array[25], 2);

			if((int)$passive_service_check_accept)
			{
				$process_obj->$passive_service_check = true;
			}
			else
			{
				$process_obj->$passive_service_check = false;
			}

			//host check being executed?
			list($host_check, $host_check_execute) = explode('=', $process_array[26], 2);

			if((int)$host_check_execute)
			{
				$process_obj->$host_check = true;
			}
			else
			{
				$process_obj->$host_check = false;
			}

			//passive host check being accepted?
			list($passive_host_check, $passive_host_check_accept) = explode('=', $process_array[27], 2);

			if((int)$passive_host_check_accept)
			{
				$process_obj->$passive_host_check = true;
			}
			else
			{
				$process_obj->$passive_host_check = false;
			}

			//enable event handler?
			list($event_handler, $event_handler_enable) = explode('=', $process_array[28], 2);

			if((int)$event_handler_enable)
			{
				$process_obj->$event_handler = true;
			}
			else
			{
				$process_obj->$event_handler = false;
			}

			//obsessing over service?
			list($obsess_service, $obsess_over_service) = explode('=', $process_array[29], 2);

			if((int)$obsess_over_service)
			{
				$process_obj->$obsess_service = true;
			}
			else
			{
				$process_obj->$obsess_service = false;
			}

			//obsessing over host?
			list($obsess_host, $obsess_over_host) = explode('=', $process_array[30], 2);

			if((int)$obsess_over_host)
			{
				$process_obj->$obsess_host = true;
			}
			else
			{
				$process_obj->$obsess_host = false;
			}

			//enable flap detection?
			list($flap_detection, $flap_detection_enable) = explode('=', $process_array[33], 2);

			if((int)$flap_detection_enable)
			{
				$process_obj->$flap_detection = true;
			}
			else
			{
				$process_obj->$flap_detection = false;
			}

			//performance data being processed?
			list($performance_data, $process_performance_data) = explode('=', $process_array[34], 2);

			if((int)$process_performance_data)
			{
				$process_obj->$performance_data = true;
			}
			else
			{
				$process_obj->$performance_data = false;
			}

			foreach($process_obj as $items)
			{
				$items = json_encode($items);
			}

			return $process_obj;
		}
		//$return_type = 'PERFORMANCE'
		else
		{
			//active service checked total
			list($info1, $active_service_check_total) = explode(':', $parse_array[17], 2);
			$performance_obj->active_service_checked_since_program_start = trim($active_service_check_total);

			//active service checked last 1/5/15/60 min
			list($info1, $active_service_check_duration) = explode(':', $parse_array[23], 2);
			list($active_service_check_1, $active_service_check_5, $active_service_check_15, $active_service_check_60) = explode('/', $active_service_check_duration, 4);

			$performance_obj->active_service_check_1 = trim($active_service_check_1);
			$performance_obj->active_service_check_5 = trim($active_service_check_5);
			$performance_obj->active_service_check_15 = trim($active_service_check_15);
			$performance_obj->active_service_check_60 = trim($active_service_check_60);

			//active service execution time
			list($info1, $active_service_execution_time) = explode(':', $parse_array[21], 2);
			$active_service_execution_time = trim($active_service_execution_time, 'sec');
			list($active_service_execution_min, $active_service_execution_max, $active_service_execution_average) = explode('/', $active_service_execution_time, 3);

			$performance_obj->active_service_execution_min = trim($active_service_execution_min).' sec';
			$performance_obj->active_service_execution_max = trim($active_service_execution_max).' sec';
			$performance_obj->active_service_execution_average = trim($active_service_execution_average).' sec';

			//active service latency
			list($info1, $active_service_latency) = explode(':', $parse_array[20], 2);
			$active_service_latency = trim($active_service_latency, 'sec');
			list($active_service_latency_min, $active_service_latency_max, $active_service_latency_average) = explode('/', $active_service_latency, 3);

			$performance_obj->active_service_latency_min = trim($active_service_latency_min).' sec';
			$performance_obj->active_service_latency_max = trim($active_service_latency_max).' sec';
			$performance_obj->active_service_latency_average = trim($active_service_latency_average).' sec';

			//active service percent state change
			list($info1, $active_service_state_change) = explode(':', $parse_array[22], 2);
			$active_service_state_change = trim($active_service_state_change, '%');
			list($active_service_state_change_min, $active_service_state_change_max, $active_service_state_change_average) = explode('/', $active_service_state_change, 3);

			$performance_obj->active_service_state_change_min = trim($active_service_state_change_min).' %';
			$performance_obj->active_service_state_change_max = trim($active_service_state_change_max).' %';
			$performance_obj->active_service_state_change_average = trim($active_service_state_change_average).' %';

			//passive service checked total
			list($info1, $passive_service_check_total) = explode(':', $parse_array[18], 2);
			$performance_obj->passive_service_checked_since_program_start = trim($passive_service_check_total);

			//passive service checked last 1/5/15/60 min
			list($info1, $passive_service_check_duration) = explode(':', $parse_array[26], 2);
			list($passive_service_check_1, $passive_service_check_5, $passive_service_check_15, $passive_service_check_60) = explode('/', $passive_service_check_duration, 4);

			$performance_obj->passive_service_check_1 = trim($passive_service_check_1);
			$performance_obj->passive_service_check_5 = trim($passive_service_check_5);
			$performance_obj->passive_service_check_15 = trim($passive_service_check_15);
			$performance_obj->passive_service_check_60 = trim($passive_service_check_60);

			//passive service percent state change
			list($info1, $passive_service_state_change) = explode(':', $parse_array[25], 2);
			$passive_service_state_change = trim($passive_service_state_change, '%');
			list($passive_service_state_change_min, $passive_service_state_change_max, $passive_service_state_change_average) = explode('/', $passive_service_state_change, 3);

			$performance_obj->passive_service_state_change_min = trim($passive_service_state_change_min).' %';
			$performance_obj->passive_service_state_change_max = trim($passive_service_state_change_max).' %';
			$performance_obj->passive_service_state_change_average = trim($passive_service_state_change_average).' %';

			//active host checked total
			list($info1, $active_host_check_total) = explode(':', $parse_array[34], 2);
			$performance_obj->active_host_checked_since_program_start = trim($active_host_check_total);

			//active host checked last 1/5/15/60 min
			list($info1, $active_host_check_duration) = explode(':', $parse_array[40], 2);
			list($active_host_check_1, $active_host_check_5, $active_host_check_15, $active_host_check_60) = explode('/', $active_host_check_duration, 4);

			$performance_obj->active_host_check_1 = trim($active_host_check_1);
			$performance_obj->active_host_check_5 = trim($active_host_check_5);
			$performance_obj->active_host_check_15 = trim($active_host_check_15);
			$performance_obj->active_host_check_60 = trim($active_host_check_60);

			//active host execution time
			list($info1, $active_host_execution_time) = explode(':', $parse_array[38], 2);
			$active_host_execution_time = trim($active_host_execution_time, 'sec');
			list($active_host_execution_min, $active_host_execution_max, $active_host_execution_average) = explode('/', $active_host_execution_time, 3);

			$performance_obj->active_host_execution_min = trim($active_host_execution_min).' sec';
			$performance_obj->active_host_execution_max = trim($active_host_execution_max).' sec';
			$performance_obj->active_host_execution_average = trim($active_host_execution_average).' sec';

			//active host latency
			list($info1, $active_host_latency) = explode(':', $parse_array[37], 2);
			$active_host_latency = trim($active_host_latency, 'sec');
			list($active_host_latency_min, $active_host_latency_max, $active_host_latency_average) = explode('/', $active_host_latency, 3);

			$performance_obj->active_host_latency_min = trim($active_host_latency_min).' sec';
			$performance_obj->active_host_latency_max = trim($active_host_latency_max).' sec';
			$performance_obj->active_host_latency_average = trim($active_host_latency_average).' sec';

			//active host percent state change
			list($info1, $active_host_state_change) = explode(':', $parse_array[39], 2);
			$active_host_state_change = trim($active_host_state_change, '%');
			list($active_host_state_change_min, $active_host_state_change_max, $active_host_state_change_average) = explode('/', $active_host_state_change, 3);

			$performance_obj->active_host_state_change_min = trim($active_host_state_change_min).' %';
			$performance_obj->active_host_state_change_max = trim($active_host_state_change_max).' %';
			$performance_obj->active_host_state_change_average = trim($active_host_state_change_average).' %';

			//passive host checked total
			list($info1, $passive_host_check_total) = explode(':', $parse_array[35], 2);
			$performance_obj->passive_host_checked_since_program_start = trim($passive_host_check_total);

			//passive host checked last 1/5/15/60 min
			list($info1, $passive_host_check_duration) = explode(':', $parse_array[43], 2);
			list($passive_host_check_1, $passive_host_check_5, $passive_host_check_15, $passive_host_check_60) = explode('/', $passive_host_check_duration, 4);

			$performance_obj->passive_host_check_1 = trim($passive_host_check_1);
			$performance_obj->passive_host_check_5 = trim($passive_host_check_5);
			$performance_obj->passive_host_check_15 = trim($passive_host_check_15);
			$performance_obj->passive_host_check_60 = trim($passive_host_check_60);

			//passive host percent state change
			list($info1, $passive_host_state_change) = explode(':', $parse_array[42], 2);
			$passive_host_state_change = trim($passive_host_state_change, '%');
			list($passive_host_state_change_min, $passive_host_state_change_max, $passive_host_state_change_average) = explode('/', $passive_host_state_change, 3);

			$performance_obj->passive_host_state_change_min = trim($passive_host_state_change_min).' %';
			$performance_obj->passive_host_state_change_max = trim($passive_host_state_change_max).' %';
			$performance_obj->passive_host_state_change_average = trim($passive_host_state_change_average).' %';

			//active scheduled host checks
			list($info1, $active_schedule_host_check) = explode(':', $parse_array[49], 2);
			list($active_schedule_host_check_1, $active_schedule_host_check_5, $active_schedule_host_check_15) = explode('/', $active_schedule_host_check, 3);

			$performance_obj->active_schedule_host_check_1 = trim($active_schedule_host_check_1);
			$performance_obj->active_schedule_host_check_5 = trim($active_schedule_host_check_5);
			$performance_obj->active_schedule_host_check_15 = trim($active_schedule_host_check_15);

			//active on-demand host checks
			list($info1, $active_ondemand_host_check) = explode(':', $parse_array[50], 2);
			list($active_ondemand_host_check_1, $active_ondemand_host_check_5, $active_ondemand_host_check_15) = explode('/', $active_ondemand_host_check, 3);

			$performance_obj->active_ondemand_host_check_1 = trim($active_ondemand_host_check_1);
			$performance_obj->active_ondemand_host_check_5 = trim($active_ondemand_host_check_5);
			$performance_obj->active_ondemand_host_check_15 = trim($active_ondemand_host_check_15);

			//parallel host checks
			list($info1, $parallel_host_check) = explode(':', $parse_array[51], 2);
			list($parallel_host_check_1, $parallel_host_check_5, $parallel_host_check_15) = explode('/', $parallel_host_check, 3);

			$performance_obj->parallel_host_check_1 = trim($parallel_host_check_1);
			$performance_obj->parallel_host_check_5 = trim($parallel_host_check_5);
			$performance_obj->parallel_host_check_15 = trim($parallel_host_check_15);

			//serial host checks
			list($info1, $serial_host_check) = explode(':', $parse_array[52], 2);
			list($serial_host_check_1, $serial_host_check_5, $serial_host_check_15) = explode('/', $serial_host_check, 3);

			$performance_obj->serial_host_check_1 = trim($serial_host_check_1);
			$performance_obj->serial_host_check_5 = trim($serial_host_check_5);
			$performance_obj->serial_host_check_15 = trim($serial_host_check_15);

			//cached host checks
			list($info1, $cached_host_check) = explode(':', $parse_array[53], 2);
			list($cached_host_check_1, $cached_host_check_5, $cached_host_check_15) = explode('/', $cached_host_check, 3);

			$performance_obj->cached_host_check_1 = trim($cached_host_check_1);
			$performance_obj->cached_host_check_5 = trim($cached_host_check_5);
			$performance_obj->cached_host_check_15 = trim($cached_host_check_15);

			//passive host checks
			list($info1, $passive_host_check_last) = explode(':', $parse_array[54], 2);
			list($passive_host_check_last_1, $passive_host_check_last_5, $passive_host_check_last_15) = explode('/', $passive_host_check_last, 3);

			$performance_obj->passive_host_check_last_1 = trim($passive_host_check_last_1);
			$performance_obj->passive_host_check_last_5 = trim($passive_host_check_last_5);
			$performance_obj->passive_host_check_last_15 = trim($passive_host_check_last_15);

			//active scheduled service checks
			list($info1, $active_schedule_service_check) = explode(':', $parse_array[56], 2);
			list($active_schedule_service_check_1, $active_schedule_service_check_5, $active_schedule_service_check_15) = explode('/', $active_schedule_service_check, 3);

			$performance_obj->active_schedule_service_check_1 = trim($active_schedule_service_check_1);
			$performance_obj->active_schedule_service_check_5 = trim($active_schedule_service_check_5);
			$performance_obj->active_schedule_service_check_15 = trim($active_schedule_service_check_15);

			//active on-demand service checks
			list($info1, $active_ondemand_service_check) = explode(':', $parse_array[57], 2);
			list($active_ondemand_service_check_1, $active_ondemand_service_check_5, $active_ondemand_service_check_15) = explode('/', $active_ondemand_service_check, 3);

			$performance_obj->active_ondemand_service_check_1 = trim($active_ondemand_service_check_1);
			$performance_obj->active_ondemand_service_check_5 = trim($active_ondemand_service_check_5);
			$performance_obj->active_ondemand_service_check_15 = trim($active_ondemand_service_check_15);

			//cached service checks
			list($info1, $cached_service_check) = explode(':', $parse_array[58], 2);
			list($cached_service_check_1, $cached_service_check_5, $cached_service_check_15) = explode('/', $cached_service_check, 3);

			$performance_obj->cached_service_check_1 = trim($cached_service_check_1);
			$performance_obj->cached_service_check_5 = trim($cached_service_check_5);
			$performance_obj->cached_service_check_15 = trim($cached_service_check_15);

			//passive service checks
			list($info1, $passive_service_check_last) = explode(':', $parse_array[59], 2);
			list($passive_service_check_last_1, $passive_service_check_last_5, $passive_service_check_last_15) = explode('/', $passive_service_check_last, 3);

			$performance_obj->passive_service_check_last_1 = trim($passive_service_check_last_1);
			$performance_obj->passive_service_check_last_5 = trim($passive_service_check_last_5);
			$performance_obj->passive_service_check_last_15 = trim($passive_service_check_last_15);

			//external commands
			list($info1, $external_commands_last) = explode(':', $parse_array[61], 2);
			list($external_commands_last_1, $external_commands_last_5, $external_commands_last_15) = explode('/', $external_commands_last, 3);

			$performance_obj->external_commands_last_1 = trim($external_commands_last_1);
			$performance_obj->external_commands_last_5 = trim($external_commands_last_5);
			$performance_obj->external_commands_last_15 = trim($external_commands_last_15);

			foreach($performance_obj as $data)
			{
				$data = json_encode($data);
			}

			return $performance_obj;
		}
	}



}

?>