<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class System_commands extends CI_Model
{
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
		}
	}

	//command id = 58
	public function stop_obsess_over_host_check()
	{
		$commands = 'STOP_OBSESSING_OVER_HOST_CHECKS';

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

	//command id = 59
	public function start_obsess_over_host($input_host_name)
	{
		$commands = 'START_OBSESSING_OVER_HOST;'.$input_host_name;

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

	//command id = 60
	public function stop_obsess_over_host($input_host_name)
	{
		$commands = 'STOP_OBSESSING_OVER_HOST;'.$input_host_name;

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

	//command id = 61
	public function start_obsess_oveer_svc($input_host_name, $input_service_description)
	{
		$commands = 'START_OBSESSING_OVER_SVC;'.$input_host_name.';'.$input_service_description;

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

	//command id = 62
	public function stop_obsess_over_svc($input_host_name, $input_service_description)
	{
		$commands = 'STOP_OBSESSING_OVER_SVC;'.$input_host_name.';'.$input_service_description;

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

	//command id = 65
	public function enable_performance_data()
	{
		$commands = 'ENABLE_PERFORMANCE_DATA';

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

	//command id = 66
	public function disable_performance_data()
	{
		$commands = 'DISABLE_PERFORMANCE_DATA';

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

	//command id = 67
	public function start_host_check()
	{
		$commands = 'START_EXECUTING_HOST_CHECKS';

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

	//command id = 68
	public function stop_host_check()
	{
		$commands = 'STOP_EXECUTING_HOST_CHECKS';

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

	//command id = 69
	public function start_passive_host_check()
	{
		$commands = 'START_ACCEPTING_PASSIVE_HOST_CHECKS';

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

	//command id = 70
	public function stop_passive_host_check()
	{
		$commands = 'STOP_ACCEPTING_PASSIVE_HOST_CHECKS';

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

	//command id = 71
	public function enable_passive_host_check($input_host_name)
	{
		$commands = 'ENABLE_PASSIVE_HOST_CHECKS;'.$input_host_name;

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

	//command id = 72
	public function disable_passive_host_check($input_host_name)
	{
		$commands = 'DISABLE_PASSIVE_HOST_CHECKS;'.$input_host_name;

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

	//command id = 73
	public function enable_flap_detection()
	{
		$commands = 'ENABLE_FLAP_DETECTION';

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

	//command id = 74
	public function disable_flap_detection()
	{
		$commands = 'DISABLE_FLAP_DETECTION';

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

	//command id = 75
	public function enable_host_flap_detection($input_host_name)
	{
		$commands = 'ENABLE_HOST_FLAP_DETECTION;'.$input_host_name;

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

	//command id = 76
	public function enable_svc_flap_detection($input_host_name, $input_service_description)
	{
		$commands = 'ENABLE_SVC_FLAP_DETECTION;'.$input_host_name.';'.$input_service_description;

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

	//command id = 77
	public function disable_host_flap_detection($input_host_name)
	{
		$commands = 'DISABLE_HOST_FLAP_DETECTION;'.$input_host_name;

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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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
			return "The command failed to run !";
		}
		else
		{
			return trim($this->return_value);
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




}

?>