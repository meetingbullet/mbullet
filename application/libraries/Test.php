<?php
class test
{
	public $ci;

	public function __construct()
	{
		$this->ci &= get_instance();
	}

	public function run()
	{
		dump($this->ci);
		die(1);
	}
}