<?php defined('BASEPATH') || exit('No direct script access allowed');
// before each method need to verify user
class Project extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('project');
		$this->load->library('mb_project');
		$this->load->library('form_validation');
		$this->load->library('invite/invitation');
		$this->load->helper('mb_form_helper');
		$this->load->helper('mb_general');
		$this->load->model('users/user_model');
		$this->load->model('project_model');
		$this->load->model('project_constraint_model');
		$this->load->model('project_expectation_model');
		$this->load->model('project_member_model');
		$this->load->model('meeting/meeting_model');
		$this->load->model('action/action_model');

		Assets::add_module_js('project', 'projects.js');
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

	public function create()
	{
		Template::set('close_modal', 0);
		// Get invite emails
		Template::set('invite_emails', $this->user_model->get_organization_members($this->current_user->current_organization_id));

		if (isset($_POST['save'])) {
			;

			if ($project = $this->save_project()) {
				Template::set('close_modal', 1);
				Template::set('message_type', 'success');
				Template::set('message', lang('pj_project_successfully_created'));
				Template::set('data', $project);
				Template::set('content', '');
			} else {
				Template::set('message_type', 'danger');
			}
			Template::render();
			return;
		}

		Template::set('message_type', null);
		Template::set('message', '');
		Template::render();
	}

	public function update($project_key)
	{
		if (! IS_AJAX) {
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
		// Get invite emails
		Template::set('invite_emails', $this->user_model->get_organization_members($this->current_user->current_organization_id));

		$project = $this->project_model->join('project_constraints pc', 'pc.project_id = projects.project_id', 'left')
									->join('project_expectations pe', 'pe.project_id = projects.project_id', 'left')
									->find_by('projects.project_id', $project_id);
		
		$project_members = $this->project_member_model->select('email')
													->join('users u', 'u.user_id = project_members.user_id', 'inner')
													->as_array()
													->find_all_by('project_id', $project_id);
		
		if (! empty($project_members)) {
			$project_members = array_column($project_members, 'email');
			$project->invite_team = implode(',', $project_members);
		}
		Template::set('project', $project);

		if (isset($_POST['save'])) {
			if ($project = $this->save_project('update', $project_id)) {
				Template::set('close_modal', 1);
				Template::set('message_type', 'success');
				Template::set('message', lang('pj_project_successfully_updated'));
				Template::set('data', $project);

				// Just to reduce AJAX request size
				if (IS_AJAX) {
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

	public function join($project_id)
	{
		if (! is_numeric($project_id) || ! has_permission('Project.Edit.All')) {
			echo json_encode([
				'message' => lang('pj_invalid_action'),
				'message_type' => 'danger'
			]);

			return;
		}

		// Add to project members if not in
		// Prevent duplicate row by MySQL Insert Ignore
		$query = $this->db->insert_string('project_members', [
			'project_id' => $project_id,
			'user_id' => $this->current_user->user_id
		]);

		$query = str_replace('INSERT', 'INSERT IGNORE', $query);
		$query = $this->db->query($query);

		echo json_encode([
			'message_type' => 'success'
		]);
	}

	private function save_project($type = 'insert', $project_id = null)
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

		if ($check_cost_code !== false && ($type == 'insert' || ($type == 'update' && $check_cost_code->project_id != $project_id))) {
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
				$new_invite_emails = [];

				foreach ($invited_team as $email) {
					if (! $registered_users) {
						$new_invite_emails = $invited_team;
						break;
					}

					$is_found = false;

					foreach ($registered_users as $user) {
						if ($user->email == $email) {
							$is_found = true;
							$project_members[$user->user_id] = [
								'project_id' => $project_id,
								'user_id' => $user->user_id
							];
							break;
						}
					}

					// Invite to the party
					if ( ! $is_found) {
						$new_invite_emails[] = $email;
					}
				}

				$this->project_member_model->insert_batch($project_members);

				foreach ($invited_team as $email) {
					if (! $registered_users) {
						$message = $this->invitation->send_invitation('project', $project_id, $email, $new_invite_emails);
						continue;
					}

					$is_found = false;
					foreach ($registered_users as $user) {

						if ($user->email == $email) {
							$is_found = true;
							break;
						}

					}

					// Invite to the party
					if ( ! $is_found) {
						$message = $this->invitation->send_invitation('project', $project_id, $email, $new_invite_emails);
					}
				}
			}

			if (isset($message) && $message !== 1) {
				return $message;
			}


			$data['constraints']['project_id'] = $project_id;
			$data['expectations']['project_id'] = $project_id;

			$this->project_constraint_model->insert($data['constraints']);
			$this->project_expectation_model->insert($data['expectations']);

			/*
				Temporary disable Action functionality, auto create an Action after creating Project 
				and automatically uses it as default action for creating Meeting
			*/

			$this->action_model->insert([
				'project_id' => $project_id,
				'action_key' => $project_data['cost_code'] . '-1', // PJK-1
				'owner_id' => $this->current_user->user_id,
				'name' => '[default_action]',
				'action_type' => 'decide',
				'success_condition' => 'action_gate',
				'sort_order' => 999
			]);

			return $this->ajax_project_data($project_id);
		} else {
			if (empty($project_id)) {
				return false;
			}

			$project_data['organization_id'] = $this->current_user->current_organization_id;
			$project_data['owner_id'] = $project_data['created_by'] = $this->current_user->user_id;
			$project_data['cost_code'] = strtoupper($project_data['cost_code']);

			$project_old_cost_code = $this->project_model->get_field($project_id, 'cost_code');
			$project_old_status = $this->project_model->get_field($project_id, 'status');

			$project_updated = $this->project_model->update($project_id, $project_data);
			if ($project_updated === false) {
				logit('update project failed');
				return false;
			}

			if ($project_data['cost_code'] != $project_old_cost_code) {
				$this->update_childs($project_data['cost_code'], $project_id);
			}

			$this->project_constraint_model->update($project_id, $data['constraints']);
			$this->project_expectation_model->update($project_id, $data['expectations']);

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

			$this->project_member_model->delete_where(['project_id' => $project_id]);
			$this->project_member_model->insert_batch($project_members);
			return $this->ajax_project_data($project_id);
		}

		if ($type = 'insert') {
			$this->mb_project->notify_members($id, 'project', $this->current_user, 'insert');
		} else {
			if ((! empty($project_data['status'])) && $project_data['status'] != $project_old_status) {
				$this->mb_project->notify_members($id, 'project', $this->current_user, 'update_status');
			}
		}

		return isset($message) && $message != 1 ? $message : true;
	}

	public function detail($project_key = null)
	{
		/***************** PROJECT AND USER CHECK *****************/
		if ($project_key == null) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$project_id = $this->mb_project->get_object_id('project', $project_key);

		if (! $this->mb_project->has_permission('project', $project_id, 'Project.View.All')) {
			Template::set_message(lang('pj_no_permission_to_access'), 'danger');
			$this->auth->restrict();
		}

		$project = $this->project_model->get_project_by_key($project_key, $this->current_user->current_organization_id, 'projects.*, u.email, u.avatar, u.first_name, u.last_name');
		if ($project === false) {
			Template::set_message(lang('pj_no_permission_to_access'), 'danger');
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

		// pagination for meetings
		$config_meetings = $pagination_config;
		$config_meetings['query_string_segment'] = 'meetings_page';
		$config_meetings['total_rows'] = $this->project_model->count_meetings($project_id);
		$this->pagination->initialize($config_meetings);
		// generate links
		$meetings_links = $this->pagination->create_links();

		// pagination for agendas
		$config_agendas = $pagination_config;
		$config_agendas['query_string_segment'] = 'agendas_page';
		$config_agendas['total_rows'] = $this->project_model->count_agendas($project_id);
		$this->pagination->initialize($config_agendas);
		// generate links
		$agendas_links = $this->pagination->create_links();

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
				$action->point_used = number_format($this->mb_project->total_point_used('action', $action->action_id), 2);

				$project->total_project_point_used += $action->point_used;
			}
		}

		// get meetings current page
		$meetings_current_page = 1;
		if (! empty($this->input->get('meetings_page'))) {
			$meetings_current_page = $this->input->get('meetings_page');
		}
		// get meetings list
		$meetings = $this->project_model->get_meetings($project_id, $pagination_config['per_page'], ($meetings_current_page - 1) * $pagination_config['per_page']);

		// @TODO need to optimize query
		if ($meetings) {
			foreach ($meetings as &$meeting) {
				$meeting->point_used = number_format($this->mb_project->total_point_used('meeting', $meeting->meeting_id), 2);
			}
		}

		// get agendas current page
		$agendas_current_page = 1;
		if (! empty($this->input->get('agendas_page'))) {
			$agendas_current_page = $this->input->get('agendas_page');
		}
		// get agendas list
		$agendas = $this->project_model->get_agendas($project_id, $pagination_config['per_page'], ($agendas_current_page - 1) * $pagination_config['per_page']);

		Template::set('info_tab_data', [
			'paginations' => [
				'actions' => $actions_links,
				'meetings' => $meetings_links,
				'agendas' => $agendas_links,
			],
			'lists' => [
				'actions' => $actions,
				'meetings' => $meetings,
				'agendas' => $agendas,
			]
		]);

		/*---------------------------------- Action TAB ----------------------------------*/
		Template::set('action_tab_data', [
			'actions' => $this->get_actions($project_id)
		]);

		/*---------------------------------- Report TAB ----------------------------------*/
		Template::set('report_tab_data', []);

		Assets::add_module_css('action', 'action.css');
		Assets::add_module_js('action', 'action.js');
		Assets::add_module_css('project', 'projects.css');
		Assets::add_module_js('project', 'action_board.js');
		Template::set('project_name', $project->name);
		Template::set('project_key', $project_key);
		Template::set_view('detail');
		Template::render();
	}

	public function sort_action($project_key = null)
	{
		if (! IS_AJAX) {
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
		if (! IS_AJAX) {
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
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$project_id = $this->project_model->get_project_id($project_key, $this->current_user->current_organization_id);
		if ($project_id !== false) {
			$members = $this->db->select('u.user_id, CONCAT(u.first_name, " ", u.last_name) as full_name')
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

		Assets::add_module_css('project', 'projects.css');
		Template::set('project', $project);
		Template::set('project_key', $project_key);
		Template::render();
	}

	public function update_project_status($project_key)
	{
		if (empty($project_key) || empty($this->input->get('status'))) {
			echo json_encode([
				'message' => lang('pj_invalid_action'),
				'message_type' => 'danger'
			]);
			return;
		}

		if ($project_id = $this->project_model->get_project_id($project_key, $this->current_user->current_organization_id, '*', false)) {
			if (! $this->project_model->update($project_id, ['status' => $this->input->get('status')])) {
				echo json_encode([
					'message' => lang('pj_unable_to_update_status'),
					'message_type' => 'danger'
				]);
				return;
			}
		}

		$this->mb_project->notify_members($project_id, 'project', $this->current_user, 'update_status');

		echo json_encode([
			'message' => lang('pj_project_status_updated'),
			'message_type' => 'success',
			'status' => strtolower($this->input->get('status')),
			'lang_status' => lang('pj_' . strtolower($this->input->get('status')))
		]);

	}

	/**
	*	X-Editable AJAX request to edit project fields
	*	pk: primary key
	*	name: column name
	*	value: new value
	*/
	public function ajax_edit()
	{
		// Validation
		if ( ! in_array($this->input->post('name'), ['name'])) {
				header('HTTP/1.0 403 Forbidden City', true, 403);
				echo lang('hw_unknown_error');
				return;
		}

		// Only member of PJ or Creator can edit
		if (! $this->mb_project->has_permission('project', $this->input->post('pk'), 'Project.Edit.All')) {
			header('HTTP/1.0 401 Unauthorized 💔', true, 401);
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('pj_no_permission_to_edit')
			]);
			return;
		}

		// Update the field
		$update = $this->project_model->update(
			$this->input->post('pk'), [
			$this->input->post('name') => $this->input->post('value')
		]);

		if ($update === false) {
			header('HTTP/1.0 500 Server error 💔', true, 500);
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('pj_unknown_error')
			]);
			return;
		}

		if ($this->input->post('name') == 'name') {
			echo json_encode([
				'message_type' => 'success',
				'message' => lang('pj_project_name_updated')
			]);
		}
	}

	private function get_actions($project_id)
	{
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

	private function update_childs($new_cost_code, $project_id)
	{
		$actions = $this->project_model->get_actions($project_id, null, null, true, null, 'a.action_key, a.action_id', true);
		$meetings = $this->project_model->get_meetings($project_id, null, null, true, null, 's.meeting_key, s.meeting_id', true);
		$agendas = $this->project_model->get_agendas($project_id, null, null, true, null, 't.agenda_key, t.agenda_id', true);

		if (! empty($actions)) {
			foreach ($actions as &$action) {
				$keys = explode('-', $action['action_key']);
				$keys[0] = $new_cost_code;
				$action['action_key'] = implode('-', $keys);
			}

			$this->db->update_batch('actions', $actions, 'action_id');
		}

		if (! empty($meetings)) {
			foreach ($meetings as &$meeting) {
				$keys = explode('-', $meeting['meeting_key']);
				$keys[0] = $new_cost_code;
				$meeting['meeting_key'] = implode('-', $keys);
			}

			$this->db->update_batch('meetings', $meetings, 'meeting_id');
		}

		if (! empty($agendas)) {
			foreach ($agendas as &$agenda) {
				$keys = explode('-', $agenda['agenda_key']);
				$keys[0] = $new_cost_code;
				$agenda['agenda_key'] = implode('-', $keys);
			}

			$this->db->update_batch('agendas', $agendas, 'agenda_id');
		}
	}

	private function ajax_project_data($project_id)
	{
		$project = $this->project_model->select('projects.*, CONCAT(first_name, " ", last_name) AS full_name, first_name, last_name, avatar, email')
									->join('users u', 'u.user_id = owner_id', 'LEFT')
									->limit(1)
									->find($project_id);

		if ($project) {
			$project->display_user = display_user($project->email, $project->first_name, $project->last_name, $project->avatar);
			$project->lang_status = lang('pj_' . $project->status);
		}

		return $project;
	}
}