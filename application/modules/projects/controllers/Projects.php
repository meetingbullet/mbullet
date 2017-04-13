<?php defined('BASEPATH') || exit('No direct script access allowed');

class Projects extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('projects');
		$this->load->library('form_validation');
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
				Template::redirect('projects');
			}
		}

		Template::render();
	}

	private function save_project($type = 'insert')
	{
		$data = $this->input->post();
		$project_data = $this->project_model->prep_data($data);

		$constraint_rules = $this->project_constraint_model->project_validation_rules;
		foreach ($constraint_rules as &$rule) {
			$rule['field'] = "constraints[{$rule['field']}]";
		}

		$expectation_rules = $this->project_expectation_model->project_validation_rules;
		foreach ($expectation_rules as &$rule) {
			$rule['field'] = "expectations[{$rule['field']}]";
		}

		$this->form_validation->set_rules(array_merge(
			$this->project_model->project_validation_rules,
			$constraint_rules,
			$expectation_rules
		));

		if ($this->form_validation->run() === false) {
			dump(array_merge(
			$this->project_model->project_validation_rules,
			$constraint_rules,
			$expectation_rules
		));
			return false;
		}


		if ($type == 'insert') {
			$project_data['owner'] = $project_data['created_by'] = $this->current_user->user_id;

			$project_id = $this->project_model->insert($project_data);

			if ($project_id === false) {
				return false;
			}

			$data['constraints']['project_id'] = $project_id;
			$data['expectations']['project_id'] = $project_id;

			$test = $this->project_constraint_model->insert($data['constraints']);
			$this->project_expectation_model->insert($data['expectations']);
		} else {

		}

		return true;
	}
}