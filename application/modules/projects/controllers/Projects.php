<?php defined('BASEPATH') || exit('No direct script access allowed');
// before each method need to verify user
class Projects extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('projects');
		$this->load->library('project');
		$this->load->library('form_validation');
		$this->load->library('invite/invitation');
		$this->load->helper('mb_form_helper');
		$this->load->helper('mb_general');
		$this->load->model('users/user_model');
		$this->load->model('project_model');
		$this->load->model('project_constraint_model');
		$this->load->model('project_expectation_model');
		$this->load->model('project_member_model');
		$this->load->model('step/step_model');
		$this->load->model('action/action_model');

		Assets::add_module_js('projects', 'projects.js');
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

	public function create()
	{
		// Get invite emails
		Template::set('invite_emails', $this->user_model->get_organization_members($this->current_user->current_organization_id));

		if (isset($_POST['save'])) {
			if ($this->save_project()) {
				Template::set('close_modal', 1);
				Template::set('message_type', 'success');
				Template::set('message', lang('pj_project_successfully_created'));

				// Just to reduce AJAX request size
				if ($this->input->is_ajax_request()) {
					Template::set('content', '');
				}
				
				Template::render();
				return;
			} else {
				Template::set('close_modal', 0);
				Template::set('message_type', 'danger');
				Template::render();
				return;
			}
		}

		Template::set('close_modal', 0);
		Template::set('message_type', null);
		Template::set('message', '');
		Template::render();
	}

	private function save_project($type = 'insert')
	{
		$data = $this->input->post();
		$project_data = $this->project_model->prep_data($data);

		$constraint_rules = $this->project_constraint_model->project_validation_rules;
		foreach ($constraint_rules as &$rule) {
			$rule['field'] = "constraints[{$rule['field']}]";
		}

		$expectation_rules = $this->project_expectation_model->project_validation_rules;
		foreach ($expectation_rules as &$rule) {
			$rule['field'] = "expectations[{$rule['field']}]";
		}

		$this->form_validation->set_rules(array_merge(
			$this->project_model->project_validation_rules,
			$constraint_rules,
			$expectation_rules
		));

		if ($this->form_validation->run() === false) {
			logit('form_validation false');
			Template::set('message', lang('pj_there_was_a_problem_while_creating_project'));
			return false;
		}

		$check_cost_code = $this->project_model->where('organization_id', $this->current_user->current_organization_id)->find_by('cost_code', $project_data['cost_code']);

		if ($check_cost_code !== false) {
			Template::set('message', lang('pj_duplicated_cost_code'));
			return false;
		}


		if ($type == 'insert') {
			$project_data['organization_id'] = $this->current_user->current_organization_id;
			$project_data['owner_id'] = $project_data['created_by'] = $this->current_user->user_id;
			$project_data['cost_code'] = strtoupper($project_data['cost_code']);

			$project_id = $this->project_model->insert($project_data);

			if ($project_id === false) {
				logit('project_id false');
				return false;
			}

			$data['constraints']['project_id'] = $project_id;
			$data['expectations']['project_id'] = $project_id;

			$this->project_constraint_model->insert($data['constraints']);
			$this->project_expectation_model->insert($data['expectations']);

			/*
				For now, we're going to add invited members immediately into project members
				because all of their account is already created and is in inviter's organization

				We need to point the unregistered emails to the "User invite" after functionality is finished.
			*/

			$project_members = [];
			$project_members[$this->current_user->user_id] = [
					'project_id' => $project_id,
					'user_id' => $this->current_user->user_id
			];

			$invited_team = $this->input->post('invite_team');
			$invited_team = explode(',', $invited_team);

			$registered_users = $this->user_model->select('users.user_id, email')
									->join('user_to_organizations uto', 'users.user_id = uto.user_id AND enabled = 1 AND organization_id = ' . $this->current_user->current_organization_id, 'RIGHT')
									->where_in('email', $invited_team)
									->find_all();

			if ($invited_team) {
				foreach ($invited_team as $email) {
					if (! $registered_users) {
						// $this->invitation->generate($email, $this->current_user);
						continue;
					}

					foreach ($registered_users as $user) {
						$is_found = false;

						if ($user->email == $email) {
							$project_members[$user->user_id] = [
								'project_id' => $project_id,
								'user_id' => $user->user_id
							];

							$is_found = true;
							break;
						}

						// Invite to the party
						if ( ! $is_found) {
							// $this->invitation->generate($email, $this->current_user);
						}
					}
				}
			}

			$this->project_member_model->insert_batch($project_members);
		} else {

		}

		return true;
	}

	public function detail($project_key = null)
	{
		/***************** PROJECT AND USER CHECK *****************/
		if ($project_key == null) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$project_id = $this->project->get_object_id('project', $project_key);

		if (! $this->project->has_permission('project', $project_id, 'Project.View.All')) {
			$this->auth->restrict();
		}

		$project = $this->project_model->get_project_by_key($project_key, $this->current_user->current_organization_id, 'projects.*, u.email, u.avatar, CONCAT(u.first_name, u.last_name) as full_name');
		if ($project === false) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$project_id = $project->project_id;

		if ($this->project_model->is_project_owner($project_id, $this->current_user->user_id) === false
		&& $this->project_member_model->is_project_member($project_id, $this->current_user->user_id) === false
		&& $this->auth->has_permission('Project.View.All') === false) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}
		/***************** PROJECT DETAIL *****************/
		$constraint = $this->project_constraint_model->find($project_id);
		$expectation = $this->project_expectation_model->find($project_id);

		Template::set('detail', [
			'constraint' => $constraint,
			'expectation' => $expectation,
			'project' => $project
		]);
		/*---------------------------------- INFO TAB ----------------------------------*/
		/***************** PAGINATION *****************/
		$this->load->library('pagination');
		// general pagination config
		$pagination_config = [
			'base_url' => current_url(),
			'per_page' => 5,
			'use_page_numbers' => true,
			'page_query_string' => true,
			'reuse_query_string' => true,
			'enable_query_strings' => true,
			'full_tag_open' => '<ul class="pagination">',
			'full_tag_close' => '</ul>',
			'num_tag_open' => '<li>',
			'num_tag_close' => '</li>',
			'cur_tag_open' => '<li><a class="active">',
			'cur_tag_close' => '</a></li>',
			'prev_link' => '<span aria-hidden="true"><i class="ion-chevron-left"></i></span>',
			'prev_tag_open' => '<li>',
			'prev_tag_close' => '</li>',
			'next_link' => '<span aria-hidden="true"><i class="ion-chevron-right"></i></span>',
			'next_tag_open' => '<li>',
			'next_tag_close' => '</li>',
			'last_link' => lang('pj_info_pager_last'),
			'last_tag_open' => '<li>',
			'last_tag_close' => '</li>',
			'first_link' => lang('pj_info_pager_first'),
			'first_tag_open' => '<li>',
			'first_tag_close' => '</li>'
		];

		// pagination for actions
		$config_actions = $pagination_config;
		$config_actions['query_string_segment'] = 'actions_page';
		$config_actions['total_rows'] = $this->project_model->count_actions($project_id);
		$this->pagination->initialize($config_actions);
		// generate links
		$actions_links = $this->pagination->create_links();

		// pagination for steps
		$config_steps = $pagination_config;
		$config_steps['query_string_segment'] = 'steps_page';
		$config_steps['total_rows'] = $this->project_model->count_steps($project_id);
		$this->pagination->initialize($config_steps);
		// generate links
		$steps_links = $this->pagination->create_links();

		// pagination for tasks
		$config_tasks = $pagination_config;
		$config_tasks['query_string_segment'] = 'tasks_page';
		$config_tasks['total_rows'] = $this->project_model->count_tasks($project_id);
		$this->pagination->initialize($config_tasks);
		// generate links
		$tasks_links = $this->pagination->create_links();

		/***************** GET DATA *****************/
		// get actions current page
		$actions_current_page = 1;
		if (! empty($this->input->get('actions_page'))) {
			$actions_current_page = $this->input->get('actions_page');
		}
		// get actions list
		$actions = $this->project_model->get_actions($project_id, $pagination_config['per_page'], ($actions_current_page - 1) * $pagination_config['per_page']);

		// @TODO need to optimize query
		$project->total_project_point_used = 0;
		if ($actions) {
			foreach ($actions as &$action) {
				$point_used = $this->step_model->select('CAST(SUM(`cost_of_time` * `in`) AS DECIMAL(10,1)) AS point_used', false)
													->join('step_members sm', 'steps.step_id = sm.step_id')
													->join('user_to_organizations uto', 'uto.user_id = sm.user_id')
													->where('action_id', $action->action_id)
													->find_all();

				$action->point_used = $point_used && count($point_used) > 0 && is_numeric($point_used[0]->point_used) ? $point_used[0]->point_used : 0;

				$project->total_project_point_used += $action->point_used;
			}
		}

		// get steps current page
		$steps_current_page = 1;
		if (! empty($this->input->get('steps_page'))) {
			$steps_current_page = $this->input->get('steps_page');
		}
		// get steps list
		$steps = $this->project_model->get_steps($project_id, $pagination_config['per_page'], ($steps_current_page - 1) * $pagination_config['per_page']);

		// @TODO need to optimize query
		if ($steps) {
			foreach ($steps as &$step) {
				$point_used = $this->step_model->select('CAST(SUM(`cost_of_time` * `in`) AS DECIMAL(10,1)) AS point_used', false)
								->join('step_members sm', 'steps.step_id = sm.step_id')
								->join('user_to_organizations uto', 'uto.user_id = sm.user_id')
								->where('sm.step_id', $step->step_id)
								->find_all();

				$step->point_used = $point_used && count($point_used) > 0 && is_numeric($point_used[0]->point_used) ? $point_used[0]->point_used : 0;
			}
		}

		// get tasks current page
		$tasks_current_page = 1;
		if (! empty($this->input->get('tasks_page'))) {
			$tasks_current_page = $this->input->get('tasks_page');
		}
		// get tasks list
		$tasks = $this->project_model->get_tasks($project_id, $pagination_config['per_page'], ($tasks_current_page - 1) * $pagination_config['per_page']);

		Template::set('info_tab_data', [
			'paginations' => [
				'actions' => $actions_links,
				'steps' => $steps_links,
				'tasks' => $tasks_links,
			],
			'lists' => [
				'actions' => $actions,
				'steps' => $steps,
				'tasks' => $tasks,
			]
		]);

		/*---------------------------------- Action TAB ----------------------------------*/
		Template::set('action_tab_data', [
			'actions' => $this->get_actions($project_id)
		]);

		/*---------------------------------- Report TAB ----------------------------------*/
		Template::set('report_tab_data', []);

		if (! function_exists('avatar_url')) {
			$this->load->helper('mb_general');
		}

		Assets::add_module_css('action', 'action.css');
		Assets::add_module_js('action', 'action.js');
		Assets::add_module_css('projects', 'projects.css');
		Assets::add_module_js('projects', 'action_board.js');
		Template::set('project_name', $project->name);
		Template::set('project_key', $project_key);
		Template::set_view('detail');
		Template::render();
	}

	public function sort_action($project_key = null)
	{
		if (! $this->input->is_ajax_request()) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$project_id = $this->project_model->get_project_id($project_key, $this->current_user->current_organization_id);

		// $project_id = 1; // test
		$action_id = trim($this->input->get('action_id'));
		$status = trim($this->input->get('status'));
		$status_order = trim($this->input->get('status_order'));

		if (empty($project_id) || $action_id == '' || $status == '' || $status_order == '') {
			echo json_encode([
				'status' => '0',
				'message' => 'failed at position 1'
			]);
			exit;
		}

		$action = $this->db->select('status, sort_order')
						->where('project_id', $project_id)
						->where('action_id', $action_id)
						->get('actions')->row();
		if (! $action) {
			echo json_encode([
				'status' => '0',
				'message' => 'failed at position 2'
			]);
			exit;
		}

		try {
			$this->db->trans_begin();

			$old_status_order_updated = $this->db->where('action_id !=', $action_id)
												->where('status', $action->status)
												->where('sort_order >=', $action->sort_order)
												->set('sort_order', '`sort_order`-1', false)
												->set('modified_on', date('Y-m-d H:i:s'))
												->update('actions');
			if (! $old_status_order_updated) {
				throw new Exception('failed at position 3');
			}

			$new_status_order_updated = $this->db->where('action_id !=', $action_id)
												->where('status', $status)
												->where('sort_order >=', $status_order)
												->set('sort_order', '`sort_order`+1', false)
												->set('modified_on', date('Y-m-d H:i:s'))
												->update('actions');
			if (! $new_status_order_updated) {
				throw new Exception('failed at position 4');
			}

			$action_updated = $this->db->where('action_id', $action_id)
									->update('actions', [
										'status' => $status,
										'sort_order' => $status_order,
										'modified_on' => date('Y-m-d H:i:s'),
									]);
			if (! $action_updated) {
				throw new Exception('failed at position 5');
			}

			if ($this->db->trans_status() === FALSE) {
				throw new Exception('failed at position 6');
			} else {
				$this->db->trans_commit();
			}
		} catch (Exception $e) {
			$this->db->trans_rollback();

			echo json_encode([
				'status' => '0',
				'message' => $e->getMessage()
			]);
			exit;
		}

		echo json_encode([
			'status' => '1',
			'message' => 'success'
		]);
		exit;
	}

	public function get_action_board_data($project_key = null)
	{
		if (! $this->input->is_ajax_request()) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$project_id = $this->project_model->get_project_id($project_key, $this->current_user->current_organization_id);
		// $project_id = 1; // test
		if ($project_id !== false) {
			$actions = $this->get_actions($project_id);
		} else {
			$actions = [
				'open' => [],
				'inprogress' => [],
				'ready' => [],
				'resolved' => []
			];
		}

		echo json_encode($actions);
		exit;
	}

	public function get_members($project_key = null)
	{
		if (! $this->input->is_ajax_request()) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$project_id = $this->project_model->get_project_id($project_key, $this->current_user->current_organization_id);
		if ($project_id !== false) {
			$members = $this->db->select('u.user_id, CONCAT(u.first_name, u.last_name) as full_name')
								->from('users u')
								->join('project_members pm', 'u.user_id = pm.user_id', 'inner')
								->like('CONCAT(u.first_name, u.last_name)', $this->input->get('member_name'))
								->where('pm.project_id', $project_id)
								->get()->result();
			$result = $members;
		} else {
			$result = [];
		}

		echo json_encode($result);
		exit;
	}

	public function settings($project_key = null)
	{
		$project = $this->project_model->get_project_by_key($project_key, $this->current_user->current_organization_id, '*', false);

		if ($project === false) {
			redirect(DEFAULT_LOGIN_LOCATION);
		} elseif ($project !== false) {
			if (! class_exists('Role_model')) {
				$this->load->model('roles/role_model');
			}

			if ($this->project_model->is_project_owner($project->project_id, $this->current_user->user_id) === false
			&& $this->project_member_model->is_project_member($project->project_id, $this->current_user->user_id) === false
			&& $this->auth->has_permission('Project.Config.All') === false
			&& $this->auth->has_permission('Project.Config.Joined') === false) {
				Template::set_message(lang('pj_not_have_config_permission') , 'danger');
				redirect(DEFAULT_LOGIN_LOCATION);
			}
		}

		if ($this->input->post()) {
			$rules = $this->project_model->get_validation_rules();
			$this->form_validation->set_rules($rules['settings']);

			if ($this->form_validation->run() !== false) {
				$settings = $this->project_model->prep_data($this->input->post());
				foreach ($settings as $key => $setting) {
					if ($setting == '') {
						$settings[$key] = null;
					}
				}
				$updated = $this->project_model->update($project->project_id, $settings);
				if (! $updated) {
					$error = true;
				}
			} else {
				$error = true;
			}

			if (! empty($error)) {
				Template::set_message(lang('pj_there_was_a_problem_while_updating_project_settings'), 'danger');
			}
		}

		Assets::add_module_css('projects', 'projects.css');
		Template::set('project', $project);
		Template::set('project_key', $project_key);
		Template::render();
	}

	public function update_project_status($project_key)
	{
		if (empty($project_key) || empty($this->input->get('status'))) {
			echo 0;
			exit;
		}
		$project_id = $this->project_model->get_project_id($project_key, $this->current_user->current_organization_id, '*', false);
		if ($project_id !== false) {
			$updated = $this->project_model->update($project_id, ['status' => $this->input->get('status')]);
			if (! $updated) {
				echo 0;
				exit;
			}
		}
		echo 1;
		exit;
	}

	private function get_actions($project_id)
	{
		if (! function_exists('avatar_url')) {
			$this->load->helper('mb_general');
		}
		// get all project actions, sort by sort order
		$all_actions = $this->db->select('a.action_id, a.action_key, a.name, a.status, IF (a.modified_on IS NULL, a.created_on, a.modified_on) AS sort_time, u.avatar, u.email')
								->from('actions a')
								->join('users u', 'u.user_id = a.owner_id', 'LEFT')
								->where('a.project_id', $project_id)
								->order_by('a.sort_order', 'asc')
								->order_by('sort_time', 'desc')
								->get()->result();
		// filter actions by status
		$open = [];
		$inprogress = [];
		$ready = [];
		$resolved = [];
		foreach ($all_actions as $action) {
			$action->avatar_url = avatar_url($action->avatar, $action->email);
			switch ($action->status) {
				case 'inprogress':
					$inprogress[] = $action;
					break;
				case 'ready':
					$ready[] = $action;
					break;
				case 'resolved':
					$resolved[] = $action;
					break;
				default:
					$open[] = $action;
			}
		}

		$actions = [
			'open' => $open,
			'inprogress' => $inprogress,
			'ready' => $ready,
			'resolved' => $resolved
		];

		return $actions;
	}
}