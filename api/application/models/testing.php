<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
class Testing extends CI_Model
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
	public function test_add_host_comment($input_host_name, $input_persistent, $input_author, $input_comments)
	{
		$host_name = $input_host_name;
		$persistent = $input_persistent;
		$author = $input_author;
		$comments = $input_comments;
		$this->return_value = shell_exec("sh /usr/local/vshell2/api/application/scripts/add_host_comment.sh ".escapeshellarg($host_name)." ".escapeshellarg($persistent)." ".escapeshellarg($author)." ".escapeshellarg($comments));
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
	public function test_performance_info()
	{
		$this->output = shell_exec("sh /usr/local/vshell2/api/application/scripts/performance_info.sh");
		return $this->output;
	}
}
?>