<?php defined('BASEPATH') || exit('No direct script access allowed');

class Projects extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('projects');
		$this->load->helper('mb_form_helper');
	}

	public function index()
	{
		Template::render();
	}

	public function create()
	{
		if ($data = $this->input->post()) {
			dump($data);
		}

		Template::render();
	}
}