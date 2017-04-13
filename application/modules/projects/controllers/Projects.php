<?php defined('BASEPATH') || exit('No direct script access allowed');

class Projects extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('projects');
		$this->load->helper('mb_form_helper');
		$this->load->model('project_model');
		$this->load->model('project_constraint_model');
		$this->load->model('project_expectation_model');
	}

	public function index()
	{
		Template::render();
	}

	public function create()
	{
		if (isset($_POST['save'])) {
			if ($this->save_project()) {
				Template::set_message(lang('pj_project_successfully_created'), 'success');
			} else {
				Template::set_message(lang('pj_failed_to_create_project'), 'danger');
			}
		}

		Template::render();
	}

	private function save_project($type = 'insert')
	{
		$data = $this->input->post();
		$project_data = $this->project_model->prep_data($data);

		if ($type == 'insert') {
			$project_data['owner'] = $project_data['created_by'] = $this->current_user->user_id;

			$project_id = $this->project_model->insert($project_data);
			$data['contraints']['project_id'] = $project_id;
			$data['expectations']['project_id'] = $project_id;

			$this->project_constraint_model->insert($data['contraints']);
			$this->project_expectation_model->insert($data['expectations']);
			return true;
		} else {

		}

		return false;
	}
}