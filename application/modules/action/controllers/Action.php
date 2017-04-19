<?php defined('BASEPATH') || exit('No direct script access allowed');

class Action extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->model('action_model');
		$this->lang->load('action');
	}

	public function create($project_key = null)
	{
		$this->load->model('projects/project_model');
		$this->load->helper('mb_form');

		if (empty($project_key)) {
			redirect('/dashboard');
		}

		$project_id = $this->project_model->get_project_id($project_key, $this->current_user);
		if ($project_id === false) {
			redirect('/dashboard');
		}

		Assets::add_module_css('action.css');
		Template::render();
	}
}