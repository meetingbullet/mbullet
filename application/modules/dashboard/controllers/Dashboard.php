<?php defined('BASEPATH') || exit('No direct script access allowed');

class Dashboard extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('dashboard');
		$this->lang->load('step/step');
		$this->load->library('mb_project');
		$this->load->model('project/project_model');
		$this->load->model('step/step_model');
		$this->load->model('step/step_member_model');
		$this->load->helper('date');

		Assets::add_module_js('dashboard', 'dashboard.js');
		Assets::add_module_css('dashboard', 'dashboard.css');
		Assets::add_module_css('step', 'step.css');
	}

	public function index()
	{
		$projects = $this->project_model->select('projects.*, u.first_name, u.last_name, u.email, u.avatar')
										->join('users u', 'u.user_id = projects.owner_id')
										->join('project_members pm', 'projects.project_id = pm.project_id AND pm.user_id = ' . $this->current_user->user_id)
										->where('projects.status !=', 'archive')
										->where('organization_id', $this->current_user->current_organization_id)
										->find_all();

		$my_steps = $this->step_model->select('steps.*, u.first_name, u.last_name, u.email, u.avatar')
									->join('users u', 'u.user_id = steps.owner_id')
									->join('actions a', 'a.action_id = steps.action_id')
									->join('projects p', 'p.project_id = a.project_id')
									->where('(steps.status = "ready" OR steps.status = "inprogress")', null, false)
									->where('steps.owner_id', $this->current_user->user_id)
									->where('organization_id', $this->current_user->current_organization_id)
									->find_all();
		$my_steps = $my_steps && count($my_steps) > 0 ? $my_steps : [];

		$member_steps = $this->step_member_model->select('s.*, u.first_name, u.last_name, u.email, u.avatar')
									->join('steps s', 's.step_id = step_members.step_id AND s.owner_id != ' . $this->current_user->user_id)
									->join('users u', 'u.user_id = s.owner_id')
									->join('actions a', 'a.action_id = s.action_id')
									->join('projects p', 'p.project_id = a.project_id')
									->where('(s.status = "ready" OR s.status = "inprogress")', null, false)
									->where('step_members.user_id', $this->current_user->user_id)
									->find_all();

		$member_steps = $member_steps && count($member_steps) > 0 ? $member_steps : [];

		$user = $this->user_model->select('users.user_id, avatar, email, first_name, CONCAT(first_name, " ", last_name) AS full_name, ROUND(SUM(smr.rate) / COUNT(smr.rate)) AS avarage_rate')
									->join('step_member_rates smr', 'smr.attendee_id = users.user_id')
									->find($this->current_user->user_id);

		$user->meeting_count = $this->step_model->select('COUNT(*) AS meeting_count')
									->join('step_members sm', 'sm.step_id = steps.step_id')
									->where('owner_id', $this->current_user->user_id)
									->or_where('sm.user_id', $this->current_user->user_id)
									->find_all();
		$user->meeting_count = $user->meeting_count && count($user->meeting_count) ? $user->meeting_count[0]->meeting_count : 0;

		$user->total_point_used = $this->mb_project->total_point_used('user', $this->current_user->user_id);

		Assets::add_js($this->load->view('index_js', ['now' => gmdate('Y-m-d H:i:s')], true), 'inline');
		Template::set('projects', $projects && count($projects) > 0 ? $projects : []);
		Template::set('my_steps', array_merge($my_steps, $member_steps));
		Template::set('current_user', $this->current_user);
		Template::set('now', gmdate('Y-m-d H:i:s'));
		Template::set('user', $user);
		Template::render();
	}
}