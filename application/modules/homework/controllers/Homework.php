<?php defined('BASEPATH') || exit('No direct script access allowed');

class Homework extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('homework');

		$this->load->helper('mb_form');
		$this->load->helper('text');

		$this->load->model('meeting/meeting_model');
		$this->load->model('users/user_model');
		
		$this->load->model('homework_model');
		$this->load->model('homework_member_model');
		$this->load->model('homework_attachment_model');
		$this->load->model('homework_read_model');
		$this->load->model('homework_rate_model');

		Assets::add_module_css('homework', 'homework.css');
	}

	public function create($meeting_key)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (is_numeric($meeting_key)) {
			$meeting_id = $meeting_key;
			$meeting = $this->meeting_model->join('users u', 'u.user_id = meetings.owner_id', 'left')
									->where('organization_id', $this->current_user->current_organization_id)
									->where('created_by', $this->current_user->user_id)
									->where('is_private', 1)
									->find($meeting_id);
			if (empty($meeting)) {
				Template::set_message(lang('hw_meeting_key_does_not_exist'), 'danger');
				Template::set('message_type', 'danger');
				Template::set('close_modal', 1);
				Template::render();
				return;
			}
		} else {
			$meeting_id = $this->mb_project->get_object_id('meeting', $meeting_key);

			if (empty($meeting_id)) {
				Template::set_message(lang('hw_meeting_key_does_not_exist'), 'danger');
				Template::set('message_type', 'danger');
				Template::set('close_modal', 1);
				Template::render();
				return;
			}

			$keys = explode('-', $meeting_key);
			if (empty($keys) || count($keys) < 3) {
				Template::set_message(lang('hw_meeting_key_does_not_exist'), 'danger');
				Template::set('message_type', 'danger');
				Template::set('close_modal', 1);
				Template::render();
				return;
			}

			$project_key = $keys[0];

			if (! $this->mb_project->has_permission('meeting', $meeting_id, 'Project.Edit.All')) {
				Template::set('message_type', 'danger');
				Template::set('message', lang('hw_not_have_permission'));
				Template::set('close_modal', 1);
				Template::render();
				return;
			}
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
					$this->mb_project->add_experience_point(1);
					$this->mb_project->update_parent_objects('homework', $homework_id);
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

								if ($attachments = $this->input->post('attachments')) {
									if (is_array($attachments)) {
										foreach ($attachments as &$att) {
											$att['homework_id'] = $homework_id;

											if ($att['title'] == '') $att['title'] = null;
											if ($att['favicon'] == '') $att['favicon'] = null;
										}

										$this->homework_attachment_model->insert_batch($attachments);
									}
								}

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
		
		Assets::add_js($this->load->view('create_js', [
			'organization_members' => $organization_members
		], true), 'inline');
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
		->where('(homework.created_by = "' . $this->current_user->user_id . '" OR hwm.user_id = "' . $this->current_user->user_id . '")')
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

		$this->mb_project->update_parent_objects('homework', $this->input->post('pk'));

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
			$data->attachments = [];

			$members = $this->homework_member_model->select('avatar, email, CONCAT(first_name, " ", last_name) AS full_name')
			->join('users u', 'u.user_id = homework_members.user_id')
			->where('homework_id', $homework_id)
			->find_all();

			if (is_array($members)) {
				foreach ($members AS $user) {
					$data->members[] = ['html' => '<img class="user-avatar" title="'. $user->full_name .'" src="'. avatar_url($user->avatar, $user->email) .'">'];
				}
			}

			$attachments = $this->homework_attachment_model
			->where('homework_id', $homework_id)
			->find_all();


			if (is_array($attachments)) {
				foreach ($attachments AS $att) {
					$data->attachments[] = [
						'html' => "<a href='{$att->url}' title='". ($att->title ? $att->title : $att->url ) ."'>
									<span class='icon'>" . 
										($att->favicon 
										? "<img src='{$att->favicon}' alt='[A]' title='". ($att->title ? $att->title : $att->url ) ."'/>" 
										: '<i class="icon-file"></i>') . 
									'</span></a>'
					];
				}
			}
		}

		return $data;
	}

	public function edit($homework_id)
	{
		$test = $this->homework_model->select('homework.*, s.meeting_id, s.status as meeting_status')
									->join('homework_members hwm', 'hwm.homework_id = homework.homework_id AND hwm.user_id = ' . $this->current_user->user_id, 'LEFT')
									->join('meetings s', 's.meeting_id = homework.meeting_id AND (s.status = "open" OR s.status = "ready" OR s.status = "inprogress")') // Can only edit when meeting is OPEN
									->where('(homework.created_by = "' . $this->current_user->user_id . '" OR hwm.user_id = "' . $this->current_user->user_id . '")')
									->find($homework_id);

		if ($test === false || ! has_permission('Project.Edit.All') || ($test->meeting_status != 'open' && $test->meeting_status != 'ready')) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('hw_no_permission_to_edit')
			]);
			exit;
		}

		Template::set('homework', $test);

		$organization_members = $this->user_model->get_organization_members($this->current_user->current_organization_id);
		if (empty($organization_members)) {
			$organization_members = [];
		}

		$selected_members = $this->homework_member_model->select('user_id')->where('homework_id', $homework_id)->as_array()->find_all();
		if (empty($selected_members)) {
			$selected_members = [];
		} else {
			$selected_members = array_column($selected_members, 'user_id');
		}
		Template::set('homework_members', $selected_members);

		$selected_attachments = $this->homework_attachment_model->where('homework_id', $homework_id)->as_array()->find_all();
		if (empty($selected_attachments)) {
			$selected_attachments = [];
		}
		Template::set('homework_attachments', $selected_attachments);

		Template::set('close_modal', 0);
		Template::set('organization_members', $organization_members);

		if ($this->input->post()) {
			$data = $this->homework_model->prep_data($this->input->post());

			$this->form_validation->set_rules($this->homework_model->get_validation_rules('update'));

			if ($this->form_validation->run() === false) {
				Template::set('message', lang('hw_unable_to_update_homework'));
				Template::set('message_type', 'danger');
				Template::render();
				return;
			}

			$updated = $this->homework_model->update($homework_id, $data);

			if ($updated) {
				$this->homework_member_model->delete($homework_id);
				$this->mb_project->update_parent_objects('homework', $homework_id);
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
							$this->homework_attachment_model->delete_where(['homework_id' => $homework_id]);
							if ($attachments = $this->input->post('attachments')) {
								if (is_array($attachments)) {
									foreach ($attachments as &$att) {
										$att['homework_id'] = $homework_id;

										if ($att['title'] == '') $att['title'] = null;
										if ($att['favicon'] == '') $att['favicon'] = null;
									}

									$this->homework_attachment_model->insert_batch($attachments);
								}
							}

							//$this->mb_project->notify_members($homework_id, 'homework', $this->current_user, 'insert');
							Template::set('message', lang('hw_update_success'));
							Template::set('message_type', 'success');
							Template::set('data', $this->ajax_homework_data($homework_id));
							Template::set('close_modal', 1);
						} else {
							Template::set('message', lang('hw_unable_to_update_homework'));
							Template::set('message_type', 'danger');
						}
					} else {
						Template::set('message', lang('hw_update_success'));
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
				Template::set('message', lang('hw_unable_to_update_homework'));
				Template::set('message_type', 'danger');
			}
		}

		Assets::add_js($this->load->view('create_js', [
			'organization_members' => $organization_members
		], true), 'inline');
		Template::render();
	}

	public function delete($homework_id)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$test = $this->homework_model->select('homework.*')
									->join('homework_members hwm', 'hwm.homework_id = homework.homework_id AND hwm.user_id = ' . $this->current_user->user_id, 'LEFT')
									->join('meetings s', 's.meeting_id = homework.meeting_id AND (s.status = "open" OR s.status = "ready")') // Can only edit when meeting is OPEN
									->where('(homework.created_by = "' . $this->current_user->user_id . '" OR hwm.user_id = "' . $this->current_user->user_id . '")')
									->find($homework_id);

		if ($test === false || ! has_permission('Project.Edit.All')) {
			echo json_encode([
				'status' => 0,
				'message_type' => 'danger',
				'message' => lang('hw_no_permission_to_edit')
			]);
			exit;
		}

		$deleted = $this->homework_model->delete($homework_id);
		if ($deleted) {
			$this->homework_member_model->delete($homework_id);
			$this->homework_read_model->delete_where(['homework_id' => $homework_id]);
			$this->homework_rate_model->delete_where(['homework_id' => $homework_id]);
			$this->homework_attachment_model->delete_where(['homework_id' => $homework_id]);
			echo json_encode([
				'status' => 1,
				'message' => lang('hw_delete_success'),
				'message_type' => 'success',
			]);
			exit;
		}

		echo json_encode([
			'status' => 0,
			'message' => lang('hw_delete_fail'),
			'message_type' => 'danger'
		]);
		exit;
	}
}