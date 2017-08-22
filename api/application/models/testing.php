<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Testing extends CI_Model
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

	//******** add $this->system_commands->restart_nagios();
	
	//Function used to add new object
	public function add($type, $input_items, $is_var)
	{
		//open different object file based on request
		switch($type)
		{
			//$type = 'commands'
			case 1:
				$conf_file = fopen(COMMANDS, 'rw') or die('File not found !');
				break;
			//$type = 'contacts'
			case 2:
				$conf_file = fopen(CONTACTS, 'rw') or die('File not found !');
				break;
			//$type = 'contactgroups'
			case 3:
				$conf_file = fopen(CONTACTGROUPS, 'rw') or die('File not found !');
				break;
			//$type = 'hosts'
			case 4:
				$conf_file = fopen(HOSTS, 'rw') or die('File not found !');
				break;
			//$type = 'hostgroups'
			case 5:
				$conf_file = fopen(HOSTGROUPS, 'rw') or die('File not found !');
				break;
			//$type = 'timeperiods'
			case 6:
				$conf_file = fopen(TIMEPERIODS, 'rw') or die('File not found !');
				break;
			//$type = 'services'
			case 7:
				$conf_file = fopen(SERVICES, 'rw') or die('File not found !');
				break;
			//$type = 'servicegroups'
			case 8:
				$conf_file = fopen(SERVICEGROUPS, 'rw') or die('File not found !');
				break;
			//$type = 'template_commands'
			case 9:
				$conf_file = fopen(TEMPLATE_COMMANDS, 'rw') or die('File not found !');
				break;
			//$type = 'template_contacts'
			case 10:
				$conf_file = fopen(TEMPLATE_CONTACTS, 'rw') or die('File not found !');
				break;
			//$type = 'template_contactgroups'
			case 11:
				$conf_file = fopen(TEMPLATE_CONTATCGROUPS, 'rw') or die('File not found !');
				break;
			//$type = 'template_hosts'
			case 12:
				$conf_file = fopen(TEMPLATE_HOSTS, 'rw') or die('File not found !');
				break;
			//$type = 'template_hostgroups'
			case 13:
				$conf_file = fopen(TEMPLATE_HOSTGROUPS, 'rw') or die('File not found !');
				break;
			//$type = 'template_timeperiods'
			case 14:
				$conf_file = fopen(TEMPLATE_TIMEPERIODS, 'rw') or die('File not found !');
				break;
			//$type = 'template_services'
			case 15:
				$conf_file = fopen(TEMPLATE_SERVICES, 'rw') or die('File not found !');
				break;
			//$type = 'template_servicegroups'
			case 16:
				$conf_file = fopen(TEMPLATE_SERVICEGROUPS, 'rw') or die('File not found !');
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

				if (strpos($line,'}') !== FALSE)
				{
					$obj_array[] = $obj;

					unset($obj);

					$obj->end_definition = $line."\n\n";
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
	                $obj_array[] = $obj;

	                unset($obj);
	            }
			}
		}

		$write_array = array();
		$is_object = false;

		foreach($input_items as $items)
		{
			list($input_obj, $input_key, $input_item) = explode(',', $items, 3);

			foreach($obj_array as $object)
			{
				foreach($object as $key => $value)
				{
					//start of object definition
					if (strcmp($key, 'definition') == 0)
					{
						$write_array[] = $value."\n";
					}

					//compare object 
					if(strcmp($input_obj, $value) == 0)
					{
						$is_object = true;
					}

					if($is_var)
					{
						//compare object definition type
						if(strcmp($key, $input_key) == 0)
						{
							if($is_object)
							{	
								//end of object definition
								if (strpos($value, '}') !== false)
								{
									$write_array[] = $value;
									$is_object = false;
								}
								//object definition
								else
								{
									$write_array[] = "\t".$key."\t".$value."\n";
								}
							}
							else
							{
								//end of object definition
								if (strpos($value, '}') !== false)
								{
									$write_array[] = $value;
								}
								//object definition
								else
								{
									$write_array[] = "\t".$key."\t".$value."\n";
								}
							}
						}
					}
					else
					{
						
					}
				}
			}
		}

		foreach($write_array as $write)
		{
			//write into the configuration file
			fwrite($conf_file, $write);
		}
		
		fclose($conf_file);

		return true;
	}

	//Function used to delete variable of existing object
	public function delete($type, $input_items, $is_var)
	{
		//open different object file based on request
		switch($type)
		{
			//$type = 'commands'
			case 1:
				$conf_file = fopen(COMMANDS, 'rw') or die('File not found !');
				break;
			//$type = 'contacts'
			case 2:
				$conf_file = fopen(CONTACTS, 'rw') or die('File not found !');
				break;
			//$type = 'contactgroups'
			case 3:
				$conf_file = fopen(CONTACTGROUPS, 'rw') or die('File not found !');
				break;
			//$type = 'hosts'
			case 4:
				$conf_file = fopen(HOSTS, 'rw') or die('File not found !');
				break;
			//$type = 'hostgroups'
			case 5:
				$conf_file = fopen(HOSTGROUPS, 'rw') or die('File not found !');
				break;
			//$type = 'timeperiods'
			case 6:
				$conf_file = fopen(TIMEPERIODS, 'rw') or die('File not found !');
				break;
			//$type = 'services'
			case 7:
				$conf_file = fopen(SERVICES, 'rw') or die('File not found !');
				break;
			//$type = 'servicegroups'
			case 8:
				$conf_file = fopen(SERVICEGROUPS, 'rw') or die('File not found !');
				break;
			//$type = 'template_commands'
			case 9:
				$conf_file = fopen(TEMPLATE_COMMANDS, 'rw') or die('File not found !');
				break;
			//$type = 'template_contacts'
			case 10:
				$conf_file = fopen(TEMPLATE_CONTACTS, 'rw') or die('File not found !');
				break;
			//$type = 'template_contactgroups'
			case 11:
				$conf_file = fopen(TEMPLATE_CONTATCGROUPS, 'rw') or die('File not found !');
				break;
			//$type = 'template_hosts'
			case 12:
				$conf_file = fopen(TEMPLATE_HOSTS, 'rw') or die('File not found !');
				break;
			//$type = 'template_hostgroups'
			case 13:
				$conf_file = fopen(TEMPLATE_HOSTGROUPS, 'rw') or die('File not found !');
				break;
			//$type = 'template_timeperiods'
			case 14:
				$conf_file = fopen(TEMPLATE_TIMEPERIODS, 'rw') or die('File not found !');
				break;
			//$type = 'template_services'
			case 15:
				$conf_file = fopen(TEMPLATE_SERVICES, 'rw') or die('File not found !');
				break;
			//$type = 'template_servicegroups'
			case 16:
				$conf_file = fopen(TEMPLATE_SERVICEGROUPS, 'rw') or die('File not found !');
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

				if (strpos($line,'}') !== FALSE)
				{
					$obj_array[] = $obj;

					unset($obj);

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
	                $obj_array[] = $obj;

	                unset($obj);
	            }
			}
		}

		$write_array = array();
		$is_object = false;
		$limit_pop = true;

		
		list($input_obj, $input_key, $input_item) = explode(',', $items, 3);

		foreach($obj_array as $object)
		{
			foreach($object as $key => $value)
			{
				//compare object 
				if(strcmp($input_obj, $value) == 0)
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
						$write_array[] = $value."\n\n";
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
							$write_array[] = $value."\n\n";
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
				$conf_file = fopen(COMMANDS, 'rw') or die('File not found !');
				break;
			//$type = 'contacts'
			case 2:
				$conf_file = fopen(CONTACTS, 'rw') or die('File not found !');
				break;
			//$type = 'contactgroups'
			case 3:
				$conf_file = fopen(CONTACTGROUPS, 'rw') or die('File not found !');
				break;
			//$type = 'hosts'
			case 4:
				$conf_file = fopen(HOSTS, 'rw') or die('File not found !');
				break;
			//$type = 'hostgroups'
			case 5:
				$conf_file = fopen(HOSTGROUPS, 'rw') or die('File not found !');
				break;
			//$type = 'timeperiods'
			case 6:
				$conf_file = fopen(TIMEPERIODS, 'rw') or die('File not found !');
				break;
			//$type = 'services'
			case 7:
				$conf_file = fopen(SERVICES, 'rw') or die('File not found !');
				break;
			//$type = 'servicegroups'
			case 8:
				$conf_file = fopen(SERVICEGROUPS, 'rw') or die('File not found !');
				break;
			//$type = 'template_commands'
			case 9:
				$conf_file = fopen(TEMPLATE_COMMANDS, 'rw') or die('File not found !');
				break;
			//$type = 'template_contacts'
			case 10:
				$conf_file = fopen(TEMPLATE_CONTACTS, 'rw') or die('File not found !');
				break;
			//$type = 'template_contactgroups'
			case 11:
				$conf_file = fopen(TEMPLATE_CONTATCGROUPS, 'rw') or die('File not found !');
				break;
			//$type = 'template_hosts'
			case 12:
				$conf_file = fopen(TEMPLATE_HOSTS, 'rw') or die('File not found !');
				break;
			//$type = 'template_hostgroups'
			case 13:
				$conf_file = fopen(TEMPLATE_HOSTGROUPS, 'rw') or die('File not found !');
				break;
			//$type = 'template_timeperiods'
			case 14:
				$conf_file = fopen(TEMPLATE_TIMEPERIODS, 'rw') or die('File not found !');
				break;
			//$type = 'template_services'
			case 15:
				$conf_file = fopen(TEMPLATE_SERVICES, 'rw') or die('File not found !');
				break;
			//$type = 'template_servicegroups'
			case 16:
				$conf_file = fopen(TEMPLATE_SERVICEGROUPS, 'rw') or die('File not found !');
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

				if (strpos($line,'}') !== FALSE)
				{
					$obj_array[] = $obj;

					unset($obj);

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
	                $obj_array[] = $obj;

	                unset($obj);
	            }
			}
		}

		$write_array = array();
		$is_object = false;

		
		list($input_obj, $input_key, $input_item) = explode(',', $input_items, 3);

		foreach($obj_array as $object)
		{
			foreach($object as $key => $value)
			{
				//compare host
				if(strcmp($input_obj, $value) == 0)
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
					$write_array[] = $value."\n\n";
				}
				else
				{
					$write_array[] = "\t".$key."\t".$value."\n";
				}
			}
		}

		foreach($write_array as $write)
		{
			//write into the configuration file
			fwrite($conf_file, $write);
		}
		
		fclose($conf_file);

		$this->system_commands->restart_nagios();

		return true;
	}




}




?>