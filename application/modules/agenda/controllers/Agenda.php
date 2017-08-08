<?php defined('BASEPATH') || exit('No direct script access allowed');

class Agenda extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('agenda');

		$this->load->helper('mb_form');
		$this->load->helper('text');

		$this->load->model('agenda_model');
		$this->load->model('agenda_member_model');
		$this->load->model('agenda_read_model');
		$this->load->model('agenda_rate_model');
		$this->load->model('agenda_vote_model');
		$this->load->model('agenda_attachment_model');

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
			Template::set_message(lang('ag_meeting_key_does_not_exist'), 'danger');
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
			Template::set('message', lang('ag_not_have_permission'));
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
						$this->mb_project->add_experience_point(1);
						$this->mb_project->update_parent_objects('agenda', $agenda_id);
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
									Template::set('message', lang('ag_create_agenda_success'));
									Template::set('message_type', 'success');
									Template::set('data', $this->ajax_agenda_data($agenda_id));
									Template::set('close_modal', 1);

								} else {
									Template::set('message', lang('ag_add_agenda_member_fail'));
									Template::set('message_type', 'danger');
								}
							} else {
								Template::set('message', lang('ag_create_agenda_success'));
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
					Template::set('message', lang('ag_create_agenda_fail'));
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

	/**
		X-Editable AJAX request to edit homework fields
		pk: primary key
		name: column name
		value: new value
	*/
	public function ajax_edit()
	{
		// Validation
		if ( ! in_array($this->input->post('name'), ['confirm_status'])) {
				header('HTTP/1.0 403 Forbidden City', true, 403);
				echo lang('ag_unknown_error');
				return;
		}

		// Only member of Agenda or the owner can edit
		$test = $this->agenda_model->select('s.meeting_id, agendas.' . $this->input->post('name'))
		->join('agenda_members am', 'am.agenda_id = agendas.agenda_id AND am.user_id = ' . $this->current_user->user_id, 'LEFT')
		->join('meetings s', 's.meeting_id = agendas.meeting_id AND (s.status = "open" OR s.status = "ready" OR s.status = "inprogress")') // Can only edit when meeting is OPEN
		->where('agendas.owner_id', $this->current_user->user_id)
		->or_where('am.user_id', $this->current_user->user_id)
		->find($this->input->post('pk'));

		if ($test === false) {
			header('HTTP/1.0 401 Unauthorized 💔', true, 401);
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('ag_not_have_permission')
			]);
			return;
		}

		// Update the field
		$update = $this->agenda_model->update($this->input->post('pk'), [
			$this->input->post('name') => $this->input->post('value')
		]);

		if ($update === false) {
			header('HTTP/1.0 500 Server error 💔', true, 500);
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('ag_unknown_error')
			]);
			return;
		}
		$this->mb_project->update_parent_objects('agenda', $this->input->post('pk'));

		if ($this->input->post('name') == 'confirm_status') {
			// Switch meeting_state after every confirm_status is filled
			$this->agenda_model->select('COUNT(*) AS total_unconfirmed_status', false)->where()->find_by('meeting_id', $test->meeting_id);

			echo json_encode([
				'message_type' => 'success',
				'message' => lang('ag_agenda_status_confirmed')
			]);
		}
	}

	private function ajax_agenda_data($agenda_id)
	{
		$data = $this->agenda_model->limit(1)->find($agenda_id);

		if ($data) {
			$data->description = word_limiter($data->description, 20);
			$data->lang_status = lang('ag_' . $data->status);
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

	public function edit($agenda_key)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$agenda_id = $this->mb_project->get_object_id('agenda', $agenda_key);

		if (empty($agenda_id)) {
			Template::set_message(lang('ag_agenda_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		// $keys = explode('-', $meeting_key);
		// if (empty($keys) || count($keys) < 3) {
		// 	redirect(DEFAULT_LOGIN_LOCATION);
		// }

		// $project_key = $keys[0];

		if (! $this->mb_project->has_permission('agenda', $agenda_id, 'Project.Edit.All')) {
			$this->auth->restrict();
		}

		$organization_members = $this->user_model->get_organization_members($this->current_user->current_organization_id);
		if (empty($organization_members)) {
			$organization_members = [];
		}

		if ($agenda_id === false) {
			Template::set('message', lang('ag_not_have_permission'));
			Template::set('message_type', 'danger');
		} else {
			Template::set('close_modal', 0);

			$agenda = $this->agenda_model->find($agenda_id);
			$selected_members = $this->agenda_member_model->select('user_id')->where('agenda_id', $agenda_id)->as_array()->find_all();
			if (empty($selected_members)) {
				$selected_members = [];
			} else {
				$selected_members = array_column($selected_members, 'user_id');
			}

			if ($this->input->post()) {
				$rules = $this->agenda_model->get_validation_rules();
				$this->form_validation->set_rules($rules['create']);

				if ($this->form_validation->run() !== false) {
					$data = $this->agenda_model->prep_data($this->input->post());
					$updated = $this->agenda_model->skip_validation(true)->update($agenda_id, $data);

					if ($updated) {
						//$this->mb_project->add_experience_point(1);
						$this->mb_project->update_parent_objects('agenda', $agenda_id);
						$this->agenda_member_model->delete($agenda_id);
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
									//$this->mb_project->notify_members($agenda_id, 'agenda', $this->current_user, 'insert');
									Template::set('message', lang('ag_update_agenda_success'));
									Template::set('message_type', 'success');
									Template::set('data', $this->ajax_agenda_data($agenda_id));
									Template::set('close_modal', 1);

								} else {
									Template::set('message', lang('ag_add_agenda_member_fail'));
									Template::set('message_type', 'danger');
								}
							} else {
								Template::set('message', lang('ag_update_agenda_success'));
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
					Template::set('message', lang('ag_update_agenda_fail'));
					Template::set('message_type', 'danger');
				}
			}
		}
		// Assets::add_js($this->load->view('create_js', [
		// 	'organization_members' => $organization_members
		// ], true), 'inline');
		Template::set('organization_members', $organization_members);
		Template::set('agenda_members', $selected_members);
		Template::set('agenda', $agenda);
		Template::render();
	}

	public function delete($agenda_key)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$agenda_id = $this->mb_project->get_object_id('agenda', $agenda_key);
		$agenda_count = $this->agenda_model->join('meetings', 'meetings.meeting_id = agendas.meeting_id AND meetings.status = "open"', 'LEFT')
									->where('agendas.agenda_id', $agenda_id)
									->count_all();

		if (empty($agenda_id) || $agenda_count == 0) {
			Template::set_message(lang('ag_agenda_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (! $this->mb_project->has_permission('agenda', $agenda_id, 'Project.Edit.All')) {
			$this->auth->restrict();
		}

		$deleted = $this->agenda_model->delete($agenda_id);
		if ($deleted) {
			$this->agenda_member_model->delete($agenda_id);
			$this->agenda_read_model->delete_where(['agenda_id' => $agenda_id]);
			$this->agenda_rate_model->delete_where(['agenda_id' => $agenda_id]);
			$this->agenda_vote_model->delete_where(['agenda_id' => $agenda_id]);
			$this->agenda_attachment_model->delete_where(['agenda_id' => $agenda_id]);
			echo json_encode([
				'status' => 1,
				'message' => lang('ag_delete_success'),
				'message_type' => 'success',
				'data' => [
					'agenda_id' => $agenda_id
				]
			]);
			exit;
		}

		echo json_encode([
			'status' => 0,
			'message' => lang('ag_delete_fail'),
			'message_type' => 'danger'
		]);
		exit;
	}
}