<?php defined('BASEPATH') || exit('No direct script access allowed');

class Dashboard extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('dashboard');
		$this->lang->load('meeting/meeting');
		$this->load->library('mb_project');
		$this->load->model('project/project_model');
		$this->load->model('project/project_member_model');
		$this->load->model('homework/homework_model');
		$this->load->model('homework/homework_member_model');
		$this->load->model('meeting/meeting_model');
		$this->load->model('meeting/meeting_member_model');
		$this->load->model('agenda/agenda_model');
		$this->load->model('agenda/agenda_member_model');
		$this->load->helper('date');
		$this->load->helper('text');

		Assets::add_module_js('dashboard', 'dashboard.js');
		Assets::add_module_css('dashboard', 'dashboard.css');
		Assets::add_module_css('meeting', 'meeting.css');
	}

	// public function index()
	// {
	// 	$projects = $this->project_model->select('projects.*, u.first_name, u.last_name, u.email, u.avatar')
	// 									->join('users u', 'u.user_id = projects.owner_id')
	// 									->join('project_members pm', 'projects.project_id = pm.project_id AND pm.user_id = ' . $this->current_user->user_id)
	// 									->where('projects.status !=', 'archive')
	// 									->where('organization_id', $this->current_user->current_organization_id)
	// 									->find_all();

	// 	$my_meetings = $this->meeting_model->select('meetings.*, u.first_name, u.last_name, u.email, u.avatar')
	// 								->join('users u', 'u.user_id = meetings.owner_id')
	// 								->join('actions a', 'a.action_id = meetings.action_id')
	// 								->join('projects p', 'p.project_id = a.project_id')
	// 								->where('(meetings.status = "ready" OR meetings.status = "inprogress")', null, false)
	// 								->where('meetings.owner_id', $this->current_user->user_id)
	// 								->where('organization_id', $this->current_user->current_organization_id)
	// 								->find_all();
	// 	$my_meetings = $my_meetings && count($my_meetings) > 0 ? $my_meetings : [];

	// 	$member_meetings = $this->meeting_member_model->select('s.*, u.first_name, u.last_name, u.email, u.avatar')
	// 								->join('meetings s', 's.meeting_id = meeting_members.meeting_id AND s.owner_id != ' . $this->current_user->user_id)
	// 								->join('users u', 'u.user_id = s.owner_id')
	// 								->join('actions a', 'a.action_id = s.action_id')
	// 								->join('projects p', 'p.project_id = a.project_id')
	// 								->where('(s.status = "ready" OR s.status = "inprogress")', null, false)
	// 								->where('meeting_members.user_id', $this->current_user->user_id)
	// 								->find_all();

	// 	$member_meetings = $member_meetings && count($member_meetings) > 0 ? $member_meetings : [];

	// 	$user = $this->user_model->select('users.user_id, avatar, email, first_name, CONCAT(first_name, " ", last_name) AS full_name, ROUND(SUM(smr.rate) / COUNT(smr.rate)) AS avarage_rate')
	// 								->join('meeting_member_rates smr', 'smr.attendee_id = users.user_id')
	// 								->find($this->current_user->user_id);

	// 	$user->meeting_count = $this->meeting_model->select('COUNT(*) AS meeting_count')
	// 								->join('meeting_members sm', 'sm.meeting_id = meetings.meeting_id')
	// 								->where('owner_id', $this->current_user->user_id)
	// 								->or_where('sm.user_id', $this->current_user->user_id)
	// 								->find_all();
	// 	$user->meeting_count = $user->meeting_count && count($user->meeting_count) ? $user->meeting_count[0]->meeting_count : 0;

	// 	$user->total_point_used = $this->mb_project->total_point_used('user', $this->current_user->user_id);

	// 	Assets::add_js($this->load->view('index_js', ['now' => gmdate('Y-m-d H:i:s')], true), 'inline');
	// 	Template::set('projects', $projects && count($projects) > 0 ? $projects : []);
	// 	Template::set('my_meetings', array_merge($my_meetings, $member_meetings));
	// 	Template::set('current_user', $this->current_user);
	// 	Template::set('now', gmdate('Y-m-d H:i:s'));
	// 	Template::set('user', $user);
	// 	Template::render();
	// }

	public function index()
	{
		$projects = $this->get_my_projects();
		$my_todo = $this->get_my_todo();

		$my_meetings = $this->meeting_model->select('meetings.*, u.first_name, u.last_name, u.email, u.avatar')
									->join('users u', 'u.user_id = meetings.owner_id')
									->join('actions a', 'a.action_id = meetings.action_id')
									->join('projects p', 'p.project_id = a.project_id')
									->where('(meetings.status = "ready" OR meetings.status = "inprogress")', null, false)
									->where('meetings.owner_id', $this->current_user->user_id)
									->where('organization_id', $this->current_user->current_organization_id)
									->find_all();
		$my_meetings = $my_meetings && count($my_meetings) > 0 ? $my_meetings : [];

		$member_meetings = $this->meeting_member_model->select('s.*, u.first_name, u.last_name, u.email, u.avatar')
									->join('meetings s', 's.meeting_id = meeting_members.meeting_id AND s.owner_id != ' . $this->current_user->user_id)
									->join('users u', 'u.user_id = s.owner_id')
									->join('actions a', 'a.action_id = s.action_id')
									->join('projects p', 'p.project_id = a.project_id')
									->where('(s.status = "ready" OR s.status = "inprogress")', null, false)
									->where('meeting_members.user_id', $this->current_user->user_id)
									->find_all();

		$member_meetings = $member_meetings && count($member_meetings) > 0 ? $member_meetings : [];

		$user = $this->user_model->select('users.user_id, avatar, email, first_name, CONCAT(first_name, " ", last_name) AS full_name, ROUND(SUM(smr.rate) / COUNT(smr.rate)) AS avarage_rate')
									->join('meeting_member_rates smr', 'smr.attendee_id = users.user_id')
									->find($this->current_user->user_id);

		$user->meeting_count = $this->meeting_model->select('COUNT(*) AS meeting_count')
									->join('meeting_members sm', 'sm.meeting_id = meetings.meeting_id')
									->where('owner_id', $this->current_user->user_id)
									->or_where('sm.user_id', $this->current_user->user_id)
									->find_all();
		$user->meeting_count = $user->meeting_count && count($user->meeting_count) ? $user->meeting_count[0]->meeting_count : 0;

		$user->total_point_used = $this->mb_project->total_point_used('user', $this->current_user->user_id);

		// Meeting Calendar
		$meeting_calendar = $this->meeting_model->select('CONCAT(meeting_key, " ", name) AS title, scheduled_start_time AS start, CONCAT("'. site_url('meeting/') .'", meeting_key) AS url')
						->join('meeting_members sm', 'sm.meeting_id = meetings.meeting_id AND sm.user_id = ' . $this->current_user->user_id, 'LEFT')
						->where("(owner_id = {$this->current_user->user_id} OR sm.user_id = {$this->current_user->user_id})", null, false)
						->where('status', 'ready')
						->where('scheduled_start_time IS NOT NULL', null, false)
						->order_by('scheduled_start_time')
						->group_by('meeting_key')
						->find_all();

		Assets::add_js($this->load->view('index_js', [
			'now' => gmdate('Y-m-d H:i:s'),
			'meeting_calendar' => $meeting_calendar ? $meeting_calendar : []
		], true), 'inline');

		Template::set('projects', $projects && count($projects) > 0 ? $projects : []);
		Template::set('my_todo', $my_todo && count($my_todo) > 0 ? $my_todo : []);
		Template::set('my_meetings', array_merge($my_meetings, $member_meetings));
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
			$active_steps = $this->meeting_model->select('meetings.*, u.first_name, u.last_name, u.email, u.avatar')
									->join('users u', 'u.user_id = meetings.owner_id')
									->join('actions a', 'a.action_id = meetings.action_id')
									->join('projects p', 'p.project_id = a.project_id')
									->join('meeting_members sm', 'sm.meeting_id = meetings.meeting_id', 'LEFT')
									->where('(meetings.status = "ready" OR meetings.status = "inprogress")', null, false)
									->where('organization_id', $this->current_user->current_organization_id)
									->where('p.project_id', $project->project_id)
									->group_by('meetings.meeting_id')
									->find_all();
			if (empty($active_steps)) {
				$active_steps = [];
			}

			$project->no_of_unfinished_step = count($active_steps);
			$project->no_of_step = $this->meeting_model->join('actions a', 'a.action_id = meetings.action_id')
													->join('projects p', 'p.project_id = a.project_id')
													->where('organization_id', $this->current_user->current_organization_id)
													->where('p.project_id', $project->project_id)->count_all();

			$rate = $this->meeting_model->select('SUM(sm.rate) as total_rate, (COUNT(*) * 5) as max_rate')
									->join('actions a', 'a.action_id = meetings.action_id')
									->join('projects p', 'p.project_id = a.project_id')
									->join('meeting_members sm', 'sm.meeting_id = meetings.meeting_id', 'LEFT')
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

		$homeworks = $this->homework_model->select('homework.*, "homework" as todo_type, meeting_key')
										->join('meetings s', 's.meeting_id = homework.meeting_id')
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

		// $evaluate_agendas = $this->meeting_model->select('meetings.*, meetings.name as meeting_name, ag.*, ag.name as agenda_name, ag.description as agenda_description, "agenda" as evaluate_mode, "evaluate" as todo_type')
		// 						->join('actions a', 'a.action_id = meetings.action_id')
		// 						->join('projects p', 'p.project_id = a.project_id')
		// 						->join('agendas ag', 'ag.meeting_id = meetings.meeting_id')
		// 						->join('meeting_members sm', 'sm.meeting_id = meetings.meeting_id', 'LEFT')
		// 						->where('(sm.user_id = \'' . $this->current_user->user_id . '\' OR meetings.owner_id = \'' . $this->current_user->user_id . '\')')
		// 						->where('organization_id', $this->current_user->current_organization_id)
		// 						->where('meetings.manage_state = \'evaluate\'')
		// 						->group_by('ag.agenda_id')
		// 						->find_all();
		if (empty($evaluate_agendas)) {
			$evaluate_agendas = [];
		}

		$evaluate_members = $this->meeting_model->select('meetings.*, meetings.name as meeting_name, u.*, "user" as evaluate_mode, "evaluate" as todo_type')
								->join('actions a', 'a.action_id = meetings.action_id')
								->join('projects p', 'p.project_id = a.project_id')
								->join('agendas ag', 'ag.meeting_id = meetings.meeting_id', 'LEFT')
								->join('meeting_members sm', 'sm.meeting_id = meetings.meeting_id', 'LEFT')
								->join('users u', 'u.user_id = sm.user_id')
								->where('(sm.user_id = \'' . $this->current_user->user_id . '\' OR meetings.owner_id = \'' . $this->current_user->user_id . '\')')
								->where('organization_id', $this->current_user->current_organization_id)
								->where('meetings.manage_state = \'evaluate\'')
								->group_by('u.user_id')
								->find_all();
		if (empty($evaluate_members)) {
			$evaluate_members = [];
		}

		$evaluates = array_merge($evaluate_members, $evaluate_agendas);

		$decides = $this->meeting_model->select('meetings.*, meetings.name as meeting_name, ag.*, ag.name as agenda_name, ag.description as agenda_description, "decide" as todo_type')
								->join('actions a', 'a.action_id = meetings.action_id')
								->join('projects p', 'p.project_id = a.project_id')
								->join('agendas ag', 'ag.meeting_id = meetings.meeting_id', 'LEFT')
								->join('meeting_members sm', 'sm.meeting_id = meetings.meeting_id', 'LEFT')
								->where('meetings.owner_id', $this->current_user->user_id)
								->where('organization_id', $this->current_user->current_organization_id)
								->where('meetings.manage_state', 'decide')
								->where('ag.confirm_status IS NULL')
								->group_by('ag.agenda_id')
								->find_all();
		if (empty($decides)) {
			$decides = [];
		}

		return array_merge($homeworks, $evaluates, $decides);
	}
}