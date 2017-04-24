<?php defined('BASEPATH') || exit('No direct script access allowed');

class Step extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('step_model');
		$this->lang->load('step');
	}

	public function detail($step_key)
	{
		// $keys = explode($step_key);
		// if (empty($keys) || count($keys) < 3) {
		// 	redirect('/dashboard');
		// }

		// test
		$keys = ['GGS', 'ACT-1'];

		$project_key = $keys[0];
		$action_key = $keys[0] . '-' . $keys[1];

		$this->load->model('projects/project_model');
		$project_id = $this->project_model->get_project_id($project_key, $this->current_user);

		$step = $this->step_model->select('steps.*, CONCAT(u.first_name, u.last_name) as owner_name')
								->join('users u', 'u.user_id = steps.owner_id', 'left')
								->find_by('step_key', $step_key);

		$this->load->model('task/task_model');
		$tasks = $this->task_model->select('tasks.*, CONCAT(u.first_name, u.last_name) as owner_name')
								->join('users u', 'u.user_id = tasks.owner_id', 'left')
								->where('step_id', $step->step_id)->find_all();

		Assets::add_module_css('step', 'step.css');
		Assets::add_module_js('step', 'step.js');
		Template::set('step', $step);
		Template::set('tasks', $tasks);
		Template::set('project_key', $project_key);
		Template::set('action_key', $action_key);
		Template::set('step_key', $step_key);
		Template::render();
	}
}