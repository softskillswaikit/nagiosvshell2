<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class System_commands extends CI_Model
{
	protected $return_bool;

	//constructor
	public function __construct()
	{
		parent::__construct();

		$this->return_bool = false;
	}

	//add host comment
	public function add_host_comment($hostname, $persistent, $author, $comments)
	{
		$this->return_bool = shell_exec("sh /usr/local/vshell2/api/application/scripts/add_host_comment.sh");

		return $this->return_bool;
	}
}

?>