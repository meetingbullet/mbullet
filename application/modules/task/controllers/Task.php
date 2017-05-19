<?php defined('BASEPATH') || exit('No direct script access allowed');

class Task extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('mb_project');
		$this->lang->load('task');
		$this->load->helper('mb_form');
		$this->load->model('step/step_model');
		$this->load->model('users/user_model');
		$this->load->model('task_model');
		$this->load->model('task_member_model');
		$this->load->model('project/project_model');
		$this->load->model('project/project_member_model');
	}

	public function create($step_key)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$step_id = $this->mb_project->get_object_id('step', $step_key);

		if (empty($step_id)) {
			Template::set_message(lang('tk_step_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$keys = explode('-', $step_key);
		if (empty($keys) || count($keys) < 3) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$project_key = $keys[0];

		if (! $this->mb_project->has_permission('step', $step_id, 'Project.Edit.All')) {
			$this->auth->restrict();
		}

		$organization_members = $this->user_model->get_organization_members($this->current_user->current_organization_id);
		if (empty($organization_members)) {
			$organization_members = [];
		}

		if ($step_id === false) {
			Template::set('message', lang('tk_not_have_permission'));
			Template::set('message_type', 'danger');
		} else {
			Template::set('close_modal', 0);

			if ($this->input->post()) {
				$rules = $this->task_model->get_validation_rules();
				$this->form_validation->set_rules($rules['create']);

				if ($this->form_validation->run() !== false) {
					$data = $this->task_model->prep_data($this->input->post());
					$data['step_id'] = $step_id;
					$data['owner_id'] = $this->current_user->user_id;
					$data['created_by'] = $this->current_user->user_id;
					$data['task_key'] = $this->mb_project->get_next_key($step_key);

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
									Template::set('data', $this->ajax_task_data($task_id));
									Template::set('close_modal', 1);

								} else {
									Template::set('message', lang('tk_add_task_member_fail'));
									Template::set('message_type', 'danger');
								}
							} else {
								Template::set('message', lang('tk_create_task_success'));
								Template::set('message_type', 'success');
								Template::set('data', $this->ajax_task_data($task_id));
								Template::set('close_modal', 1);
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
				}
			}
		}
		// Assets::add_js($this->load->view('create_js', [
		// 	'organization_members' => $organization_members
		// ], true), 'inline');
		Template::set('organization_members', $organization_members);
		Template::render();
	}

	private function ajax_task_data($task_id)
	{
		$data = $this->task_model->limit(1)->find($task_id);

		if ($data) {
			$data->lang_status = lang('tk_' . $data->status);
			$data->assignees = [];
			$assignees = $this->task_member_model->select('avatar, email, CONCAT(first_name, " ", last_name) AS full_name')->join('users u', 'u.user_id = task_members.user_id')->where('task_id', $task_id)->find_all();

			if (is_array($assignees)) {
				foreach ($assignees AS $user) {
					$data->assignees[] = '<img class="avatar avatar-list" title="'. $user->full_name .'" src="'. avatar_url($user->avatar, $user->email) .'">';
				}
			}
		}

		return $data;
	}
}