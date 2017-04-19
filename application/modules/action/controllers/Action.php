<?php defined('BASEPATH') || exit('No direct script access allowed');

class Action extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->model('action_model');
	}

	public function create()
	{
		Assets::add_module_css('action.css');
		Template::render();
	}
}