<?php defined('BASEPATH') || exit('No direct script access allowed');

class Dashboard extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('dashboard');
		$this->lang->load('project/project');
		$this->lang->load('meeting/meeting');
		$this->load->library('mb_project');
		$this->load->model('project/project_model');
		$this->load->model('project/project_member_model');
		$this->load->model('homework/homework_model');
		$this->load->model('homework/homework_member_model');
		$this->load->model('homework/homework_attachment_model');
		$this->load->model('homework/homework_rate_model');
		$this->load->model('meeting/meeting_model');
		$this->load->model('meeting/meeting_member_model');
		$this->load->model('meeting/meeting_member_rate_model');
		$this->load->model('agenda/agenda_model');
		$this->load->model('agenda/agenda_member_model');
		$this->load->model('agenda/agenda_rate_model');
		$this->load->helper('date');
		$this->load->helper('text');

		Assets::add_module_js('dashboard', 'dashboard.js');
		Assets::add_module_css('dashboard', 'dashboard.css');
		Assets::add_module_css('meeting', 'meeting.css');
		Assets::add_module_css('homework', 'homework.css');
	}

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

		$user = $this->user_model->select('users.user_id, avatar, email, first_name, CONCAT(first_name, " ", last_name) AS full_name, ROUND(SUM(mmr.rate) / COUNT(mmr.rate)) AS avarage_rate, uto.experience_point as total_xp')
									->join('meeting_member_rates mmr', 'mmr.attendee_id = users.user_id')
									->join('user_to_organizations uto', 'users.user_id = uto.user_id AND uto.organization_id = "' . $this->current_user->current_organization_id . '"')
									->find($this->current_user->user_id);

		$user->meeting_count = $this->meeting_model->select('COUNT(*) AS meeting_count')
									->join('meeting_members mm', 'mm.meeting_id = meetings.meeting_id')
									->where('owner_id', $this->current_user->user_id)
									->or_where('mm.user_id', $this->current_user->user_id)
									->find_all();
		$user->meeting_count = $user->meeting_count && count($user->meeting_count) ? $user->meeting_count[0]->meeting_count : 0;

		$user->total_point_used = $this->mb_project->total_point_used('user', $this->current_user->user_id);

		/* Meeting Calendar show when
			♥ Current user is member or owner
			♥ Status = Ready || inprogress
			♥ scheduled_start_time is defined
		*/
		$meeting_calendar = [];

		$meeting_calendar_scheduled = $this->meeting_model
		->select('CONCAT(meeting_key, " ", name) AS title, scheduled_start_time AS start, 
		CONCAT("'. site_url('meeting/') .'", meeting_key) AS url')
		->join('meeting_members mm', 'mm.meeting_id = meetings.meeting_id AND mm.user_id = ' . $this->current_user->user_id, 'LEFT')
		->where("(owner_id = {$this->current_user->user_id} OR mm.user_id = {$this->current_user->user_id})", null, false)
		->where('status', 'ready')
		->where('scheduled_start_time IS NOT NULL', null, false)
		->group_by('meeting_key')
		->find_all();

		$meeting_calendar_scheduled || $meeting_calendar_scheduled = [];

		$meeting_calendar_started = $this->meeting_model
		->select('CONCAT(meeting_key, " ", name) AS title, actual_start_time AS start, 
		CONCAT("'. site_url('meeting/') .'", meeting_key) AS url, "#eb547c" AS backgroundColor')
		->join('meeting_members mm', 'mm.meeting_id = meetings.meeting_id AND mm.user_id = ' . $this->current_user->user_id, 'LEFT')
		->where("(owner_id = {$this->current_user->user_id} OR mm.user_id = {$this->current_user->user_id})", null, false)
		->where('status', 'inprogress')
		->group_by('meeting_key')
		->find_all();

		$meeting_calendar_started || $meeting_calendar_started = [];

		$meeting_calendar_ended = $this->meeting_model
		->select('CONCAT(meeting_key, " ", name) AS title, 
		actual_start_time AS start, actual_end_time AS end,
		CONCAT("'. site_url('meeting/preview/') .'", meeting_key) AS url, 
		"#999" AS backgroundColor')
		->join('meeting_members mm', 'mm.meeting_id = meetings.meeting_id AND mm.user_id = ' . $this->current_user->user_id, 'LEFT')
		->where("(owner_id = {$this->current_user->user_id} OR mm.user_id = {$this->current_user->user_id})", null, false)
		->where('status', 'finished')
		->or_where('status', 'resolved')
		->group_by('meeting_key')
		->find_all();

		$meeting_calendar_ended || $meeting_calendar_ended = [];

		$meeting_calendar = array_merge($meeting_calendar_scheduled, $meeting_calendar_started, $meeting_calendar_ended);

		if (IS_AJAX) {
			echo json_encode([$user, $projects, $my_todo]); exit;
		}

		Assets::add_js($this->load->view('index_js', [
			'now' => gmdate('Y-m-d H:i:s'),
			'meeting_calendar' => $meeting_calendar
		], true), 'inline');

		Template::set('projects', $projects && count($projects) > 0 ? $projects : []);
		Template::set('my_todo', $my_todo && count($my_todo) > 0 ? $my_todo : []);
		Template::set('my_meetings', array_merge($my_meetings, $member_meetings));
		Template::set('current_user', $this->current_user);
		Template::set('user', $user);
		Template::render('dashboard');
	}

	public function my_projects()
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$projects = $this->get_my_projects();

		Template::set('projects', $projects && count($projects) > 0 ? $projects : []);
		Template::set('current_user', $this->current_user);
		Template::render();
	}

	private function get_my_projects()
	{
		$projects = $this->project_model
		->select('projects.name, projects.project_id, projects.cost_code, u.email, u.avatar, u.first_name, u.last_name, 
		(SELECT COUNT(*) FROM ' . $this->db->dbprefix('project_members') . ' WHERE ' . 
		$this->db->dbprefix('project_members') . '.project_id = ' . 
		$this->db->dbprefix('projects') . '.project_id) as member_number')
		->join('users u', 'u.user_id = projects.owner_id')
		->join('project_members pm', 'projects.project_id = pm.project_id AND pm.user_id =' . $this->current_user->user_id, 'LEFT')
		->where('projects.status !=', 'archive')
		->where('(pm.user_id = \'' . $this->current_user->user_id . '\' OR projects.owner_id = \'' . $this->current_user->user_id . '\')')
		->where('organization_id', $this->current_user->current_organization_id)
		->order_by('projects.name')
		->find_all();
		if (empty($projects)) {
			$projects = [];
		}

		foreach ($projects as &$project) {
			$project->total_used = $this->mb_project->total_used('project', $project->project_id);
			$project->no_of_meeting = $this->meeting_model
			->join('actions a', 'a.action_id = meetings.action_id')
			->join('projects p', 'p.project_id = a.project_id')
			->where('organization_id', $this->current_user->current_organization_id)
			->where('p.project_id', $project->project_id)->count_all();

			$project->next_meeting = $this->meeting_model->
			select('meetings.meeting_id, meetings.name, meetings.meeting_key, scheduled_start_time, 
			meetings.status, u.email, u.avatar, u.first_name, u.last_name')
			->join('actions a', 'a.action_id = meetings.action_id')
			->join('projects p', 'p.project_id = a.project_id')
			->join('users u', 'u.user_id = meetings.owner_id')
			->join('meeting_members mm', 'mm.meeting_id = meetings.meeting_id AND mm.user_id = ' . $this->current_user->user_id, 'LEFT')
			->where('(mm.user_id = \'' . $this->current_user->user_id . '\' OR meetings.owner_id = \'' . $this->current_user->user_id . '\')')
			->where('meetings.status', 'ready')
			->where('scheduled_start_time > CURRENT_TIMESTAMP()', null, false)
			->find_by('p.project_id', $project->project_id);

			$this->meeting_model->
			select('meetings.meeting_id, meetings.name, meetings.meeting_key, scheduled_start_time, 
			meetings.status, u.email, u.avatar, u.first_name, u.last_name')
			->join('actions a', 'a.action_id = meetings.action_id')
			->join('projects p', 'p.project_id = a.project_id')
			->join('users u', 'u.user_id = meetings.owner_id')
			->join('meeting_members mm', 'mm.meeting_id = meetings.meeting_id AND mm.user_id = ' . $this->current_user->user_id, 'LEFT')
			->where('(mm.user_id = \'' . $this->current_user->user_id . '\' OR meetings.owner_id = \'' . $this->current_user->user_id . '\')')
			->where('meetings.status', 'ready')
			->where('p.project_id', $project->project_id);

			if ($project->next_meeting) {
				$this->meeting_model->where('meetings.meeting_id !=', $project->next_meeting->meeting_id);
			}

			$project->scheduled_meetings = $this->meeting_model->find_all();
			$project->scheduled_meetings || $project->scheduled_meetings = [];

			$project->completed_meetings = $this->meeting_model->
			select('meetings.meeting_id, meetings.name, meetings.meeting_key, scheduled_start_time, 
			meetings.status, u.email, u.avatar, u.first_name, u.last_name')
			->join('actions a', 'a.action_id = meetings.action_id')
			->join('projects p', 'p.project_id = a.project_id')
			->join('users u', 'u.user_id = meetings.owner_id')
			->join('meeting_members mm', 'mm.meeting_id = meetings.meeting_id AND mm.user_id = ' . $this->current_user->user_id, 'LEFT')
			->where('(mm.user_id = \'' . $this->current_user->user_id . '\' OR meetings.owner_id = \'' . $this->current_user->user_id . '\')')
			->where('meetings.status', 'finished')
			->where('p.project_id', $project->project_id)
			->find_all();

			$project->completed_meetings || $project->completed_meetings = [];
		}

		return $projects;
	}

	private function get_my_todo()
	{
		$homeworks_query = $this->homework_model
		->select('homework.homework_id, homework.name, s.meeting_key, 
		s.name AS meeting_name, s.scheduled_start_time, s.in, s.in_type')
		->join('meetings s', 's.meeting_id = homework.meeting_id')
		->join('actions a', 'a.action_id = s.action_id')
		->join('projects p', 'p.project_id = a.project_id')
		->join('homework_members hm', 'hm.homework_id = homework.homework_id AND hm.user_id = ' . $this->current_user->user_id, 'LEFT')
		->where('homework.status', 'open')
		->where('organization_id', $this->current_user->current_organization_id)
		->where('(homework.created_by = \'' . $this->current_user->user_id . '\' OR hm.user_id = \'' . $this->current_user->user_id . '\' )')
		->order_by('s.meeting_key')
		->find_all();

		$homeworks_query || $homeworks_query = [];
		$homeworks = [];

		foreach ($homeworks_query as &$item) {
			$item->attachments = $this->homework_attachment_model->where('homework_id', $item->homework_id)->find_all();
			$item->attachments = $item->attachments ? $item->attachments : [];

			isset($homeworks[$item->meeting_key]) || $homeworks[$item->meeting_key] = [];

			$homeworks[$item->meeting_key][] = $item;
		}

		$evaluate_meetings = $this->meeting_model->select('meetings.*, sm.rate, meetings.name as meeting_name, u.first_name, u.last_name, u.email, IF(' . $this->db->dbprefix('meetings') . '.owner_id = "' . $this->current_user->user_id . '", 1 , 0) AS is_owner, "evaluate" AS todo_type, "meeting" AS evaluate_mode')
												->join('actions a', 'a.action_id = meetings.action_id')
												->join('projects p', 'p.project_id = a.project_id')
												->join('users u', 'u.user_id = meetings.owner_id')
												->join('meeting_members sm', 'sm.meeting_id = meetings.meeting_id AND sm.user_id = "' . $this->current_user->user_id . '"', 'LEFT')
												->where('organization_id', $this->current_user->current_organization_id)
												->where('(sm.user_id = "' . $this->current_user->user_id . '" OR meetings.owner_id = "' . $this->current_user->user_id . '")')
												->where('meetings.manage_state', 'evaluate')
												->group_by('meetings.meeting_id')
												->find_all();
		if (empty($evaluate_meetings)) {
			$evaluate_meetings = [];
		}

		$owner_meeting_ids = [];
		$member_meeting_ids = [];

		foreach($evaluate_meetings as $key => $meeting) {
			if ($this->is_evaluated($meeting->meeting_id) || ! $this->is_evaluated($meeting->meeting_id, $meeting->owner_id)) {
				unset($evaluate_meetings[$key]);
			}

			if (! empty($meeting->rate)) {
				unset($evaluate_meetings[$key]);
			}

			if ($meeting->is_owner) {
				$owner_meeting_ids[] = $meeting->meeting_id;
			} elseif ($this->is_evaluated($meeting->meeting_id, $meeting->owner_id)) {
				$member_meeting_ids[] = $meeting->meeting_id;
			} else {
				//unset($evaluate_meetings[$key]);
			}
		}

		$evaluate_agendas = [];
		$evaluate_members = [];
		$evaluate_homeworks = [];

		if (! empty($member_meeting_ids)) {
			$evaluate_agendas = $this->agenda_model->select('agendas.*, "evaluate" AS todo_type, "agenda" AS evaluate_mode,
												(SELECT m.meeting_key FROM ' . $this->db->dbprefix('meetings') . ' m WHERE m.meeting_id = ' . $this->db->dbprefix('agendas') . '.meeting_id) AS meeting_key')
												->where_in('agendas.meeting_id', $member_meeting_ids)
												->where($this->db->dbprefix('agendas.agenda_id') . ' NOT IN (SELECT ' . $this->db->dbprefix('agenda_rates') . '.agenda_id FROM ' . $this->db->dbprefix('agenda_rates') . ' WHERE ' . $this->db->dbprefix('agenda_rates') . '.agenda_id = ' . $this->db->dbprefix('agendas') . '.agenda_id AND ' . $this->db->dbprefix('agenda_rates') . '.user_id = "' . $this->current_user->user_id . '")')
												->find_all();
			if (empty($evaluate_agendas)) {
				$evaluate_agendas = [];
			}

			$evaluate_homeworks = $this->homework_model->select('homework.*, "evaluate" AS todo_type, "homework" AS evaluate_mode,
													(SELECT m.meeting_key FROM ' . $this->db->dbprefix('meetings') . ' m WHERE m.meeting_id = ' . $this->db->dbprefix('homework') . '.meeting_id) AS meeting_key')
													->where_in('homework.meeting_id', $member_meeting_ids)
													->where($this->db->dbprefix('homework.homework_id') . ' NOT IN (SELECT ' . $this->db->dbprefix('homework_rates') . '.homework_id FROM ' . $this->db->dbprefix('homework_rates') . ' WHERE ' . $this->db->dbprefix('homework_rates') . '.homework_id = ' . $this->db->dbprefix('homework') . '.homework_id AND ' . $this->db->dbprefix('homework_rates') . '.user_id = "' . $this->current_user->user_id . '")')
													->find_all();
			if (empty($evaluate_homeworks)) {
				$evaluate_homeworks = [];
			}
		}

		if (! empty($owner_meeting_ids)) {
			$evaluate_members = $this->meeting_member_model->select('u.*, meeting_members.meeting_id, "evaluate" AS todo_type, "user" AS evaluate_mode,
														(SELECT m.meeting_key FROM ' . $this->db->dbprefix('meetings') . ' m WHERE m.meeting_id = ' . $this->db->dbprefix('meeting_members') . '.meeting_id) AS meeting_key')
														->join('users u', 'u.user_id = meeting_members.user_id', 'LEFT')
														->where_in('meeting_members.meeting_id', $owner_meeting_ids)
														->where('(' . $this->db->dbprefix('meeting_members') . '.user_id, ' . $this->db->dbprefix('meeting_members') . '.meeting_id) NOT IN (SELECT ' . $this->db->dbprefix('meeting_member_rates') . '.attendee_id, ' . $this->db->dbprefix('meeting_member_rates') . '.meeting_id FROM ' . $this->db->dbprefix('meeting_member_rates') . ' WHERE ' . $this->db->dbprefix('meeting_member_rates') . '.meeting_id IN (' . implode(',', $owner_meeting_ids) . ') AND ' . $this->db->dbprefix('meeting_member_rates') . '.user_id = "' . $this->current_user->user_id . '")')
														->find_all();
			if (empty($evaluate_members)) {
				$evaluate_members = [];
			}
		}

		$evaluates = array_merge($evaluate_meetings, $evaluate_members, $evaluate_agendas, $evaluate_homeworks);
	
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

		return [
			'homeworks_count' => count($homeworks_query),
			'homeworks' => $homeworks,
			'evaluates' => $evaluates,
			'decides' => $decides
		];
	}

	// copied from meeting controller
	private function is_evaluated($meeting_id, $user_id = null) {
		// $evaluated_members = $this->meeting_member_rate_model
		// 						->select('user_id')
		// 						->where('meeting_id', $meeting_id)
		// 						->where('user_id', $this->current_user->user_id)
		// 						->group_by('user_id')
		// 						->as_array()
		// 						->find_all();
		
		// $evaluated_ids = [];
		// $evaluated = false;

		// if (is_array($evaluated_members) && count($evaluated_members) > 0) {
		// 	$evaluated_ids = array_column($evaluated_members, 'user_id');
		// 	if (in_array($this->current_user->user_id, $evaluated_ids)) {
		// 		$evaluated = true;
		// 	}
		// }

		if (empty($user_id)) {
			$user_id = $this->current_user->user_id;
		}

		$owner_id = $this->meeting_model->get_field($meeting_id, 'owner_id');

		$evaluated = false;

		if ($owner_id == $user_id) { // if is owner
			$evaluated_members = $this->meeting_member_rate_model
								->where('meeting_id', $meeting_id)
								->where('user_id', $user_id)
								->count_all();
			$all = $this->meeting_member_model
								->where('meeting_id', $meeting_id)
								->count_all();
			if ($all == $evaluated_members && $all > 0) {
				$evaluated = true;
			}
		} else {
			$meeting_rated = $this->meeting_member_model
									->where('meeting_id', $meeting_id)
									->where('user_id', $user_id)
									->where('rate IS NOT NULL')
									->count_all() == 1;

			$all_agendas = $this->agenda_model->select('agenda_id')
								->join('meetings m', 'm.meeting_id = agendas.meeting_id')
								->where('m.meeting_id', $meeting_id)
								->as_array()
								->find_all();
			if (empty($all_agendas)) $all_agendas = [];
			$all_agenda_ids = array_column($all_agendas, 'agenda_id');

			$agendas_rated = count($all_agenda_ids) > 0 ? ($this->agenda_rate_model
																->where('user_id', $user_id)
																->where_in('agenda_id', $all_agenda_ids)
																->count_all() == count($all_agenda_ids)) : false;

			$all_homeworks = $this->homework_model->select('homework_id')
								->join('meetings m', 'm.meeting_id = homework.meeting_id')
								->where('m.meeting_id', $meeting_id)
								->as_array()
								->find_all();
			if (empty($all_homeworks)) $all_homeworks = [];
			$all_homework_ids = array_column($all_homeworks, 'homework_id');

			$homeworks_rated = count($all_homework_ids) > 0 ? ($this->homework_rate_model
																->where('user_id', $user_id)
																->where_in('homework_id', $all_homework_ids)
																->count_all() == count($all_homework_ids)) : true;

			if ($meeting_rated && $agendas_rated && $homeworks_rated) {
				$evaluated = true;
			}
		}

		return $evaluated;
	}

	public function test() {
		$meeting = $this->meeting_model->find(72);
		$meeting->members = $this->meeting_member_model->where('meeting_id', 72)->find_all();


		$owner_evaluated = false;
		$members_evaluated = false;
		$owner_id = $meeting->owner_id;
		$members = $meeting->members;
		$meeting_id = $meeting->meeting_id;
		// check owner evaluated or not
		$evaluated_members = $this->meeting_member_rate_model
							->where('meeting_id', $meeting_id)
							->where('user_id', $owner_id)
							->count_all();
		$all = $this->meeting_member_model
							->where('meeting_id', $meeting_id)
							->count_all();
		if ($all == $evaluated_members && $all > 0) {
			$owner_evaluated = true;
		}
		// check members evaluated or not
		$meeting_rated = $this->meeting_member_model
							->where('meeting_id', $meeting_id)
							->where('rate IS NOT NULL')
							->count_all() == count($members);

		$all_agendas = $this->agenda_model->select('agenda_id')
								->join('meetings m', 'm.meeting_id = agendas.meeting_id')
								->where('m.meeting_id', $meeting_id)
								->as_array()
								->find_all();
		if (empty($all_agendas)) $all_agendas = [];
		$all_agenda_ids = array_column($all_agendas, 'agenda_id');

		$agendas_rated = count($all_agenda_ids) > 0 ? ($this->agenda_rate_model
															->where_in('agenda_id', $all_agenda_ids)
															->count_all() == (count($all_agenda_ids) * count($members))) : false;

		$all_homeworks = $this->homework_model->select('homework_id')
							->join('meetings m', 'm.meeting_id = homework.meeting_id')
							->where('m.meeting_id', $meeting_id)
							->as_array()
							->find_all();
		if (empty($all_homeworks)) $all_homeworks = [];
		$all_homework_ids = array_column($all_homeworks, 'homework_id');

		$homeworks_rated = count($all_homework_ids) > 0 ? ($this->homework_member_model
																->where_in('homework_id', $all_homework_ids)
																->where('rate IS NOT NULL')
																->count_all() == (count($all_homework_ids) * count($members))) : true;

		if ($meeting_rated && $agendas_rated && $homeworks_rated) {
			$members_evaluated = true;
		}

		if ($owner_evaluated && $members_evaluated) {
			//$this->meeting_model->skip_validation(true)->update($meeting->meeting_id, ['manage_state' => 'done']);
			echo 'done';
		} else {
			echo 'undone';
		}
		die;
	}
}