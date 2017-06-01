<?php defined('BASEPATH') || exit('No direct script access allowed');

class Homework extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('homework');

		$this->load->helper('mb_form');
		$this->load->helper('text');

		$this->load->library('mb_project');
		$this->load->model('meeting/meeting_model');
		$this->load->model('users/user_model');
		
		$this->load->model('homework/homework_model');
		$this->load->model('homework/homework_member_model');

	}

	public function create($meeting_key)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$meeting_id = $this->mb_project->get_object_id('meeting', $meeting_key);

		if (empty($meeting_id)) {
			Template::set_message(lang('hw_meeting_key_does_not_exist'), 'danger');
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
			Template::set('message_type', 'danger');
			Template::set('message', lang('hw_not_have_permission'));
		} else {
			Template::set('close_modal', 0);
			Template::set('organization_members', $organization_members);

			if ($this->input->post()) {
				$data = $this->homework_model->prep_data($this->input->post());
				$data['meeting_id'] = $meeting_id;

				$this->form_validation->set_rules($this->homework_model->get_validation_rules('update'));

				if ($this->form_validation->run() === false) {
					Template::set('message', lang('hw_unable_to_add_homework'));
					Template::set('message_type', 'danger');
					Template::render();
					return;
				}

				$homework_id = $this->homework_model->insert($data);

				if ($homework_id) {
					$members = $this->input->post('member');
					$members = explode(',', $members);
					if (! empty($members)) {
						foreach ($members as $user_id) {
							if (! empty($user_id)) {
								$homework_members[] = [
									'homework_id' => $homework_id,
									'user_id' => $user_id
								];
							}
						}

						if (! empty($homework_members)) {
							if ($inserted = $this->homework_member_model->insert_batch($homework_members) ) {
								$this->mb_project->notify_members($homework_id, 'homework', $this->current_user, 'insert');
								Template::set('message', lang('hw_new_homework_added'));
								Template::set('message_type', 'success');
								Template::set('data', $this->ajax_homework_data($homework_id));
								Template::set('close_modal', 1);
							} else {
								Template::set('message', lang('hw_unable_to_add_homework'));
								Template::set('message_type', 'danger');
							}
						} else {
							Template::set('message', lang('hw_new_homework_added'));
							Template::set('message_type', 'success');
							Template::set('data', $this->ajax_homework_data($homework_id));
							Template::set('close_modal', 1);
						}
					} else {
						$error = true;
					}
				} else {
					$error = true;
				}

				if (! empty($error)) {
					Template::set('message', lang('hw_unable_to_add_homework'));
					Template::set('message_type', 'danger');
				}
			}
		}
		
		Template::render();
	}

	
	// X-Editable AJAX request to edit homework fields
	public function ajax_edit()
	{
		// Validation
		if ( ! in_array($this->input->post('name'), ['time-spent', 'status', 'description'])) {
				header('HTTP/1.0 403 Forbidden City', true, 403);
				echo lang('hw_unknown_error');
				return;
		}

		if ($this->input->post('name') == 'time-spent') {
			if ( ! is_numeric($this->input->post('value')) ) {
				header('HTTP/1.0 403 Forbidden City', true, 403);
				echo lang('hw_unknown_error');
				return;
			}
		}

		// Only member of HW or Creator can edit
		$test = $this->homework_model->select('homework.' . $this->input->post('name'))
		->join('homework_members hwm', 'hwm.homework_id = homework.homework_id AND hwm.user_id = ' . $this->current_user->user_id, 'LEFT')
		->join('meetings s', 's.meeting_id = homework.meeting_id AND (s.status = "open" OR s.status = "ready" OR s.status = "inprogress")') // Can only edit when meeting is OPEN
		->where('homework.created_by', $this->current_user->user_id)
		->or_where('hwm.user_id', $this->current_user->user_id)
		->find($this->input->post('pk'));

		if ($test === false) {
			header('HTTP/1.0 401 Unauthorized 💔', true, 401);
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('hw_no_permission_to_edit')
			]);
			return;
		}

		// Update the field
		$update = $this->homework_model->update($this->input->post('pk'), [
			$this->input->post('name') => $this->input->post('value')
		]);

		if ($update === false) {
			header('HTTP/1.0 500 Server error 💔', true, 500);
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('hw_unknown_error')
			]);
			return;
		}

		if ($this->input->post('name') == 'description') {
			echo json_encode([
				'message_type' => 'success',
				'value' => $this->input->post('value')
			]);
		}

		if ($this->input->post('name') == 'status') {
			echo json_encode([
				'message_type' => 'success',
				'message' => lang('hw_homework_status_updated')
			]);
		}
	}

	private function ajax_homework_data($homework_id)
	{
		if ($data = $this->homework_model->limit(1)->find($homework_id) ) {
			$data->short_description = word_limiter($data->description, 18);
			$data->lang_status = lang('hw_' . $data->status);
			$data->members = [];

			$members = $this->homework_member_model->select('avatar, email, CONCAT(first_name, " ", last_name) AS full_name')->join('users u', 'u.user_id = homework_members.user_id')->where('homework_id', $homework_id)->find_all();

			if (is_array($members)) {
				foreach ($members AS $user) {
					$data->members[] = ['html' => '<img class="user-avatar" title="'. $user->full_name .'" src="'. avatar_url($user->avatar, $user->email) .'">'];
				}
			}
		}

		return $data;
	}
}