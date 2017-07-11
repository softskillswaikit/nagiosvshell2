<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Testing extends CI_Model
{
	protected $test_data;

	//constructor
	public function __construct()
	{
		parent::__construct();

		$this->test_data = '30';
	}

	public function testing_1()
	{
		return $this->test_data;
	}
}
?>
