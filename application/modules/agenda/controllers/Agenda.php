<?php defined('BASEPATH') || exit('No direct script access allowed');

class Agenda extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('mb_project');

		$this->lang->load('agenda');

		$this->load->helper('mb_form');
		$this->load->helper('text');

		$this->load->model('agenda_model');
		$this->load->model('agenda_member_model');

		$this->load->model('meeting/meeting_model');
		$this->load->model('users/user_model');
		
		$this->load->model('project/project_model');
		$this->load->model('project/project_member_model');
	}

	public function create($meeting_key)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$meeting_id = $this->mb_project->get_object_id('meeting', $meeting_key);

		if (empty($meeting_id)) {
			Template::set_message(lang('tk_meeting_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$keys = explode('-', $meeting_key);
		if (empty($keys) || count($keys) < 3) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$project_key = $keys[0];

		if (! $this->mb_project->has_permission('meeting', $meeting_id, 'Project.Edit.All')) {
			$this->auth->restrict();
		}

		$organization_members = $this->user_model->get_organization_members($this->current_user->current_organization_id);
		if (empty($organization_members)) {
			$organization_members = [];
		}

		if ($meeting_id === false) {
			Template::set('message', lang('tk_not_have_permission'));
			Template::set('message_type', 'danger');
		} else {
			Template::set('close_modal', 0);

			if ($this->input->post()) {
				$rules = $this->agenda_model->get_validation_rules();
				$this->form_validation->set_rules($rules['create']);

				if ($this->form_validation->run() !== false) {
					$data = $this->agenda_model->prep_data($this->input->post());
					$data['meeting_id'] = $meeting_id;
					$data['owner_id'] = $this->current_user->user_id;
					$data['created_by'] = $this->current_user->user_id;
					$data['agenda_key'] = $this->mb_project->get_next_key($meeting_key);

					$agenda_id = $this->agenda_model->insert($data);
					if ($agenda_id) {
						$assignees = $this->input->post('assignee');
						$assignees = explode(',', $assignees);
						if (! empty($assignees)) {
							foreach ($assignees as $user_id) {
								if (! empty($user_id)) {
									$agenda_members[] = [
										'agenda_id' => $agenda_id,
										'user_id' => $user_id
									];
								}
							}

							if (! empty($agenda_members)) {
								$inserted = $this->agenda_member_model->insert_batch($agenda_members);
								if ($inserted) {
									$this->mb_project->notify_members($agenda_id, 'agenda', $this->current_user, 'insert');
									Template::set('message', lang('tk_create_agenda_success'));
									Template::set('message_type', 'success');
									Template::set('data', $this->ajax_agenda_data($agenda_id));
									Template::set('close_modal', 1);

								} else {
									Template::set('message', lang('tk_add_agenda_member_fail'));
									Template::set('message_type', 'danger');
								}
							} else {
								Template::set('message', lang('tk_create_agenda_success'));
								Template::set('message_type', 'success');
								Template::set('data', $this->ajax_agenda_data($agenda_id));
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
					Template::set('message', lang('tk_create_agenda_fail'));
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

	private function ajax_agenda_data($agenda_id)
	{
		$data = $this->agenda_model->limit(1)->find($agenda_id);

		if ($data) {
			$data->description = word_limiter($data->description, 20);
			$data->lang_status = lang('tk_' . $data->status);
			$data->assignees = [];
			$assignees = $this->agenda_member_model->select('avatar, email, CONCAT(first_name, " ", last_name) AS full_name')->join('users u', 'u.user_id = agenda_members.user_id')->where('agenda_id', $agenda_id)->find_all();

			if (is_array($assignees)) {
				foreach ($assignees AS $user) {
					$data->assignees[] = ['html' => '<img class="user-avatar" title="'. $user->full_name .'" src="'. avatar_url($user->avatar, $user->email) .'">'];
				}
			}
		}

		return $data;
	}
}