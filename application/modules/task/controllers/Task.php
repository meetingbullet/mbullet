<?php defined('BASEPATH') || exit('No direct script access allowed');

class Task extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('task_model');
		$this->load->model('task_member_model');
		$this->lang->load('task');
	}

	public function create($step_key)
	{
		if (! $this->input->is_ajax_request()) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}
		$this->load->model('step/step_model');
		$this->load->helper('mb_form');
		$this->load->helper('mb_general');

		$step_id = $this->step_model->get_step_id($step_key, $this->current_user);

		if (! class_exists('User_model')) {
			$this->load->model('users/user_model');
		}
		$organization_members = $this->user_model->get_organization_members($this->current_user->current_organization_id);
		if (empty($organization_members)) {
			$organization_members = [];
		}

		if ($step_id === false) {
			Template::set('message', lang('tk_not_have_permission'));
			Template::set('message_type', 'danger');
		} else {
			if ($this->input->post()) {
				$rules = $this->task_model->get_validation_rules();
				$this->form_validation->set_rules($rules['create']);

				if ($this->form_validation->run() !== false) {
					$data = $this->task_model->prep_data($this->input->post());
					$data['step_id'] = $step_id;
					$data['owner_id'] = $this->current_user->user_id;
					$data['created_by'] = $this->current_user->user_id;
					$this->load->library('project');
					$data['task_key'] = $this->project->get_next_key($step_key);

					$task_id = $this->task_model->insert($data);
					if ($task_id) {
						$assignees = $this->input->post('assignee');
						$assignees = explode(',', $assignees);
						if (! empty($assignees)) {
							foreach ($assignees as $user_id) {
								if (! empty($user_id)) {
									$task_members[] = [
										'task_id' => $task_id,
										'user_id' => $user_id
									];
								}
							}

							if (! empty($task_members)) {
								$inserted = $this->task_member_model->insert_batch($task_members);
								if ($inserted) {
									Template::set('message', lang('tk_create_task_success'));
									Template::set('message_type', 'success');
								} else {
									Template::set('message', lang('tk_add_task_member_fail'));
									Template::set('message_type', 'danger');
									Template::set('close_modal', 0);
								}
							} else {
								Template::set('message', lang('tk_create_task_success'));
								Template::set('message_type', 'success');
							}
						} else {
							$error = true;
						}
					} else {
						$error = true;
					}
				} else {
					$error = true;
				}

				if (! empty($error)) {
					Template::set('message', lang('tk_create_task_fail'));
					Template::set('message_type', 'danger');
					Template::set('close_modal', 0);
				}
			}
		}
		// Assets::add_js($this->load->view('create_js', [
		// 	'organization_members' => $organization_members
		// ], true), 'inline');
		Template::set('close_modal', 0);
		Template::set('organization_members', $organization_members);
		Template::render();
	}
}