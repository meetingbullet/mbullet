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
		$this->load->model('meeting/meeting_model');
		$this->load->model('meeting/meeting_member_model');
		$this->load->helper('date');

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
		$projects = $this->project_model->select('projects.*, u.first_name, u.last_name, u.email, u.avatar')
										->join('users u', 'u.user_id = projects.owner_id')
										->join('project_members pm', 'projects.project_id = pm.project_id AND pm.user_id = ' . $this->current_user->user_id)
										->where('projects.status !=', 'archive')
										->where('organization_id', $this->current_user->current_organization_id)
										->find_all();

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
		Template::set('my_meetings', array_merge($my_meetings, $member_meetings));
		Template::set('current_user', $this->current_user);
		Template::set('now', gmdate('Y-m-d H:i:s'));
		Template::set('user', $user);
		Template::render();
	}
}