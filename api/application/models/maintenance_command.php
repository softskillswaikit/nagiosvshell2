<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Maintenance_command extends CI_Model
{
	//constructor
	public function __construct()
	{
		parent::__construct();

		date_default_timezone_set('UTC');

		define('COMMANDS', '/usr/local/nagios/etc/objects/commands.cfg');
		define('CONTACTS', '/usr/local/nagios/etc/objects/contacts.cfg');
		define('CONTACTGROUPS', '/usr/local/nagios/etc/objects/contactgroups.cfg');
		define('HOSTS', '/usr/local/nagios/etc/objects/hosts.cfg');
		define('HOSTGROUPS', '/usr/local/nagios/etc/objects/hostgroups.cfg');
		define('TIMEPERIODS', '/usr/local/nagios/etc/objects/timeperiods.cfg');
		define('SERVICES', '/usr/local/nagios/etc/objects/services.cfg');
		define('SERVICEGROUPS', '/usr/local/nagios/etc/objects/servicegroups.cfg');
		define('TEMPLATE_COMMANDS', '/usr/local/nagios/etc/objects/template_commands.cfg');
		define('TEMPLATE_CONTACTS', '/usr/local/nagios/etc/objects/template_contacts.cfg');
		define('TEMPLATE_CONTATCGROUPS', '/usr/local/nagios/etc/objects/template_contactgroups.cfg');
		define('TEMPLATE_HOSTS', '/usr/local/nagios/etc/objects/template_hosts.cfg');
		define('TEMPLATE_HOSTGROUPS', '/usr/local/nagios/etc/objects/template_hostgroups.cfg');
		define('TEMPLATE_TIMEPERIODS', '/usr/local/nagios/etc/objects/template_timeperiods.cfg');
		define('TEMPLATE_SERVICES', '/usr/local/nagios/etc/objects/template_services.cfg');
		define('TEMPLATE_SERVICEGROUPS', '/usr/local/nagios/etc/objects/template_servicegroups.cfg');
	}
	
	public function add($type, $input_array)
	{
		//$test_array = array(
		//	'command_name' => 'test command 1', 
		//	'command_line' => 'test command 2'
		//);

		//$type = 'commands'
		if($type === 1)
		{
			//open the configuration file
			//place the pointer at end of file
			$conf_file = fopen(COMMANDS, 'ab') or die('File not found !');

			//write into the configuration file
			fwrite($conf_file, "\ndefine command{");

			foreach($input_array as $key => $value)
			{
				$line = "\n\t".$key."\t".$value;
				fwrite($conf_file, $line);
			}

			fwrite($conf_file, "\n}");

			//close the configuration file
			fclose($conf_file);

			$this->system_commands->restart_nagios();
			return 'done';
		}
		//$type = 'contacts'
		else if($type === 2)
		{

		}
		//$type = 'contactgroups'
		else if($type === 3)
		{
			
		}
		//$type = 'hosts'
		else if($type === 4)
		{
			
		}
		//$type = 'hostgroups'
		else if($type === 5)
		{
			
		}
		//$type = 'timeperiods'
		else if($type === 6)
		{
			
		}
		//$type = 'services'
		else if($type === 7)
		{
			
		}
		//$type = 'servicegroups'
		else if($type === 8)
		{
			
		}
		//$type = 'template_commands'
		else if($type === 9)
		{
			
		}
		//$type = 'template_contacts'
		else if($type === 10)
		{
			
		}
		//$type = 'template_contactgroups'
		else if($type === 11)
		{
			
		}
		//$type = 'template_hosts'
		else if($type === 12)
		{
			
		}
		//$type = 'template_hostgroups'
		else if($type === 13)
		{
			
		}
		//$type = 'template_timeperiods'
		else if($type === 14)
		{
			
		}
		//$type = 'template_services'
		else if($type === 15)
		{
			
		}
		//$type = 'template_servicegroups'
		else if($type === 16)
		{
			
		}
	}




}




?>