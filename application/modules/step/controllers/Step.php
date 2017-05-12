<?php defined('BASEPATH') || exit('No direct script access allowed');

class Step extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('step');
		$this->load->helper('mb_form');
		$this->load->helper('mb_general');
		$this->load->helper('text');
		$this->load->helper('date');
		$this->load->library('mb_project');
		
		$this->load->model('users/user_model');
		$this->load->model('task/task_model');
		$this->load->model('task/task_member_model');
		$this->load->model('task/task_rate_model');

		$this->load->model('step_model');
		$this->load->model('step_member_model');
		$this->load->model('step_member_rate_model');

		$this->load->model('action/action_model');
		$this->load->model('action/action_member_model');

		$this->load->model('project/project_model');
		$this->load->model('project/project_member_model');

		Assets::add_module_css('step', 'step.css');
		Assets::add_module_js('step', 'step.js');
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

	public function create($action_key = null)
	{
		if (empty($action_key)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$action_id = $this->mb_project->get_object_id('action', $action_key);

		if (empty($action_id)) {
			Template::set_message(lang('st_action_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (! $this->mb_project->has_permission('action', $action_id, 'Project.Edit.All')) {
			$this->auth->restrict();
		}

		$action = $this->action_model->select('action_id')
									->join('projects p', 'actions.project_id = p.project_id')
									->join('user_to_organizations uto', 'uto.organization_id = p.organization_id AND uto.user_id = ' . $this->current_user->user_id)
									->limit(1)
									->find_by('action_key', $action_key);

		if ($action === false) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$project_key = explode('-', $action_key);
		$project_key = $project_key[0];

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

		$project_members = $this->user_model->get_organization_members($this->current_user->current_organization_id);

		Assets::add_js($this->load->view('create_js', [
			'project_members' => $project_members
		], true), 'inline');

		if ($data = $this->input->post()) {
			$data = $this->step_model->prep_data($data);
			$data['action_id'] = $action->action_id;
			$data['created_by'] = $this->current_user->user_id;

			if ($this->input->post('owner_id') == '') {
				$data['owner_id'] = $this->current_user->user_id;
			}

			$data['step_key'] = $this->mb_project->get_next_key($action_key);

			if ($id = $this->step_model->insert($data)) {
				if ($team = $this->input->post('team')) {
					if ($team = explode(',', $team)) {
						$member_data = [];
						foreach ($team as $member) {
							$member_data[] = [
								'step_id' => $id,
								'user_id' => $member
							];
						}

						$this->step_member_model->insert_batch($member_data);
					}
				}

				Template::set('close_modal', 1);
				Template::set('message_type', 'success');
				Template::set('message', lang('st_step_successfully_created'));

				// Just to reduce AJAX request size
				if (IS_AJAX) {
					Template::set('content', '');
				}
				
			} else {
				Template::set('close_modal', 0);
				Template::set('message_type', 'danger');
				Template::set('message', lang('st_there_was_a_problem_while_creating_step'));
			}

			Template::render();
			return;
		}


		Template::set('project_members', $project_members);
		Template::set('action_key', $action_key);
		Template::render();
	}

	public function edit($step_key = null)
	{

		if (empty($step_key)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$step_id = $this->mb_project->get_object_id('step', $step_key);

		if (empty($step_id)) {
			Template::set_message(lang('st_step_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (! $this->mb_project->has_permission('step', $step_id, 'Project.Edit.All')) {
			$this->auth->restrict();
		}

		$keys = explode('-', $step_key);
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

		$step = $this->step_model->select('steps.*, p.project_id')
								->join('actions a', 'a.action_id = steps.action_id')
								->join('projects p', 'a.project_id = p.project_id')
								->join('user_to_organizations uto', 'uto.organization_id = p.organization_id AND uto.user_id = ' . $this->current_user->user_id)
								->limit(1)
								->find_by('step_key', $step_key);

		if ($step === false) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$step_members = $this->step_member_model->where('step_id', $step->step_id)->as_array()->find_all();
		$step_members = $step_members && count($step_members) > 0 ? array_column($step_members, 'user_id') : [];
		Template::set('step_members', $step_members);
		Template::set('step', $step);

		$project_key = explode('-', $step_key);
		$project_key = $project_key[0];

		$project_members = $this->user_model->get_organization_members($this->current_user->current_organization_id);

		Template::set('project_members', $project_members);
		Assets::add_js($this->load->view('create_js', [
			'project_members' => $project_members
		], true), 'inline');

		if ($data = $this->input->post()) {
			$data = $this->step_model->prep_data($data);
			$data['modified_by'] = $this->current_user->user_id;

			if ($this->input->post('owner_id') == '') {
				$data['owner_id'] = $this->current_user->user_id;
			}

			// Add to project members if not in
			// Prevent duplicate row by MySQL Insert Ignore
			$query = $this->db->insert_string('project_members', [
				'project_id' => $step->project_id,
				'user_id' => $data['owner_id']
			]);

			$query = str_replace('INSERT', 'INSERT IGNORE', $query);
			$this->db->query($query);

			if ($this->step_model->update($step->step_id, $data)) {
				$this->step_member_model->delete_where(['step_id' => $step->step_id]);

				if ($team = $this->input->post('team')) {
					if ($team = explode(',', $team)) {
						$member_data = [];
						foreach ($team as $member) {
							$member_data[] = [
								'step_id' => $step->step_id,
								'user_id' => $member
							];

							// Add to project members if not in
							// Prevent duplicate row by MySQL Insert Ignore
							$query = $this->db->insert_string('project_members', [
								'project_id' => $step->project_id,
								'user_id' => $member
							]);

							$query = str_replace('INSERT', 'INSERT IGNORE', $query);
							$this->db->query($query);
						}

						$this->step_member_model->insert_batch($member_data);
					}
				}

				Template::set('close_modal', 1);
				Template::set('message_type', 'success');
				Template::set('message', lang('st_step_successfully_updated'));

				// Just to reduce AJAX request size
				if (IS_AJAX) {
					Template::set('content', '');
				}
				
			} else {
				Template::set('close_modal', 0);
				Template::set('message_type', 'danger');
				Template::set('message', lang('st_there_was_a_problem_while_creating_step'));
			}

			Template::render();
			return;
		}


		Template::render();
	}

	public function detail($step_key = null)
	{
		if (empty($step_key)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$keys = explode('-', $step_key);
		if (empty($keys) || count($keys) < 3) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$step_id = $this->mb_project->get_object_id('step', $step_key);

		if (empty($step_id)) {
			Template::set_message(lang('st_step_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (! $this->mb_project->has_permission('step', $step_id, 'Project.View.All')) {
			$this->auth->restrict();
		}

		$step = $this->step_model->select('steps.*')
								->join('actions a', 'a.action_id = steps.action_id')
								->join('projects p', 'a.project_id = p.project_id')
								->join('user_to_organizations uto', 'uto.organization_id = p.organization_id AND uto.user_id = ' . $this->current_user->user_id)
								->limit(1)
								->find_by('step_key', $step_key);
		$project_key = $keys[0];
		$action_key = $keys[0] . '-' . $keys[1];

		$project_id = $this->mb_project->get_object_id('project', $project_key);
		if (empty($project_id)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		// get projecct id
		// $project_id = $this->project_model->get_project_id($project_key, $this->current_user->current_organization_id);
		// if ($project_id === false) {
		// 	redirect(DEFAULT_LOGIN_LOCATION);
		// }

		// if ($this->project_model->is_project_owner($project_id, $this->current_user->user_id) === false
		// && $this->project_member_model->is_project_member($project_id, $this->current_user->user_id) === false
		// && $this->auth->has_permission('Project.View.All') === false) {
		// 	redirect(DEFAULT_LOGIN_LOCATION);
		// }

		$step = $this->step_model->get_step_by_key($step_key, $this->current_user->current_organization_id, 'steps.*, u.email, u.first_name, u.last_name, u.avatar');

		if (! $step) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}
		$step_id = $step->step_id;

		$tasks = $this->task_model->select('tasks.*, u.email, u.first_name, u.last_name, u.avatar')
								->join('users u', 'u.user_id = tasks.owner_id', 'left')
								->where('step_id', $step_id)->find_all();

		if ($tasks) {
			foreach ($tasks as &$task) {
				$task->members = $this->task_member_model->select('avatar, email, first_name, last_name')->join('users u', 'u.user_id = task_members.user_id')->where('task_id', $task->task_id)->find_all();
			}
		}

		$invited_members =  $this->user_model
								->select('uto.user_id, email, first_name, last_name, avatar,
									avatar, cost_of_time, 
									IF(
										uto.cost_of_time = 1, 
										p.cost_of_time_1,
										IF(
											uto.cost_of_time = 2, 
											p.cost_of_time_2,
											IF(
												uto.cost_of_time = 3, 
												p.cost_of_time_3,
												IF(
													uto.cost_of_time = 4, 
													p.cost_of_time_4,
													p.cost_of_time_5
												)
											)
										)
									) AS cost_of_time_name', false)
									->join('step_members sm', 'sm.user_id = users.user_id AND sm.step_id = ' . $step_id)
									->join('user_to_organizations uto', 'users.user_id = uto.user_id AND enabled = 1 AND organization_id = ' . $this->current_user->current_organization_id)
									->join('projects p', 'p.project_id = ' . $project_id)
									->order_by('name')
									->order_by('uto.cost_of_time', 'DESC')
									->as_array()
									->find_all();
									
		$point_used = number_format($this->mb_project->total_point_used('step', $step->step_id), 2);

		Assets::add_js($this->load->view('detail_js', ['step_key' => $step_key], true), 'inline');
		Template::set('invited_members', $invited_members);
		Template::set('point_used', $point_used);
		Template::set('step', $step);
		Template::set('tasks', $tasks);
		Template::set('project_key', $project_key);
		Template::set('action_key', $action_key);
		Template::set('step_key', $step_key);
		Template::set('current_user', $this->current_user);
		Template::set_view('detail');
		Template::render();
	}

	public function monitor($step_key = null)
	{
		if (empty($step_key)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$keys = explode('-', $step_key);
		if (empty($keys) || count($keys) < 3) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$step_id = $this->mb_project->get_object_id('step', $step_key);

		if (empty($step_id)) {
			Template::set_message(lang('st_step_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (! $this->mb_project->has_permission('step', $step_id, 'Project.View.All')) {
			$this->auth->restrict();
		}

		/*
			To access Step Monitor, user must be owner or team member of Step
		*/

		$step = $this->step_model->find_by('step_key', $step_key);

		if (! $step) {
			Template::set_message(lang('st_invalid_step_key'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}


		$tasks = $this->task_model->select('tasks.*, 
											IF((SELECT tv.user_id FROM mb_task_votes tv WHERE mb_tasks.task_id = tv.task_id AND tv.user_id = "'. $this->current_user->user_id .'") IS NOT NULL, 1, 0) AS voted_skip,
											(SELECT COUNT(*) FROM mb_task_votes tv WHERE mb_tasks.task_id = tv.task_id) AS skip_votes', false)
									->join('users u', 'u.user_id = tasks.owner_id', 'left')
									->where('step_id', $step->step_id)->find_all();
		
		// We can't start without Tasks
		if ($tasks === false) {
			Template::set('message_type', 'warning');
			Template::set('message', lang('st_cannot_start_step_without_any_task'));
			Template::set('content', '');
			Template::render();
			return;
		}

		foreach ($tasks as &$task) {
			$task->members = $this->task_member_model->select('avatar, email, first_name, last_name')->join('users u', 'u.user_id = task_members.user_id')->where('task_id', $task->task_id)->find_all();
		}


		Assets::add_js($this->load->view('monitor_js', [
			
		], true), 'inline');
		Template::set('close_modal', 0);
		Template::set('current_user', $this->current_user);
		Template::set('tasks', $tasks);
		Template::set('step', $step);
		Template::set('now', gmdate('Y-m-d H:i:s'));
		Template::render();
	}

	public function resolve_task($task_id = null)
	{
		if (empty($task_id)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		Template::set('id', 'resolve-task');
		Template::set('close_modal', 0);
		Template::set('current_user', $this->current_user);
		Template::set('task_id', $task_id);
		Template::render();
	}

	public function get_monitor_data($step_id)
	{
		if (empty($step_id)) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_step_key')
			]);
			return ;
		}

		$tasks = $this->task_model->select('tasks.task_id, tasks.status, tasks.started_on, tasks.time_assigned, 
											(SELECT COUNT(*) FROM mb_task_votes tv WHERE mb_tasks.task_id = tv.task_id) AS skip_votes', false)
									->join('users u', 'u.user_id = tasks.owner_id', 'left')
									->where('step_id', $step_id)->find_all();

		if ($tasks === false) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_step_key')
			]);
			return;
		}

		echo json_encode([
			'message_type' => 'success',
			'data' => $tasks,
			'step' => $this->step_model->select('status')->limit(1)->find($step_id),
			'current_time' => gmdate('Y-m-d H:i:s')
		]);
	}

	public function vote_skip($task_id)
	{
		// Prevent duplicate row by MySQL Insert Ignore
		$query = $this->db->insert_string('task_votes', [
			'task_id' => $task_id,
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

	public function update_step_schedule() {

		$step = $this->step_model->select('steps.*, u.timezone')
								->join('actions a', 'a.action_id = steps.action_id')
								->join('projects p', 'a.project_id = p.project_id')
								->join('users u', 'u.user_id = ' . $this->current_user->user_id)
								->join('user_to_organizations uto', 'uto.organization_id = p.organization_id AND uto.user_id = ' . $this->current_user->user_id)
								->where('steps.owner_id', $this->current_user->user_id)
								->limit(1)
								->find($this->input->post('step_id'));

		if ($step === false) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_step_key')
			]);
			return;
		}

		// Start step?
		if ($this->input->post('start')) {
			if ($step->status != 'ready') {
				echo json_encode([
					'message_type' => 'danger',
					'message' => lang('st_invalid_step_status')
				]);

				return;
			}

			if ($step->scheduled_start_time === NULL) {
				echo json_encode([
					'message_type' => 'danger',
					'message' => lang('st_invalid_schedule_time')
				]);

				return;
			}

			$current_time = gmdate('Y-m-d H:i:s');
			$query = $this->step_model->skip_validation(1)->update($step->step_id, [
				'status' => 'inprogress',
				'actual_start_time' => $current_time,
			]);

			if ($query) {
				if ( is_array($this->input->post('time_assigned')) ) {
					$task_data = [];
					foreach ($this->input->post('time_assigned') as $task_id => $time_assigned) {
						$task_data[] = [
							'task_id' => $task_id,
							'time_assigned' => $time_assigned
						];
					}

					$this->task_model->skip_validation(1)->update_batch($task_data, 'task_id');
				}

				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_step_started'),
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

		// Finish step
		if ($this->input->post('finish')) {
			if ($step->status != 'inprogress') {
				echo json_encode([
					'message_type' => 'danger',
					'message' => lang('st_invalid_step_status')
				]);

				return;
			}

			$tasks = $this->task_model->select('task_key')->where('step_id', $step->step_id)->where('(status = "inprogress" OR status ="open")', null, false)->find_all();

			if ($tasks) {
				echo json_encode([
					'message_type' => 'danger',
					'message' => lang('st_please_resolve_all_task_before_finish')
				]);

				return;
			}

			$current_time = gmdate('Y-m-d H:i:s');
			$query = $this->step_model->skip_validation(1)->update($step->step_id, [
				'status' => 'finished',
				'actual_end_time' => $current_time,
			]);
			
			if ($query) {
				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_step_finished'),
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

		$query = $this->step_model->skip_validation(1)->update($step->step_id, [
			'status' => 'ready',
			'scheduled_start_time' => $this->input->post('scheduled_start_time')
		]);

		if ($query) {
			if ( is_array($this->input->post('time_assigned')) ) {
				$task_data = [];
				foreach ($this->input->post('time_assigned') as $task_id => $time_assigned) {
					$task_data[] = [
						'task_id' => $task_id,
						'time_assigned' => $time_assigned
					];
				}

				$this->task_model->skip_validation(1)->update_batch($task_data, 'task_id');
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

	public function update_task_status()
	{
		$task = $this->task_model->select('tasks.*, u.timezone, s.step_id')
								->join('steps s', 's.step_id = tasks.step_id')
								->join('actions a', 'a.action_id = s.action_id')
								->join('projects p', 'a.project_id = p.project_id')
								->join('user_to_organizations uto', 'uto.organization_id = p.organization_id AND uto.user_id = ' . $this->current_user->user_id)
								->join('users u', 'u.user_id = ' . $this->current_user->user_id)
								->where('s.owner_id', $this->current_user->user_id)
								->where('s.status', 'inprogress')
								->limit(1)
								->find($this->input->post('task_id'));

		if ($task === false) {
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
				if ($task->status != 'open') {
					echo json_encode([
						'message_type' => 'danger',
						'message' => lang('st_invalid_task_status')
					]);

					return;
				}

				// We can only start 1 task at a time
				$task_in_progress = $this->task_model->select('tasks.*, u.timezone')
								->join('steps s', 's.step_id = tasks.step_id')
								->join('actions a', 'a.action_id = s.action_id')
								->join('projects p', 'a.project_id = p.project_id')
								->join('user_to_organizations uto', 'uto.organization_id = p.organization_id AND uto.user_id = ' . $this->current_user->user_id)
								->join('users u', 'u.user_id = ' . $this->current_user->user_id)
								->where('s.owner_id', $this->current_user->user_id)
								->where('tasks.status', 'inprogress')
								->limit(1)
								->find_by('tasks.step_id', $task->step_id);

				if ($task_in_progress) {
					echo json_encode([
						'message_type' => 'danger',
						'message' => lang('st_please_finish_other_task')
					]);
					return;
				}
				
				$this->task_model->update($task->task_id, [
					'status' => 'inprogress', 
					'time_assigned' => $this->input->post('time_assigned'), 
					'started_on' => $current_time,
					'modified_by' => $this->current_user->user_id
				]);

				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_task_started'),
					'started_on' => $current_time
				]);

				break;

			case 'jumped':
				if ($task->status != 'inprogress') {
					echo json_encode([
						'message_type' => 'danger',
						'message' => lang('st_invalid_task_status')
					]);

					return;
				}

				$this->task_model->update($task->task_id, [
					'status' => 'jumped', 
					'finished_on' => $current_time, 
					'modified_by' => $this->current_user->user_id
				]);

				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_task_jumped')
				]);

				break;
			case 'skipped':

				if ($task->status != 'open') {
					echo json_encode([
						'message_type' => 'danger',
						'message' => lang('st_invalid_task_status')
					]);

					return;
				}

				$this->task_model->update($task->task_id, [
					'status' => 'skipped', 
					'modified_by' => $this->current_user->user_id
				]);

				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_task_skipped')
				]);

				break;

			case 'resolved':
				if ($task->status != 'inprogress') {
						echo json_encode([
							'message_type' => 'danger',
							'message' => lang('st_invalid_task_status')
						]);

						return;
				}

				$this->task_model->update($task->task_id, [
					'status' => 'resolved',
					'comment' => $this->input->post('comment'),
					'modified_by' => $this->current_user->user_id
				]);

				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_task_resolved')
				]);

				break;

			case 'parking_lot':
				if ($task->status != 'inprogress') {
						echo json_encode([
							'message_type' => 'danger',
							'message' => lang('st_invalid_task_status')
						]);

						return;
				}

				$this->task_model->update($task->task_id, [
					'status' => 'parking_lot',
					'comment' => $this->input->post('comment'),
					'modified_by' => $this->current_user->user_id
				]);

				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_task_placed')
				]);

				break;

			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_task_status')
			]);
			return;
		}
	}

	public function update_status($step_key = null)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (empty($step_key)) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_update_status_fail')
			]);
			exit;
		}

		$step_id = $this->step_model->get_step_id($step_key, $this->current_user->current_organization_id);
		if (! $step_id) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_update_status_fail')
			]);
			exit;
		}

		$buttons = [
			'open' => [
				'icon' => 'ion-ios-play',
				'label' => lang('st_start_step'),
				'next_status' => 'inprogress',
			],
			'in-progress' => [
				'icon' => 'ion-android-done',
				'label' => lang('st_ready'),
				'next_status' => 'ready',
			],
			'ready-for-review' => [
				'icon' => 'ion-android-done-all',
				'label' => lang('st_resolve_step'),
				'next_status' => 'resolved',
			],
			'resolved' => [
				'icon' => 'ion-ios-book',
				'label' => lang('st_reopen'),
				'next_status' => 'open',
			]
		];

		$status = $this->input->post('status');
		$updated = $this->step_model->skip_validation(true)->update($step_id, [
										'status' => $status
									]);
		if (! $updated) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => $status//lang('st_update_status_fail')
			]);
			exit;
		}

		echo json_encode([
			'message_type' => 'success',
			'message' => lang('st_update_status_success')
		]);
		exit;
	}

	public function add_team_member($step_key = null)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (empty($step_key)) {
			echo 0;
			exit;
		}

		$step_id = $this->step_model->get_step_id($step_key, $this->current_user->current_organization_id);
		if (! $step_id) {
			echo 0;
			exit;
		}

		$user_id = $this->input->post('user_id');

		if ($user_id === NULL || $step_id === NULL) {
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
		$query = $this->db->insert_string('step_members', ['user_id' => $user_id, 'step_id' => $step_id]);
		$query = str_replace('INSERT', 'INSERT IGNORE', $query);
		echo (int) $this->db->query($query);
	}

	public function remove_team_member($step_key = null)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (empty($step_key)) {
			echo 0;
			exit;
		}

		$step_id = $this->step_model->get_step_id($step_key, $this->current_user->current_organization_id);
		if (! $step_id) {
			echo 0;
			exit;
		}

		$user_id = $this->input->post('user_id');

		if ($user_id === NULL || $step_id === NULL) {
			echo 0;
			return;
		}

		// Prevent duplicate row by MySQL Insert Ignore
		echo (int) $this->step_member_model->delete_where(['user_id' => $user_id, 'step_id' => $step_id]);
	}

	public function evaluator($step_key)
	{
		if (! $this->input->is_ajax_request()) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (empty($step_key)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$keys = explode('-', $step_key);
		if (empty($keys) || count($keys) < 3) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$step_id = $this->mb_project->get_object_id('step', $step_key);

		if (empty($step_id)) {
			Template::set_message(lang('st_step_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (! $this->mb_project->has_permission('step', $step_id, 'Project.Edit.All')) {
			$this->auth->restrict();
		}

		/*
			To access Step Monitor, user must be owner or team member of Step
		*/

		$step = $this->step_model->find_by('step_key', $step_key);

		if (! $step) {
			Template::set_message(lang('st_invalid_step_key'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$step->members = $this->step_member_model
							->select('u.user_id, avatar, email, first_name, last_name')
							->join('users u', 'u.user_id = step_members.user_id')
							->where('u.user_id !=', $this->current_user->user_id)
							->where('step_id', $step_id)
							->find_all();

		$tasks = $this->task_model->select('tasks.*, 
											IF((SELECT tv.user_id FROM mb_task_votes tv WHERE mb_tasks.task_id = tv.task_id AND tv.user_id = "'. $this->current_user->user_id .'") IS NOT NULL, 1, 0) AS voted_skip,
											(SELECT COUNT(*) FROM mb_task_votes tv WHERE mb_tasks.task_id = tv.task_id) AS skip_votes', false)
									->join('users u', 'u.user_id = tasks.owner_id', 'left')
									->where('step_id', $step->step_id)->find_all();
		if (is_array($tasks) && count($tasks) > 0) {
			foreach ($tasks as &$task) {
				$task->members = $this->task_member_model
									->select('avatar, email, first_name, last_name')
									->join('users u', 'u.user_id = task_members.user_id')
									->where('task_id', $task->task_id)
									->find_all();
			}
		}

		if ($this->input->post()) {
			$rules = [
				[
					'field' => 'attendee_rate[]',
					'label' => 'lang:st_attendees',
					'rules' => 'required'
				],
				[
					'field' => 'task_rate[]',
					'label' => 'lang:st_tasks',
					'rules' => 'required',
				],
			];

			$this->form_validation->set_rules($rules);
			if ($this->form_validation->run() !== false) {
				if (count($this->input->post('attendee_rate')) > 0) {
					$attendee_rate_data = [];
					foreach ($this->input->post('attendee_rate') as $attendee_id => $rate) {
						$attendee_rate_data[] = [
							'step_id' => $step_id,
							'user_id' => $this->current_user->user_id,
							'attendee_id' => $attendee_id,
							'rate' => $rate
						];
					}

					$attendees_rated = $this->step_member_rate_model->insert_batch($attendee_rate_data);
					if (empty($attendees_rated)) {
						$insert_error = true;
					}
				}

				if (count($this->input->post('task_rate'))) {
					$task_rate_data = [];
					foreach ($this->input->post('task_rate') as $task_id => $rate) {
						$task_rate_data[] = [
							'task_id' => $task_id,
							'user_id' => $this->current_user->user_id,
							'rate' => $rate
						];
					}

					$tasks_rated = $this->task_rate_model->insert_batch($task_rate_data);
					if (empty($tasks_rated)) {
						$insert_error = true;
					}
				}

				if (empty($insert_error)) {
					Template::set('message', lang('st_rating_success'));
					Template::set('message_type', 'success');
					Template::set('close_modal', 0);
				}

			} else {
				$validation_error = true;
			}
		} else {
			$validation_error = true;
		}

		if (! empty($validation_error)) {
			Template::set('message', lang('st_need_to_vote_all_tasks_and_attendees'));
			Template::set('message_type', 'danger');
			Template::set('close_modal', 0);
		}

		if (! empty($insert_error)) {
			Template::set('message', lang('st_there_was_a_problem_while_rating_attendees_and_tasks'));
			Template::set('message_type', 'danger');
			Template::set('close_modal', 0);
		}

		$point_used = number_format($this->mb_project->total_point_used('step', $step_id), 2);
		Template::set('point_used', $point_used);
		Template::set('step', $step);
		Template::set('tasks', $tasks);
		Template::render('ajax');
	}
}