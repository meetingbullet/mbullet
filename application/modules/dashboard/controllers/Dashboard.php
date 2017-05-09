<?php defined('BASEPATH') || exit('No direct script access allowed');

class Dashboard extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('step/step');
		$this->load->model('project/project_model');
		$this->load->model('step/step_model');
		$this->load->model('step/step_member_model');

		Assets::add_module_js('dashboard', 'dashboard.js');
		Assets::add_module_css('step', 'step.css');
	}

	public function index()
	{
		$projects = $this->project_model->select('projects.*, u.first_name, u.last_name')
										->join('users u', 'u.user_id = projects.owner_id')
										->join('project_members pm', 'projects.project_id = pm.project_id AND pm.user_id = ' . $this->current_user->user_id)
										->find_all();

		Template::set('projects', $projects && count($projects) > 0 ? $projects : []);


		$my_steps = $this->step_model->select('steps.*,  CONCAT(u.first_name, " ", u.last_name) as owner_name')
									->join('users u', 'u.user_id = steps.owner_id')
									->where('(status = "ready" OR status = "inprogress")', null, false)
									->where('owner_id', $this->current_user->user_id)
									->find_all();
		$my_steps = $my_steps && count($my_steps) > 0 ? $my_steps : [];

		$member_steps = $this->step_member_model->select('s.*,  CONCAT(u.first_name, " ", u.last_name) as owner_name')
									->join('steps s', 's.step_id = step_members.step_id')
									->join('users u', 'u.user_id = step_members.user_id')
									->where('(status = "ready" OR status = "inprogress")', null, false)
									->where('step_members.user_id', $this->current_user->user_id)
									->find_all();

		$member_steps = $member_steps && count($member_steps) > 0 ? $member_steps : [];

		Template::set('my_steps', array_merge($my_steps, $member_steps));

		Assets::add_js($this->load->view('index_js', ['now' => gmdate('Y-m-d H:i:s')], true), 'inline');
		Template::set('current_user', $this->current_user);
		Template::set('now', gmdate('Y-m-d H:i:s'));
		Template::render();
	}
}