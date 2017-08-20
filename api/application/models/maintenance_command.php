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
			return true;
		}
		//$type = 'contacts'
		else if($type === 2)
		{
			//open the configuration file
			//place the pointer at end of file
			$conf_file = fopen(CONTACTS, 'ab') or die('File not found !');

			//write into the configuration file
			fwrite($conf_file, "\ndefine contact{");

			foreach($input_array as $key => $value)
			{
				$line = "\n\t".$key."\t".$value;
				fwrite($conf_file, $line);
			}

			fwrite($conf_file, "\n}");

			//close the configuration file
			fclose($conf_file);

			$this->system_commands->restart_nagios();
			return true;
		}
		//$type = 'contactgroups'
		else if($type === 3)
		{
			//open the configuration file
			//place the pointer at end of file
			$conf_file = fopen(CONTACTGROUPS, 'ab') or die('File not found !');

			//write into the configuration file
			fwrite($conf_file, "\ndefine contactgroup{");

			foreach($input_array as $key => $value)
			{
				$line = "\n\t".$key."\t".$value;
				fwrite($conf_file, $line);
			}

			fwrite($conf_file, "\n}");

			//close the configuration file
			fclose($conf_file);

			$this->system_commands->restart_nagios();
			return true;
		}
		//$type = 'hosts'
		else if($type === 4)
		{
			//open the configuration file
			//place the pointer at end of file
			$conf_file = fopen(HOSTS, 'ab') or die('File not found !');

			//write into the configuration file
			fwrite($conf_file, "\ndefine host{");

			foreach($input_array as $key => $value)
			{
				$line = "\n\t".$key."\t".$value;
				fwrite($conf_file, $line);
			}

			fwrite($conf_file, "\n}");

			//close the configuration file
			fclose($conf_file);

			$this->system_commands->restart_nagios();
			return true;
		}
		//$type = 'hostgroups'
		else if($type === 5)
		{
			//open the configuration file
			//place the pointer at end of file
			$conf_file = fopen(HOSTGROUPS, 'ab') or die('File not found !');

			//write into the configuration file
			fwrite($conf_file, "\ndefine hostgroup{");

			foreach($input_array as $key => $value)
			{
				$line = "\n\t".$key."\t".$value;
				fwrite($conf_file, $line);
			}

			fwrite($conf_file, "\n}");

			//close the configuration file
			fclose($conf_file);

			$this->system_commands->restart_nagios();
			return true;
		}
		//$type = 'timeperiods'
		else if($type === 6)
		{
			//open the configuration file
			//place the pointer at end of file
			$conf_file = fopen(TIMEPERIODS, 'ab') or die('File not found !');

			//write into the configuration file
			fwrite($conf_file, "\ndefine timeperiod{");

			foreach($input_array as $key => $value)
			{
				$line = "\n\t".$key."\t".$value;
				fwrite($conf_file, $line);
			}

			fwrite($conf_file, "\n}");

			//close the configuration file
			fclose($conf_file);

			$this->system_commands->restart_nagios();
			return true;
		}
		//$type = 'services'
		else if($type === 7)
		{
			//open the configuration file
			//place the pointer at end of file
			$conf_file = fopen(SERVICES, 'ab') or die('File not found !');

			//write into the configuration file
			fwrite($conf_file, "\ndefine service{");

			foreach($input_array as $key => $value)
			{
				$line = "\n\t".$key."\t".$value;
				fwrite($conf_file, $line);
			}

			fwrite($conf_file, "\n}");

			//close the configuration file
			fclose($conf_file);

			$this->system_commands->restart_nagios();
			return true;
		}
		//$type = 'servicegroups'
		else if($type === 8)
		{
			//open the configuration file
			//place the pointer at end of file
			$conf_file = fopen(SERVICEGROUPS, 'ab') or die('File not found !');

			//write into the configuration file
			fwrite($conf_file, "\ndefine servicegroup{");

			foreach($input_array as $key => $value)
			{
				$line = "\n\t".$key."\t".$value;
				fwrite($conf_file, $line);
			}

			fwrite($conf_file, "\n}");

			//close the configuration file
			fclose($conf_file);

			$this->system_commands->restart_nagios();
			return true;
		}
		//$type = 'template_commands'
		else if($type === 9)
		{
			//open the configuration file
			//place the pointer at end of file
			$conf_file = fopen(TEMPLATE_COMMANDS, 'ab') or die('File not found !');

			//write into the configuration file
			fwrite($conf_file, "\ndefine template_command{");

			foreach($input_array as $key => $value)
			{
				$line = "\n\t".$key."\t".$value;
				fwrite($conf_file, $line);
			}

			fwrite($conf_file, "\n}");

			//close the configuration file
			fclose($conf_file);

			$this->system_commands->restart_nagios();
			return true;
		}
		//$type = 'template_contacts'
		else if($type === 10)
		{
			//open the configuration file
			//place the pointer at end of file
			$conf_file = fopen(TEMPLATE_CONTACTS, 'ab') or die('File not found !');

			//write into the configuration file
			fwrite($conf_file, "\ndefine template_contact{");

			foreach($input_array as $key => $value)
			{
				$line = "\n\t".$key."\t".$value;
				fwrite($conf_file, $line);
			}

			fwrite($conf_file, "\n}");

			//close the configuration file
			fclose($conf_file);

			$this->system_commands->restart_nagios();
			return true;
		}
		//$type = 'template_contactgroups'
		else if($type === 11)
		{
			//open the configuration file
			//place the pointer at end of file
			$conf_file = fopen(TEMPLATE_CONTATCGROUPS, 'ab') or die('File not found !');

			//write into the configuration file
			fwrite($conf_file, "\ndefine template_contactgroup{");

			foreach($input_array as $key => $value)
			{
				$line = "\n\t".$key."\t".$value;
				fwrite($conf_file, $line);
			}

			fwrite($conf_file, "\n}");

			//close the configuration file
			fclose($conf_file);

			$this->system_commands->restart_nagios();
			return true;
		}
		//$type = 'template_hosts'
		else if($type === 12)
		{
			//open the configuration file
			//place the pointer at end of file
			$conf_file = fopen(TEMPLATE_HOSTS, 'ab') or die('File not found !');

			//write into the configuration file
			fwrite($conf_file, "\ndefine template_host{");

			foreach($input_array as $key => $value)
			{
				$line = "\n\t".$key."\t".$value;
				fwrite($conf_file, $line);
			}

			fwrite($conf_file, "\n}");

			//close the configuration file
			fclose($conf_file);

			$this->system_commands->restart_nagios();
			return true;
		}
		//$type = 'template_hostgroups'
		else if($type === 13)
		{
			//open the configuration file
			//place the pointer at end of file
			$conf_file = fopen(TEMPLATE_HOSTGROUPS, 'ab') or die('File not found !');

			//write into the configuration file
			fwrite($conf_file, "\ndefine template_hostgroup{");

			foreach($input_array as $key => $value)
			{
				$line = "\n\t".$key."\t".$value;
				fwrite($conf_file, $line);
			}

			fwrite($conf_file, "\n}");

			//close the configuration file
			fclose($conf_file);

			$this->system_commands->restart_nagios();
			return true;
		}
		//$type = 'template_timeperiods'
		else if($type === 14)
		{
			//open the configuration file
			//place the pointer at end of file
			$conf_file = fopen(TEMPLATE_TIMEPERIODS, 'ab') or die('File not found !');

			//write into the configuration file
			fwrite($conf_file, "\ndefine template_timeperiod{");

			foreach($input_array as $key => $value)
			{
				$line = "\n\t".$key."\t".$value;
				fwrite($conf_file, $line);
			}

			fwrite($conf_file, "\n}");

			//close the configuration file
			fclose($conf_file);

			$this->system_commands->restart_nagios();
			return true;
		}
		//$type = 'template_services'
		else if($type === 15)
		{
			//open the configuration file
			//place the pointer at end of file
			$conf_file = fopen(TEMPLATE_SERVICES, 'ab') or die('File not found !');

			//write into the configuration file
			fwrite($conf_file, "\ndefine template_service{");

			foreach($input_array as $key => $value)
			{
				$line = "\n\t".$key."\t".$value;
				fwrite($conf_file, $line);
			}

			fwrite($conf_file, "\n}");

			//close the configuration file
			fclose($conf_file);

			$this->system_commands->restart_nagios();
			return true;
		}
		//$type = 'template_servicegroups'
		else if($type === 16)
		{
			//open the configuration file
			//place the pointer at end of file
			$conf_file = fopen(TEMPLATE_SERVICEGROUPS, 'ab') or die('File not found !');

			//write into the configuration file
			fwrite($conf_file, "\ndefine template_servicegroup{");

			foreach($input_array as $key => $value)
			{
				$line = "\n\t".$key."\t".$value;
				fwrite($conf_file, $line);
			}

			fwrite($conf_file, "\n}");

			//close the configuration file
			fclose($conf_file);

			$this->system_commands->restart_nagios();
			return true;
		}
	}




}




?>