<?php defined('BASEPATH') || exit('No direct script access allowed');

class Step extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('step');
		$this->load->helper('mb_form');
		$this->load->helper('mb_general');
		$this->load->model('step_model');
		$this->load->model('step_member_model');
		$this->load->model('action/action_model');
		$this->load->model('action/action_member_model');
		$this->load->model('projects/project_model');
		$this->load->model('projects/project_member_model');
		$this->load->model('users/user_model');

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

		$project_members = $this->project_member_model->select('u.user_id, email, first_name, last_name, avatar')
													->join('users u', 'u.user_id = project_members.user_id')
													->join('projects p', 'p.project_id = project_members.project_id')
													->where('p.cost_code', $project_key)
													->find_all();

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

			$this->load->library('project');
			$data['step_key'] = $this->project->get_next_key($action_key);

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
				if ($this->input->is_ajax_request()) {
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

		$step = $this->step_model->select('steps.*')
								->join('actions a', 'a.action_id = steps.action_id')
								->join('projects p', 'a.project_id = p.project_id')
								->join('user_to_organizations uto', 'uto.organization_id = p.organization_id AND uto.user_id = ' . $this->current_user->user_id)
								->limit(1)
								->find_by('step_key', $step_key);

		if ($step === false) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$step_members = $this->step_member_model->where('step_id', $step->step_id)->find_all();
		$step_members = $step_members && count($step_members) > 0 ? array_column($step_members, 'user_id') : [];
		Template::set('step_members', $step_members);
		Template::set('step', $step);

		$project_key = explode('-', $step_key);
		$project_key = $project_key[0];

		$project_members = $this->project_member_model->select('u.user_id, email, first_name, last_name, avatar')
													->join('users u', 'u.user_id = project_members.user_id')
													->join('projects p', 'p.project_id = project_members.project_id')
													->where('p.cost_code', $project_key)
													->find_all();

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
						}

						$this->step_member_model->insert_batch($member_data);
					}
				}

				Template::set('close_modal', 1);
				Template::set('message_type', 'success');
				Template::set('message', lang('st_step_successfully_updated'));

				// Just to reduce AJAX request size
				if ($this->input->is_ajax_request()) {
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
			redirect('/dashboard');
		}

		$keys = explode('-', $step_key);
		if (empty($keys) || count($keys) < 3) {
			redirect('/dashboard');
		}

		$step = $this->step_model->select('steps.*')
								->join('actions a', 'a.action_id = steps.action_id')
								->join('projects p', 'a.project_id = p.project_id')
								->join('user_to_organizations uto', 'uto.organization_id = p.organization_id AND uto.user_id = ' . $this->current_user->user_id)
								->limit(1)
								->find_by('step_key', $step_key);

		if (! $step) {
			redirect('/dashboard');
		}

		$step_id = $step->step_id;

		$project_key = $keys[0];
		$action_key = $keys[0] . '-' . $keys[1];

		$this->load->model('projects/project_model');
		$project_id = $this->project_model->get_project_id($project_key, $this->current_user);

		$step = $this->step_model->select('steps.*, CONCAT(u.first_name, " ", u.last_name) as owner_name')
								->join('users u', 'u.user_id = steps.owner_id', 'left')
								->find_by('step_id', $step_id);

		$this->load->model('task/task_model');
		$tasks = $this->task_model->select('tasks.*, CONCAT(u.first_name, " ", u.last_name) as owner_name')
								->join('users u', 'u.user_id = tasks.owner_id', 'left')
								->where('step_id', $step_id)->find_all();

		if (! class_exists('User_model')) {
			$this->load->model('users', 'user_model');
		}

		$invited_members =  $this->user_model
								->select('uto.user_id, email, CONCAT(first_name, " ", last_name) AS name,
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
									->find_all();

		Assets::add_js($this->load->view('detail_js', ['step_key' => $step_key], true), 'inline');
		Template::set('invited_members', $invited_members);
		Template::set('step', $step);
		Template::set('tasks', $tasks);
		Template::set('project_key', $project_key);
		Template::set('action_key', $action_key);
		Template::set('step_key', $step_key);
		Template::set_view('detail');
		Template::render();
	}

	public function update_status($step_key = null)
	{
		if (! $this->input->is_ajax_request()) {
			redirect('/dashboard');
		}

		if (empty($step_key)) {
			if (! $this->input->is_ajax_request()) {
				redirect('/dashboard');
			} else {
				echo json_encode([
					'message_type' => 'danger',
					'message' => lang('st_update_status_fail')
				]);
				exit;
			}
		}

		$step_id = $this->step_model->get_step_id($step_key, $this->current_user);
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
				'next_status' => 'in-progress',
			],
			'in-progress' => [
				'icon' => 'ion-android-done',
				'label' => lang('st_ready'),
				'next_status' => 'ready-for-review',
			],
			'ready-for-review' => [
				'icon' => 'ion-android-done-all',
				'label' => lang('st_resolve'),
				'next_status' => 'resolved',
			],
			'resolved' => [
				'icon' => 'ion-ios-book',
				'label' => lang('st_reopen'),
				'next_status' => 'open',
			]
		];

		$status = $this->input->get('status');
		$updated = $this->step_model->update($step_id, [
										'status' => $status
									]);
		if (! $updated) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_update_status_fail')
			]);
			exit;
		}

		echo json_encode([
			'message_type' => 'success',
			'message' => lang('st_update_status_success'),
			'data' => array_merge($buttons[$status], ['url' => current_url() . '?status=' . urlencode($buttons[$status]['next_status'])])
		]);
		exit;
	}

	public function add_team_member($step_key = null)
	{
		if (! $this->input->is_ajax_request()) {
			redirect('/dashboard');
		}

		if (empty($step_key)) {
			echo 0;
			exit;
		}

		$step_id = $this->step_model->get_step_id($step_key, $this->current_user);
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
		if (! $this->input->is_ajax_request()) {
			redirect('/dashboard');
		}

		if (empty($step_key)) {
			echo 0;
			exit;
		}

		$step_id = $this->step_model->get_step_id($step_key, $this->current_user);
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
}