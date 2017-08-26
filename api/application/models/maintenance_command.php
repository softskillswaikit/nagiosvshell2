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
		define('TEMPLATE_CONTACTGROUPS', '/usr/local/nagios/etc/objects/template_contactgroups.cfg');
		define('TEMPLATE_HOSTS', '/usr/local/nagios/etc/objects/template_hosts.cfg');
		define('TEMPLATE_HOSTGROUPS', '/usr/local/nagios/etc/objects/template_hostgroups.cfg');
		define('TEMPLATE_TIMEPERIODS', '/usr/local/nagios/etc/objects/template_timeperiods.cfg');
		define('TEMPLATE_SERVICES', '/usr/local/nagios/etc/objects/template_services.cfg');
		define('TEMPLATE_SERVICEGROUPS', '/usr/local/nagios/etc/objects/template_servicegroups.cfg');
	}
	
	//Function used to add new variable to existing object
	public function add_var($type, $input_items)
	{
		//open different object file based on request
		switch($type)
		{
			//$type = 'commands'
			case 1:
				$conf_file = fopen(COMMANDS, 'r') or die('File not found !');
				$selected = COMMANDS;
				break;
			//$type = 'contacts'
			case 2:
				$conf_file = fopen(CONTACTS, 'r') or die('File not found !');
				$selected = CONTACTS;
				break;
			//$type = 'contactgroups'
			case 3:
				$conf_file = fopen(CONTACTGROUPS, 'r') or die('File not found !');
				$selected = CONTACTGROUPS;
				break;
			//$type = 'hosts'
			case 4:
				$conf_file = fopen(HOSTS, 'r') or die('File not found !');
				$selected = HOSTS;
				break;
			//$type = 'hostgroups'
			case 5:
				$conf_file = fopen(HOSTGROUPS, 'r') or die('File not found !');
				$selected = HOSTGROUPS;
				break;
			//$type = 'timeperiods'
			case 6:
				$conf_file = fopen(TIMEPERIODS, 'r') or die('File not found !');
				$selected = TIMEPERIODS;
				break;
			//$type = 'services'
			case 7:
				$conf_file = fopen(SERVICES, 'r') or die('File not found !');
				$selected = SERVICES;
				break;
			//$type = 'servicegroups'
			case 8:
				$conf_file = fopen(SERVICEGROUPS, 'r') or die('File not found !');
				$selected = SERVICEGROUPS;
				break;
			//$type = 'template_commands'
			case 9:
				$conf_file = fopen(TEMPLATE_COMMANDS, 'r') or die('File not found !');
				$selected = TEMPLATE_COMMANDS;
				break;
			//$type = 'template_contacts'
			case 10:
				$conf_file = fopen(TEMPLATE_CONTACTS, 'r') or die('File not found !');
				$selected = TEMPLATE_CONTACTS;
				break;
			//$type = 'template_contactgroups'
			case 11:
				$conf_file = fopen(TEMPLATE_CONTACTGROUPS, 'r') or die('File not found !');
				$selected = TEMPLATE_CONTACTGROUPS;
				break;
			//$type = 'template_hosts'
			case 12:
				$conf_file = fopen(TEMPLATE_HOSTS, 'r') or die('File not found !');
				$selected = TEMPLATE_HOSTS;
				break;
			//$type = 'template_hostgroups'
			case 13:
				$conf_file = fopen(TEMPLATE_HOSTGROUPS, 'r') or die('File not found !');
				$selected = TEMPLATE_HOSTGROUPS;
				break;
			//$type = 'template_timeperiods'
			case 14:
				$conf_file = fopen(TEMPLATE_TIMEPERIODS, 'r') or die('File not found !');
				$selected = TEMPLATE_TIMEPERIODS;
				break;
			//$type = 'template_services'
			case 15:
				$conf_file = fopen(TEMPLATE_SERVICES, 'r') or die('File not found !');
				$selected = TEMPLATE_SERVICES;
				break;
			//$type = 'template_servicegroups'
			case 16:
				$conf_file = fopen(TEMPLATE_SERVICEGROUPS, 'r') or die('File not found !');
				$selected = TEMPLATE_SERVICEGROUPS;
				break;
		}

		$in_block = false;
		$matches = array();
		$obj = new StdClass();
		$obj_array = array();

		//read through the file and read object definitions
		while( !feof($conf_file) )
		{
			$line = fgets($conf_file);

			//inside a block of object definition
			if($in_block)
			{
				list($key, $value) = explode("\t", trim($line), 2);

				if (strpos($line,'}') !== false)
				{
					$obj->end_definition = $line;
					$obj_array[] = $obj;

					$in_block = false;

					unset($obj);
				}
				else
				{
					$obj->$key = $value;
				}
			}
			//outside block of object definition
			else
			{
				if (preg_match('/^\s*define\s+(\w+)\s*{\s*$/', $line, $matches)) 
				{
	                $in_block = true;

	                $obj->definition = $line;
	            }
			}
		}

		//close the configuration file
		fclose($conf_file);

		list($input_obj, $input_key, $input_item) = explode(',', $input_items, 3);

		$write_array = array();
		$is_object = false;

		foreach($obj_array as $object)
		{
			foreach($object as $key => $value)
			{
				//compare object 
				if(strpos($value, $input_obj) !== false)
				{
					$is_object = true;
				}

				//add one of the variable of the object
				if($is_object)
				{
					if(strcmp($key, 'definition') == 0)
					{
						$write_array[] = $value;
					}
					//end of object definition
					else if (strpos($value, '}') !== false)
					{
						$write_array[] = "\t".$input_key."\t".$input_item."\n";
						$write_array[] = $value."\n";
						$is_object = false;
					}
					else
					{
						$write_array[] = "\t".$key."\t".$value."\n";
					}
				}
				else if(strcmp($key, 'definition') == 0)
				{
					$write_array[] = $value;
				}
				//end of object definition
				else if (strpos($value, '}') !== false)
				{
					$write_array[] = $value."\n";
				}
				else
				{
					$write_array[] = "\t".$key."\t".$value."\n";
				}
			}
		}

		$conf_file = fopen($selected, 'wb') or die('File not found !');

		foreach($write_array as $write)
		{
			//write into the configuration file
			fwrite($conf_file, $write);
		}
		
		//close the configuration file
		fclose($conf_file);

		$this->system_commands->restart_nagios();

		return true;
	}

	//Function used to add new object
	public function add($type, $input_items)
	{
		//$type = 'commands'
		if($type === 1)
		{
			//open the configuration file
			//place the pointer at end of file
			$conf_file = fopen(COMMANDS, 'ab') or die('File not found !');

			//write into the configuration file
			fwrite($conf_file, "\ndefine command {");

			foreach($input_items as $items)
			{
				list($key, $value) = explode(',', $items, 2);

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
			fwrite($conf_file, "\ndefine contact {");

			foreach($input_items as $items)
			{
				list($key, $value) = explode(',', $items, 2);

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
			fwrite($conf_file, "\ndefine contactgroup {");

			foreach($input_items as $items)
			{
				list($key, $value) = explode(',', $items, 2);

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
			fwrite($conf_file, "\ndefine host {");

			foreach($input_items as $items)
			{
				list($key, $value) = explode(',', $items, 2);

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
			fwrite($conf_file, "\ndefine hostgroup {");

			foreach($input_items as $items)
			{
				list($key, $value) = explode(',', $items, 2);

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
			fwrite($conf_file, "\ndefine timeperiod {");

			foreach($input_items as $items)
			{
				list($key, $value) = explode(',', $items, 2);

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
			fwrite($conf_file, "\ndefine service {");

			foreach($input_items as $items)
			{
				list($key, $value) = explode(',', $items, 2);

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
			fwrite($conf_file, "\ndefine servicegroup {");

			foreach($input_items as $items)
			{
				list($key, $value) = explode(',', $items, 2);

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
			fwrite($conf_file, "\ndefine template_command {");

			foreach($input_items as $items)
			{
				list($key, $value) = explode(',', $items, 2);

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
			fwrite($conf_file, "\ndefine template_contact {");

			foreach($input_items as $items)
			{
				list($key, $value) = explode(',', $items, 2);

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
			$conf_file = fopen(TEMPLATE_CONTACTGROUPS, 'ab') or die('File not found !');

			//write into the configuration file
			fwrite($conf_file, "\ndefine template_contactgroup {");

			foreach($input_items as $items)
			{
				list($key, $value) = explode(',', $items, 2);

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
			fwrite($conf_file, "\ndefine template_host {");

			foreach($input_items as $items)
			{
				list($key, $value) = explode(',', $items, 2);

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
			fwrite($conf_file, "\ndefine template_hostgroup {");

			foreach($input_items as $items)
			{
				list($key, $value) = explode(',', $items, 2);

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
			fwrite($conf_file, "\ndefine template_timeperiod {");

			foreach($input_items as $items)
			{
				list($key, $value) = explode(',', $items, 2);

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
			fwrite($conf_file, "\ndefine template_service {");

			foreach($input_items as $items)
			{
				list($key, $value) = explode(',', $items, 2);

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
			fwrite($conf_file, "\ndefine template_servicegroup {");

			foreach($input_items as $items)
			{
				list($key, $value) = explode(',', $items, 2);

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
	
	//Function used to delete variable of existing object
	public function delete($type, $input_items, $is_var)
	{
		//open different object file based on request
		switch($type)
		{
			//$type = 'commands'
			case 1:
				$conf_file = fopen(COMMANDS, 'r') or die('File not found !');
				$selected = COMMANDS;
				break;
			//$type = 'contacts'
			case 2:
				$conf_file = fopen(CONTACTS, 'r') or die('File not found !');
				$selected = CONTACTS;
				break;
			//$type = 'contactgroups'
			case 3:
				$conf_file = fopen(CONTACTGROUPS, 'r') or die('File not found !');
				$selected = CONTACTGROUPS;
				break;
			//$type = 'hosts'
			case 4:
				$conf_file = fopen(HOSTS, 'r') or die('File not found !');
				$selected = HOSTS;
				break;
			//$type = 'hostgroups'
			case 5:
				$conf_file = fopen(HOSTGROUPS, 'r') or die('File not found !');
				$selected = HOSTGROUPS;
				break;
			//$type = 'timeperiods'
			case 6:
				$conf_file = fopen(TIMEPERIODS, 'r') or die('File not found !');
				$selected = TIMEPERIODS;
				break;
			//$type = 'services'
			case 7:
				$conf_file = fopen(SERVICES, 'r') or die('File not found !');
				$selected = SERVICES;
				break;
			//$type = 'servicegroups'
			case 8:
				$conf_file = fopen(SERVICEGROUPS, 'r') or die('File not found !');
				$selected = SERVICEGROUPS;
				break;
			//$type = 'template_commands'
			case 9:
				$conf_file = fopen(TEMPLATE_COMMANDS, 'r') or die('File not found !');
				$selected = TEMPLATE_COMMANDS;
				break;
			//$type = 'template_contacts'
			case 10:
				$conf_file = fopen(TEMPLATE_CONTACTS, 'r') or die('File not found !');
				$selected = TEMPLATE_CONTACTS;
				break;
			//$type = 'template_contactgroups'
			case 11:
				$conf_file = fopen(TEMPLATE_CONTACTGROUPS, 'r') or die('File not found !');
				$selected = TEMPLATE_CONTACTGROUPS;
				break;
			//$type = 'template_hosts'
			case 12:
				$conf_file = fopen(TEMPLATE_HOSTS, 'r') or die('File not found !');
				$selected = TEMPLATE_HOSTS;
				break;
			//$type = 'template_hostgroups'
			case 13:
				$conf_file = fopen(TEMPLATE_HOSTGROUPS, 'r') or die('File not found !');
				$selected = TEMPLATE_HOSTGROUPS;
				break;
			//$type = 'template_timeperiods'
			case 14:
				$conf_file = fopen(TEMPLATE_TIMEPERIODS, 'r') or die('File not found !');
				$selected = TEMPLATE_TIMEPERIODS;
				break;
			//$type = 'template_services'
			case 15:
				$conf_file = fopen(TEMPLATE_SERVICES, 'r') or die('File not found !');
				$selected = TEMPLATE_SERVICES;
				break;
			//$type = 'template_servicegroups'
			case 16:
				$conf_file = fopen(TEMPLATE_SERVICEGROUPS, 'r') or die('File not found !');
				$selected = TEMPLATE_SERVICEGROUPS;
				break;
		}

		$in_block = false;
		$matches = array();
		$obj = new StdClass();
		$obj_array = array();

		//read through the file and read object definitions
		while( !feof($conf_file) )
		{
			$line = fgets($conf_file);

			//inside a block of object definition
			if($in_block)
			{
				list($key, $value) = explode("\t", trim($line), 2);

				if (strpos($line,'}') !== false)
				{
					$obj->end_definition = $line;
					$obj_array[] = $obj;

					$in_block = false;

					unset($obj);
				}
				else
				{
					$obj->$key = $value;
				}
			}
			//outside block of object definition
			else
			{
				if (preg_match('/^\s*define\s+(\w+)\s*{\s*$/', $line, $matches)) 
				{
	                $in_block = true;

	                $obj->definition = $line;
	            }
			}
		}

		//close the configuration file
		fclose($conf_file);

		$write_array = array();
		$is_object = false;
		$limit_pop = true;
	
		list($input_obj, $input_key, $input_item) = explode(',', $input_items, 3);

		foreach($obj_array as $object)
		{
			foreach($object as $key => $value)
			{
				//compare object 
				if(strpos($value, $input_obj) !== false)
				{
					$is_object = true;
				}

				//delete one of the variable of the object
				if($is_var)
				{
					//compare object definition type
					if(strcmp($key, $input_key) == 0)
					{
						if($is_object)
						{
							if(strcmp($input_item, $value))
							{
								continue;
								$is_object = false;
							}		
						}
						else
						{
							$write_array[] = "\t".$key."\t".$value."\n";
						}
					}
					//start of object definition
					else if(strcmp($key, 'definition') == 0)
					{
						$write_array[] = $value."\n";
					}
					//end of object definition
					else if (strpos($value, '}') !== false)
					{
						$write_array[] = $value."\n";
					}
					else
					{
						$write_array[] = "\t".$key."\t".$value."\n";
					}
				}
				//delete whole object
				else
				{
					if($is_object)
					{
						if($limit_pop)
						{
							array_pop($write_array);
							$limit_pop = false;
						}
						else
						{
							//end of object definition
							if (strpos($value, '}') !== false)
							{
								$is_object = false;
							}

							continue;
						}
					}
					else
					{
						//start of object definition
						if (strcmp($key, 'definition') == 0)
						{
							$write_array[] = $value."\n";
						}
						//end of object definition
						else if (strpos($value, '}') !== false)
						{
							$write_array[] = $value."\n";
						}
						//object definition
						else
						{
							$write_array[] = "\t".$key."\t".$value."\n";
						}
					}
				}
			}
		}

		$conf_file = fopen($selected, 'wb') or die('File not found !');

		foreach($write_array as $write)
		{
			//write into the configuration file
			fwrite($conf_file, $write);
		}
		
		fclose($conf_file);

		$this->system_commands->restart_nagios();

		return true;
	}
	
	//Function used to edit variable of existing object
	public function edit($type, $input_items)
	{
		//open different object file based on request
		switch($type)
		{
			//$type = 'commands'
			case 1:
				$conf_file = fopen(COMMANDS, 'r') or die('File not found !');
				$selected = COMMANDS;
				break;
			//$type = 'contacts'
			case 2:
				$conf_file = fopen(CONTACTS, 'r') or die('File not found !');
				$selected = CONTACTS;
				break;
			//$type = 'contactgroups'
			case 3:
				$conf_file = fopen(CONTACTGROUPS, 'r') or die('File not found !');
				$selected = CONTACTGROUPS;
				break;
			//$type = 'hosts'
			case 4:
				$conf_file = fopen(HOSTS, 'r') or die('File not found !');
				$selected = HOSTS;
				break;
			//$type = 'hostgroups'
			case 5:
				$conf_file = fopen(HOSTGROUPS, 'r') or die('File not found !');
				$selected = HOSTGROUPS;
				break;
			//$type = 'timeperiods'
			case 6:
				$conf_file = fopen(TIMEPERIODS, 'r') or die('File not found !');
				$selected = TIMEPERIODS;
				break;
			//$type = 'services'
			case 7:
				$conf_file = fopen(SERVICES, 'r') or die('File not found !');
				$selected = SERVICES;
				break;
			//$type = 'servicegroups'
			case 8:
				$conf_file = fopen(SERVICEGROUPS, 'r') or die('File not found !');
				$selected = SERVICEGROUPS;
				break;
			//$type = 'template_commands'
			case 9:
				$conf_file = fopen(TEMPLATE_COMMANDS, 'r') or die('File not found !');
				$selected = TEMPLATE_COMMANDS;
				break;
			//$type = 'template_contacts'
			case 10:
				$conf_file = fopen(TEMPLATE_CONTACTS, 'r') or die('File not found !');
				$selected = TEMPLATE_CONTACTS;
				break;
			//$type = 'template_contactgroups'
			case 11:
				$conf_file = fopen(TEMPLATE_CONTACTGROUPS, 'r') or die('File not found !');
				$selected = TEMPLATE_CONTACTGROUPS;
				break;
			//$type = 'template_hosts'
			case 12:
				$conf_file = fopen(TEMPLATE_HOSTS, 'r') or die('File not found !');
				$selected = TEMPLATE_HOSTS;
				break;
			//$type = 'template_hostgroups'
			case 13:
				$conf_file = fopen(TEMPLATE_HOSTGROUPS, 'r') or die('File not found !');
				$selected = TEMPLATE_HOSTGROUPS;
				break;
			//$type = 'template_timeperiods'
			case 14:
				$conf_file = fopen(TEMPLATE_TIMEPERIODS, 'r') or die('File not found !');
				$selected = TEMPLATE_TIMEPERIODS;
				break;
			//$type = 'template_services'
			case 15:
				$conf_file = fopen(TEMPLATE_SERVICES, 'r') or die('File not found !');
				$selected = TEMPLATE_SERVICES;
				break;
			//$type = 'template_servicegroups'
			case 16:
				$conf_file = fopen(TEMPLATE_SERVICEGROUPS, 'r') or die('File not found !');
				$selected = TEMPLATE_SERVICEGROUPS;
				break;
		}

		$in_block = false;
		$matches = array();
		$obj = new StdClass();
		$obj_array = array();

		//read through the file and read object definitions
		while( !feof($conf_file) )
		{
			$line = fgets($conf_file);

			//inside a block of object definition
			if($in_block)
			{
				list($key, $value) = explode("\t", trim($line), 2);

				if (strpos($line,'}') !== false)
				{
					$obj->end_definition = $line;
					$obj_array[] = $obj;

					$in_block = false;

					unset($obj);
				}
				else
				{
					$obj->$key = $value;
				}
			}
			//outside block of object definition
			else
			{
				if (preg_match('/^\s*define\s+(\w+)\s*{\s*$/', $line, $matches)) 
				{
	                $in_block = true;

	                $obj->definition = $line;
	            }
			}
		}

		//close the configuration file
		fclose($conf_file);

		$write_array = array();
		$is_object = false;

		list($input_obj, $input_key, $input_item) = explode(',', $input_items, 3);

		foreach($obj_array as $object)
		{
			foreach($object as $key => $value)
			{
				//compare host
				if(strpos($value, $input_obj) !== false)
				{
					$is_object = true;
				}

				//compare object definition type
				if(strcmp($key, $input_key) == 0)
				{
					if($is_object)
					{
						$write_array[] = "\t".$key."\t".$input_item."\n";
						$is_object = false;
					}
					else
					{
						$write_array[] = "\t".$key."\t".$value."\n";
					}
				}
				//start of object definition
				else if(strcmp($key, 'definition') == 0)
				{
					$write_array[] = $value."\n";
				}
				//end of object definition
				else if (strpos($value, '}') !== false)
				{
					$write_array[] = $value."\n";
				}
				else
				{
					$write_array[] = "\t".$key."\t".$value."\n";
				}
			}
		}

		$conf_file = fopen($selected, 'wb') or die('File not found !');

		foreach($write_array as $write)
		{
			//write into the configuration file
			fwrite($conf_file, $write);
		}
		
		//close the configuration file
		fclose($conf_file);

		$this->system_commands->restart_nagios();

		return true;
	}




}




?>