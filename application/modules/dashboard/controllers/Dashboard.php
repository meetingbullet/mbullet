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
		$this->load->model('project/project_member_model');
		$this->load->model('step/step_model');
		$this->load->model('step/step_member_model');
		$this->load->model('homework/homework_model');
		$this->load->model('homework/homework_member_model');
		$this->load->helper('date');

		Assets::add_module_js('dashboard', 'dashboard.js');
		Assets::add_module_css('dashboard', 'dashboard.css');
		Assets::add_module_css('step', 'step.css');
	}

	// public function index()
	// {
	// 	$projects = $this->project_model->select('projects.*, u.first_name, u.last_name, u.email, u.avatar')
	// 									->join('users u', 'u.user_id = projects.owner_id')
	// 									->join('project_members pm', 'projects.project_id = pm.project_id AND pm.user_id = ' . $this->current_user->user_id)
	// 									->where('projects.status !=', 'archive')
	// 									->where('organization_id', $this->current_user->current_organization_id)
	// 									->find_all();

	// 	$my_steps = $this->step_model->select('steps.*, u.first_name, u.last_name, u.email, u.avatar')
	// 								->join('users u', 'u.user_id = steps.owner_id')
	// 								->join('actions a', 'a.action_id = steps.action_id')
	// 								->join('projects p', 'p.project_id = a.project_id')
	// 								->where('(steps.status = "ready" OR steps.status = "inprogress")', null, false)
	// 								->where('steps.owner_id', $this->current_user->user_id)
	// 								->where('organization_id', $this->current_user->current_organization_id)
	// 								->find_all();
	// 	$my_steps = $my_steps && count($my_steps) > 0 ? $my_steps : [];

	// 	$member_steps = $this->step_member_model->select('s.*, u.first_name, u.last_name, u.email, u.avatar')
	// 								->join('steps s', 's.step_id = step_members.step_id AND s.owner_id != ' . $this->current_user->user_id)
	// 								->join('users u', 'u.user_id = s.owner_id')
	// 								->join('actions a', 'a.action_id = s.action_id')
	// 								->join('projects p', 'p.project_id = a.project_id')
	// 								->where('(s.status = "ready" OR s.status = "inprogress")', null, false)
	// 								->where('step_members.user_id', $this->current_user->user_id)
	// 								->find_all();

	// 	$member_steps = $member_steps && count($member_steps) > 0 ? $member_steps : [];

	// 	$user = $this->user_model->select('users.user_id, avatar, email, first_name, CONCAT(first_name, " ", last_name) AS full_name, ROUND(SUM(smr.rate) / COUNT(smr.rate)) AS avarage_rate')
	// 								->join('step_member_rates smr', 'smr.attendee_id = users.user_id')
	// 								->find($this->current_user->user_id);

	// 	$user->meeting_count = $this->step_model->select('COUNT(*) AS meeting_count')
	// 								->join('step_members sm', 'sm.step_id = steps.step_id')
	// 								->where('owner_id', $this->current_user->user_id)
	// 								->or_where('sm.user_id', $this->current_user->user_id)
	// 								->find_all();
	// 	$user->meeting_count = $user->meeting_count && count($user->meeting_count) ? $user->meeting_count[0]->meeting_count : 0;

	// 	$user->total_point_used = $this->mb_project->total_point_used('user', $this->current_user->user_id);

	// 	Assets::add_js($this->load->view('index_js', ['now' => gmdate('Y-m-d H:i:s')], true), 'inline');
	// 	Template::set('projects', $projects && count($projects) > 0 ? $projects : []);
	// 	Template::set('my_steps', array_merge($my_steps, $member_steps));
	// 	Template::set('current_user', $this->current_user);
	// 	Template::set('now', gmdate('Y-m-d H:i:s'));
	// 	Template::set('user', $user);
	// 	Template::render();
	// }

	public function index()
	{
		$projects = $this->get_my_projects();
		$my_todo = $this->get_my_todo();

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

	public function my_projects()
	{
		if (! $this->input->is_ajax_request()) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$projects = $this->get_my_projects();

		Template::set('projects', $projects && count($projects) > 0 ? $projects : []);
		Template::set('current_user', $this->current_user);
		Template::render();
	}

	private function get_my_projects()
	{
		$projects = $this->project_model->select('projects.*, u.first_name, u.last_name, u.email, u.avatar,
										(SELECT COUNT(*) FROM ' . $this->db->dbprefix('project_members') . ' WHERE ' . $this->db->dbprefix('project_members') . '.project_id = ' . $this->db->dbprefix('projects') . '.project_id) as member_number
										')
										->join('users u', 'u.user_id = projects.owner_id')
										->join('project_members pm', 'projects.project_id = pm.project_id')
										->where('projects.status !=', 'archive')
										->where('(pm.user_id = \'' . $this->current_user->user_id . '\' OR projects.owner_id = \'' . $this->current_user->user_id . '\')')
										->where('organization_id', $this->current_user->current_organization_id)
										->group_by('projects.project_id')
										->find_all();
		if (empty($projects)) {
			$projects = [];
		}

		foreach ($projects as &$project) {
			$project->point_used = $this->mb_project->total_point_used('project', $project->project_id, $this->current_user->current_organization_id);
			$project->step_owners = [];
			$active_steps = $this->step_model->select('steps.*, u.first_name, u.last_name, u.email, u.avatar')
									->join('users u', 'u.user_id = steps.owner_id')
									->join('actions a', 'a.action_id = steps.action_id')
									->join('projects p', 'p.project_id = a.project_id')
									->join('step_members sm', 'sm.step_id = steps.step_id', 'LEFT')
									->where('(steps.status = "ready" OR steps.status = "inprogress")', null, false)
									->where('organization_id', $this->current_user->current_organization_id)
									->where('p.project_id', $project->project_id)
									->group_by('steps.step_id')
									->find_all();
			if (empty($active_steps)) {
				$active_steps = [];
			}

			$project->no_of_unfinished_step = count($active_steps);
			$project->no_of_step = $this->step_model->join('actions a', 'a.action_id = steps.action_id')
													->join('projects p', 'p.project_id = a.project_id')
													->where('organization_id', $this->current_user->current_organization_id)
													->where('p.project_id', $project->project_id)->count_all();

			$rate = $this->step_model->select('SUM(sm.rate) as total_rate, (COUNT(*) * 5) as max_rate')
									->join('actions a', 'a.action_id = steps.action_id')
									->join('projects p', 'p.project_id = a.project_id')
									->join('step_members sm', 'sm.step_id = steps.step_id', 'LEFT')
									->where('organization_id', $this->current_user->current_organization_id)
									->find_by('p.project_id', $project->project_id);
			$project->total_rate = empty($rate->total_rate) ? 0 : $rate->total_rate;
			$project->max_rate = empty($rate->max_rate) ? 0 : $rate->max_rate;

			foreach ($active_steps as $step) {
				if (isset($project->step_owners[$step->owner_id])) {
					$project->step_owners[$step->owner_id]['items'][] = $step;
				} else {
					$project->step_owners[$step->owner_id] = [
						'info' => [
							'first_name' => $step->first_name,
							'last_name' => $step->last_name,
							'email' => $step->email,
							'avatar' => $step->avatar,
							'user_id' => $step->owner_id
						],
						'items' => [
							$step
						]
					];
				}
			}
		}

		return $projects;
	}

	private function get_my_todo()
	{
		$homeworks = $this->homework_model->select('homework.*')
										->join('steps s', 's.step_id = homework.step_id')
										->join('actions a', 'a.action_id = s.action_id')
										->join('projects p', 'p.project_id = a.project_id')
										->join('homework_members hm', 'hm.homework_id = homework.homework_id', 'LEFT')
										->where('homework.status !=', 'done')
										->where('organization_id', $this->current_user->current_organization_id)
										->where('(homework.created_by = \'' . $this->current_user->user_id . '\' OR hm.user_id = \'' . $this->current_user->user_id . '\' )')
										->group_by('homework.homework_id')
										->find_all();
		if (empty($homeworks)) {
			$homeworks = [];
		}
		$evaluates = $this->step_model->select('steps.*, u.first_name, u.last_name, u.email, u.avatar')
								->join('users u', 'u.user_id = steps.owner_id')
								->join('actions a', 'a.action_id = steps.action_id')
								->join('projects p', 'p.project_id = a.project_id')
								->join('step_members sm', 'sm.step_id = steps.step_id', 'LEFT')
								->where('(sm.user_id = \'' . $this->current_user->user_id . '\' OR steps.owner_id = \'' . $this->current_user->user_id . '\')')
								->where('organization_id', $this->current_user->current_organization_id)
								->where('steps.manage_state = \'evaluate\'')
								->group_by('steps.step_id')
								->find_all();
		if (empty($evaluates)) {
			$evaluates = [];
		}
		$decides = $this->step_model->select('steps.*, u.first_name, u.last_name, u.email, u.avatar')
								->join('users u', 'u.user_id = steps.owner_id')
								->join('actions a', 'a.action_id = steps.action_id')
								->join('projects p', 'p.project_id = a.project_id')
								->join('step_members sm', 'sm.step_id = steps.step_id', 'LEFT')
								->where('steps.owner_id', $this->current_user->user_id)
								->where('organization_id', $this->current_user->current_organization_id)
								->where('steps.manage_state = \'decide\'')
								->group_by('steps.step_id')
								->find_all();
		if (empty($decides)) {
			$decides = [];
		}
	}
}