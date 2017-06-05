<?php defined('BASEPATH') || exit('No direct script access allowed');

class Meeting extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('meeting');
		$this->load->helper('mb_form');
		$this->load->helper('text');
		$this->load->helper('date');
		$this->load->library('mb_project');
		
		$this->load->model('users/user_model');

		$this->lang->load('homework/homework');
		$this->load->model('homework/homework_model');
		$this->load->model('homework/homework_member_model');
		
		$this->load->model('agenda/agenda_model');
		$this->load->model('agenda/agenda_member_model');
		$this->load->model('agenda/agenda_rate_model');

		$this->load->model('meeting_model');
		$this->load->model('meeting_member_model');
		$this->load->model('meeting_member_rate_model');

		$this->load->model('action/action_model');
		$this->load->model('action/action_member_model');

		$this->load->model('project/project_model');
		$this->load->model('project/project_member_model');

		Assets::add_module_css('meeting', 'meeting.css');
		Assets::add_module_js('meeting', 'meeting.js');
	}

	public function _remap($method, $params = array())
	{
		if (method_exists($this, $method))
		{
			return call_user_func_array(array($this, $method), $params);
		} else {
			$this->detail($method);
		}
	}

	public function index()
	{
		Template::render();
	}

	public function create($project_key = null)
	{
		if (empty($project_key)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$project_id = $this->mb_project->get_object_id('project', $project_key);

		if (empty($project_id)) {
			Template::set_message(lang('st_project_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (! $this->mb_project->has_permission('project', $project_id, 'Project.Edit.All')) {
			$this->auth->restrict();
		}

		$action = $this->action_model->select('action_id, action_key, p.project_id')
									->join('projects p', 'actions.project_id = p.project_id')
									->join('user_to_organizations uto', 'uto.organization_id = p.organization_id AND uto.user_id = ' . $this->current_user->user_id)
									->limit(1)
									->find_by('action_key', $project_key . '-1');

		if ($action === false) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		// Get list resource/team member
		$project_members = $this->user_model->get_organization_members($this->current_user->current_organization_id, $project_id);

		// Create Meeting from Open Parking Lot agendas
		if (isset($_POST['from_meeting'])) {
			$open_agendas = $this->agenda_model->where('confirm_status', 'open_parking_lot')
											->where('meeting_id', $this->input->post('from_meeting'))
											->find_all();
											
			Template::set('open_agendas', $open_agendas);
		} else {
			Template::set('open_agendas', false);
		}

		Assets::add_js($this->load->view('create_js', [
			'project_members' => $project_members
		], true), 'inline');

		if (isset($_POST['save'])) {
			$data = $this->meeting_model->prep_data($this->input->post());
			$data['action_id'] = $action->action_id;
			$data['created_by'] = $this->current_user->user_id;

			if ($this->input->post('owner_id') == '') {
				$data['owner_id'] = $this->current_user->user_id;
			}

			$data['meeting_key'] = $this->mb_project->get_next_key($action->action_key);

			if ($id = $this->meeting_model->insert($data)) {
				if ($team = $this->input->post('team')) {
					if ($team = explode(',', $team)) {
						$member_data = [];
						foreach ($team as $member) {
							$member_data[] = [
								'meeting_id' => $id,
								'user_id' => $member
							];
						}

						$this->meeting_member_model->insert_batch($member_data);
						$this->mb_project->notify_members($id, 'meeting', $this->current_user, 'insert');
					}
				}

				Template::set('close_modal', 1);
				Template::set('message_type', 'success');
				Template::set('message', lang('st_meeting_successfully_created'));
				Template::set('data', $this->ajax_meeting_data($id));
				// Just to reduce AJAX request size
				if (IS_AJAX) {
					Template::set('content', '');
				}
				
			} else {
				Template::set('close_modal', 0);
				Template::set('message_type', 'danger');
				Template::set('message', lang('st_there_was_a_problem_while_creating_meeting'));
			}

			Template::render();
			return;
		}

		Template::set('project_members', $project_members);
		Template::set('action_key', $action->action_key);
		Template::render();
	}

	public function edit($meeting_key = null)
	{

		if (empty($meeting_key)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$meeting_id = $this->mb_project->get_object_id('meeting', $meeting_key);

		if (empty($meeting_id)) {
			Template::set_message(lang('st_meeting_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (! $this->mb_project->has_permission('meeting', $meeting_id, 'Project.Edit.All')) {
			$this->auth->restrict();
		}

		$keys = explode('-', $meeting_key);
		if (empty($keys) || count($keys) < 3) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$project_key = $keys[0];

		// get projecct id
		// $project_id = $this->project_model->get_project_id($project_key, $this->current_user->current_organization_id);
		// if ($project_id === false) {
		// 	redirect(DEFAULT_LOGIN_LOCATION);
		// }

		// if ($this->project_model->is_project_owner($project_id, $this->current_user->user_id) === false
		// && $this->project_member_model->is_project_member($project_id, $this->current_user->user_id) === false
		// && $this->auth->has_permission('Project.Edit.All') === false) {
		// 	redirect(DEFAULT_LOGIN_LOCATION);
		// }

		$meeting = $this->meeting_model->select('meetings.*, p.project_id')
								->join('actions a', 'a.action_id = meetings.action_id')
								->join('projects p', 'a.project_id = p.project_id')
								->join('user_to_organizations uto', 'uto.organization_id = p.organization_id AND uto.user_id = ' . $this->current_user->user_id)
								->limit(1)
								->find_by('meeting_key', $meeting_key);

		if ($meeting === false) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$meeting_members = $this->meeting_member_model->where('meeting_id', $meeting->meeting_id)->as_array()->find_all();
		$meeting_members = $meeting_members && count($meeting_members) > 0 ? array_column($meeting_members, 'user_id') : [];
		Template::set('meeting_members', $meeting_members);
		Template::set('meeting', $meeting);

		$project_key = explode('-', $meeting_key);
		$project_key = $project_key[0];

		$project_members = $this->user_model->get_organization_members($this->current_user->current_organization_id, $meeting->project_id);

		Template::set('project_members', $project_members);
		Assets::add_js($this->load->view('create_js', [
			'project_members' => $project_members
		], true), 'inline');

		if ($data = $this->input->post()) {
			$data = $this->meeting_model->prep_data($data);
			$data['modified_by'] = $this->current_user->user_id;

			if ($this->input->post('owner_id') == '') {
				$data['owner_id'] = $this->current_user->user_id;
			}

			// Add to project members if not in
			// Prevent duplicate row by MySQL Insert Ignore
			$query = $this->db->insert_string('project_members', [
				'project_id' => $meeting->project_id,
				'user_id' => $data['owner_id']
			]);

			$query = str_replace('INSERT', 'INSERT IGNORE', $query);
			$this->db->query($query);

			if ($this->meeting_model->update($meeting->meeting_id, $data)) {
				$this->meeting_member_model->delete_where(['meeting_id' => $meeting->meeting_id]);

				if ($team = $this->input->post('team')) {
					if ($team = explode(',', $team)) {
						$member_data = [];
						foreach ($team as $member) {
							$member_data[] = [
								'meeting_id' => $meeting->meeting_id,
								'user_id' => $member
							];

							// Add to project members if not in
							// Prevent duplicate row by MySQL Insert Ignore
							$query = $this->db->insert_string('project_members', [
								'project_id' => $meeting->project_id,
								'user_id' => $member
							]);

							$query = str_replace('INSERT', 'INSERT IGNORE', $query);
							$this->db->query($query);
						}

						$this->meeting_member_model->insert_batch($member_data);
						if ((! empty($data['status'])) && $data['status'] != $meeting->status) {
							$this->mb_project->notify_members($meeting->meeting_id, 'meeting', $this->current_user, 'update_status');
						}
					}
				}

				Template::set('close_modal', 1);
				Template::set('message_type', 'success');
				Template::set('message', lang('st_meeting_successfully_updated'));

				// Just to reduce AJAX request size
				if (IS_AJAX) {
					Template::set('content', '');
				}
				
			} else {
				Template::set('close_modal', 0);
				Template::set('message_type', 'danger');
				Template::set('message', lang('st_there_was_a_problem_while_creating_meeting'));
			}

			Template::render();
			return;
		}


		Template::render();
	}

	public function detail($meeting_key = null)
	{
		if (empty($meeting_key)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$keys = explode('-', $meeting_key);
		if (empty($keys) || count($keys) < 3) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$meeting_id = $this->mb_project->get_object_id('meeting', $meeting_key);

		if (empty($meeting_id)) {
			Template::set_message(lang('st_meeting_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (! $this->mb_project->has_permission('meeting', $meeting_id, 'Project.View.All')) {
			$this->auth->restrict();
		}

		$project_key = $keys[0];
		$action_key = $keys[0] . '-' . $keys[1];

		$project_id = $this->mb_project->get_object_id('project', $project_key);
		if (empty($project_id)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$meeting = $this->meeting_model->get_meeting_by_key($meeting_key, $this->current_user->current_organization_id, 'meetings.*, u.email, u.first_name, u.last_name, u.avatar');

		if (! $meeting) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$agendas = $this->agenda_model->select('agendas.*, u.email, u.first_name, u.last_name, u.avatar')
								->join('users u', 'u.user_id = agendas.owner_id', 'left')
								->where('meeting_id', $meeting_id)->find_all();

		if ($agendas) {
			foreach ($agendas as &$agenda) {
				$agenda->members = $this->agenda_member_model->select('avatar, email, first_name, last_name')
				->join('users u', 'u.user_id = agenda_members.user_id')
				->where('agenda_id', $agenda->agenda_id)
				->find_all();
			}
		}

		$homeworks = $this->homework_model->where('meeting_id', $meeting_id)->find_all();

		if ($homeworks) {
			foreach ($homeworks as &$homework) {
				$homework->members = $this->homework_member_model->select('u.user_id, avatar, email, last_name, first_name, CONCAT(first_name, " ", last_name) AS full_name')
				->join('users u', 'u.user_id = homework_members.user_id')
				->where('homework_id', $homework->homework_id)
				->find_all();

				$homework->members = $homework->members ? $homework->members : [];
			}
		}

		$invited_members = $this->meeting_member_model->get_meeting_member($meeting_id);
									
		$point_used = number_format($this->mb_project->total_point_used('meeting', $meeting->meeting_id), 2);

		$evaluated = $this->is_evaluated($meeting_id);
		if ($evaluated === true) {
			Template::set_message(lang('st_meeting_already_evaluated'), 'info');
		}

		Assets::add_js($this->load->view('detail_js', ['meeting_key' => $meeting_key, 'current_user' => $this->current_user], true), 'inline');
		Template::set('evaluated', $evaluated);
		Template::set('invited_members', $invited_members);
		Template::set('point_used', $point_used);
		Template::set('meeting', $meeting);
		Template::set('agendas', $agendas);
		Template::set('homeworks', $homeworks);
		Template::set('project_key', $project_key);
		Template::set('action_key', $action_key);
		Template::set('meeting_key', $meeting_key);
		Template::set('current_user', $this->current_user);
		Template::set_view('detail');
		Template::render();
	}

	public function monitor($meeting_key = null)
	{
		if (empty($meeting_key)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$keys = explode('-', $meeting_key);
		if (empty($keys) || count($keys) < 3) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$meeting_id = $this->mb_project->get_object_id('meeting', $meeting_key);

		if (empty($meeting_id)) {
			Template::set_message(lang('st_meeting_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (! $this->mb_project->has_permission('meeting', $meeting_id, 'Project.View.All')) {
			$this->auth->restrict();
		}

		/*
			To access Meeting Monitor, user must be owner or team member of Meeting
		*/

		$meeting = $this->meeting_model->find_by('meeting_key', $meeting_key);

		if (! $meeting) {
			Template::set_message(lang('st_invalid_meeting_key'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$agendas = $this->agenda_model->select('agendas.*, 
											IF((SELECT tv.user_id FROM mb_agenda_votes tv WHERE mb_agendas.agenda_id = tv.agenda_id AND tv.user_id = "'. $this->current_user->user_id .'") IS NOT NULL, 1, 0) AS voted_skip,
											(SELECT COUNT(*) FROM mb_agenda_votes tv WHERE mb_agendas.agenda_id = tv.agenda_id) AS skip_votes', false)
									->join('users u', 'u.user_id = agendas.owner_id', 'left')
									->where('meeting_id', $meeting->meeting_id)->find_all();

		// We can't start without agendas
		if ($agendas === false) {
			Template::set('message_type', 'warning');
			Template::set('message', lang('st_cannot_start_meeting_without_any_agenda'));
			Template::set('content', '');
			Template::render();
			return;
		}

		foreach ($agendas as &$agenda) {
			$agenda->members = $this->agenda_member_model->select('avatar, email, first_name, last_name')->join('users u', 'u.user_id = agenda_members.user_id')->where('agenda_id', $agenda->agenda_id)->find_all();
		}

		$homeworks = $this->homework_model->where('meeting_id', $meeting_id)->find_all();

		if ($homeworks) {
			foreach ($homeworks as &$homework) {
				$homework->members = $this->homework_member_model->select('u.user_id, avatar, email, last_name, first_name')
				->join('users u', 'u.user_id = homework_members.user_id')
				->where('homework_id', $homework->homework_id)
				->find_all();

				$homework->members = $homework->members ? $homework->members : [];
			}
		}

		Assets::add_js($this->load->view('monitor_js', [
			'meeting_key' => $meeting_key
		], true), 'inline');
		Template::set('close_modal', 0);
		Template::set('current_user', $this->current_user);
		Template::set('agendas', $agendas);
		Template::set('homeworks', $homeworks);
		Template::set('meeting', $meeting);
		Template::set('now', gmdate('Y-m-d H:i:s'));
		Template::render();
	}

	public function decider($meeting_key = null)
	{
		if (empty($meeting_key)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$keys = explode('-', $meeting_key);
		if (empty($keys) || count($keys) < 3) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$meeting_id = $this->mb_project->get_object_id('meeting', $meeting_key);

		if (empty($meeting_id)) {
			Template::set_message(lang('st_meeting_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (! $this->mb_project->has_permission('meeting', $meeting_id, 'Project.View.All')) {
			$this->auth->restrict();
		}

		/*
			To access Meeting Monitor, user must be owner or team member of Meeting
		*/

		$meeting = $this->meeting_model->find_by('meeting_key', $meeting_key);

		if (! $meeting) {
			Template::set_message(lang('st_invalid_meeting_key'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$action_key = $keys[0] . '-' . $keys[1];
		$meeting->members = $this->meeting_member_model->get_meeting_member($meeting_id);
		$agendas = $this->agenda_model->select('agendas.*, (finished_on - started_on) / 60 AS duration, 
											IF((SELECT tv.user_id FROM mb_agenda_votes tv WHERE mb_agendas.agenda_id = tv.agenda_id AND tv.user_id = "'. $this->current_user->user_id .'") IS NOT NULL, 1, 0) AS voted_skip,
											(SELECT COUNT(*) FROM mb_agenda_votes tv WHERE mb_agendas.agenda_id = tv.agenda_id) AS skip_votes', false)
									->join('users u', 'u.user_id = agendas.owner_id', 'left')
									->where('meeting_id', $meeting->meeting_id)->find_all();
		
		// We can't start without agendas
		if ($agendas === false) {
			Template::set('message_type', 'warning');
			Template::set('message', lang('st_cannot_start_meeting_without_any_agenda'));
			Template::set('content', '');
			Template::render();
			return;
		}

		foreach ($agendas as &$agenda) {
			$agenda->members = $this->agenda_member_model->select('avatar, email, first_name, last_name')->join('users u', 'u.user_id = agenda_members.user_id')->where('agenda_id', $agenda->agenda_id)->find_all();
		}


		Assets::add_js($this->load->view('decider_js', [
			'action_key' => $action_key,
			'meeting_key' => $meeting->meeting_key,
			'meeting_id' => $meeting_id
		], true), 'inline');
		Template::set('close_modal', 0);
		Template::set('current_user', $this->current_user);
		Template::set('agendas', $agendas);
		Template::set('meeting', $meeting);
		Template::set('now', gmdate('Y-m-d H:i:s'));
		Template::render();
	}

	public function update_decider($meeting_key)
	{
		if (empty($meeting_key)) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_meeting_key_does_not_exist')
			]);
			return;
		}

		$keys = explode('-', $meeting_key);
		if (empty($keys) || count($keys) < 3) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_meeting_key_does_not_exist')
			]);
			return;
		}

		$meeting_id = $this->mb_project->get_object_id('meeting', $meeting_key);

		if (empty($meeting_id)) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_meeting_key_does_not_exist')
			]);
			return;
		}

		if (! $this->mb_project->has_permission('meeting', $meeting_id, 'Project.View.All')) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_action')
			]);
			return;
		}

		if (! is_array($this->input->post('agendas')) && count($this->input->post('agendas')) == 0) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_action')
			]);
			return;
		}

		// Prepare data

		$agenda_data = [];

		foreach ($this->input->post('agendas') as $agenda_key => $confirmation_status) {
			$agenda_data[] = [
				'agenda_key' => $agenda_key,
				'confirm_status' => $confirmation_status,
				'modified_by' => $this->current_user->user_id
			];
		}

		if ($this->agenda_model->update_batch($agenda_data, 'agenda_key') ) {
			$notes = $this->input->post('note') ? $this->input->post('note') : null;

			$this->meeting_model->skip_validation(TRUE)->update($meeting_id, [
				'manage_state' => 'evaluate',
				'notes' => $notes
			]);

			echo json_encode([
				'message_type' => 'success',
				'message' => lang('st_all_agenda_confirmed_meeting_closed_out')
			]);
			return;
		}

		echo json_encode([
			'message_type' => 'danger',
			'message' => lang('st_unknown_error')
		]);
	}

	public function resolve_agenda($agenda_id = null)
	{
		if (empty($agenda_id)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$agenda = $this->agenda_model->select('name')->limit(1)->find($agenda_id);

		Template::set('id', 'resolve-agenda');
		Template::set('close_modal', 0);
		Template::set('current_user', $this->current_user);
		Template::set('agenda_id', $agenda_id);
		Template::set('agenda', $agenda);
		Template::render();
	}

	public function get_monitor_data($meeting_id)
	{
		if (empty($meeting_id)) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_meeting_key')
			]);
			return ;
		}

		$agendas = $this->agenda_model->select('agendas.agenda_id, agendas.status, agendas.started_on, agendas.time_assigned, 
											(SELECT COUNT(*) FROM mb_agenda_votes tv WHERE mb_agendas.agenda_id = tv.agenda_id) AS skip_votes', false)
									->join('users u', 'u.user_id = agendas.owner_id', 'left')
									->where('meeting_id', $meeting_id)->find_all();

		if ($agendas === false) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_meeting_key')
			]);
			return;
		}

		
		$homeworks = $this->homework_model->select('homework_id, description, status, time_spent')
										->where('meeting_id', $meeting_id)
										->find_all();
		$homeworks = $homeworks ? $homeworks : [];

		foreach ($homeworks as &$hw) {
			$hw->short_description = word_limiter($hw->description, 18);
		}

		$current_time = gmdate('Y-m-d H:i:s');

		// Real-time joiner
		$interval = 3;
		$this->meeting_member_model->where('user_id', $this->current_user->user_id)->update($meeting_id, ['last_online' => $current_time]);
		$online_members = $this->meeting_member_model->select('u.user_id, CONCAT(first_name, " ", last_name) AS full_name, avatar, email')
													->join('users u', 'u.user_id = meeting_members.user_id')
													->where('TIMEDIFF(DATE_ADD(last_online, INTERVAL '. $interval  .' SECOND), "'. $current_time .'") >= 0 ', null, false)
													->where('meeting_id', $meeting_id)
													->order_by('u.user_id')
													->find_all();

		echo json_encode([
			'message_type' => 'success',
			'agendas' => $agendas,
			'homeworks' => $homeworks ? $homeworks : [],
			'meeting' => $this->meeting_model->select('status')->limit(1)->find($meeting_id),
			'online_members' => $online_members ? $online_members : [],
			'current_time' => $current_time,
		]);
	}

	public function vote_skip($agenda_id)
	{
		// Prevent duplicate row by MySQL Insert Ignore
		$query = $this->db->insert_string('agenda_votes', [
			'agenda_id' => $agenda_id,
			'user_id' => $this->current_user->user_id
		]);

		$query = str_replace('INSERT', 'INSERT IGNORE', $query);
		$test = $this->db->query($query);

		if ($test) {
			echo 1;
			return;
		}

		echo 0;
	}

	public function update_meeting_schedule() {

		$meeting = $this->meeting_model->select('meetings.*, u.timezone')
								->join('actions a', 'a.action_id = meetings.action_id')
								->join('projects p', 'a.project_id = p.project_id')
								->join('users u', 'u.user_id = ' . $this->current_user->user_id)
								->join('user_to_organizations uto', 'uto.organization_id = p.organization_id AND uto.user_id = ' . $this->current_user->user_id)
								->where('meetings.owner_id', $this->current_user->user_id)
								->limit(1)
								->find($this->input->post('meeting_id'));

		if ($meeting === false) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_meeting_key')
			]);
			return;
		}

		// Start meeting?
		if ($this->input->post('start')) {
			if ($meeting->status != 'ready') {
				echo json_encode([
					'message_type' => 'danger',
					'message' => lang('st_invalid_meeting_status')
				]);

				return;
			}

			if ($meeting->scheduled_start_time === NULL) {
				echo json_encode([
					'message_type' => 'danger',
					'message' => lang('st_invalid_schedule_time')
				]);

				return;
			}

			$current_time = gmdate('Y-m-d H:i:s');
			$query = $this->meeting_model->skip_validation(1)->update($meeting->meeting_id, [
				'status' => 'inprogress',
				'actual_start_time' => $current_time,
			]);

			if ($query) {
				if ( is_array($this->input->post('time_assigned')) ) {
					$agenda_data = [];
					foreach ($this->input->post('time_assigned') as $agenda_id => $time_assigned) {
						$agenda_data[] = [
							'agenda_id' => $agenda_id,
							'time_assigned' => $time_assigned
						];
					}

					$this->agenda_model->skip_validation(1)->update_batch($agenda_data, 'agenda_id');
				}

				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_meeting_started'),
					'actual_start_time' => $current_time
				]);

				return;
			}

			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_unknown_error')
			]);
			return;
		}

		// Finish meeting
		if ($this->input->post('finish')) {
			if ($meeting->status != 'inprogress') {
				echo json_encode([
					'message_type' => 'danger',
					'message' => lang('st_invalid_meeting_status')
				]);

				return;
			}

			$agendas = $this->agenda_model->select('agenda_key')->where('meeting_id', $meeting->meeting_id)->where('(status = "inprogress" OR status ="open")', null, false)->find_all();

			if ($agendas) {
				echo json_encode([
					'message_type' => 'danger',
					'message' => lang('st_please_resolve_all_agenda_before_finish')
				]);

				return;
			}

			$current_time = gmdate('Y-m-d H:i:s');
			$query = $this->meeting_model->skip_validation(1)->update($meeting->meeting_id, [
				'status' => 'finished',
				'manage_state' => 'decide',
				'actual_end_time' => $current_time,
			]);
			
			if ($query) {
				$this->mb_project->notify_members($meeting->meeting_id, 'meeting', $this->current_user, 'update_status');
				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_meeting_finished'),
					'actual_end_time' => $current_time
				]);

				return;
			}

			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_unknown_error')
			]);
			return;
		}

		// Validation
		if ( ! strtotime($this->input->post('scheduled_start_time')) ) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_schedule_time')
			]);

			return;
		}

		$query = $this->meeting_model->skip_validation(1)->update($meeting->meeting_id, [
			'status' => 'ready',
			'manage_state' => 'monitor',
			'scheduled_start_time' => $this->input->post('scheduled_start_time')
		]);

		if ($query) {
			if ( is_array($this->input->post('time_assigned')) ) {
				$agenda_data = [];
				foreach ($this->input->post('time_assigned') as $agenda_id => $time_assigned) {
					$agenda_data[] = [
						'agenda_id' => $agenda_id,
						'time_assigned' => $time_assigned
					];
				}

				$this->agenda_model->skip_validation(1)->update_batch($agenda_data, 'agenda_id');
			}
			echo json_encode([
				'message_type' => 'success',
				'message' => lang('st_schedule_time_saved')
			]);

			return;
		}

		echo json_encode([
			'message_type' => 'danger',
			'message' => lang('st_unknown_error')
		]);
	}

	public function update_agenda_status()
	{
		$agenda = $this->agenda_model->select('agendas.*, u.timezone, s.meeting_id')
								->join('meetings s', 's.meeting_id = agendas.meeting_id')
								->join('actions a', 'a.action_id = s.action_id')
								->join('projects p', 'a.project_id = p.project_id')
								->join('user_to_organizations uto', 'uto.organization_id = p.organization_id AND uto.user_id = ' . $this->current_user->user_id)
								->join('users u', 'u.user_id = ' . $this->current_user->user_id)
								->where('s.owner_id', $this->current_user->user_id)
								->where('s.status', 'inprogress')
								->limit(1)
								->find($this->input->post('agenda_id'));

		if ($agenda === false) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_action')
			]);
			return;
		}

		// Save timezone to user's locale
		$current_time = gmdate('Y-m-d H:i:s');

		switch ($this->input->post('status')) {
			case 'inprogress':
				if ($agenda->status != 'open') {
					echo json_encode([
						'message_type' => 'danger',
						'message' => lang('st_invalid_agenda_status')
					]);

					return;
				}

				// We can only start 1 agenda at a time
				$agenda_in_progress = $this->agenda_model->select('agendas.*, u.timezone')
								->join('meetings s', 's.meeting_id = agendas.meeting_id')
								->join('actions a', 'a.action_id = s.action_id')
								->join('projects p', 'a.project_id = p.project_id')
								->join('user_to_organizations uto', 'uto.organization_id = p.organization_id AND uto.user_id = ' . $this->current_user->user_id)
								->join('users u', 'u.user_id = ' . $this->current_user->user_id)
								->where('s.owner_id', $this->current_user->user_id)
								->where('agendas.status', 'inprogress')
								->limit(1)
								->find_by('agendas.meeting_id', $agenda->meeting_id);

				if ($agenda_in_progress) {
					echo json_encode([
						'message_type' => 'danger',
						'message' => lang('st_please_finish_other_agenda')
					]);
					return;
				}
				
				$this->agenda_model->update($agenda->agenda_id, [
					'status' => 'inprogress', 
					'time_assigned' => $this->input->post('time_assigned'), 
					'started_on' => $current_time,
					'modified_by' => $this->current_user->user_id
				]);
				$this->mb_project->notify_members($agenda->agenda_id, 'agenda', $this->current_user, 'update_status');
				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_agenda_started'),
					'started_on' => $current_time
				]);

				break;

			case 'jumped':
				if ($agenda->status != 'inprogress') {
					echo json_encode([
						'message_type' => 'danger',
						'message' => lang('st_invalid_agenda_status')
					]);

					return;
				}

				$this->agenda_model->update($agenda->agenda_id, [
					'status' => 'jumped', 
					'finished_on' => $current_time, 
					'modified_by' => $this->current_user->user_id
				]);
				$this->mb_project->notify_members($agenda->agenda_id, 'agenda', $this->current_user, 'update_status');
				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_agenda_jumped')
				]);

				break;
			case 'skipped':

				if ($agenda->status != 'open') {
					echo json_encode([
						'message_type' => 'danger',
						'message' => lang('st_invalid_agenda_status')
					]);

					return;
				}

				$this->agenda_model->update($agenda->agenda_id, [
					'status' => 'skipped', 
					'modified_by' => $this->current_user->user_id
				]);
				$this->mb_project->notify_members($agenda->agenda_id, 'agenda', $this->current_user, 'update_status');
				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_agenda_skipped')
				]);

				break;

			case 'resolved':
				if ($agenda->status != 'inprogress') {
						echo json_encode([
							'message_type' => 'danger',
							'message' => lang('st_invalid_agenda_status')
						]);

						return;
				}

				$this->agenda_model->update($agenda->agenda_id, [
					'status' => 'resolved',
					'finished_on' => $current_time, 
					'comment' => $this->input->post('comment'),
					'modified_by' => $this->current_user->user_id
				]);
				$this->mb_project->notify_members($agenda->agenda_id, 'agenda', $this->current_user, 'update_status');
				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_agenda_resolved')
				]);

				break;

			case 'parking_lot':
				if ($agenda->status != 'inprogress') {
						echo json_encode([
							'message_type' => 'danger',
							'message' => lang('st_invalid_agenda_status')
						]);

						return;
				}

				$this->agenda_model->update($agenda->agenda_id, [
					'status' => 'parking_lot',
					'finished_on' => $current_time, 
					'comment' => $this->input->post('comment'),
					'modified_by' => $this->current_user->user_id
				]);
				$this->mb_project->notify_members($agenda->agenda_id, 'agenda', $this->current_user, 'update_status');
				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_agenda_placed')
				]);

				break;

			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_agenda_status')
			]);
			return;
		}
	}

	public function update_status($meeting_key = null)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (empty($meeting_key)) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_update_status_fail')
			]);
			exit;
		}

		$meeting_id = $this->meeting_model->get_meeting_id($meeting_key, $this->current_user->current_organization_id);
		if (! $meeting_id) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_update_status_fail')
			]);
			exit;
		}

		$buttons = [
			'open' => [
				'icon' => 'ion-ios-play',
				'label' => lang('st_start_meeting'),
				'next_status' => 'inprogress',
			],
			'in-progress' => [
				'icon' => 'ion-android-done',
				'label' => lang('st_ready'),
				'next_status' => 'ready',
			],
			'ready-for-review' => [
				'icon' => 'ion-android-done-all',
				'label' => lang('st_resolve_meeting'),
				'next_status' => 'resolved',
			],
			'resolved' => [
				'icon' => 'ion-ios-book',
				'label' => lang('st_reopen'),
				'next_status' => 'open',
			]
		];

		$status = $this->input->post('status');
		$updated = $this->meeting_model->skip_validation(true)->update($meeting_id, [
										'status' => $status
									]);
		if (! $updated) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => $status//lang('st_update_status_fail')
			]);
			exit;
		}
		$this->mb_project->notify_members($meeting_id, 'meeting', $this->current_user, 'update_status');
		echo json_encode([
			'message_type' => 'success',
			'message' => lang('st_update_status_success')
		]);
		exit;
	}

	public function add_team_member($meeting_key = null)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (empty($meeting_key)) {
			echo 0;
			exit;
		}

		$meeting_id = $this->meeting_model->get_meeting_id($meeting_key, $this->current_user->current_organization_id);
		if (! $meeting_id) {
			echo 0;
			exit;
		}

		$user_id = $this->input->post('user_id');

		if ($user_id === NULL || $meeting_id === NULL) {
			echo 0;
			return;
		}

		if (! class_exists('User_model')) {
			$this->load->model('users', 'user_model');
		}
		// Is the target user inside current user's organization?
		$check = $this->user_model->join('user_to_organizations uto', 'uto.user_id = users.user_id')
									->where('uto.organization_id', $this->current_user->current_organization_id)
									->count_by('users.user_id', $user_id);

		if ($check === 0) {
			echo 0;
			return;
		}

		// Prevent duplicate row by MySQL Insert Ignore
		$query = $this->db->insert_string('meeting_members', ['user_id' => $user_id, 'meeting_id' => $meeting_id]);
		$query = str_replace('INSERT', 'INSERT IGNORE', $query);
		echo (int) $this->db->query($query);
	}

	public function remove_team_member($meeting_key = null)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (empty($meeting_key)) {
			echo 0;
			exit;
		}

		$meeting_id = $this->meeting_model->get_meeting_id($meeting_key, $this->current_user->current_organization_id);
		if (! $meeting_id) {
			echo 0;
			exit;
		}

		$user_id = $this->input->post('user_id');

		if ($user_id === NULL || $meeting_id === NULL) {
			echo 0;
			return;
		}

		// Prevent duplicate row by MySQL Insert Ignore
		echo (int) $this->meeting_member_model->delete_where(['user_id' => $user_id, 'meeting_id' => $meeting_id]);
	}

	public function evaluator($meeting_key)
	{
		if (! $this->input->is_ajax_request()) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (empty($meeting_key)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$keys = explode('-', $meeting_key);
		if (empty($keys) || count($keys) < 3) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$meeting_id = $this->mb_project->get_object_id('meeting', $meeting_key);

		if (empty($meeting_id)) {
			Template::set_message(lang('st_meeting_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (! $this->mb_project->has_permission('meeting', $meeting_id, 'Project.Edit.All')) {
			$this->auth->restrict();
		}

		/*
			To access Meeting Monitor, user must be owner or team member of Meeting
		*/

		$meeting = $this->meeting_model->select('*, (actual_end_time - actual_start_time) / 60 AS actual_elapsed_time')->find($meeting_id);

		if (! $meeting) {
			Template::set_message(lang('st_invalid_meeting_key'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$evaluated = $this->is_evaluated($meeting_id);

		if ($evaluated === true || $meeting->manage_state != 'evaluate') {
			Template::set('message', $evaluated === true ? lang('st_meeting_already_evaluated') : lang('st_meeting_not_ready_for_evaluate'));
			Template::set('message_type', 'danger');
			Template::set('close_modal', 1);
		}

		$meeting->members = $this->meeting_member_model
							->select('u.user_id, avatar, email, first_name, last_name')
							->join('users u', 'u.user_id = meeting_members.user_id')
							->where('u.user_id !=', $this->current_user->user_id)
							->where('meeting_id', $meeting_id)
							->as_array()
							->find_all();

		$agendas = $this->agenda_model->select('agendas.*, 
											IF((SELECT tv.user_id FROM mb_agenda_votes tv WHERE mb_agendas.agenda_id = tv.agenda_id AND tv.user_id = "'. $this->current_user->user_id .'") IS NOT NULL, 1, 0) AS voted_skip,
											(SELECT COUNT(*) FROM mb_agenda_votes tv WHERE mb_agendas.agenda_id = tv.agenda_id) AS skip_votes', false)
									->join('users u', 'u.user_id = agendas.owner_id', 'left')
									->where('meeting_id', $meeting->meeting_id)->find_all();
		if (is_array($agendas) && count($agendas) > 0) {
			foreach ($agendas as &$agenda) {
				$agenda->members = $this->agenda_member_model
									->select('avatar, email, first_name, last_name')
									->join('users u', 'u.user_id = agenda_members.user_id')
									->where('agenda_id', $agenda->agenda_id)
									->find_all();
			}
		}

		if ($evaluated === false || $meeting->manage_state == 'evaluate') {
			if ($this->input->post()) {
				if (! is_array($this->input->post('attendee_rate'))
				|| count($this->input->post('attendee_rate')) != count($meeting->members)
				|| ! is_array($this->input->post('agenda_rate'))
				|| count($this->input->post('agenda_rate')) != count($agendas)) {
					$validation_error = true;
				}

				if (empty($validation_error)) {
					if (count($this->input->post('attendee_rate')) > 0) {
						$attendee_rate_data = [];
						foreach ($this->input->post('attendee_rate') as $attendee_id => $rate) {
							$attendee_rate_data[] = [
								'meeting_id' => $meeting_id,
								'user_id' => $this->current_user->user_id,
								'attendee_id' => $attendee_id,
								'rate' => $rate
							];
						}

						$attendees_rated = $this->meeting_member_rate_model->insert_batch($attendee_rate_data);
						if (empty($attendees_rated)) {
							$insert_error = true;
						}
					}

					if (count($this->input->post('agenda_rate'))) {
						$agenda_rate_data = [];
						foreach ($this->input->post('agenda_rate') as $agenda_id => $rate) {
							$agenda_rate_data[] = [
								'agenda_id' => $agenda_id,
								'user_id' => $this->current_user->user_id,
								'rate' => $rate
							];
						}

						$agendas_rated = $this->agenda_rate_model->insert_batch($agenda_rate_data);
						if (empty($agendas_rated)) {
							$insert_error = true;
						}
					}

					if (empty($insert_error)) {
						Template::set('message', lang('st_rating_success'));
						Template::set('message_type', 'success');
						Template::set('close_modal', 0);
						$this->done_meeting_if_qualified($meeting);
					}
				}
			} else {
				$validation_error = true;
			}
		}

		if (! empty($validation_error)) {
			Template::set('message', lang('st_need_to_vote_all_agendas_and_attendees'));
			Template::set('message_type', 'danger');
			Template::set('close_modal', 0);
		}

		if (! empty($insert_error)) {
			Template::set('message', lang('st_there_was_a_problem_while_rating_attendees_and_agendas'));
			Template::set('message_type', 'danger');
			Template::set('close_modal', 0);
		}

		$point_used = number_format($this->mb_project->total_point_used('meeting', $meeting_id), 2);
		Template::set('point_used', $point_used);
		Template::set('meeting', $meeting);
		Template::set('agendas', $agendas);
		Template::render('ajax');
	}

	public function check_state($meeting_key)
	{
		if (! $this->input->is_ajax_request()) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (empty($meeting_key)) {
			echo 0;
			exit;
		}

		$meeting_id = $this->mb_project->get_object_id('meeting', $meeting_key);

		if (empty($meeting_id)) {
			echo 0;
			exit;
		}

		$meeting = $this->meeting_model->find($meeting_id);
		if ($meeting->manage_state != 'evaluate') {
			echo 0;
			exit;
		}

		echo 1;
		exit;
	}

	public function dashboard_evaluate($mode = 'user')
	{
		if (! $this->input->is_ajax_request()) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$modes = ['agenda', 'user'];

		if (! in_array($mode, $modes)) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_wrong_mode')
			]); return;
		}

		if ($mode == 'user') {
			$user_id = $this->input->post('user_id');
			$meeting_id = $this->input->post('meeting_id');
			$rate = $this->input->post('rate');

			if (empty($user_id) || empty($rate) || empty($meeting_id)) {
				echo json_encode([
					'message_type' => 'danger',
					'message' => lang('st_missing_data')
				]); return;
			}

			$manage_state = $this->meeting_model->get_field($meeting_id, 'manage_state');
			$evaluated = $this->is_evaluated($meeting_id);

			if ($evaluated === false || $manage_state == 'evaluate') {
				$added = $this->meeting_member_rate_model->skip_validation(true)->insert([
					'meeting_id' => $meeting_id,
					'user_id' => $this->current_user->user_id,
					'attendee_id' => $user_id,
					'rate' => $rate
				]);

				$meeting = $this->meeting_model->select('owner_id')->find($meeting_id);
				$meeting->meeting_id = $meeting_id;
				$meeting->members = $this->meeting_member_model
										->select('u.user_id, avatar, email, first_name, last_name')
										->join('users u', 'u.user_id = meeting_members.user_id')
										->where('u.user_id !=', $this->current_user->user_id)
										->where('meeting_id', $meeting_id)
										->as_array()
										->find_all();

				$this->done_meeting_if_qualified($meeting);
				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_rate_success')
				]); return;
			} else {
				echo json_encode([
					'message_type' => 'danger',
					'message' => lang('st_evaluated')
				]); return;
			}
		}
	}

	private function ajax_meeting_data($meeting_id)
	{
		$data = $this->meeting_model->select('meetings.*, CONCAT(first_name, " ", last_name) AS full_name, first_name, last_name, avatar, email')
									->join('users u', 'u.user_id = owner_id', 'LEFT')
									->limit(1)
									->find($meeting_id);

		if ($data) {
			$data->display_user = display_user($data->email, $data->first_name, $data->last_name, $data->avatar);
			$data->lang_status = lang('st_' . $data->status);
		}

		return $data;
	}

	private function done_meeting_if_qualified($meeting)
	{
		$team_ids[] = $meeting->owner_id;
		if (! empty($meeting->members)) {
			$members = (array) $meeting->members;
			$team_ids = array_merge($team_ids, array_column($members, 'user_id'));
		}

		$team_ids = array_unique($team_ids);
		// $can_done = $this->meeting_member_rate_model
		// 				->select('user_id')
		// 				->where('meeting_id', $meeting->meeting_id)
		// 				->where_in('user_id', $team_ids)
		// 				->group_by('user_id')
		// 				->count_all() == count($team_ids) ? true : false;

		$time_voted_by_members = pow((count($members) - 1), 2);
		$time_voted_by_owner = count($members);

		if (in_array($meeting->owner_id, array_column($members, 'user_id'))) {
			$time_voted_by_owner = 0;
		}

		$can_done = $this->meeting_member_rate_model
						->select('user_id')
						->where('meeting_id', $meeting->meeting_id)
						->where_in('user_id', $team_ids)
						->count_all() == ($time_voted_by_members + $time_voted_by_owner) ? true : false;

		if ($can_done) {
			$this->meeting_model->skip_validation(true)->update($meeting->meeting_id, ['manage_state' => 'done']);
		}
	}

	private function is_evaluated($meeting_id) {
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

		$evaluated = false;

		$evaluated_members = $this->meeting_member_rate_model
								->select('user_id')
								->where('meeting_id', $meeting_id)
								->where('user_id', $this->current_user->user_id)
								->as_array()
								->find_all();

		$meeting_members = $this->meeting_member_model
							->select('user_id')
							->where('meeting_id', $meeting_id)
							->as_array()
							->find_all();

		if (is_array($evaluated_members) && count($evaluated_members) > 0 && is_array($meeting_members) && count($meeting_members) > 0) {
			$evaluated_ids = array_column($evaluated_members, 'user_id');
			$member_ids = array_column($meeting_members, 'user_id');

			if ((in_array($this->current_user->user_id, $member_ids) && count($evaluated_ids) == (count($member_ids) - 1))
			|| ((! in_array($this->current_user->user_id, $member_ids)) && count($evaluated_ids) == (count($member_ids)))) {
				$evaluated = true;
			}
		}

		return $evaluated;
	}
}