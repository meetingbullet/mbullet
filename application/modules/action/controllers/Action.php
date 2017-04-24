<?php defined('BASEPATH') || exit('No direct script access allowed');

class Action extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->lang->load('action');
		$this->load->helper('mb_form');
		$this->load->model('action_model');
		$this->load->model('projects/project_model');
		$this->load->model('step/step_model');
		Assets::add_module_css('action', 'action.css');
	}

	public function index()
	{
		Template::render();
	}

	public function detail($project_key, $action_key)
	{
		if (empty($project_key) || empty($action_key)) {
			Template::set_message(lang('ac_invalid_action_key'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$action = $this->action_model->select('actions.*, CONCAT(u.first_name, " ", u.last_name) as owner_name')
									->join('projects p', 'p.project_id = actions.project_id')
									->join('users u', 'u.user_id = actions.owner_id')
									->where('p.cost_code', $project_key)
									->limit(1)
									->find_by('action_key', $action_key);

		if (! $action) {
			Template::set_message(lang('ac_invalid_action_key'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$steps = $this->step_model->select('steps.*, CONCAT(u.first_name, " ", u.last_name) as owner_name')
									->join('users u', 'u.user_id = steps.owner_id')
									->where('action_id', $action->action_id)
									->order_by('step_id')
									->order_by('status')
									->find_all();

		Template::set('project_key', $project_key);
		Template::set('action_key', $action_key);
		Template::set('action', $action);
		Template::set('steps', $steps);
		Template::render();
	}

	public function create($project_key = null)
	{

		if (empty($project_key)) {
			redirect('/dashboard');
		}
		// get projecct id
		$project_id = $this->project_model->get_project_id($project_key, $this->current_user);
		if ($project_id === false) {
			redirect('/dashboard');
		}

		$form_error = [];
		$error_message = '';
		if ($this->input->post()) {
			// get last action id
			$last_id = $this->db->select('MAX(action_id) as max_id')->get('actions')->row()->max_id;
			if (empty($last_id)) {
				$last_id = 0;
			}
			// generate action key
			$_POST['action_key'] = $project_key . "-" . ($last_id + 1);
			$_POST['project_id'] = $project_id;
			// validate owner_id and resource id
			if (trim($this->input->post('owner_id')) != '') {
				$valid_owner_id = $this->db->select('COUNT(*) as count')
										->from('project_members')
										->where('project_id', $project_id)
										->where('user_id', $this->input->post('owner_id'))
										->get()->row()->count > 0 ? true : false;
				if (! $valid_owner_id) {
					$form_error['owner_id'] = lang('not_valid_owner');
				}
			} elseif (trim($this->input->post('owner_id')) == '' && trim($this->input->post('owner_name')) != '') {
				$form_error['owner_id'] = lang('not_valid_owner');
			}

			if (trim($this->input->post('action_members')) != '[]' || count(json_decode(trim($this->input->post('action_members')))) != 0) {
				$action_member_ids = array_map(create_function('$o', 'return $o->value;'), json_decode(trim($this->input->post('action_members'))));

				$valid_member_ids = $this->db->select('COUNT(*) as count')
										->from('project_members')
										->where('project_id', $project_id)
										->where_in('user_id', $action_member_ids)
										->get()->row()->count == count($action_member_ids) ? true : false;
				if (! $valid_member_ids) {
					$form_error['member_ids'] = lang('not_valid_members');
				}
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

				if ($this->input->post('point_value_defined') != '') {
					$data['point_value_defined'] = $this->input->post('point_value_defined');
				}

				if ($this->input->post('point_used') != '') {
					$data['point_used'] = $this->input->post('point_used');
				}

				if ($this->input->post('avarage_stars') != '') {
					$data['avarage_stars'] = $this->input->post('avarage_stars');
				}

				try {
					$action_id = $this->action_model->insert($data);
					if (!$action_id) {
						logit('line 99: unable to insert data to table mb_actions');
						throw new Exception(lang('unable_create_action'));
					}

					if (! empty($action_member_ids)) {
						$action_members = array_map(function($id) use ($action_id)
						{
							return [
								'action_id' => $action_id,
								'user_id' => $id
							];
						}, $action_member_ids);

						$added = $this->db->insert_batch('action_members', $action_members);
						if (!$added) {
							logit('line 112: unable to insert data to table mb_action_members');
							throw new Exception(lang('unable_add_action_members'));
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
						Template::set_message(lang('create_success'), 'success');
				} else {
					Template::set('message_type', 'success');
					Template::set('message', lang('create_success'));
					Template::set('content', '');
				}
			}
		}

		Assets::add_module_js('action', 'action.js');
		Assets::add_module_css('action', 'action.css');
		Template::set('project_key', $project_key);
		Template::set('form_error', $form_error);
		if (! $this->input->is_ajax_request()) {
			Template::render();
		} else {
			Template::render('ajax');
		}
	}
}