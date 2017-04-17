<?php defined('BASEPATH') || exit('No direct script access allowed');

class Dashboard extends Authenticated_Controller
{
	public function __contruct()
	{
		parent::__contruct();
	}

	public function index()
	{
		Template::render();
	}
}