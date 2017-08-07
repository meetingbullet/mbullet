<?php defined('BASEPATH') || exit('No direct script access allowed');

class Dashboard extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('dashboard');
		$this->lang->load('project/project');
		$this->lang->load('meeting/meeting');
		$this->load->model('project/project_model');
		$this->load->model('project/project_constraint_model');
		$this->load->model('project/project_member_model');
		$this->load->model('homework/homework_model');
		$this->load->model('homework/homework_member_model');
		$this->load->model('homework/homework_attachment_model');
		$this->load->model('homework/homework_rate_model');
		$this->load->model('meeting/meeting_model');
		$this->load->model('meeting/meeting_member_model');
		$this->load->model('meeting/meeting_member_rate_model');
		$this->load->model('meeting/meeting_member_invite_model');
		$this->load->model('agenda/agenda_model');
		$this->load->model('agenda/agenda_member_model');
		$this->load->model('agenda/agenda_rate_model');
		$this->load->model('invite/user_to_organizations_model');
		$this->load->helper('date');
		$this->load->helper('text');

		Assets::add_module_js('dashboard', 'jquery.tmpl.js');
		Assets::add_module_js('dashboard', 'jquery.tools.dateinput.js');
		Assets::add_module_js('dashboard', 'jquery.tools.overlay.js');
		Assets::add_module_js('dashboard', 'jquery.recurrenceinput.js');
		Assets::add_module_js('dashboard', 'dashboard.js');
		// Assets::add_module_css('dashboard', 'jquery.tools.dateinput.css');
		// Assets::add_module_css('dashboard', 'jquery.tools.overlay.css');
		// Assets::add_module_css('dashboard', 'jquery.recurrenceinput.css');
		Assets::add_module_css('dashboard', 'dashboard.css');
		Assets::add_module_css('meeting', 'meeting.css');
		Assets::add_module_css('homework', 'homework.css');
	}

	public function index()
	{
		$my_projects = $this->get_my_projects();
		$my_project_ids = array_column(json_decode(json_encode($my_projects), true), 'project_id');
		$other_projects = $this->get_other_projects($my_project_ids);
		$my_todo = $this->get_my_todo();
		$evaluates = [];
		foreach ($my_todo['meetings'] as $meeting) {
			foreach ($my_todo['evaluates'] as $todo) {
				if ($todo->meeting_id == $meeting->meeting_id) {
					$meeting->evaluates[] = $todo;
					$evaluates[$meeting->meeting_id] = $meeting;
				}
			}
		}
		unset($my_todo['meetings']);
		$my_todo['evaluates'] = $evaluates;

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
			echo json_encode([$user, $my_projects, $my_todo]); exit;
		}

		$event_sources = [
			[
				'id' => 'mbc',
				'url' => site_url('meeting/get_events/mbc'),
				'color' => '#70c1b3',
				'textColor' => 'white',
				'className' => 'mbc-event'
			],
			[
				'id' => 'ggc',
				'url' => site_url('meeting/get_events/ggc'),
				'color' => '#999',
				'textColor' => 'white',
				'className' => 'ggc-event'
			]
		];

		// Meeting invites
		$meeting_invites = $this->meeting_member_invite_model
			->select('m.name, m.meeting_key, m.meeting_id, invite_code,
			u.first_name, u.last_name, u.email, u.avatar')
			->join('meetings m', 'm.meeting_id = meeting_member_invites.meeting_id')
			->join('actions a', 'm.action_id = a.action_id')
			->join('projects p', 'p.project_id = a.project_id')
			->join('users u', 'u.user_id = m.created_by')
			->where('meeting_member_invites.status', 'NEEDS-ACTION')
			->where('invite_email', $this->current_user->email)
			->where('p.organization_id', $this->current_user->current_organization_id)
			->find_all();

		$meeting_invites || $meeting_invites = [];

		Assets::add_js($this->load->view('calendar_js', [
			'event_sources' => $event_sources
		], true), 'inline');

		Assets::add_js($this->load->view('index_js', [
			'now' => gmdate('Y-m-d H:i:s'),
			'meeting_calendar' => $meeting_calendar,
			'current_user' => $this->current_user
		], true), 'inline');

		Template::set('my_projects', $my_projects);
		Template::set('meeting_invites', $meeting_invites);
		Template::set('other_projects', $other_projects);
		Template::set('my_todo', $my_todo && count($my_todo) > 0 ? $my_todo : []);
		Template::set('current_user', $this->current_user);
		Template::set('user', $user);
		Template::set('page_title', lang('db_dashboard'));
		Template::render('dashboard');
	}

	public function skip_setup()
	{
		if ($this->current_user->inited) {
			echo json_encode([
				'message' => lang('db_setup_skipped'),
				'message_type' => 'success'
			]);
			return;
		}

		if ( $this->user_to_organizations_model->update(
			$this->current_user->user_id, ['inited' => 1]) ) {
			echo json_encode([
				'message' => lang('db_setup_skipped'),
				'message_type' => 'success'
			]);
			return;
		}

		echo json_encode([
			'message' => lang('db_something_went_wrong'),
			'message_type' => 'danger'
		]);
	}

	public function mark_as_read($object_type, $object_id, $user_id = null)
	{
		if ( empty($object_type) || empty($object_id) ) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('db_unknown_error')
			]);
			return;
		}

		if ( !in_array($object_type, ['project', 'meeting', 'user', 'agenda', 'homework']) ) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('db_unknown_error')
			]);
			return;
		}

		$object_pk = $object_type;

		$insert_data = [
			$object_pk . '_id' => $object_id,
			'user_id' => $this->current_user->user_id
		];

		if ($object_type == 'user') {
			$object_type = 'meeting_member';
			$insert_data = [
				'meeting_id' => $object_id,
				'rating_user_id' => $user_id,
				'user_id' => $this->current_user->user_id
			];
		}

		// Prevent duplicate row by MySQL Insert Ignore
		$query = $this->db->insert_string($object_type . '_reads', $insert_data);

		$query = str_replace('INSERT', 'INSERT IGNORE', $query);

		if ($this->db->query($query)) {
			echo json_encode([
				'message_type' => 'success'
			]);
			return;
		}

		echo json_encode([
			'message_type' => 'danger',
			'message' => lang('db_unknown_error')
		]);
	}

	private function get_my_projects()
	{
		$projects = $this->project_model
		->select('projects.name, projects.project_id, projects.cost_code, u.email, u.avatar, u.first_name, u.last_name, 
		(SELECT COUNT(*) FROM ' . $this->db->dbprefix('project_members') . ' WHERE ' . 
		$this->db->dbprefix('project_members') . '.project_id = ' . 
		$this->db->dbprefix('projects') . '.project_id) as member_number,
		pr.user_id IS NOT NULL as is_read', false)
		->join('users u', 'u.user_id = projects.owner_id')
		->join('project_members pm', 'projects.project_id = pm.project_id AND pm.user_id =' . $this->current_user->user_id, 'LEFT')
		->join('project_reads pr', 'projects.project_id = pr.project_id AND pr.user_id =' . $this->current_user->user_id, 'LEFT')
		->where('projects.status !=', 'archive')
		->where('(pm.user_id = \'' . $this->current_user->user_id . '\' OR projects.owner_id = \'' . $this->current_user->user_id . '\')')
		->where('organization_id', $this->current_user->current_organization_id)
		->order_by('projects.name')
		->find_all();

		if (empty($projects)) {
			return [];
		}

		foreach ($projects as &$project) {
			$project->owned_by_x = sprintf(lang('db_owned_by_x'), $project->first_name);
		}


		return $projects;
	}

	public function get_project_detail($project_id)
	{
		$result = [];

		// Restrict
		if (! $this->mb_project->has_permission('project', $project_id, 'Project.View.All')) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('db_you_have_not_earned_permission_to_view_this_project'),
				'lqr' => $this->db->last_query(),
			]);
			return;
		}

		$result['allowed_point'] = $this->project_constraint_model->select('total_point_project')->find($project_id);
		$result['allowed_point'] && $result['allowed_point'] = (int) $result['allowed_point']->total_point_project;
		$result['total_used'] = $this->mb_project->total_used('project', $project_id);
		$result['no_of_meeting'] = $this->meeting_model
		->join('actions a', 'a.action_id = meetings.action_id')
		->join('projects p', 'p.project_id = a.project_id')
		->where('p.project_id', $project_id)->count_all();

		$result['unscheduled_meetings'] = $this->meeting_model->
		select('meetings.meeting_id, meetings.name, meetings.meeting_key, scheduled_start_time, 
		meetings.status, u.email, u.avatar, u.first_name, u.last_name')
		->join('actions a', 'a.action_id = meetings.action_id')
		->join('projects p', 'p.project_id = a.project_id')
		->join('users u', 'u.user_id = meetings.owner_id')
		->where('scheduled_start_time IS NULL', null, false)
		->where('p.project_id', $project_id)
		->find_all();

		$result['next_meeting'] = $this->meeting_model->
		select('meetings.meeting_id, meetings.name, meetings.meeting_key, scheduled_start_time, 
		meetings.status, u.email, u.avatar, u.first_name, u.last_name')
		->join('actions a', 'a.action_id = meetings.action_id')
		->join('projects p', 'p.project_id = a.project_id')
		->join('users u', 'u.user_id = meetings.owner_id')
		->where('meetings.status', 'ready')
		->where('scheduled_start_time > CURRENT_TIMESTAMP()', null, false)
		->find_by('p.project_id', $project_id);


		$this->meeting_model->
		select('meetings.meeting_id, meetings.name, meetings.meeting_key, scheduled_start_time, 
		meetings.status, u.email, u.avatar, u.first_name, u.last_name')
		->join('actions a', 'a.action_id = meetings.action_id')
		->join('projects p', 'p.project_id = a.project_id')
		->join('users u', 'u.user_id = meetings.owner_id')
		->where('meetings.status', 'ready')
		->where('p.project_id', $project_id);

		if ($result['next_meeting']) {
			$result['next_meeting']->scheduled_start_time = display_time($result['next_meeting']->scheduled_start_time);
			$this->meeting_model->where('meetings.meeting_id !=', $result['next_meeting']->meeting_id);
		}

		$result['scheduled_meetings'] = $this->meeting_model->find_all();

		$result['completed_meetings'] = $this->meeting_model->
		select('meetings.meeting_id, meetings.name, meetings.meeting_key, scheduled_start_time, 
		meetings.status, u.email, u.avatar, u.first_name, u.last_name')
		->join('actions a', 'a.action_id = meetings.action_id')
		->join('projects p', 'p.project_id = a.project_id')
		->join('users u', 'u.user_id = meetings.owner_id')
		->where('meetings.status', 'finished')
		->where('p.project_id', $project_id)
		->find_all();

		$this->parse_display_time($result['unscheduled_meetings']);
		$this->parse_display_time($result['scheduled_meetings']);
		$this->parse_display_time($result['completed_meetings']);

		$result['has_permission_project_edit'] = $this->mb_project->has_permission('project', $project_id, 'Project.Edit.All');
		$result['has_permission_project_view_all'] = has_permission('Project.View.All');

		// Progress
		$cnt_unscheduled = $result['unscheduled_meetings'] ? count($result['unscheduled_meetings']) : 0;
		$cnt_completed = $result['completed_meetings'] ? count($result['completed_meetings']) : 0;
		$result['pending_meeting'] = ((int) $result['next_meeting']) + $cnt_unscheduled;
		$result['used_meeting'] = $cnt_completed;
		$result['total_stars'] = 0;
		$result['rated_stars'] = 0;

		$all_meetings = $this->meeting_model->select('meeting_id')
		->join('actions a', 'meetings.action_id = a.action_id')
		->join('projects p', 'p.project_id = a.project_id AND p.project_id = ' . $project_id)
		->as_array()
		->find_all();

		$all_meetings || $all_meetings = [];

		if (count($all_meetings) > 0) {
			$all_meetings = array_column($all_meetings, 'meeting_id');
			
			$count_homework = $this->homework_model->where_in('meeting_id', $all_meetings)->count_all();
			$count_agenda = $this->agenda_model->where_in('meeting_id', $all_meetings)->count_all();
			$count_member = $this->meeting_member_model->where_in('meeting_id', $all_meetings)->count_all();

			$result['total_stars'] += count($all_meetings) + $count_agenda + $count_homework + $count_member;
			$result['total_stars'] *= 5;

			$count_homework = $this->homework_model->where_in('meeting_id', $all_meetings)
			->join('homework_rates r', 'r.homework_id = homework.homework_id')
			->count_all();

			$count_agenda = $this->agenda_model->where_in('meeting_id', $all_meetings)
			->join('agenda_rates r', 'r.agenda_id = agendas.agenda_id')
			->count_all();

			$count_member = $this->meeting_member_model->where_in('meeting_members.meeting_id', $all_meetings)
			->join('meeting_member_rates r', 'r.meeting_id = meeting_members.meeting_id')
			->count_all();

			$count_meeting_rate = $this->meeting_model->where_in('meetings.meeting_id', $all_meetings)
			->join('meeting_members r', 'r.meeting_id = meetings.meeting_id AND r.rate IS NOT NULL')
			->count_all();

			$result['rated_stars'] += $count_homework + $count_agenda + $count_member + $count_meeting_rate;
		}

		// Team
		$project_members = $this->project_member_model->select('u.user_id, 
		CONCAT(first_name, " ", last_name) AS full_name, email, avatar')
		->where('project_id', $project_id)
		->join('users u', 'u.user_id = project_members.user_id')
		->find_all();

		$project_members || $project_members = [];

		foreach ($project_members as &$user) {
			$user->total_stars = 0;
			$user->rated_stars = 0;
			$user->avg_stars = 0;
			$user->pts = 0;
			$user->avatar_url = avatar_url($user->avatar, $user->email);
		}

		if (is_array($project_members) && count($project_members) > 0 && count($all_meetings) > 0) {
			foreach ($project_members as &$user) {
				$joined_meetings = $this->meeting_model
				->select('meetings.meeting_id')
				->join('meeting_members mm', 'mm.meeting_id = meetings.meeting_id AND mm.user_id = ' . $user->user_id)
				->where_in('meetings.meeting_id', $all_meetings)
				->as_array()
				->find_all();

				$joined_meetings || $joined_meetings = [];
				$user->total_stars = count($joined_meetings) * 5;

				if (count($joined_meetings) > 0) {
					$rated_stars = $this->meeting_member_rate_model->select_sum('rate')
					->where_in('meeting_id', array_column($joined_meetings, 'meeting_id'))
					->find_by('attendee_id', $user->user_id);

					if ($rated_stars && is_numeric($rated_stars->rate)) {
						$user->rated_stars = $rated_stars->rate;
					}
				}

				$user->pts = $this->mb_project->total_user_point_used($user->user_id, 'project', $project_id);
			}
		}

		$result['members'] = $project_members;

		// Stats
		$result['stats'] = [];
		$result['stats']['team'] = [];
		$result['stats']['rate'] = [];
		$result['stats']['hour'] = [];

		$latest_meeting = $this->meeting_model
		->select('meetings.meeting_id, meetings.created_on,
		(SELECT COUNT(*) 
			FROM mb_meeting_members mm 
			WHERE mm.meeting_id = mb_meetings.meeting_id) AS team,
		(SELECT IFNULL(SUM(rate) / COUNT(*), 0)
			FROM mb_meeting_members mm 
			WHERE mm.meeting_id = mb_meetings.meeting_id AND rate IS NOT NULL) AS rate,
		(SELECT TRUNCATE(SUM(IF(in_type = "minutes", `in`, IF(in_type = "hours", `in` * 60, IF(in_type = "days", `in` * 24 * 60, `in` * 5 * 24 * 60)))) / 60, 2)
			FROM mb_meetings m2 
			WHERE m2.meeting_id = mb_meetings.meeting_id
			AND m2.created_on <= mb_meetings.created_on) AS total_meeting_hours,
		(SELECT IFNULL(SUM(time_spent), 0) FROM mb_homework hw 
			JOIN mb_meetings m2 ON m2.meeting_id = hw.meeting_id 
			WHERE m2.meeting_id = mb_meetings.meeting_id
			AND m2.created_on <= mb_meetings.created_on) AS total_homework_hours
		', false)
		->join('actions a', 'a.action_id = meetings.action_id')
		->join('projects p', 'a.project_id = p.project_id AND p.project_id = '. $project_id)
		->order_by('meetings.created_on', 'DESC')
		->limit(4)
		->as_array()
		->find_all();

		$result['l'] = $this->db->last_query();

		if ($latest_meeting && count($latest_meeting) > 0) {
			$i = 0;

			for ($i=3; $i>=0; $i--) {
				if (isset($latest_meeting[$i])) {
					$result['stats']['team'][] = $latest_meeting[$i]['team'];
					$result['stats']['rate'][] = $latest_meeting[$i]['rate']; // AVG Rating for meeting
					$result['stats']['hour'][] = $latest_meeting[$i]['total_meeting_hours'] + $latest_meeting[$i]['total_homework_hours'];
				} else {
					$result['stats']['team'][] = null;
					$result['stats']['rate'][] = null;
					$result['stats']['hour'][] = null;
				}
			}
		}

		echo json_encode($result);
	}

	private function get_meeting_stats($date) {

	}

	private function parse_display_time(&$meetings)
	{
		if (! is_array($meetings)) return false;

		foreach ($meetings as &$meeting) {
			if ($meeting->scheduled_start_time !== null) {
				$meeting->scheduled_start_time = display_time($meeting->scheduled_start_time);
			}

			if (isset($meeting->status)) {
				$meeting->lang_status = lang('st_' . $meeting->status);
			}
		}
	}

	private function get_other_projects($my_project_ids)
	{
		$projects = $this->project_model
		->select('projects.name, projects.project_id, projects.cost_code, 
		u.email, u.avatar, u.first_name, u.last_name, 
		(SELECT COUNT(*) FROM ' . $this->db->dbprefix('project_members') . ' WHERE ' . 
		$this->db->dbprefix('project_members') . '.project_id = ' . 
		$this->db->dbprefix('projects') . '.project_id) as member_number', false)

		->join('users u', 'u.user_id = projects.owner_id')
		->where('projects.status !=', 'archive')
		->where_not_in('project_id', count($my_project_ids) > 0 ? $my_project_ids : -1)
		->where('organization_id', $this->current_user->current_organization_id)
		->order_by('projects.name')
		->find_all();

		if (empty($projects)) {
			return [];
		}

		foreach ($projects as &$project) {
			$project->owned_by_x = sprintf(lang('db_owned_by_x'), $project->first_name);
		}

		return $projects;
	}

	private function get_my_todo()
	{
		$homeworks_query = $this->homework_model
		->select('homework.homework_id, homework.name, s.meeting_key, 
		s.name AS meeting_name, s.scheduled_start_time, s.in, s.in_type,
		hr.user_id IS NOT NULL AS is_read', false)
		->join('meetings s', 's.meeting_id = homework.meeting_id AND s.scheduled_start_time IS NOT NULL')
		->join('actions a', 'a.action_id = s.action_id')
		->join('projects p', 'p.project_id = a.project_id')
		->join('homework_members hm', 'hm.homework_id = homework.homework_id AND hm.user_id = ' . $this->current_user->user_id, 'LEFT')
		->join('homework_reads hr', 'homework.homework_id = hr.homework_id AND hr.user_id =' . $this->current_user->user_id, 'LEFT')
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

		$evaluate_meetings = $this->meeting_model
		->select('meetings.*, sm.rate, meetings.name as meeting_name, 
		u.first_name, u.last_name, u.email, 
		IF(' . $this->db->dbprefix('meetings') . '.owner_id = "' . $this->current_user->user_id . '", 1 , 0) AS is_owner, 
		"evaluate" AS todo_type, "meeting" AS evaluate_mode,
		r.user_id IS NOT NULL AS is_read', false)
		->join('actions a', 'a.action_id = meetings.action_id')
		->join('projects p', 'p.project_id = a.project_id')
		->join('users u', 'u.user_id = meetings.owner_id')
		->join('meeting_members sm', 'sm.meeting_id = meetings.meeting_id AND sm.user_id = "' . $this->current_user->user_id . '"', 'LEFT')
		->join('meeting_reads r', 'meetings.meeting_id = r.meeting_id AND r.user_id =' . $this->current_user->user_id, 'LEFT')
		->where('organization_id', $this->current_user->current_organization_id)
		->where('(sm.user_id = "' . $this->current_user->user_id . '" OR meetings.owner_id = "' . $this->current_user->user_id . '")')
		->where('meetings.manage_state', 'evaluate')
		->group_by('meetings.meeting_id')
		->find_all();

		if (empty($evaluate_meetings)) {
			$evaluate_meetings = [];
		}

		$meetings = $evaluate_meetings;

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
			$evaluate_agendas = $this->agenda_model
			->select('agendas.*, "evaluate" AS todo_type, "agenda" AS evaluate_mode,
			r.user_id IS NOT NULL AS is_read, 
			(SELECT m.meeting_key FROM ' . $this->db->dbprefix('meetings') . ' m WHERE m.meeting_id = ' . $this->db->dbprefix('agendas') . '.meeting_id) AS meeting_key', false)
			->join('agenda_reads r', 'agendas.agenda_id = r.agenda_id AND r.user_id =' . $this->current_user->user_id, 'LEFT')
			->where_in('agendas.meeting_id', $member_meeting_ids)
			->where($this->db->dbprefix('agendas.agenda_id') . ' NOT IN (SELECT ' . $this->db->dbprefix('agenda_rates') . '.agenda_id FROM ' . $this->db->dbprefix('agenda_rates') . ' WHERE ' . $this->db->dbprefix('agenda_rates') . '.agenda_id = ' . $this->db->dbprefix('agendas') . '.agenda_id AND ' . $this->db->dbprefix('agenda_rates') . '.user_id = "' . $this->current_user->user_id . '")')
			->find_all();
			if (empty($evaluate_agendas)) {
				$evaluate_agendas = [];
			}

			$evaluate_homeworks = $this->homework_model
			->select('homework.*, "evaluate" AS todo_type, "homework" AS evaluate_mode,
			r.user_id IS NOT NULL AS is_read, 
			(SELECT m.meeting_key FROM ' . $this->db->dbprefix('meetings') . ' m WHERE m.meeting_id = ' . $this->db->dbprefix('homework') . '.meeting_id) AS meeting_key', false)
			->join('homework_reads r', 'homework.homework_id = r.homework_id AND r.user_id =' . $this->current_user->user_id, 'LEFT')
			->where_in('homework.meeting_id', $member_meeting_ids)
			->where($this->db->dbprefix('homework.homework_id') . ' NOT IN (SELECT ' . $this->db->dbprefix('homework_rates') . '.homework_id FROM ' . $this->db->dbprefix('homework_rates') . ' WHERE ' . $this->db->dbprefix('homework_rates') . '.homework_id = ' . $this->db->dbprefix('homework') . '.homework_id AND ' . $this->db->dbprefix('homework_rates') . '.user_id = "' . $this->current_user->user_id . '")')
			->find_all();
			if (empty($evaluate_homeworks)) {
				$evaluate_homeworks = [];
			}
		}

		if (! empty($owner_meeting_ids)) {
			$evaluate_members = $this->meeting_member_model
			->select('u.user_id, u.email, u.first_name, u.last_name, u.avatar, meeting_members.meeting_id, "evaluate" AS todo_type, "user" AS evaluate_mode,
			r.user_id IS NOT NULL AS is_read, 
			(SELECT m.meeting_key FROM ' . $this->db->dbprefix('meetings') . ' m WHERE m.meeting_id = ' . $this->db->dbprefix('meeting_members') . '.meeting_id) AS meeting_key', false)
			->join('users u', 'u.user_id = meeting_members.user_id', 'LEFT')
			->join('meeting_member_reads r', 'meeting_members.meeting_id = r.meeting_id AND meeting_members.user_id = r.rating_user_id AND r.user_id =' . $this->current_user->user_id, 'LEFT')
			->where_in('meeting_members.meeting_id', $owner_meeting_ids)
			->where('(' . $this->db->dbprefix('meeting_members') . '.user_id, ' . $this->db->dbprefix('meeting_members') . '.meeting_id) NOT IN (SELECT ' . $this->db->dbprefix('meeting_member_rates') . '.attendee_id, ' . $this->db->dbprefix('meeting_member_rates') . '.meeting_id FROM ' . $this->db->dbprefix('meeting_member_rates') . ' WHERE ' . $this->db->dbprefix('meeting_member_rates') . '.meeting_id IN (' . implode(',', $owner_meeting_ids) . ') AND ' . $this->db->dbprefix('meeting_member_rates') . '.user_id = "' . $this->current_user->user_id . '")')
			->find_all();

			if (empty($evaluate_members)) {
				$evaluate_members = [];
			}
		}

		$evaluates = array_merge($evaluate_meetings, $evaluate_members, $evaluate_agendas, $evaluate_homeworks);
	
		$decides = $this->meeting_model
		->select('meetings.*, meetings.name AS meeting_name, ag.*, 
		ag.name AS agenda_name, ag.description AS agenda_description, 
		"decide" AS todo_type')
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
			'decides' => $decides,
			'meetings' => $meetings
		];
	}

	// copied from meeting controller
	private function is_evaluated($meeting_id, $user_id = null) {

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

	public function init()
	{
		Template::render();
	}

	public function check_meeting_by_google_event_id()
	{
		$eventIDs = $this->input->post('eventIDs');

		if (empty($eventIDs)) {
			echo "[]";
			return;
		}

		$meeting = $this->meeting_model
		->select('google_event_id')
		->where_in('google_event_id', $eventIDs)
		->limit(count($eventIDs))
		->as_array()
		->find_all();

		echo $meeting 
		? json_encode(array_column($meeting, 'google_event_id')) 
		: "[]";
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