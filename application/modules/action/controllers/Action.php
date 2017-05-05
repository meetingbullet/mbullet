<?php defined('BASEPATH') || exit('No direct script access allowed');

class Action extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('project');
		$this->lang->load('action');
		$this->load->helper('mb_form');
		$this->load->helper('mb_general');
		$this->load->model('action_model');
		$this->load->model('step/step_model');
		$this->load->model('users/user_model');
		$this->load->model('action_member_model');
		$this->load->model('projects/project_model');
		$this->load->model('projects/project_member_model');

		Assets::add_module_css('action', 'action.css');
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

	public function detail($action_key)
	{
		if (empty($action_key)) {
			Template::set_message(lang('ac_invalid_action_key'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$action_id = $this->project->get_object_id('action', $action_key);

		if (empty($action_id)) {
			Template::set_message(lang('ac_action_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (! $this->project->has_permission('action', $action_id, 'Project.View.All')) {
			$this->auth->restrict();
		}

		$action = $this->action_model->select('actions.*, CONCAT(u.first_name, " ", u.last_name) as owner_name, pm.user_id AS member_id, p.owner_id AS project_owner_id')
									->join('users u', 'u.user_id = actions.owner_id')
									->join('projects p', 'p.project_id = actions.project_id')
									->join('project_members pm', 'pm.project_id = actions.project_id AND pm.user_id = ' . $this->current_user->user_id, 'LEFT')
									->limit(1)
									->find_by('action_key', $action_key);

		/*
			Permission to access this page:
			User must be in the project member or is project owner
		*/
		if (! $action || $action->project_owner_id != $this->current_user->user_id && $action->member_id != $this->current_user->user_id) {
			Template::set_message(lang('ac_invalid_action_key'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$point_used = $this->step_model->select('CAST(SUM(`cost_of_time` * `in`) AS DECIMAL(10,1)) AS point_used', false)
													->join('step_members sm', 'steps.step_id = sm.step_id')
													->join('user_to_organizations uto', 'uto.user_id = sm.user_id')
													->where('action_id', $action->action_id)
													->find_all();

		$action->point_used = $point_used && count($point_used) > 0 && is_numeric($point_used[0]->point_used) ? $point_used[0]->point_used : 0;

		if (isset($_POST['update'])) {
			$next_status = 'resolved';

			if ($action->status == 'open') $next_status = 'inprogress';
			if ($action->status == 'inprogress') $next_status = 'ready';


			$this->action_model->update($action->action_id, ['status' => $next_status]);

			$action = $this->action_model->select('actions.*, CONCAT(u.first_name, " ", u.last_name) as owner_name')
									->join('users u', 'u.user_id = actions.owner_id')
									->limit(1)
									->find_by('action_key', $action_key);
		}

		$steps = $this->step_model->select('steps.*, CONCAT(u.first_name, " ", u.last_name) as owner_name')
									->join('users u', 'u.user_id = steps.owner_id')
									->where('action_id', $action->action_id)
									->order_by('step_id')
									->order_by('status')
									->find_all();	


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
									->join('action_members am', 'am.user_id = users.user_id AND am.action_id = ' . $action->action_id)
									->join('user_to_organizations uto', 'users.user_id = uto.user_id AND enabled = 1 AND organization_id = ' . $this->current_user->current_organization_id)
									->join('projects p', 'p.project_id = ' . $action->project_id)
									->order_by('name')
									->order_by('uto.cost_of_time', 'DESC')
									->find_all();

		Assets::add_js($this->load->view('detail_js', [
			'action_key' => $action_key,
			'action' => $action
		], true), 'inline');

		Assets::add_module_js('action', 'detail.js');
		Assets::add_module_js('project', 'project.js');
		Template::set('invited_members', $invited_members);
		Template::set('action_key', $action_key);
		Template::set('action', $action);
		Template::set('steps', $steps);
		Template::set_view('detail');
		Template::render();
	}

	public function create($project_key = null, $action_key = null)
	{
		if (empty($project_key)) {
			Template::set_message(lang('ac_action_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}
		
		$project_id = $this->project->get_object_id('project', $project_key);

		if (empty($project_id)) {
			Template::set_message(lang('ac_project_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (! $this->project->has_permission('project', $project_id, 'Project.Edit.All')) {
			$this->auth->restrict();
		}

		// get projecct id
		$project_id = $this->project_model->get_project_id($project_key, $this->current_user);
		if ($project_id === false) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if(! empty($action_key)) {
			$action = $this->action_model->get_action_by_key($action_key, $this->current_user, 'actions.*');
			if ($action !== false) {
				$action->members = $this->action_member_model->select('user_id')->find_all_by('action_id', $action->action_id);
				if (! empty($action->members)) {
					$members = [];
					foreach ($action->members as $member) {
						$members[] = $member->user_id;
					}
					$action->members = implode(',', $members);
				} else {
					$action->members = '';
				}
				Template::set('action', $action);
			}
		}
		$project_members = $this->user_model->get_organization_members($this->current_user->current_organization_id);

		Assets::add_js($this->load->view('create_js', [
			'project_members' => $project_members
		], true), 'inline');

		$form_error = [];
		$error_message = '';
		if ($this->input->post()) {
			// generate action key
			$this->load->library('project');
			if(empty($action_key)) {
				$_POST['action_key'] = $this->project->get_next_key($project_key);
			} else {
				$_POST['action_key'] = $action->action_key;
			}
			$_POST['project_id'] = $project_id;
			// validate owner_id and resource id
			if (trim($this->input->post('owner_id')) != '') {
				$valid_owner_id = $this->db->select('COUNT(*) as count')
										->from('user_to_organizations')
										->where('organization_id', $this->current_user->current_organization_id)
										->where('user_id', $this->input->post('owner_id'))
										->get()->row()->count > 0 ? true : false;
				if (! $valid_owner_id) {
					$form_error['owner_id'] = lang('not_valid_owner');
				}
			} elseif (trim($this->input->post('owner_id')) == '' && trim($this->input->post('owner_name')) != '') {
				$form_error['owner_id'] = lang('not_valid_owner');
			}

			$rules = $this->action_model->get_validation_rules();
			$this->form_validation->set_rules($rules['create_action']);
			if ($this->form_validation->run() !== false) {
				$data = [
					'name' => $this->input->post('name'),
					'success_condition' => $this->input->post('success_condition'),
					'action_type' => $this->input->post('action_type'),
					'owner_id' => $this->current_user->user_id,
					'action_key' => $this->input->post('action_key'),
					'project_id' => $this->input->post('project_id')
				];

				if ($this->input->post('owner_id') != '') {
					$data['owner_id'] = $this->input->post('owner_id');
				}

				// Add to project members if not in
				// Prevent duplicate row by MySQL Insert Ignore
				$query = $this->db->insert_string('project_members', [
					'project_id' => $data['project_id'],
					'user_id' => $data['owner_id']
				]);

				$query = str_replace('INSERT', 'INSERT IGNORE', $query);
				$this->db->query($query);

				if ($this->input->post('point_value') != '') {
					$data['point_value'] = $this->input->post('point_value');
				}

				if ($this->input->post('point_used') != '') {
					$data['point_used'] = $this->input->post('point_used');
				}

				if ($this->input->post('avarage_stars') != '') {
					$data['avarage_stars'] = $this->input->post('avarage_stars');
				}

				try {
					if (empty($action_key)) {
						$action_id = $this->action_model->insert($data);
						if (!$action_id) {
							logit('line 99: unable to insert data to table mb_actions');
							throw new Exception(lang('unable_create_action'));
						}


						if ($team = $this->input->post('team')) {
							if ($team = explode(',', $team)) {
								$member_data = [];
								foreach ($team as $member) {
									$member_data[] = [
										'action_id' => $action_id,
										'user_id' => $member
									];

									// Add to project members if not in
									// Prevent duplicate row by MySQL Insert Ignore
									$query = $this->db->insert_string('project_members', [
										'project_id' => $data['project_id'],
										'user_id' => $member
									]);

									$query = str_replace('INSERT', 'INSERT IGNORE', $query);
									$this->db->query($query);
								}

								$inserted = $this->action_member_model->insert_batch($member_data);
								if (! $inserted) {
									logit('line 210: unable to add action members');
									throw new Exception(lang('unable_add_action_members'));
								}
							}
						}
					} else {
						$updated = $this->action_model->update($action->action_id, $data);
						if (!$updated) {
							logit('line 99: unable to update data to table mb_actions');
							throw new Exception(lang('unable_update_action'));
						}

						if ($team = $this->input->post('team')) {
							if ($team = explode(',', $team)) {
								$member_data = [];
								foreach ($team as $member) {
									$member_data[] = [
										'action_id' => $action->action_id,
										'user_id' => $member
									];
								}

								$this->action_member_model->delete_where(['action_id' => $action->action_id]);
								$inserted = $this->action_member_model->insert_batch($member_data);
								if (! $inserted) {
									logit('line 210: unable to add action members');
									throw new Exception(lang('unable_add_action_members'));
								}
							}
						}
					}
				} catch (Exception $e) {
					$form_error['other_error'] = $e->getMessage;
				}
			} else {
				$form_error = array_merge($form_error, $this->form_validation->error_array());
			}

			if (count($form_error) > 0) {
				$error_message .= "<ul style='list-style: none; padding-left: 0;'>";
				foreach ($form_error as $message) {
					$error_message .= "<li>" . $message . "</li>";
				}
				$error_message .= "</ul>";
				if (! $this->input->is_ajax_request()) {
					Template::set_message($error_message, 'danger');
				} else {
					Template::set('close_modal', 0);
					Template::set('message_type', 'danger');
					Template::set('message', $error_message);
				}
			} else {
				if (! $this->input->is_ajax_request()) {
						Template::set_message(empty($action_key) ? lang('create_success') : lang('update_success'), 'success');
				} else {
					Template::set('message_type', 'success');
					Template::set('message', empty($action_key) ? lang('create_success') : lang('update_success'));
					Template::set('content', '');
				}
			}
		}

		Assets::add_module_js('action', 'create.js');
		Template::set('project_key', $project_key);
		Template::set('form_error', $form_error);
		Template::render();
	}

	public function add_team_member()
	{
		$user_id = $this->input->post('user_id');
		$action_id = $this->input->post('action_id');

		if ($user_id === NULL || $action_id === NULL) {
			echo 0;
			return;
		}

		// Does the current user has permission to add team member?
		$check = $this->action_model->join('action_members am', 'actions.action_id = am.action_id', 'LEFT')
										->join('projects p', 'p.project_id = actions.project_id')
										->join('user_to_organizations uto', 'uto.user_id = am.user_id OR uto.user_id = ' . $this->current_user->user_id)
										->where('am.action_id', $action_id)
										->where('am.user_id', $this->current_user->user_id)
										->or_where('actions.owner_id', $this->current_user->user_id)
										->where('actions.action_id', $action_id)
										->count_all();

		if ($check === 0) {
			echo -1;
			return;
		}

		// Is the target user inside current user's organization?
		$check = $this->user_model->join('user_to_organizations uto', 'uto.user_id = users.user_id')
									->where('uto.organization_id', $this->current_user->current_organization_id)
									->count_by('users.user_id', $user_id);

		if ($check === 0) {
			echo -2;
			return;
		}

		// Prevent duplicate row by MySQL Insert Ignore
		$query = $this->db->insert_string('action_members', ['user_id' => $user_id, 'action_id' => $action_id]);
		$query = str_replace('INSERT', 'INSERT IGNORE', $query);
		echo (int) $this->db->query($query);
	}

	public function remove_team_member()
	{
		$user_id = $this->input->post('user_id');
		$action_id = $this->input->post('action_id');

		if ($user_id === NULL || $action_id === NULL) {
			echo 0;
			return;
		}

		// Does the current user has permission to add team member?
		$check = $this->action_model->join('action_members am', 'actions.action_id = am.action_id', 'LEFT')
										->join('projects p', 'p.project_id = actions.project_id')
										->join('user_to_organizations uto', 'uto.user_id = am.user_id OR uto.user_id = ' . $this->current_user->user_id)
										->where('am.action_id', $action_id)
										->where('am.user_id', $this->current_user->user_id)
										->or_where('actions.owner_id', $this->current_user->user_id)
										->where('actions.action_id', $action_id)
										->count_all();

		if ($check === 0) {
			echo -1;
			return;
		}

		// Prevent duplicate row by MySQL Insert Ignore
		echo (int) $this->action_member_model->delete_where(['user_id' => $user_id, 'action_id' => $action_id]);
	}
}