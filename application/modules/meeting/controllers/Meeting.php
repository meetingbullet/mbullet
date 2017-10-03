<?php defined('BASEPATH') || exit('No direct script access allowed');

class Meeting extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('meeting');
		$this->load->helper('mb_form');
		$this->load->helper('text');
		$this->load->helper('date');
		$this->load->helper('mb_general');
		$this->load->library('mb_project');
		
		$this->load->model('users/user_model');

		$this->lang->load('homework/homework');
		$this->load->model('homework/homework_model');
		$this->load->model('homework/homework_rate_model');
		$this->load->model('homework/homework_member_model');
		$this->load->model('homework/homework_attachment_model');
		
		$this->load->model('agenda/agenda_model');
		$this->load->model('agenda/agenda_member_model');
		$this->load->model('agenda/agenda_rate_model');
		$this->load->model('agenda/agenda_attachment_model');

		$this->load->model('meeting_model');
		$this->load->model('meeting_member_model');
		$this->load->model('meeting_member_rate_model');
		$this->load->model('meeting_member_invite_model');
		$this->load->model('meeting_comment_model');
		$this->load->model('meeting/goal_model');

		$this->load->model('action/action_model');
		$this->load->model('action/action_member_model');

		$this->load->model('project/project_model');
		$this->load->model('project/project_member_model');

		$this->load->model('invite/user_to_organizations_model');

		Assets::add_module_css('homework', 'homework.css');
		Assets::add_module_css('meeting', 'meeting.css');
		Assets::add_module_js('meeting', 'meeting.js');
	}

	public function _remap($method, $params = array())
	{
		if (method_exists($this, $method))
		{
			if ($method == 'edit' && is_numeric($params[0])) {
				$method = 'edit_private';
			}
			
			return call_user_func_array(array($this, $method), $params);
		} else {
			// if (is_numeric($method)) {
			// 	$this->detail_private($method);
			// } else {
			// 	$this->detail($method);
			// }

			$this->detail($method);
		}
	}

	public function index()
	{
		Template::render();
	}

	public function create($project_key = null)
	{
		if (empty($project_key)) {
			Template::set('close_modal', 0);
			Template::set('message_type', 'danger');
			Template::set('message', lang('st_project_key_does_not_exist'));
			return;
		}

		$project_id = $this->mb_project->get_object_id('project', $project_key);

		if (empty($project_id) && $project_key == 'USP') {
			$organiztion_owner = $this->user_to_organizations_model->join('roles r', 'r.role_id = user_to_organizations.role_id')
																->where('r.is_public', 1)
																->where('r.system_default', 0)
																->where('r.join_default', 0)
																->limit(1)
																->find_by('user_to_organizations.organization_id', $this->current_user->current_organization_id);

			$unspecified_project_id = $this->project_model->insert([
				'cost_code' => 'USP',
				'owner_id' => $organiztion_owner->user_id,
				'name' => 'Unspecified Project',
				'organization_id' => $this->current_user->current_organization_id,
				'is_unspecified_project' => 1,
				'created_by' => $this->current_user->user_id
			]);
			$project_id = $unspecified_project_id;

			/*
				Temporary disable Action functionality, auto create an Action after creating Project 
				and automatically uses it as default action for creating Meeting
			*/

			$this->action_model->insert([
				'project_id' => $project_id,
				'action_key' => 'USP-1', // PJK-1
				'owner_id' => $organiztion_owner->user_id,
				'name' => '[default_action]',
				'action_type' => 'decide',
				'success_condition' => 'action_gate',
				'sort_order' => 999
			]);

			Template::set('need_refresh', 1);
		}

		if (empty($project_id)) {
			Template::set('close_modal', 0);
			Template::set('message_type', 'danger');
			Template::set('message', lang('st_project_key_does_not_exist'));
			return;
		}

		if (! $this->mb_project->has_permission('project', $project_id, 'Project.Edit.All')) {
			Template::set('close_modal', 0);
			Template::set('message_type', 'danger');
			Template::set('message', lang('st_you_have_not_earned_permission_to_create_meeting'));
			return;
		}

		$action = $this->action_model
			->select('action_id, action_key, p.project_id')
			->join('projects p', 'actions.project_id = p.project_id')
			->join('user_to_organizations uto', 'uto.organization_id = p.organization_id AND uto.user_id = ' . $this->current_user->user_id)
			->limit(1)
			->find_by('action_key', $project_key . '-1');

		if ($action === false) {
			Template::set('close_modal', 0);
			Template::set('message_type', 'danger');
			Template::set('message', lang('st_project_key_does_not_exist'));
			return;
		}

		$project = $this->project_model->select('cost_of_time_1, value_of_time_1')->as_array()->find($project_id);

		Template::set('default_cost_of_time', $project['value_of_time_1']);
		Template::set('default_cost_of_time_name', $project['cost_of_time_1']);

		// Get list resource/team member
		$project_members = $this->user_model->get_organization_members($this->current_user->current_organization_id, $project_id);

		// Create Meeting from Open Parking Lot agendas
		if (isset($_POST['from_meeting'])) {
			$open_agendas = $this->agenda_model
			->where('confirm_status', 'open_parking_lot')
			->where('meeting_id', $this->input->post('from_meeting'))
			->find_all();
											
			Template::set('open_agendas', $open_agendas);
		} else {
			Template::set('open_agendas', false);
		}

		Assets::add_js($this->load->view('create_js', [
			'project_members' => $project_members,
			'default_cost_of_time' => $project['value_of_time_1'],
			'default_cost_of_time_name' => $project['cost_of_time_1']
		], true), 'inline');

		if (isset($_POST['save'])) {
			$data = $this->meeting_model->prep_data($this->input->post());
			$data['action_id'] = $action->action_id;
			$data['created_by'] = $this->current_user->user_id;
			unset($data['need_refresh']);

			if ($this->input->post('owner_id') == '') {
				$data['owner_id'] = $this->current_user->user_id;
			}
			$query = $this->db->insert_string('project_members', [
				'project_id' => $project_id,
				'user_id' => $data['owner_id']
			]);

			$query = str_replace('INSERT', 'INSERT IGNORE', $query);
			$this->db->query($query);

			$data['meeting_key'] = $this->mb_project->get_next_key($action->action_key);
			// only when create meeting on dashboard calendar
			if (! empty($this->input->post_get('scheduled_start_time'))) {
				$data['scheduled_start_time'] = get_utc_time($this->input->post_get('scheduled_start_time'));
			} else {
				unset($data['scheduled_start_time']);
			}

			if ($team = $this->input->post('team')) {
				if ($team = explode(',', $team)) {
					if ($data['owner_id'] != $this->current_user->user_id && ! in_array($this->current_user->email, $team)) {
						$team[] = $this->current_user->email;
					}

					$members = $this->user_model->select('email, user_id,
												IF((SELECT COUNT(*) FROM ' . $this->db->dbprefix('user_to_organizations') . ' uto WHERE uto.user_id = ' . $this->db->dbprefix('users') . '.user_id AND uto.organization_id = "' . $this->current_user->current_organization_id . '") > 0, 1, 0) AS in_organization')
												->where_in('email', $team)
												->as_array()
												->find_all();
					if (! in_array($data['owner_id'], array_column($members, 'user_id'))) {
						if (empty($this->input->post('repeat'))) {
							if ($id = $this->meeting_model->insert($data)) {
								$this->mb_project->add_experience_point(10);
								$this->mb_project->update_parent_objects('meeting', $id);
								if (! empty($data['scheduled_start_time'])) {
									$this->mb_project->notify_meeting_start_time($id);
								}
								$user_emails = [$this->user_model->get_field($data['owner_id'], 'email')];
								$this->load->library('invite/invitation');
								$invite_data = [];
								$member_data = [];
								foreach ($team as $email) {
									$index = array_search($email, array_column($members, 'email'));
									if ($index !== false) {
										$member = $members[$index];
										do {
											$invite_code = $this->invitation->generateRandomString(64);
										} while ($this->meeting_member_invite_model->count_by('invite_code', $invite_code) > 0);

										if ($member['user_id'] != $this->current_user->user_id && $member['user_id'] != $data['owner_id']) {
											$invite_data[] = [
												'meeting_id' => $id,
												'invite_email' => $member['email'],
												'invite_code' => $invite_code,
											];
											if ($member['in_organization']) {
												$member_data[] = [
													'meeting_id' => $id,
													'user_id' => $member['user_id']
												];
											}
										} elseif ($member['user_id'] == $this->current_user->user_id && $member['user_id'] != $data['owner_id']) {
											$this->meeting_member_invite_model->insert([
												'meeting_id' => $id,
												'invite_email' => $member['email'],
												'invite_code' => $invite_code,
												'status' => 'accepted'
											]);
											$this->meeting_member_model->insert([
												'meeting_id' => $id,
												'user_id' => $member['user_id']
											]);
										}
									} else {
										do {
											$invite_code = $this->invitation->generateRandomString(64);
										} while ($this->meeting_member_invite_model->count_by('invite_code', $invite_code) > 0);

										$invite_data[] = [
											'meeting_id' => $id,
											'invite_email' => $email,
											'invite_code' => $invite_code,
										];
									}

									if (! in_array($email, $user_emails)) {
										$user_emails[] = $email;
									}
								}

								if (! empty($invite_data)) {
									$this->meeting_member_invite_model->insert_batch($invite_data);
								}

								if (! empty($member_data)) {
									$this->meeting_member_model->insert_batch($member_data);
								}
								// $this->mb_project->notify_members($id, 'meeting', $this->current_user, 'insert');
								$this->mb_project->invite_emails($id, 'meeting', $this->current_user, $user_emails);

								Template::set('close_modal', 1);
								Template::set('message_type', 'success');
								Template::set('message', lang('st_meeting_successfully_created'));
								Template::set('data', $this->ajax_meeting_data($id));

								// Just to reduce AJAX request size
								if (IS_AJAX) {
									Template::set('content', '');
								}
							}
						} else {
							require_once APPPATH . 'modules/meeting/libraries/rrule/RRuleInterface.php';
							require_once APPPATH . 'modules/meeting/libraries/rrule/RfcParser.php';
							require_once APPPATH . 'modules/meeting/libraries/rrule/RSet.php';
							require_once APPPATH . 'modules/meeting/libraries/rrule/RRule.php';

							$rule = RRule\RfcParser::parseRRule($this->input->post('rrule_recurring'));
							$rule['DTSTART'] = $data['scheduled_start_time'];
							$rrule = new RRule\RRule($rule);

							if ($rrule->isInfinite()) {
								$occurrences = $rrule->getOccurrencesBetween(null , date('Y-m-d', strtotime($rule['DTSTART'] . ' + 6 months')));
							} else {
								$occurrences = $rrule->getOccurrences();
							}

							$this->load->library('invite/invitation');
							foreach ($occurrences as $occurrence) {
								$invite_data = [];
								$member_data = [];
								$data['scheduled_start_time'] = $occurrence->format('Y-m-d H:i:s');
								if ($id = $this->meeting_model->insert($data)) {
									$this->mb_project->add_experience_point(10);
									$this->mb_project->update_parent_objects('meeting', $id);
									$this->mb_project->notify_meeting_start_time($id);
									$user_emails = [$this->user_model->get_field($data['owner_id'], 'email')];
									$this->load->library('invite/invitation');
									$invite_data = [];
									$member_data = [];
									foreach ($team as $email) {
										$index = array_search($email, array_column($members, 'email'));
										if ($index !== false) {
											$member = $members[$index];
											do {
												$invite_code = $this->invitation->generateRandomString(64);
											} while ($this->meeting_member_invite_model->count_by('invite_code', $invite_code) > 0);
	
											if ($member['user_id'] != $this->current_user->user_id && $member['user_id'] != $data['owner_id']) {
												$invite_data[] = [
													'meeting_id' => $id,
													'invite_email' => $member['email'],
													'invite_code' => $invite_code,
												];
												if ($member['in_organization']) {
													$member_data[] = [
														'meeting_id' => $id,
														'user_id' => $member['user_id']
													];
												}
											} elseif ($member['user_id'] == $this->current_user->user_id && $member['user_id'] != $data['owner_id']) {
												$this->meeting_member_invite_model->insert([
													'meeting_id' => $id,
													'invite_email' => $member['email'],
													'invite_code' => $invite_code,
													'status' => 'accepted'
												]);
												$this->meeting_member_model->insert([
													'meeting_id' => $id,
													'user_id' => $member['user_id']
												]);
											}
										} else {
											do {
												$invite_code = $this->invitation->generateRandomString(64);
											} while ($this->meeting_member_invite_model->count_by('invite_code', $invite_code) > 0);
	
											$invite_data[] = [
												'meeting_id' => $id,
												'invite_email' => $email,
												'invite_code' => $invite_code,
											];
										}
	
										if (! in_array($email, $user_emails)) {
											$user_emails[] = $email;
										}
									}
									
									$this->meeting_member_invite_model->insert_batch($invite_data);
									if (! empty($member_data)) {
										$this->meeting_member_model->insert_batch($member_data);
									}
									// $this->mb_project->notify_members($id, 'meeting', $this->current_user, 'insert');
									$this->mb_project->invite_emails($id, 'meeting', $this->current_user, $user_emails);
								}
							}

							Template::set('data', $data);
							Template::set('close_modal', 1);
							Template::set('message_type', 'success');
							Template::set('message', lang('st_meeting_successfully_created'));

							if (IS_AJAX) {
								Template::set('content', '');
							}
						}
					} else {
						Template::set('close_modal', 0);
						Template::set('message_type', 'danger');
						Template::set('message', lang('st_owner_can_not_be_member'));
					}
				}
			} else {
				Template::set('close_modal', 0);
				Template::set('message_type', 'danger');
				Template::set('message', lang('st_there_was_a_problem_while_creating_meeting'));
			}

			Template::render();
			return;
		}

		Template::set('project_members', $project_members);
		Template::set('action_key', $action->action_key);
		Template::render();
	}

	public function edit($meeting_key = null)
	{

		if (empty($meeting_key)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$meeting_id = $this->mb_project->get_object_id('meeting', $meeting_key);

		if (empty($meeting_id)) {
			Template::set_message(lang('st_meeting_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (! $this->mb_project->has_permission('meeting', $meeting_id, 'Project.Edit.All')) {
			$this->auth->restrict();
		}

		$keys = explode('-', $meeting_key);
		if (empty($keys) || count($keys) < 3) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$project_key = $keys[0];

		// get projecct id
		// $project_id = $this->project_model->get_project_id($project_key, $this->current_user->current_organization_id);
		// if ($project_id === false) {
		// 	redirect(DEFAULT_LOGIN_LOCATION);
		// }

		// if ($this->project_model->is_project_owner($project_id, $this->current_user->user_id) === false
		// && $this->project_member_model->is_project_member($project_id, $this->current_user->user_id) === false
		// && $this->auth->has_permission('Project.Edit.All') === false) {
		// 	redirect(DEFAULT_LOGIN_LOCATION);
		// }

		$project_id = $this->mb_project->get_object_id('project', $project_key);
		$project = $this->project_model->select('cost_of_time_1, value_of_time_1')->as_array()->find($project_id);

		Template::set('default_cost_of_time', $project['value_of_time_1']);
		Template::set('default_cost_of_time_name', $project['cost_of_time_1']);

		$meeting = $this->meeting_model->select('meetings.*, p.project_id')
								->join('actions a', 'a.action_id = meetings.action_id')
								->join('projects p', 'a.project_id = p.project_id')
								->join('user_to_organizations uto', 'uto.organization_id = p.organization_id AND uto.user_id = ' . $this->current_user->user_id)
								->limit(1)
								->find_by('meeting_key', $meeting_key);

		if ($meeting === false) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		// $meeting_members = $this->meeting_member_model->where('meeting_id', $meeting->meeting_id)->as_array()->find_all();
		// $meeting_members = $meeting_members && count($meeting_members) > 0 ? array_column($meeting_members, 'user_id') : [];
		$meeting_invitees = $this->meeting_member_invite_model->get_meeting_invited_members($meeting_id);
		$meeting_invitee_emails = empty($meeting_invitees) ? [] : array_column($meeting_invitees, 'invite_email');
		Template::set('meeting_members', $meeting_invitee_emails);
		Template::set('meeting', $meeting);

		$project_members = $this->user_model->get_organization_members($this->current_user->current_organization_id, $meeting->project_id);
		//$project_members = $this->meeting_member_invite_model->get_meeting_invited_members($meeting_id);

		Assets::add_js($this->load->view('create_js', [
			'project_members' => $project_members,
			'default_cost_of_time' => $project['value_of_time_1'],
			'default_cost_of_time_name' => $project['cost_of_time_1'],
			'meeting_members' => $meeting_invitee_emails
		], true), 'inline');

		if ($data = $this->input->post()) {
			$data = $this->meeting_model->prep_data($data);
			$data['modified_by'] = $this->current_user->user_id;

			if ($this->input->post('owner_id') == '') {
				$data['owner_id'] = $this->current_user->user_id;
			}

			if (! empty($this->input->post_get('scheduled_start_time'))) {
				$data['scheduled_start_time'] = get_utc_time($this->input->post_get('scheduled_start_time'));
			} else {
				unset($data['scheduled_start_time']);
			}

			// Add to project members if not in
			// Prevent duplicate row by MySQL Insert Ignore
			$query = $this->db->insert_string('project_members', [
				'project_id' => $meeting->project_id,
				'user_id' => $data['owner_id']
			]);

			$query = str_replace('INSERT', 'INSERT IGNORE', $query);
			$this->db->query($query);


			if ($team = $this->input->post('team')) {
				if ($team = explode(',', $team)) {
					$member_data = [];
					$owner_email = $this->user_model->get_field($data['owner_id'], 'email');
					// foreach ($team as $member) {
					// 	$member_data[] = [
					// 		'meeting_id' => $meeting->meeting_id,
					// 		'user_id' => $member
					// 	];

					// 	// Add to project members if not in
					// 	// Prevent duplicate row by MySQL Insert Ignore
					// 	$query = $this->db->insert_string('project_members', [
					// 		'project_id' => $meeting->project_id,
					// 		'user_id' => $member
					// 	]);

					// 	$query = str_replace('INSERT', 'INSERT IGNORE', $query);
					// 	$this->db->query($query);
					// }

					// $this->meeting_member_model->insert_batch($member_data);
					if (! empty($team)) {
						if (! in_array($owner_email, $team)) {
							$protected_members = [];

							$agenda_members = $this->agenda_model->select('u.email')
																->join('agenda_members am', 'agendas.agenda_id = am.agenda_id')
																->join('users u', 'u.user_id = am.user_id')
																->where('meeting_id', $meeting->meeting_id)
																->group_by('u.email')
																->as_array()
																->find_all();
							if (! empty($agenda_members)) {
								$protected_members = array_merge($protected_members, array_column($agenda_members, 'email'));
							}

							$homework_members = $this->homework_model->select('u.email')
														->join('homework_members hm', 'homework.homework_id = hm.homework_id')
														->join('users u', 'u.user_id = hm.user_id')
														->where('meeting_id', $meeting->meeting_id)
														->group_by('u.email')
														->as_array()
														->find_all();
							if (! empty($homework_members)) {
								$protected_members = array_merge($protected_members, array_column($homework_members, 'email'));
							}

							$protected_members = array_unique($protected_members);

							if (count($protected_members) == count(array_intersect($protected_members, array_unique(array_merge($team, [$owner_email]))))) {
								if ($this->meeting_model->update($meeting->meeting_id, $data)) {
									// $this->meeting_member_model->delete_where(['meeting_id' => $meeting->meeting_id]);
									if (! empty($data['scheduled_start_time'])) {
										$this->mb_project->notify_meeting_start_time($meeting->meeting_id);
									}
									$this->mb_project->update_parent_objects('meeting', $meeting->meeting_id);
									$meeting_members = $this->meeting_member_model->join('users', 'users.user_id = meeting_members.user_id')->where('meeting_id', $meeting->meeting_id)->as_array()->find_all();
									$member_emails = empty($meeting_members) ? [] : array_column($meeting_members, 'email');
									$member_ids = empty($meeting_members) ? [] : array_column($meeting_members, 'user_id');
									$invitee_emails = $meeting_invitee_emails;

									$members = $this->user_model->select('email, user_id,
																IF((SELECT COUNT(*) FROM ' . $this->db->dbprefix('user_to_organizations') . ' uto WHERE uto.user_id = ' . $this->db->dbprefix('users') . '.user_id AND uto.organization_id = "' . $this->current_user->current_organization_id . '") > 0, 1, 0) AS in_organization')
																->where_in('email', $team)
																->as_array()
																->find_all();

									if ($owner_email != $this->current_user->email && ! in_array($this->current_user->email, $team)) {
										$team[] = $this->current_user->email;
									}

									$invite_data = [];
									$member_data = [];
									$this->load->library('invite/invitation');
									foreach ($team as $email) {
										if (array_search($email, $invitee_emails) === false && $email != $this->current_user->email) {
											do {
												$invite_code = $this->invitation->generateRandomString(64);
											} while ($this->meeting_member_invite_model->count_by('invite_code', $invite_code) > 0);

											$invite_data[] = [
												'invite_email' => $email,
												'meeting_id' => $meeting->meeting_id,
												'invite_code' => $invite_code,
											];

											$index = array_search($email, array_column($members, 'email'));
											if ($index !== false) {
												$member = $members[$index];
												if ($member['in_organization']) {
													$member_data[] = [
														'meeting_id' => $meeting->meeting_id,
														'user_id' => $member['user_id']
													];
												}
											}
										}

										if ($email == $this->current_user->email && ! in_array($email, $member_emails)) {
											do {
												$invite_code = $this->invitation->generateRandomString(64);
											} while ($this->meeting_member_invite_model->count_by('invite_code', $invite_code) > 0);

											$this->meeting_member_invite_model->insert([
												'invite_email' => $email,
												'meeting_id' => $meeting->meeting_id,
												'invite_code' => $invite_code,
												'status' => 'accepted'
											]);
											$this->meeting_member_model->insert([
												'meeting_id' => $meeting->meeting_id,
												'user_id' => $this->current_user->user_id
											]);
										}
									}

									$removed_members = $this->meeting_member_model->select('users.user_id')
																					->join('users', 'users.user_id = meeting_members.user_id')
																					->where_not_in('email', $team)
																					->where('meeting_id', $meeting->meeting_id)
																					->as_array()
																					->find_all();
									$removed_member_ids = empty($removed_members) ? [] : array_column($removed_members, 'user_id');
									if (! empty($removed_member_ids)) {
										$this->meeting_member_model->where_in('user_id', $removed_member_ids)->delete_where(['meeting_id' => $meeting->meeting_id]);
									}
				
									$this->meeting_member_invite_model->where_not_in('invite_email', $team)->delete_where(['meeting_id' => $meeting->meeting_id]);

									if (! empty($invite_data)) {
										$this->meeting_member_invite_model->insert_batch($invite_data);
									}

									if (! empty($member_data)) {
										$this->meeting_member_model->insert_batch($member_data);
									}

									if ((! empty($data['status'])) && $data['status'] != $meeting->status) {
										$this->mb_project->notify_members($meeting->meeting_id, 'meeting', $this->current_user, 'update_status');
									}

									Template::set('close_modal', 1);
									Template::set('message_type', 'success');
									Template::set('message', lang('st_meeting_successfully_updated'));

									// Just to reduce AJAX request size
									if (IS_AJAX) {
										Template::set('content', '');
									}
								} else {
									Template::set('close_modal', 0);
									Template::set('message_type', 'danger');
									Template::set('message', lang('st_please_add_team_member'));
								}
							} else {
								Template::set('close_modal', 0);
								Template::set('message_type', 'danger');
								Template::set('message', lang('st_can_not_remove_member_of_hw_or_ag'));
							}
						} else {
							Template::set('close_modal', 0);
							Template::set('message_type', 'danger');
							Template::set('message', lang('st_owner_can_not_be_member'));
						}
					}
				}
			} else {
				Template::set('close_modal', 0);
				Template::set('message_type', 'danger');
				Template::set('message', lang('st_please_add_team_member'));
			}

			Template::render();
			return;
		}


		Template::render();
	}

	public function detail($meeting_key = null)
	{
		if (empty($meeting_key)) {
			Template::set_message(lang('st_meeting_does_not_exist'), 'danger');
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
				Template::set_message(lang('st_meeting_does_not_exist'), 'danger');
				redirect(DEFAULT_LOGIN_LOCATION);
			}

			$invited_emails = json_decode($meeting->members);
			if (! empty($invited_emails)) {
				$invited_members = $this->user_model->select('user_id, email, first_name, last_name, avatar')->where_in('email', $invited_emails)->as_array()->find_all();
			} else {
				$invited_emails = [];
				$invited_members = [];
			}

			$invited = [];
			foreach($invited_emails as $email) {
				$index = array_search($email, array_column($invited_members, 'email'));
				if ($index !== false) {
					$invited[] = $invited_members[$index];
				} else {
					$invited[count($invited)]['email'] = $email;
				}
			}
			$invited_members = $invited;
		} else {

			$keys = explode('-', $meeting_key);
			if (empty($keys) || count($keys) < 3) {
				Template::set_message(lang('st_meeting_key_does_not_exist'), 'danger');
				redirect(DEFAULT_LOGIN_LOCATION);
			}

			$meeting_id = $this->mb_project->get_object_id('meeting', $meeting_key);

			if (empty($meeting_id)) {
				Template::set_message(lang('st_meeting_key_does_not_exist'), 'danger');
				redirect(DEFAULT_LOGIN_LOCATION);
			}

			if (! $can_view = $this->mb_project->has_permission('meeting', $meeting_id, 'Project.View.All')) {
				Template::set_message(lang('st_you_have_no_permission_to_view_this_meeting'), 'warning');
				redirect(DEFAULT_LOGIN_LOCATION);
			}

			$project_key = $keys[0];
			$action_key = $keys[0] . '-' . $keys[1];

			$project_id = $this->mb_project->get_object_id('project', $project_key);
			if (empty($project_id)) {
				redirect(DEFAULT_LOGIN_LOCATION);
			}

			$meeting = $this->meeting_model->get_meeting_by_key(
				$meeting_key, 
				$this->current_user->current_organization_id, 
				'meetings.*, u.email, u.first_name, u.last_name, u.avatar'
				);

			if (! $meeting) {
				Template::set_message(lang('st_meeting_key_does_not_exist'), 'danger');
				redirect(DEFAULT_LOGIN_LOCATION);
			}

			// Only invited member or owner (even project or organization) can view
			$invited_members = $this->meeting_member_invite_model->get_meeting_invited_members($meeting_id);

			if ( !$can_view && $meeting->owner_id != $this->current_user->user_id
					&& ! in_array(
						$this->current_user->user_id, 
						array_column($invited_members, 'user_id'))
				) {

				Template::set_message(lang('st_you_have_no_permission_to_view_this_meeting'), 'warning');
				redirect(DEFAULT_LOGIN_LOCATION);
			}
		}

		$agendas = $this->agenda_model->select('agendas.*, u.email, u.first_name, u.last_name, u.avatar')
								->join('users u', 'u.user_id = agendas.owner_id', 'left')
								->where('meeting_id', $meeting_id)->find_all();

		if ($agendas) {
			foreach ($agendas as &$agenda) {
				$agenda->members = $this->agenda_member_model->select('avatar, email, first_name, last_name')
				->join('users u', 'u.user_id = agenda_members.user_id')
				->where('agenda_id', $agenda->agenda_id)
				->find_all();

				if (! empty($this->input->get('agenda_key')) && $this->input->get('agenda_key') == $agenda->agenda_key) {
					$chosen_agenda = $agenda;
				}
			}
		}

		$homeworks = $this->homework_model->where('meeting_id', $meeting_id)->find_all();

		if ($homeworks) {
			foreach ($homeworks as &$homework) {
				$homework->members = $this->homework_member_model->select('u.user_id, avatar, email, last_name, first_name, CONCAT(first_name, " ", last_name) AS full_name')
				->join('users u', 'u.user_id = homework_members.user_id')
				->where('homework_id', $homework->homework_id)
				->find_all();

				$homework->members = $homework->members ? $homework->members : [];

				$homework->attachments = $this->homework_attachment_model->where('homework_id', $homework->homework_id)->find_all();
				$homework->attachments = $homework->attachments ? $homework->attachments : [];
			}
		}

		$point_used = number_format($this->mb_project->total_point_used('meeting', $meeting->meeting_id), 2);

		$evaluated = $this->is_evaluated($meeting_id);

		if ($meeting->owner_id != $this->current_user->user_id) {
			$owner_evaluated = $this->is_evaluated($meeting_id, $meeting->owner_id);
			Template::set('owner_evaluated', $owner_evaluated);
		}
		// if not a private meeting
		if (empty($meeting->is_private)) {
			if (IS_AJAX) {
				echo json_encode([$evaluated, $invited_members , $point_used, $meeting, $agendas, $homeworks]); exit;
			}
			Assets::add_js($this->load->view('detail_js', [
				'meeting_key' => $meeting_key,
				'current_user' => $this->current_user,
				'chosen_agenda' => ! empty($chosen_agenda) ? $chosen_agenda : null,
				'evaluated' => ! empty($evaluated),
				'meeting_id' => $meeting->meeting_id
			], true), 'inline');
			Template::set('evaluated', $evaluated);
			Template::set('point_used', $point_used);
			Template::set('project_key', $project_key);
			Template::set('action_key', $action_key);
			Template::set('meeting_key', $meeting_key);
		} else {
			if (IS_AJAX) {
				echo json_encode([$invited_members, $meeting, $agendas, $homeworks]); exit;
			}

			Assets::add_js($this->load->view('detail_js', [
				'meeting_key' => null,
				'current_user' => $this->current_user,
				'chosen_agenda' => ! empty($chosen_agenda) ? $chosen_agenda : null,
				'is_private' => 1,
				'meeting_id' => $meeting->meeting_id
			], true), 'inline');
		}

		Assets::add_js($this->load->view('get_meeting_alert_js', [], true), 'inline');
		Template::set('invited_members', $invited_members);
		Template::set('page_title', $meeting->name);
		Template::set('current_user', $this->current_user);
		Template::set('agendas', $agendas);
		Template::set('homeworks', $homeworks);
		Template::set('meeting', $meeting);
		Template::set_view('detail');
		Template::render();
	}

	public function monitor($meeting_key = null)
	{
		if (empty($meeting_key)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$keys = explode('-', $meeting_key);
		if (empty($keys) || count($keys) < 3) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$meeting_id = $this->mb_project->get_object_id('meeting', $meeting_key);

		if (empty($meeting_id)) {
			Template::set_message(lang('st_meeting_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (! $this->mb_project->has_permission('meeting', $meeting_id, 'Project.View.All')) {
			$this->auth->restrict();
		}

		/*
			To access Meeting Monitor, user must be owner or team member of Meeting
		*/

		$meeting = $this->meeting_model->find_by('meeting_key', $meeting_key);

		if (! $meeting) {
			Template::set_message(lang('st_invalid_meeting_key'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$meeting->members = $this->meeting_member_model->get_meeting_member($meeting_id);

		// // We can't start without members
		// if (count($meeting->members) === 0) {
		// 	Template::set('message_type', 'warning');
		// 	Template::set('message', lang('st_cannot_start_meeting_without_any_member'));
		// 	Template::set('content', '');
		// 	Template::render();
		// 	return;
		// }
		
		$agendas = $this->agenda_model->select('agendas.*, 
											IF((SELECT tv.user_id FROM mb_agenda_votes tv WHERE mb_agendas.agenda_id = tv.agenda_id AND tv.user_id = "'. $this->current_user->user_id .'") IS NOT NULL, 1, 0) AS voted_skip,
											(SELECT COUNT(*) FROM mb_agenda_votes tv WHERE mb_agendas.agenda_id = tv.agenda_id) AS skip_votes', false)
									->join('users u', 'u.user_id = agendas.owner_id', 'left')
									->where('meeting_id', $meeting->meeting_id)->find_all();

		// We can't start without agendas
		if ($agendas === false) {
			Template::set('message_type', 'warning');
			Template::set('message', lang('st_cannot_start_meeting_without_any_agenda'));
			Template::set('content', '');
			Template::render();
			return;
		}

		foreach ($agendas as &$agenda) {
			$agenda->members = $this->agenda_member_model
			->select('avatar, email, first_name, last_name')
			->join('users u', 'u.user_id = agenda_members.user_id')
			->where('agenda_id', $agenda->agenda_id)->find_all();
		}

		$homeworks = $this->homework_model->where('meeting_id', $meeting_id)->find_all();

		if ($homeworks) {
			foreach ($homeworks as &$homework) {
				$homework->members = $this->homework_member_model->select('u.user_id, avatar, email, last_name, first_name')
				->join('users u', 'u.user_id = homework_members.user_id')
				->where('homework_id', $homework->homework_id)
				->find_all();

				$homework->members = $homework->members ? $homework->members : [];

				$homework->attachments = $this->homework_attachment_model->where('homework_id', $homework->homework_id)->find_all();
				$homework->attachments = $homework->attachments ? $homework->attachments : [];
			}
		}

		Assets::add_js($this->load->view('monitor_js', [
			'meeting_key' => $meeting_key
		], true), 'inline');
		Template::set('close_modal', 0);
		Template::set('current_user', $this->current_user);
		Template::set('agendas', $agendas);
		Template::set('homeworks', $homeworks);
		Template::set('meeting', $meeting);
		Template::set('now', gmdate('Y-m-d H:i:s'));
		Template::render();
	}

	public function decider($meeting_key = null)
	{
		if (empty($meeting_key)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$keys = explode('-', $meeting_key);
		if (empty($keys) || count($keys) < 3) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$project_key = $keys[0];

		$meeting_id = $this->mb_project->get_object_id('meeting', $meeting_key);

		if (empty($meeting_id)) {
			Template::set_message(lang('st_meeting_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (! $this->mb_project->has_permission('meeting', $meeting_id, 'Project.View.All')) {
			Template::set('message_type', 'danger');
			Template::set('message', lang('st_invalid_action'));
			Template::render();
			return;
		}

		/*
			To access Meeting Monitor, user must be owner or team member of Meeting
		*/

		$meeting = $this->meeting_model->find_by('meeting_key', $meeting_key);

		if (! $meeting) {
			Template::set_message(lang('st_invalid_meeting_key'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$action_key = $keys[0] . '-' . $keys[1];
		$meeting->members = $this->meeting_member_model->get_meeting_member($meeting_id);
		$agendas = $this->agenda_model->select('agendas.*, (finished_on - started_on) / 60 AS duration, 
											IF((SELECT tv.user_id FROM mb_agenda_votes tv WHERE mb_agendas.agenda_id = tv.agenda_id AND tv.user_id = "'. $this->current_user->user_id .'") IS NOT NULL, 1, 0) AS voted_skip,
											(SELECT COUNT(*) FROM mb_agenda_votes tv WHERE mb_agendas.agenda_id = tv.agenda_id) AS skip_votes', false)
									->join('users u', 'u.user_id = agendas.owner_id', 'left')
									->where('meeting_id', $meeting->meeting_id)->find_all();
		
		// We can't start without agendas
		if ($agendas === false) {
			Template::set('message_type', 'warning');
			Template::set('message', lang('st_cannot_start_meeting_without_any_agenda'));
			Template::set('content', '');
			Template::render();
			return;
		}

		foreach ($agendas as &$agenda) {
			$agenda->members = $this->agenda_member_model
			->select('avatar, email, first_name, last_name')
			->join('users u', 'u.user_id = agenda_members.user_id')
			->where('agenda_id', $agenda->agenda_id)->find_all();
		}

		$homeworks = $this->homework_model->where('meeting_id', $meeting->meeting_id)->find_all();
		if (empty($homeworks)) $homeworks = [];

		foreach ($homeworks as &$homework) {
			$homework->attachments = $this->homework_attachment_model->where('homework_id', $homework->homework_id)->find_all();
			$homework->attachments = $homework->attachments ? $homework->attachments : [];
		}

		$comments = $this->meeting_comment_model
		->select('meeting_comment_id, comment, meeting_comments.created_on, avatar, email, 
		IF(mb_meeting_comments.user_id = m.owner_id, 1, 0) AS is_owner,
		CONCAT(first_name, " ", last_name) AS full_name,')
		->join('users u', 'u.user_id = meeting_comments.user_id')
		->join('meetings m', 'm.meeting_id = meeting_comments.meeting_id')
		->where('meeting_comments.meeting_id', $meeting_id)
		->find_all();
		$comments = $comments ? $comments : [];

		foreach ($comments as &$comment) {
			$comment->created_on = display_time($comment->created_on, null, 'Y-m-d H:i:s');
		}

		Assets::add_js($this->load->view('decider_js', [
			'project_key' => $project_key,
			'meeting_key' => $meeting->meeting_key,
			'meeting_id' => $meeting_id
		], true), 'inline');
		Template::set('close_modal', 0);
		Template::set('current_user', $this->current_user);
		Template::set('agendas', $agendas);
		Template::set('meeting', $meeting);
		Template::set('comments', $comments);
		Template::set('homeworks', $homeworks);
		Template::set('now', gmdate('Y-m-d H:i:s'));
		Template::render();
	}

	/*
		A quick look at Goal & Comments
	*/
	public function preview($meeting_key = null)
	{
		if (empty($meeting_key)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$keys = explode('-', $meeting_key);
		if (empty($keys) || count($keys) < 3) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$project_key = $keys[0];

		$meeting_id = $this->mb_project->get_object_id('meeting', $meeting_key);

		if (empty($meeting_id)) {
			Template::set_message(lang('st_meeting_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (! $this->mb_project->has_permission('meeting', $meeting_id, 'Project.View.All')) {
			Template::set('message_type', 'danger');
			Template::set('message', lang('st_invalid_action'));
			Template::render();
			return;
		}

		$meeting = $this->meeting_model->select('owner_id, name, goal, created_on')->find_by('meeting_key', $meeting_key);

		if (! $meeting) {
			Template::set_message(lang('st_invalid_meeting_key'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$comments = $this->meeting_comment_model
		->select('meeting_comment_id, comment, meeting_comments.created_on, avatar, email, 
		IF(mb_meeting_comments.user_id = m.owner_id, 1, 0) AS is_owner,
		CONCAT(first_name, " ", last_name) AS full_name,')
		->join('users u', 'u.user_id = meeting_comments.user_id')
		->join('meetings m', 'm.meeting_id = meeting_comments.meeting_id')
		->where('meeting_comments.meeting_id', $meeting_id)
		->find_all();
		$comments = $comments ? $comments : [];

		foreach ($comments as &$comment) {
			$comment->created_on = display_time($comment->created_on, null, 'Y-m-d H:i:s');
		}

		Assets::add_js($this->load->view('preview_js', [
			'meeting_id' => $meeting_id
		], true), 'inline');
		Template::set('close_modal', 0);
		Template::set('meeting', $meeting);
		Template::set('meeting_id', $meeting_id);
		Template::set('meeting_key', $meeting_key);
		Template::set('comments', $comments);
		Template::render();
	}

	// Receive & process imcomming comment
	public function comment()
	{
		// Validation
		if ( trim($this->input->post('comment')) == '' ) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => 'empty_message'
			]);
			return;
		}

		// User in this meeting?
		if (! $this->mb_project->has_permission('meeting', $this->input->post('meeting_id'), 'Project.View.Al1l')) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_action')
			]);
			return;
		}

		if ( $id = $this->meeting_comment_model->insert([
			'comment' => trim($this->input->post('comment')),
			'user_id' => $this->current_user->user_id,
			'meeting_id' => $this->input->post('meeting_id')
		]) ) {
			$this->mb_project->update_parent_objects('meeting', $this->input->post('meeting_id'));
			echo json_encode([
				'message_type' => 'success',
				'data' => ['id' => $id]
			]);
			return;
		}

		echo json_encode([
			'message_type' => 'danger',
			'message' => lang('mt_something_went_wrong_please_refresh_and_try_again')
		]);
	}

	public function get_comment_data($meeting_id)
	{
		// User in this meeting?
		if (! $this->mb_project->has_permission('meeting', $meeting_id, 'Project.View.All')) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_action'),
				'data' => $this->mb_project->has_permission('meeting', $meeting_id, 'Project.View.All')
			]);
			return;
		}

		// Comments: Take 10 comments starting from $offset and don't take from self
		$comments = $this->meeting_comment_model
		->select('meeting_comment_id AS id, comment, meeting_comments.created_on, 
				CONCAT(first_name, " ", last_name) AS full_name, avatar, email,
				IF(mb_meeting_comments.user_id = m.owner_id, 1, 0) AS is_owner')
		->join('users u', 'u.user_id = meeting_comments.user_id')
		->join('meetings m', 'm.meeting_id = meeting_comments.meeting_id')
		->where('meeting_comments.meeting_id', $meeting_id)
		->where('meeting_comment_id >', (int) $this->input->post('commentOffset'))
		->order_by('meeting_comments.created_on')
		->limit(10)
		->find_all();

		$comments = $comments ? $comments : [];
		
		foreach ($comments as &$comment) {
			$comment->avatar_url = avatar_url($comment->avatar, $comment->email);
			$comment->mark_as_read = false;
			$comment->created_on = display_time($comment->created_on, null, 'Y-m-d H:i:s');
		}

		echo json_encode([
			'message_type' => 'success',
			'data' => [
				'comments' => $comments
			]
		]);
	}

	public function update_decider($meeting_key)
	{
		if (empty($meeting_key)) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_meeting_key_does_not_exist')
			]);
			return;
		}

		$keys = explode('-', $meeting_key);
		if (empty($keys) || count($keys) < 3) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_meeting_key_does_not_exist')
			]);
			return;
		}

		$meeting_id = $this->mb_project->get_object_id('meeting', $meeting_key);

		if (empty($meeting_id)) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_meeting_key_does_not_exist')
			]);
			return;
		}

		if (! $this->mb_project->has_permission('meeting', $meeting_id, 'Project.View.All')) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_action')
			]);
			return;
		}

		if (! is_array($this->input->post('agendas')) && count($this->input->post('agendas')) == 0) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_action')
			]);
			return;
		}

		$owner_id = $this->meeting_model->get_field($meeting_id, 'owner_id');
		$count_members = $this->meeting_member_model
		->select('u.user_id, avatar, email, first_name, last_name')
		->join('users u', 'u.user_id = meeting_members.user_id')
		->where('u.user_id !=', $owner_id)
		->where('meeting_id', $meeting_id)
		->as_array()
		->count_all();

		//evaluate validation
		if (! is_array($this->input->post('attendee_rate'))
		|| count($this->input->post('attendee_rate')) != $count_members
		|| count($this->input->post('attendee_rate')) < 1) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_please_select_all_confirmation_status')
			]);
		}

		// Prepare data

		$agenda_data = [];

		foreach ($this->input->post('agendas') as $agenda_key => $confirmation_status) {
			$agenda_data[] = [
				'agenda_key' => $agenda_key,
				'confirm_status' => $confirmation_status,
				'modified_by' => $this->current_user->user_id
			];
		}
		
		$attendee_rate_data = [];
		foreach ($this->input->post('attendee_rate') as $attendee_id => $rate) {
			$attendee_rate_data[] = [
				'meeting_id' => $meeting_id,
				'user_id' => $this->current_user->user_id,
				'attendee_id' => $attendee_id,
				'rate' => $rate
			];
		}

		if ($this->agenda_model->update_batch($agenda_data, 'agenda_key') && $this->meeting_member_rate_model->insert_batch($attendee_rate_data)) {
			$notes = $this->input->post('note') ? $this->input->post('note') : null;

			$this->meeting_model->skip_validation(TRUE)->update($meeting_id, [
				'manage_state' => 'done',
				'notes' => $notes
			]);

			$this->mb_project->update_parent_objects('meeting', $meeting_id);
			$this->send_meeting_result($meeting_id);

			echo json_encode([
				'message_type' => 'success',
				'message' => lang('st_all_agenda_confirmed_meeting_closed_out')
			]);
			return;
		}

		echo json_encode([
			'message_type' => 'danger',
			'message' => lang('st_unknown_error')
		]);
	}

	public function resolve_agenda($agenda_id = null)
	{
		if (empty($agenda_id)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$agenda = $this->agenda_model->select('name')->limit(1)->find($agenda_id);

		Template::set('id', 'resolve-agenda');
		Template::set('close_modal', 0);
		Template::set('current_user', $this->current_user);
		Template::set('agenda_id', $agenda_id);
		Template::set('agenda', $agenda);
		Template::render();
	}

	public function get_monitor_data($meeting_id)
	{
		if (empty($meeting_id)) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_meeting_key')
			]);
			return ;
		}

		$agendas = $this->agenda_model->select('agendas.agenda_id, agendas.status, agendas.started_on, agendas.time_assigned, 
											(SELECT COUNT(*) FROM mb_agenda_votes tv WHERE mb_agendas.agenda_id = tv.agenda_id) AS skip_votes', false)
									->join('users u', 'u.user_id = agendas.owner_id', 'left')
									->where('meeting_id', $meeting_id)->find_all();

		if ($agendas === false) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_meeting_key')
			]);
			return;
		}

		
		$homeworks = $this->homework_model->select('homework_id, description, status, time_spent')
										->where('meeting_id', $meeting_id)
										->find_all();
		$homeworks = $homeworks ? $homeworks : [];

		foreach ($homeworks as &$hw) {
			$hw->short_description = word_limiter($hw->description, 18);
		}

		$current_time = gmdate('Y-m-d H:i:s');

		// Real-time joiner
		$interval = 3;
		$this->meeting_member_model->where('user_id', $this->current_user->user_id)->update($meeting_id, ['last_online' => $current_time]);
		$online_members = $this->meeting_member_model->select('u.user_id, CONCAT(first_name, " ", last_name) AS full_name, avatar, email')
													->join('users u', 'u.user_id = meeting_members.user_id')
													->where('TIMEDIFF(DATE_ADD(last_online, INTERVAL '. $interval  .' SECOND), "'. $current_time .'") >= 0 ', null, false)
													->where('meeting_members.meeting_id', $meeting_id)
													->order_by('u.user_id')
													->find_all();
		$this->mb_project->update_parent_objects('meeting', $meeting_id);
		echo json_encode([
			'message_type' => 'success',
			'agendas' => $agendas,
			'homeworks' => $homeworks ? $homeworks : [],
			'meeting' => $this->meeting_model->select('status')->limit(1)->find($meeting_id),
			'online_members' => $online_members ? $online_members : [],
			'current_time' => $current_time,
		]);
	}

	public function vote_skip($agenda_id)
	{
		// Prevent duplicate row by MySQL Insert Ignore
		$query = $this->db->insert_string('agenda_votes', [
			'agenda_id' => $agenda_id,
			'user_id' => $this->current_user->user_id
		]);

		$query = str_replace('INSERT', 'INSERT IGNORE', $query);
		$test = $this->db->query($query);

		if ($test) {
			echo 1;
			return;
		}

		echo 0;
	}

	public function update_meeting_schedule() {

		$meeting = $this->meeting_model->select('meetings.*, u.timezone')
								->join('actions a', 'a.action_id = meetings.action_id')
								->join('projects p', 'a.project_id = p.project_id')
								->join('users u', 'u.user_id = ' . $this->current_user->user_id)
								->join('user_to_organizations uto', 'uto.organization_id = p.organization_id AND uto.user_id = ' . $this->current_user->user_id)
								->where('meetings.owner_id', $this->current_user->user_id)
								->limit(1)
								->find($this->input->post('meeting_id'));

		if ($meeting === false) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_meeting_key')
			]);
			return;
		}

		// Start meeting?
		if ($this->input->post('start')) {
			if ($meeting->status != 'ready') {
				echo json_encode([
					'message_type' => 'danger',
					'message' => lang('st_invalid_meeting_status')
				]);

				return;
			}

			if ($meeting->scheduled_start_time === NULL) {
				echo json_encode([
					'message_type' => 'danger',
					'message' => lang('st_invalid_schedule_time')
				]);

				return;
			}

			$current_time = gmdate('Y-m-d H:i:s');
			$query = $this->meeting_model->skip_validation(1)->update($meeting->meeting_id, [
				'status' => 'inprogress',
				'actual_start_time' => $current_time,
			]);

			if ($query) {
				$this->mb_project->update_parent_objects('meeting', $meeting->meeting_id);
				if ( is_array($this->input->post('time_assigned')) ) {
					$agenda_data = [];
					foreach ($this->input->post('time_assigned') as $agenda_id => $time_assigned) {
						$agenda_data[] = [
							'agenda_id' => $agenda_id,
							'time_assigned' => $time_assigned
						];
					}

					$this->agenda_model->skip_validation(1)->update_batch($agenda_data, 'agenda_id');
				}

				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_meeting_started'),
					'actual_start_time' => $current_time
				]);

				return;
			}

			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_unknown_error')
			]);
			return;
		}

		// Finish meeting
		if ($this->input->post('finish')) {
			if ($meeting->status != 'inprogress') {
				echo json_encode([
					'message_type' => 'danger',
					'message' => lang('st_invalid_meeting_status')
				]);

				return;
			}

			$agendas = $this->agenda_model->select('agenda_key')->where('meeting_id', $meeting->meeting_id)->where('(status = "inprogress" OR status ="open")', null, false)->find_all();

			if ($agendas) {
				echo json_encode([
					'message_type' => 'danger',
					'message' => lang('st_please_resolve_all_agenda_before_finish')
				]);

				return;
			}

			$current_time = gmdate('Y-m-d H:i:s');
			$query = $this->meeting_model->skip_validation(1)->update($meeting->meeting_id, [
				'status' => 'finished',
				'manage_state' => 'decide',
				'actual_end_time' => $current_time,
			]);
			
			if ($query) {
				$this->mb_project->update_parent_objects('meeting', $meeting->meeting_id);
				$this->mb_project->notify_members($meeting->meeting_id, 'meeting', $this->current_user, 'update_status');
				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_meeting_finished'),
					'actual_end_time' => $current_time
				]);

				return;
			}

			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_unknown_error')
			]);
			return;
		}

		// Validation
		if ( ! strtotime($this->input->post('scheduled_start_time')) ) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_schedule_time')
			]);

			return;
		}

		$query = $this->meeting_model->skip_validation(1)->update($meeting->meeting_id, [
			'status' => 'ready',
			'manage_state' => 'monitor',
			'scheduled_start_time' => get_utc_time($this->input->post('scheduled_start_time'))
		]);

		if ($query) {
			$this->mb_project->update_parent_objects('meeting', $meeting->meeting_id);
			$this->mb_project->notify_meeting_start_time($meeting->meeting_id);
			if ( is_array($this->input->post('time_assigned')) ) {
				$agenda_data = [];
				foreach ($this->input->post('time_assigned') as $agenda_id => $time_assigned) {
					$agenda_data[] = [
						'agenda_id' => $agenda_id,
						'time_assigned' => $time_assigned
					];
				}

				$this->agenda_model->skip_validation(1)->update_batch($agenda_data, 'agenda_id');
			}
			echo json_encode([
				'message_type' => 'success',
				'message' => lang('st_schedule_time_saved')
			]);

			return;
		}

		echo json_encode([
			'message_type' => 'danger',
			'message' => lang('st_unknown_error')
		]);
	}

	public function update_agenda_status()
	{
		$agenda = $this->agenda_model->select('agendas.*, u.timezone, s.meeting_id')
								->join('meetings s', 's.meeting_id = agendas.meeting_id')
								->join('actions a', 'a.action_id = s.action_id')
								->join('projects p', 'a.project_id = p.project_id')
								->join('user_to_organizations uto', 'uto.organization_id = p.organization_id AND uto.user_id = ' . $this->current_user->user_id)
								->join('users u', 'u.user_id = ' . $this->current_user->user_id)
								->where('s.owner_id', $this->current_user->user_id)
								->where('s.status', 'inprogress')
								->limit(1)
								->find($this->input->post('agenda_id'));

		if ($agenda === false) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_action')
			]);
			return;
		}

		// Save timezone to user's locale
		$current_time = gmdate('Y-m-d H:i:s');

		switch ($this->input->post('status')) {
			case 'inprogress':
				if ($agenda->status != 'open') {
					echo json_encode([
						'message_type' => 'danger',
						'message' => lang('st_invalid_agenda_status')
					]);

					return;
				}

				// We can only start 1 agenda at a time
				$agenda_in_progress = $this->agenda_model->select('agendas.*, u.timezone')
								->join('meetings s', 's.meeting_id = agendas.meeting_id')
								->join('actions a', 'a.action_id = s.action_id')
								->join('projects p', 'a.project_id = p.project_id')
								->join('user_to_organizations uto', 'uto.organization_id = p.organization_id AND uto.user_id = ' . $this->current_user->user_id)
								->join('users u', 'u.user_id = ' . $this->current_user->user_id)
								->where('s.owner_id', $this->current_user->user_id)
								->where('agendas.status', 'inprogress')
								->limit(1)
								->find_by('agendas.meeting_id', $agenda->meeting_id);

				if ($agenda_in_progress) {
					echo json_encode([
						'message_type' => 'danger',
						'message' => lang('st_please_finish_other_agenda')
					]);
					return;
				}
				
				$this->agenda_model->update($agenda->agenda_id, [
					'status' => 'inprogress', 
					'time_assigned' => $this->input->post('time_assigned'), 
					'started_on' => $current_time,
					'modified_by' => $this->current_user->user_id
				]);
				$this->mb_project->notify_members($agenda->agenda_id, 'agenda', $this->current_user, 'update_status');
				$this->mb_project->update_parent_objects('agenda', $agenda->agenda_id);
				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_agenda_started'),
					'started_on' => $current_time
				]);

				break;

			case 'jumped':
				if ($agenda->status != 'inprogress') {
					echo json_encode([
						'message_type' => 'danger',
						'message' => lang('st_invalid_agenda_status')
					]);

					return;
				}

				$this->agenda_model->update($agenda->agenda_id, [
					'status' => 'jumped', 
					'finished_on' => $current_time, 
					'modified_by' => $this->current_user->user_id
				]);
				$this->mb_project->notify_members($agenda->agenda_id, 'agenda', $this->current_user, 'update_status');
				$this->mb_project->update_parent_objects('agenda', $agenda->agenda_id);
				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_agenda_jumped')
				]);

				break;
			case 'skipped':

				if ($agenda->status != 'open') {
					echo json_encode([
						'message_type' => 'danger',
						'message' => lang('st_invalid_agenda_status')
					]);

					return;
				}

				$this->agenda_model->update($agenda->agenda_id, [
					'status' => 'skipped', 
					'modified_by' => $this->current_user->user_id
				]);
				$this->mb_project->notify_members($agenda->agenda_id, 'agenda', $this->current_user, 'update_status');
				$this->mb_project->update_parent_objects('agenda', $agenda->agenda_id);
				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_agenda_skipped')
				]);

				break;

			case 'resolved':
				if ($agenda->status != 'inprogress') {
						echo json_encode([
							'message_type' => 'danger',
							'message' => lang('st_invalid_agenda_status')
						]);

						return;
				}

				$this->agenda_model->update($agenda->agenda_id, [
					'status' => 'resolved',
					'finished_on' => $current_time, 
					'comment' => $this->input->post('comment'),
					'modified_by' => $this->current_user->user_id
				]);
				$this->mb_project->notify_members($agenda->agenda_id, 'agenda', $this->current_user, 'update_status');
				$this->mb_project->update_parent_objects('agenda', $agenda->agenda_id);
				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_agenda_resolved')
				]);

				break;

			case 'parking_lot':
				if ($agenda->status != 'inprogress') {
						echo json_encode([
							'message_type' => 'danger',
							'message' => lang('st_invalid_agenda_status')
						]);

						return;
				}

				$this->agenda_model->update($agenda->agenda_id, [
					'status' => 'parking_lot',
					'finished_on' => $current_time, 
					'comment' => $this->input->post('comment'),
					'modified_by' => $this->current_user->user_id
				]);
				$this->mb_project->notify_members($agenda->agenda_id, 'agenda', $this->current_user, 'update_status');
				$this->mb_project->update_parent_objects('agenda', $agenda->agenda_id);
				echo json_encode([
					'message_type' => 'success',
					'message' => lang('st_agenda_placed')
				]);

				break;

			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_invalid_agenda_status')
			]);
			return;
		}
	}

	public function update_status($meeting_key = null)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (empty($meeting_key)) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_update_status_fail')
			]);
			exit;
		}

		$meeting_id = $this->meeting_model->get_meeting_id($meeting_key, $this->current_user->current_organization_id);
		if (! $meeting_id) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_update_status_fail')
			]);
			exit;
		}

		$buttons = [
			'open' => [
				'icon' => 'ion-ios-play',
				'label' => lang('st_start_meeting'),
				'next_status' => 'inprogress',
			],
			'in-progress' => [
				'icon' => 'ion-android-done',
				'label' => lang('st_ready'),
				'next_status' => 'ready',
			],
			'ready-for-review' => [
				'icon' => 'ion-android-done-all',
				'label' => lang('st_resolve_meeting'),
				'next_status' => 'resolved',
			],
			'resolved' => [
				'icon' => 'ion-ios-book',
				'label' => lang('st_reopen'),
				'next_status' => 'open',
			]
		];

		$status = $this->input->post('status');
		$updated = $this->meeting_model->skip_validation(true)->update($meeting_id, [
										'status' => $status
									]);
		if (! $updated) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => $status//lang('st_update_status_fail')
			]);
			exit;
		}
		$this->mb_project->notify_members($meeting_id, 'meeting', $this->current_user, 'update_status');
		$this->mb_project->update_parent_objects('meeting', $meeting_id);
		echo json_encode([
			'message_type' => 'success',
			'message' => lang('st_update_status_success')
		]);
		exit;
	}

	public function add_team_member($meeting_key = null)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (empty($meeting_key)) {
			echo 0;
			exit;
		}

		$meeting_id = $this->meeting_model->get_meeting_id($meeting_key, $this->current_user->current_organization_id);
		if (! $meeting_id) {
			echo 0;
			exit;
		}

		$user_id = $this->input->post('user_id');

		if ($user_id === NULL || $meeting_id === NULL) {
			echo 0;
			return;
		}

		if (! class_exists('User_model')) {
			$this->load->model('users', 'user_model');
		}
		// Is the target user inside current user's organization?
		$check = $this->user_model->join('user_to_organizations uto', 'uto.user_id = users.user_id')
									->where('uto.organization_id', $this->current_user->current_organization_id)
									->count_by('users.user_id', $user_id);

		if ($check === 0) {
			echo 0;
			return;
		}

		// Prevent duplicate row by MySQL Insert Ignore
		$query = $this->db->insert_string('meeting_members', ['user_id' => $user_id, 'meeting_id' => $meeting_id]);
		$query = str_replace('INSERT', 'INSERT IGNORE', $query);
		echo (int) $this->db->query($query);
	}

	public function remove_team_member($meeting_key = null)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (empty($meeting_key)) {
			echo 0;
			exit;
		}

		$meeting_id = $this->meeting_model->get_meeting_id($meeting_key, $this->current_user->current_organization_id);
		if (! $meeting_id) {
			echo 0;
			exit;
		}

		$user_id = $this->input->post('user_id');

		if ($user_id === NULL || $meeting_id === NULL) {
			echo 0;
			return;
		}

		// Prevent duplicate row by MySQL Insert Ignore
		echo (int) $this->meeting_member_model->delete_where(['user_id' => $user_id, 'meeting_id' => $meeting_id]);
	}

	public function evaluator($meeting_key)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (empty($meeting_key)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$keys = explode('-', $meeting_key);
		if (empty($keys) || count($keys) < 3) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$meeting_id = $this->mb_project->get_object_id('meeting', $meeting_key);

		if (empty($meeting_id)) {
			Template::set_message(lang('st_meeting_key_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (! $this->mb_project->has_permission('meeting', $meeting_id, 'Project.Edit.All')) {
			$this->auth->restrict();
		}

		/*
			To access Meeting Monitor, user must be owner or team member of Meeting
		*/

		$meeting = $this->meeting_model->select('*, (actual_end_time - actual_start_time) / 60 AS actual_elapsed_time')->find($meeting_id);

		if (! $meeting) {
			Template::set_message(lang('st_invalid_meeting_key'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$evaluated = $this->is_evaluated($meeting_id);

		// if ($evaluated === true || ($meeting->manage_state != 'evaluate' && $meeting->manage_state != 'done')) {
		// 	Template::set('message', $evaluated === true ? lang('st_meeting_already_evaluated') : lang('st_meeting_not_ready_for_evaluate'));
		// 	Template::set('message_type', 'danger');
		// 	Template::set('close_modal', 1);
		// }

		$meeting->members = $this->meeting_member_model
							->select('u.user_id, avatar, email, first_name, last_name')
							->join('users u', 'u.user_id = meeting_members.user_id')
							->where('u.user_id !=', $meeting->owner_id)
							->where('meeting_id', $meeting_id)
							->as_array()
							->find_all();

		$agendas = $this->agenda_model->select('agendas.*, 
											IF((SELECT tv.user_id FROM mb_agenda_votes tv WHERE mb_agendas.agenda_id = tv.agenda_id AND tv.user_id = "'. $this->current_user->user_id .'") IS NOT NULL, 1, 0) AS voted_skip,
											(SELECT COUNT(*) FROM mb_agenda_votes tv WHERE mb_agendas.agenda_id = tv.agenda_id) AS skip_votes', false)
									->join('users u', 'u.user_id = agendas.owner_id', 'left')
									->where('meeting_id', $meeting->meeting_id)->find_all();
		if (is_array($agendas) && count($agendas) > 0) {
			foreach ($agendas as &$agenda) {
				$agenda->members = $this->agenda_member_model
									->select('avatar, email, first_name, last_name')
									->join('users u', 'u.user_id = agenda_members.user_id')
									->where('agenda_id', $agenda->agenda_id)
									->find_all();
			}
		}

		if ($meeting->owner_id == $this->current_user->user_id) {
			$role = 'owner';
		} elseif (in_array($this->current_user->user_id, array_column($meeting->members, 'user_id'))) {
			$role = 'member';
		} else {
			$role = 'other';
		}

		$homeworks = $this->homework_model->select('*, IF((SELECT COUNT(*) FROM ' . $this->db->dbprefix('homework_members') . ' hm WHERE hm.user_id = "' . $this->current_user->user_id . '" AND hm.homework_id = ' . $this->db->dbprefix('homework') . '.homework_id) > 0, 1, 0) AS is_member')
										->where('meeting_id', $meeting->meeting_id)
										->as_object()->find_all();
		if (empty($homeworks)) $homeworks = [];

		$is_hw_member_count = 0;
		foreach ($homeworks as &$homework) {
			if ($homework->is_member) {
				$is_hw_member_count += 1;
			}
			$homework->attachments = $this->homework_attachment_model->where('homework_id', $homework->homework_id)->find_all();
			$homework->attachments = $homework->attachments ? $homework->attachments : [];
		}

		if ($this->input->post()) {
			// change: still able to evaluate even when meeting manage state is done 
			if (($evaluated === false && ($meeting->manage_state == 'evaluate' || $meeting->manage_state == 'decide' || $meeting->manage_state == 'done')) && $role != 'other') {
				if ($role == 'owner') {
					if (! is_array($this->input->post('attendee_rate'))
					|| count($this->input->post('attendee_rate')) != count($meeting->members)) {
						$validation_error = true;
					}

					if (empty($validation_error)) {
						if (count($this->input->post('attendee_rate')) > 0) {
							$attendee_rate_data = [];
							foreach ($this->input->post('attendee_rate') as $attendee_id => $rate) {
								$attendee_rate_data[] = [
									'meeting_id' => $meeting_id,
									'user_id' => $this->current_user->user_id,
									'attendee_id' => $attendee_id,
									'rate' => $rate
								];
							}

							$attendees_rated = $this->meeting_member_rate_model->insert_batch($attendee_rate_data);
							if (empty($attendees_rated)) {
								$insert_error = true;
							}
						}
					}
				} else {
					if (empty($this->input->post('meeting_rate'))
					|| ! is_array($this->input->post('agenda_rate'))
					|| count($this->input->post('agenda_rate')) != count($agendas)
					|| count($this->input->post('homework_rate')) != $is_hw_member_count) {
						$validation_error = true;
					}

					if (empty($validation_error)) {
						$meeting_rated = $this->meeting_member_model->where('meeting_id', $meeting->meeting_id)
																	->update_where('user_id', $this->current_user->user_id, ['rate' => $this->input->post('meeting_rate')]);
						if (empty($meeting_rated)) {
							$insert_error = true;
						}

						if (count($this->input->post('agenda_rate')) > 0 && empty($insert_error)) {
							$agenda_rate_data = [];
							foreach ($this->input->post('agenda_rate') as $agenda_id => $rate) {
								$agenda_rate_data[] = [
									'agenda_id' => $agenda_id,
									'user_id' => $this->current_user->user_id,
									'rate' => $rate
								];
							}

							$agendas_rated = $this->agenda_rate_model->insert_batch($agenda_rate_data);
							if (empty($agendas_rated)) {
								$insert_error = true;
							}
						}

						if (count($this->input->post('homework_rate')) > 0 && empty($insert_error)) {
							$homework_rate_data = [];
							$homework_ids = [];
							foreach ($this->input->post('homework_rate') as $homework_id => $rate) {
								$homework_rate_data[] = [
									'homework_id' => $homework_id,
									'user_id' => $this->current_user->user_id,
									'rate' => $rate
								];
							}

							$homeworks_rated = $this->homework_rate_model->insert_batch($homework_rate_data);
							if (empty($homeworks_rated)) {
								$insert_error = true;
							}
						}
					}
				}

				if (empty($insert_error)) {
					Template::set('message', lang('st_rating_success'));
					Template::set('message_type', 'success');
					Template::set('close_modal', 0);
					// change: done after owner rating 
					if ($meeting->owner_id == $this->current_user->user_id) {
						$this->meeting_model->skip_validation(true)->update($meeting_id, [
							'manage_state' => 'done'
						]);
						$this->mb_project->update_parent_objects('meeting', $meeting_id);
						$this->send_meeting_result($meeting_id);
					}
					//$this->done_meeting_if_qualified($meeting);
				}
			} else {
				Template::set('message', lang('st_unable_evaluate') . $meeting->manage_state);
				Template::set('message_type', 'danger');
				Template::set('close_modal', 0);
			}
		} else {
			$validation_error = true;
		}

		if (! empty($validation_error) && ! empty($_POST)) {
			Template::set('message', lang('st_need_to_vote_all_items'));
			Template::set('message_type', 'danger');
			Template::set('close_modal', 0);
		}

		if (! empty($insert_error)) {
			Template::set('message', lang('st_there_was_a_problem_while_evaluate'));
			Template::set('message_type', 'danger');
			Template::set('close_modal', 0);
		}

		$point_used = number_format($this->mb_project->total_point_used('meeting', $meeting_id), 2);
		Template::set('role', $role);
		Template::set('point_used', $point_used);
		Template::set('meeting', $meeting);
		Template::set('agendas', $agendas);
		Template::set('homeworks', $homeworks);
		Template::render('ajax');
	}

	public function check_state($meeting_key)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (empty($meeting_key)) {
			echo -1;
			exit;
		}

		$meeting_id = $this->mb_project->get_object_id('meeting', $meeting_key);

		if (empty($meeting_id)) {
			echo -2;
			exit;
		}

		$meeting = $this->meeting_model->find($meeting_id);
		if ($meeting->manage_state != 'evaluate' || ! $this->is_evaluated($meeting_id, $meeting->owner_id)) {
			echo 0;
			exit;
		}

		echo 1;
		exit;
	}

	public function dashboard_evaluate($mode = 'user')
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$modes = ['meeting', 'agenda', 'user', 'homework'];

		if (! in_array($mode, $modes)) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_wrong_mode')
			]); return;
		}

		$meeting_id = $this->input->post('meeting_id');
		if (empty($meeting_id)) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_missing_data')
			]); return;
		}

		if ($mode == 'meeting') {
			$rate = $this->input->post('rate');

			if (empty($rate)) {
				echo json_encode([
					'message_type' => 'danger',
					'message' => lang('st_missing_data')
				]); return;
			}
		}

		if ($mode == 'user') {
			$user_id = $this->input->post('user_id');
			$rate = $this->input->post('rate');

			if (empty($user_id) || empty($rate)) {
				echo json_encode([
					'message_type' => 'danger',
					'message' => lang('st_missing_data')
				]); return;
			}
		}

		if ($mode == 'agenda') {
			$agenda_id = $this->input->post('agenda_id');
			$meeting_id = $this->input->post('meeting_id');
			$rate = $this->input->post('rate');

			if (empty($agenda_id) || empty($rate)) {
				echo json_encode([
					'message_type' => 'danger',
					'message' => lang('st_missing_data')
				]); return;
			}
		}

		if ($mode == 'homework') {
			$homework_id = $this->input->post('homework_id');
			$rate = $this->input->post('rate');

			if (empty($homework_id) || empty($rate)) {
				echo json_encode([
					'message_type' => 'danger',
					'message' => lang('st_missing_data')
				]); return;
			}
		}

		$manage_state = $this->meeting_model->get_field($meeting_id, 'manage_state');
		$evaluated = $this->is_evaluated($meeting_id);

		// change: still able to evaluate even when meeting manage state is done 
		if ($evaluated === false && ($manage_state == 'evaluate'|| $manage_state == 'decide' || $manage_state == 'done')) {
			if ($mode == 'user') {
				$rated = $this->meeting_member_rate_model->skip_validation(true)->insert([
					'meeting_id' => $meeting_id,
					'user_id' => $this->current_user->user_id,
					'attendee_id' => $user_id,
					'rate' => $rate
				]);

				$this->mb_project->update_parent_objects('meeting', $meeting_id);
			}

			if ($mode == 'meeting') {
				$rated = $this->meeting_member_model->skip_validation(true)
													->where('meeting_id', $meeting_id)
													->update_where('user_id', $this->current_user->user_id, ['rate' => $rate]);

				$this->mb_project->update_parent_objects('meeting', $meeting_id);
			}

			if ($mode == 'agenda') {
				$rated = $this->agenda_rate_model->skip_validation(true)->insert([
					'agenda_id' => $agenda_id,
					'user_id' => $this->current_user->user_id,
					'rate' => $rate
				]);

				$this->mb_project->update_parent_objects('agenda', $agenda_id);
			}

			if ($mode == 'homework') {
				$rated = $this->homework_rate_model->skip_validation(true)->insert([
					'homework_id' => $homework_id,
					'user_id' => $this->current_user->user_id,
					'rate' => $rate
				]);

				$this->mb_project->update_parent_objects('homework', $homework_id);
			}
		} else {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_evaluated')
			]); return;
		}

		$meeting = $this->meeting_model->select('owner_id')->find($meeting_id);
		$meeting->meeting_id = $meeting_id;
		$meeting->members = $this->meeting_member_model
								->select('u.user_id, avatar, email, first_name, last_name')
								->join('users u', 'u.user_id = meeting_members.user_id')
								// ->where('u.user_id !=', $this->current_user->user_id)
								->where('meeting_id', $meeting_id)
								->as_array()
								->find_all();

		// change: done after owner rating 
		if ($meeting->owner_id == $this->current_user->user_id) {
			$evaluated = $this->is_evaluated($meeting_id);
			if ($evaluated) {
				$this->meeting_model->skip_validation(true)->update($meeting_id, [
					'manage_state' => 'done'
				]);
				$this->mb_project->update_parent_objects('meeting', $meeting_id);
				$this->send_meeting_result($meeting_id);
			}
		}
		// $this->done_meeting_if_qualified($meeting);
		echo json_encode([
			'message_type' => 'success',
			'message' => lang('st_rate_db_success')
		]); return;
	}

	private function ajax_meeting_data($meeting_id)
	{
		$data = $this->meeting_model->select('meetings.*, CONCAT(first_name, " ", last_name) AS full_name, first_name, last_name, avatar, email')
									->join('users u', 'u.user_id = owner_id', 'LEFT')
									->limit(1)
									->find($meeting_id);

		if ($data) {
			$data->display_scheduled_start_time = empty($data->scheduled_start_time) ? null : display_time($data->scheduled_start_time);
			$data->display_user = display_user($data->email, $data->first_name, $data->last_name, $data->avatar);
			$data->lang_status = lang('st_' . $data->status);
		}

		return $data;
	}

	private function done_meeting_if_qualified($meeting)
	{
		// $team_ids[] = $meeting->owner_id;
		// if (! empty($meeting->members)) {
		// 	$members = (array) $meeting->members;
		// 	$team_ids = array_merge($team_ids, array_column($members, 'user_id'));
		// }

		// $team_ids = array_unique($team_ids);
		// // $can_done = $this->meeting_member_rate_model
		// // 				->select('user_id')
		// // 				->where('meeting_id', $meeting->meeting_id)
		// // 				->where_in('user_id', $team_ids)
		// // 				->group_by('user_id')
		// // 				->count_all() == count($team_ids) ? true : false;

		// $time_voted_by_members = pow((count($members) - 1), 2);
		// $time_voted_by_owner = count($members);

		// if (in_array($meeting->owner_id, array_column($members, 'user_id'))) {
		// 	$time_voted_by_owner = 0;
		// }

		// $can_done = $this->meeting_member_rate_model
		// 				->select('user_id')
		// 				->where('meeting_id', $meeting->meeting_id)
		// 				->where_in('user_id', $team_ids)
		// 				->count_all() == ($time_voted_by_members + $time_voted_by_owner) ? true : false;

		$owner_evaluated = false;
		$members_evaluated = false;
		$owner_id = $meeting->owner_id;
		$members = $meeting->members;
		$meeting_id = $meeting->meeting_id;
		// check owner evaluated or not
		$evaluated_members = $this->meeting_member_rate_model
							->where('meeting_id', $meeting_id)
							->where('user_id', $owner_id)
							->count_all();
		$all = $this->meeting_member_model
							->where('meeting_id', $meeting_id)
							->count_all();
		if ($all == $evaluated_members && $all > 0) {
			$owner_evaluated = true;
		}
		// check members evaluated or not
		$meeting_rated = $this->meeting_member_model
							->where('meeting_id', $meeting_id)
							->where('rate IS NOT NULL')
							->count_all() == count($members);

		$all_agendas = $this->agenda_model->select('agenda_id')
								->join('meetings m', 'm.meeting_id = agendas.meeting_id')
								->where('m.meeting_id', $meeting_id)
								->as_array()
								->find_all();
		if (empty($all_agendas)) $all_agendas = [];
		$all_agenda_ids = array_column($all_agendas, 'agenda_id');

		$agendas_rated = count($all_agenda_ids) > 0 ? ($this->agenda_rate_model
															->where_in('agenda_id', $all_agenda_ids)
															->count_all() == (count($all_agenda_ids) * count($members))) : false;

		$all_homeworks = $this->homework_model->select('homework_id')
							->join('meetings m', 'm.meeting_id = homework.meeting_id')
							->where('m.meeting_id', $meeting_id)
							->as_array()
							->find_all();
		if (empty($all_homeworks)) $all_homeworks = [];
		$all_homework_ids = array_column($all_homeworks, 'homework_id');

		$homeworks_rated = count($all_homework_ids) > 0 ? ($this->homework_rate_model
																->where_in('homework_id', $all_homework_ids)
																->count_all() == (count($all_homework_ids) * count($members))) : true;

		if ($meeting_rated && $agendas_rated && $homeworks_rated) {
			$members_evaluated = true;
		}

		if ($owner_evaluated && $members_evaluated) {
			$this->meeting_model->skip_validation(true)->update($meeting->meeting_id, ['manage_state' => 'done']);
			$this->mb_project->update_parent_objects('meeting', $meeting->meeting_id);
			$this->send_meeting_result($meeting->meeting_id);
		}
	}

	private function is_evaluated($meeting_id, $user_id = null) {
		// $evaluated_members = $this->meeting_member_rate_model
		// 						->select('user_id')
		// 						->where('meeting_id', $meeting_id)
		// 						->where('user_id', $this->current_user->user_id)
		// 						->group_by('user_id')
		// 						->as_array()
		// 						->find_all();
		
		// $evaluated_ids = [];
		// $evaluated = false;

		// if (is_array($evaluated_members) && count($evaluated_members) > 0) {
		// 	$evaluated_ids = array_column($evaluated_members, 'user_id');
		// 	if (in_array($this->current_user->user_id, $evaluated_ids)) {
		// 		$evaluated = true;
		// 	}
		// }

		if (empty($user_id)) {
			$user_id = $this->current_user->user_id;
		}

		$owner_id = $this->meeting_model->get_field($meeting_id, 'owner_id');

		$evaluated = false;

		if ($owner_id == $user_id) { // if is owner
			$evaluated_members = $this->meeting_member_rate_model
								->where('meeting_id', $meeting_id)
								->where('user_id', $user_id)
								->count_all();
			$all = $this->meeting_member_model
								->where('meeting_id', $meeting_id)
								->count_all();

			// change: only hw assignees can rate their hw, so meeting owner also rate homework if he/she is a hw assignee
			$all_homeworks = $this->homework_model->select('homework.homework_id')
								->join('meetings m', 'm.meeting_id = homework.meeting_id')
								->join('homework_members hm', 'hm.homework_id = homework.homework_id')
								->where('m.meeting_id', $meeting_id)
								->where('hm.user_id', $user_id)
								->group_by('homework.homework_id')
								->as_array()
								->find_all();
			if (empty($all_homeworks)) $all_homeworks = [];
			$all_homework_ids = array_column($all_homeworks, 'homework_id');

			$homeworks_rated = count($all_homework_ids) > 0 ? ($this->homework_rate_model
																->where('user_id', $user_id)
																->where_in('homework_id', $all_homework_ids)
																->count_all() == count($all_homework_ids)) : true;

			if ($all == $evaluated_members && $all > 0 && $homeworks_rated) {
				$evaluated = true;
			}
		} else {
			$meeting_rated = $this->meeting_member_model
									->where('meeting_id', $meeting_id)
									->where('user_id', $user_id)
									->where('rate IS NOT NULL')
									->count_all() == 1;

			$all_agendas = $this->agenda_model->select('agenda_id')
								->join('meetings m', 'm.meeting_id = agendas.meeting_id')
								->where('m.meeting_id', $meeting_id)
								->as_array()
								->find_all();
			if (empty($all_agendas)) $all_agendas = [];
			$all_agenda_ids = array_column($all_agendas, 'agenda_id');

			$agendas_rated = count($all_agenda_ids) > 0 ? ($this->agenda_rate_model
																->where('user_id', $user_id)
																->where_in('agenda_id', $all_agenda_ids)
																->count_all() == count($all_agenda_ids)) : false;

			$all_homeworks = $this->homework_model->select('homework.homework_id')
								->join('meetings m', 'm.meeting_id = homework.meeting_id')
								->join('homework_members hm', 'hm.homework_id = homework.homework_id')
								->where('m.meeting_id', $meeting_id)
								->where('hm.user_id', $user_id)
								->group_by('homework.homework_id')
								->as_array()
								->find_all();
			if (empty($all_homeworks)) $all_homeworks = [];
			$all_homework_ids = array_column($all_homeworks, 'homework_id');

			$homeworks_rated = count($all_homework_ids) > 0 ? ($this->homework_rate_model
																->where('user_id', $user_id)
																->where_in('homework_id', $all_homework_ids)
																->count_all() == count($all_homework_ids)) : true;

			if ($meeting_rated && $agendas_rated && $homeworks_rated) {
				$evaluated = true;
			}
		}

		return $evaluated;
	}

	public function invite($meeting_id = null, $invite_code = null, $decision = null)
	{
		$decisions = ['accept', 'maybe', 'decline'];
		$meeting = $this->meeting_model->find($meeting_id);
		$meeting_invite = $this->meeting_member_invite_model
			->where('invite_email', $this->current_user->email)
			->where("BINARY(invite_code) = BINARY('$invite_code')", null, false)
			->find_by('meeting_id', $meeting_id);

		if (empty($meeting) || empty($meeting_invite)) {
			if (IS_AJAX) {
				echo json_encode([
					'message' => lang('st_something_went_wrong'),
					'message_type' => 'danger'
				]);
				return;
			}

			Template::set_message(lang('st_something_went_wrong'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (empty($meeting_id) || empty($invite_code) || empty($decision) || ! in_array($decision, $decisions)) {
			if (IS_AJAX) {
				echo json_encode([
					'message' => lang('st_something_went_wrong'),
					'message_type' => 'danger'
				]);
				return;
			} else {
				Template::set_message(lang('st_something_went_wrong'), 'danger');
				redirect(DEFAULT_LOGIN_LOCATION);
			}
		} else {
			if ($meeting_invite->status != 'NEEDS-ACTION') {
				if (IS_AJAX) {
					echo json_encode([
						'message' => lang('st_decided'),
						'message_type' => 'warning'
					]);
					return;
				} else {
					Template::set_message(lang('st_decided'), 'warning');
					redirect('meeting/' . $meeting->meeting_key);
				}

			} elseif ($decision == 'accept' || $decision == 'maybe') {
				if ($decision == 'accept') {
					$status = 'ACCEPTED';
				} else {
					$status = 'TENTATIVE';
				}

				$this->load->model('organization/organization_model');
				$organization = $this->meeting_model->select('s.*')
									->join('actions a', 'a.action_id = meetings.action_id')
									->join('projects p', 'p.project_id = a.project_id')
									->join('organizations s', 's.organization_id = p.organization_id')
									->find_by('meetings.meeting_id', $meeting_id);
				if (empty($organization)) {
					if (IS_AJAX) {
						echo json_encode([
							'message' => lang('st_something_went_wrong'),
							'message_type' => 'danger'
						]);
						return;
					} else {
						Template::set_message(lang('st_something_went_wrong'), 'danger');
						redirect('meeting/' . $meeting->meeting_key);
					}
				}
				$in_organization = $this->user_model->join('user_to_organizations uto', 'uto.user_id = users.user_id')
													->where('organization_id', $organization->organization_id)
													->where('email', $this->current_user->email)
													->count_all() > 0;

				if (! $in_organization) {
					$this->load->model('roles/role_model');
					$default_role = $this->role_model->where('join_default', 1)->find_by('organization_id', $organization->organization_id);
					$added = $this->db->insert('user_to_organizations', [
						'user_id' => $this->current_user->user_id,
						'organization_id' => $organization->organization_id,
						'role_id' => $default_role->role_id
					]);
					if ($added === false) {
						if (IS_AJAX) {
							echo json_encode([
								'message' => lang('st_something_went_wrong'),
								'message_type' => 'danger'
							]);
							return;
						} else {
							Template::set_message(lang('st_something_went_wrong'), 'danger');
							redirect('meeting/' . $meeting->meeting_key);
						}
					}
				}

				$in_meeting = $this->meeting_member_model
											->where('meeting_id', $meeting_id)
											->where('user_id', $this->current_user->user_id)
											->count_all() > 0;
				if (! $in_meeting) {
					$added = $this->meeting_member_model->insert([
						'meeting_id' => $meeting_id,
						'user_id' => $this->current_user->user_id
					]);
					if ($added === false) {
						if (IS_AJAX) {
							echo json_encode([
								'message' => lang('st_something_went_wrong'),
								'message_type' => 'danger'
							]);
							return;
						} else {
							Template::set_message(lang('st_something_went_wrong'), 'danger');
							redirect('meeting/' . $meeting->meeting_key);
						}
					}
				}
				$this->mb_project->update_parent_objects('meeting', $meeting_id);
			} else {
				$status = 'DECLINED';
			}

			$decided = $this->meeting_member_invite_model
				->update($meeting_invite->meeting_member_invite_id, [
					'status' => $status
				]);

			if (! $decided) {
				if (IS_AJAX) {
					echo json_encode([
						'message' => lang('st_something_went_wrong'),
						'message_type' => 'danger'
					]);
					return;
				} else {
					Template::set_message(lang('st_something_went_wrong'), 'danger');
				}
			}
		}

		if (IS_AJAX) {
			if ($decision != 'decline') {
				echo json_encode([
					'message' => lang('st_welcome_to_meeting'),
					'message_type' => 'success'
				]);
			} else {
				echo json_encode([
					'message' => lang('st_declined_successfully'),
					'message_type' => 'success'
				]);
			}
			return;
		} else {
			if ($decision != 'decline') {
				Template::set_message(lang('st_welcome_to_meeting'), 'success');
			} else {
				Template::set_message(lang('st_declined_successfully'), 'success');
			}
			redirect('meeting/' . $meeting->meeting_key);
		}
	}

	public function import()
	{
		if (! $this->input->is_ajax_request()) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if ($this->input->get('save_step_1') === '') {
			$step = 2;
		}

		if ($this->input->get('save_step_2') === '') {
			$step = 3;
		}

		if (empty($step)) {
			$step = 1;
		}

		Template::set('close_modal', 0);

		$calendar_id = $this->input->get('calendarId');
		$event_id = $this->input->get('eventId');
		$start = $this->input->get('start');
		$end = $this->input->get('end');
		if (empty($calendar_id) || empty($event_id) || empty($start) || empty($end)) {
			Template::set('message', lang('st_wrong_provided_data'));
			Template::set('message_type', 'danger');
			Template::set('close_modal', 1);
		}

		$this->config->load('google_api');

		require_once APPPATH . 'modules/users/libraries/google-api-client/vendor/autoload.php';
		$client_id = $this->config->item('client_id');
		$client_secret = $this->config->item('client_secret');

		$client = new Google_Client();
		$client->setAccessType("offline");
		$client->setClientId($client_id);
		$client->setClientSecret($client_secret);
		$client->refreshToken($this->current_user->google_refresh_token);
		$token = $client->getAccessToken();

		$service = new Google_Service_Calendar($client);

		$event = $service->events->get($calendar_id, $event_id);

		if (! empty($event->recurrence)) {
			require_once APPPATH . 'modules/meeting/libraries/rrule/RRuleInterface.php';
			require_once APPPATH . 'modules/meeting/libraries/rrule/RfcParser.php';
			require_once APPPATH . 'modules/meeting/libraries/rrule/RSet.php';
			require_once APPPATH . 'modules/meeting/libraries/rrule/RRule.php';

			$rule = RRule\RfcParser::parseRRule($event->recurrence[0]);
			$rule['DTSTART'] = empty($event->start->date) ? $event->start->dateTime : $event->start->date;

			$rrule = new RRule\RRule($rule);
			$event->recurringHumanReadable = $rrule->humanReadable([
				'date_formatter' => function($date) {
					return $date->format('F j, Y - H:i:s');
				},
				'explicit_infinite' => false
			]);
		}

		// if ($step == 1) {
		// 	Template::set('close_modal', 0);
		// }

		if ($step == 2) {
			$import_mode = $this->input->get('import_mode');
			if (trim($import_mode) == '') {
				Template::set('message', lang('st_wrong_provided_data'));
				Template::set('message_type', 'danger');
				Template::set('close_modal', 0);
			}

			foreach ($event->attendees as &$attendee) {
				if ($this->user_model->where('email', $attendee->email)->count_all() > 0) {
					$attendee->in_mb_system = true;
				}
			}

			if (has_permission('Project.Edit.All')) {
				$projects = $this->project_model->select('projects.project_id, projects.name')
												->where('projects.organization_id', $this->current_user->current_organization_id)
												->where('projects.status !=', 'draft') // exclude draft
												->order_by('projects.modified_on', 'desc')
												->find_all();
			} else {
				$projects = $this->project_model->select('projects.project_id, projects.name')
												->join('users u', 'u.user_id = projects.owner_id')
												->join('project_members pm', 'projects.project_id = pm.project_id')
												->where('projects.status !=', 'archive')
												->where('projects.status !=', 'draft') // exclude draft
												->where('(pm.user_id = \'' . $this->current_user->user_id . '\' OR projects.owner_id = \'' . $this->current_user->user_id . '\')')
												->where('organization_id', $this->current_user->current_organization_id)
												->order_by('projects.modified_on', 'desc')
												->group_by('projects.project_id')
												->find_all();
			}
		}

		if ($step == 3) {
			$step = 2;
			$project_id = $this->input->get('project_id');
			$owner_email = $this->input->get('owner_email');
			$user_emails = $this->input->get('user_emails');
			$import_mode = $this->input->get('import_mode');

			if (empty($project_id) || empty($owner_email) || empty($user_emails)) {
				Template::set('message', lang('st_wrong_provided_data'));
				Template::set('message_type', 'danger');
				Template::set('close_modal', 1);
			}

			if (! empty($user_emails)) {
				$user_emails = explode(',', $user_emails);
			} else {
				$user_emails = [];
			}
			$project_key = $this->project_model->get_field($project_id, 'cost_code');
			$owner = $this->user_model->select('user_id')->find_by('email', $owner_email);

			if (empty($owner) || empty($project_key)) {
				$error = true;
			} else {
				$action_id = $this->mb_project->get_object_id('action', $project_key . '-1');
				$owner_id = $owner->user_id;

				$owner_in_organization = $this->db->select('COUNT(*) as count')
												->from('user_to_organizations uto')
												->where('user_id', $owner_id)
												->where('organization_id', $this->current_user->current_organization_id)
												->get()->row()->count > 0;

				if (! $owner_in_organization) {
					$this->load->model('roles/role_model');
					$default_role = $this->role_model->where('join_default', 1)->find_by('organization_id', $this->current_user->current_organization_id);
					$added = $this->db->insert('user_to_organizations', [
						'user_id' => $this->current_user->user_id,
						'organization_id' => $organization->organization_id,
						'role_id' => $default_role->role_id
					]);
				}

				$this->load->library('invite/invitation');
				if (empty($event->recurrence) || $import_mode == 0) {
					$meeting_data = [
						'meeting_key' => $this->mb_project->get_next_key($project_key . '-1'),
						'action_id' => $action_id,
						'scheduled_start_time' => $start,
						'in' => (strtotime($end) - strtotime($start)) / 60,
						'in_type' => 'minutes',
						'name' => $event->summary,
						'owner_id' => $owner_id,
						'google_event_id' => $event_id
					];

					$meeting_id = $this->meeting_model->skip_validation(true)->insert($meeting_data);
					if ($meeting_id === false) {
						$error = true;
					} else {
						if (! empty($user_emails)) {
							foreach ($user_emails as $email) {
								if ($email != $this->current_user->email) {
									do {
										$invite_code = $this->invitation->generateRandomString(64);
									} while ($this->meeting_member_invite_model->count_by('invite_code', $invite_code) > 0);

									$member_data[] = [
										'meeting_id' => $meeting_id,
										'invite_email' => $email,
										'invite_code' => $invite_code,
									];
								} else {
									$this->meeting_member_model->insert([
										'meeting_id' => $meeting_id,
										'user_id' => $this->current_user->user_id
									]);
								}
							}

							if (! empty($member_data)) {
								$this->meeting_member_invite_model->insert_batch($member_data);
							}
							$this->mb_project->invite_emails($meeting_id, 'meeting', $this->current_user, $user_emails);
						}

						Template::set('message', lang('st_import_success'));
						Template::set('message_type', 'success');
						Template::set('close_modal', 1);
					}
				} else {
					if ($rrule->isInfinite()) {
						$occurrences = $rrule->getOccurrencesBetween(null , date('Y-m-d', strtotime($rule['DTSTART'] . ' + 6 months')));
					} else {
						$occurrences = $rrule->getOccurrences();
					}

					foreach ($occurrences as $occurrence) {
						$meeting_data = [
							'meeting_key' => $this->mb_project->get_next_key($project_key . '-1'),
							'action_id' => $action_id,
							'scheduled_start_time' => $occurrence->format('Y-m-d H:i:s'),
							'in' => (strtotime($end) - strtotime($start)) / 60,
							'in_type' => 'minutes',
							'name' => $event->summary,
							'owner_id' => $owner_id,
							'google_event_id' => $event_id
						];

						$meeting_id = $this->meeting_model->skip_validation(true)->insert($meeting_data);
						if ($meeting_id === false) {
							$error = true; break;
						} else {
							if (! empty($user_emails)) {
								foreach ($user_emails as $email) {
									if ($email != $this->current_user->email) {
										do {
											$invite_code = $this->invitation->generateRandomString(64);
										} while ($this->meeting_member_invite_model->count_by('invite_code', $invite_code) > 0);

										$member_data[] = [
											'meeting_id' => $meeting_id,
											'invite_email' => $email,
											'invite_code' => $invite_code,
										];
									} else {
										$this->meeting_member_model->insert([
											'meeting_id' => $meeting_id,
											'user_id' => $this->current_user->user_id
										]);
									}
								}

								$this->mb_project->invite_emails($meeting_id, 'meeting', $this->current_user, $user_emails);
							}
						}
					}

					if (! empty($member_data)) {
						$this->meeting_member_invite_model->insert_batch($member_data);
					}
				}

				if (! empty($error)) {
					Template::set('message', lang('st_wrong_provided_data'));
					Template::set('message_type', 'danger');
					Template::set('close_modal', 1);
				} else {
					Template::set('message', lang('st_import_success'));
					Template::set('message_type', 'success');
					Template::set('close_modal', 1);

					if (! empty($this->input->get('delete_source'))) {
						$service->events->delete($calendar_id, $event_id);
					}
				}
			}
		}

		if (empty($projects)) {
			$projects = [];
		}

		Template::set('projects', $projects);
		Template::set('step', $step);
		Template::set('event', $event);
		Template::render();
	}

	public function get_events($type)
	{
		if (! $this->input->is_ajax_request()) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$types = ['ggc', 'mbc', 'mbcp'];
		$event_list = [];

		if (empty($type) || empty($this->input->get('start')) || empty($this->input->get('end')) || ! in_array($type, $types)) {
			echo json_encode([]);exit;
		}

		if ($type == 'ggc') {
			if (empty($this->current_user->google_refresh_token)) {
				echo json_encode([]);exit;
			}

			$this->config->load('google_api');

			require_once APPPATH . 'modules/users/libraries/google-api-client/vendor/autoload.php';
			$client_id = $this->config->item('client_id');
			$client_secret = $this->config->item('client_secret');
			$api_key = $this->config->item('api_key');

			$client = new Google_Client();
			$client->setAccessType("offline");
			$client->setClientId($client_id);
			$client->setClientSecret($client_secret);
			$client->setDeveloperKey($api_key);
			$client->refreshToken($this->current_user->google_refresh_token);
			$token = $client->getAccessToken();

			$service = new Google_Service_Calendar($client);

			// $calendars = $service->calendarList->listCalendarList();
			// $calendar_list = [];
			// if (! isset($calendars->error)) {
			// 	while (true) {
			// 		foreach ($calendars->getItems() as $calendar) {
			// 			$calendar_list[$calendar->id] = $calendar->summary;
			// 		}
			// 		$pageToken = $calendars->getNextPageToken();
			// 		if ($pageToken) {
			// 			$calOptParams['pageToken'] = $pageToken;
			// 			$calendars = $service->calendarList->listCalendarList($calOptParams);
			// 		} else {
			// 			break;
			// 		}
			// 	}
			// }

			// At this time we only get the events from the primary calendar
			$calendar_list = [
				$this->current_user->email => 'primary'
			];

			foreach ($calendar_list as $calendar_id => $calendar) {
				$time_min = new DateTime($this->input->get('start'), new DateTimeZone(standard_timezone($this->current_user->timezone)));
				$time_max = new DateTime($this->input->get('end'), new DateTimeZone(standard_timezone($this->current_user->timezone)));
				$event_options = [
					'timeMin' => $time_min->format('c'),
					'timeMax' => $time_max->format('c'),
					'singleEvents' => true,
					'timeZone' => 'UTC'
				];

				$events = $service->events->listEvents($calendar_id, $event_options);
				if (! isset($events->error)) {
					$total_time = 0;
					while (true) {
						$items = $events->getItems();
						foreach ($items as $item) {
							if (! empty($item->attendees)) {
								foreach ($item->attendees as $attendee) {
									if ($attendee->self == true) {
										if ($attendee->responseStatus !== 'declined') {
											$temp = [
												'start' => ! empty($item->start->date) ? $item->start->date : $item->start->dateTime,
												'end' => ! empty($item->end->date) ? $item->end->date : $item->end->dateTime,
												'title' => $item->summary,
												'url' => $item->htmlLink,
												'calendarId' => $calendar_id,
												'eventId' => empty($item->recurringEventId) ? $item->id : $item->recurringEventId
											];

											// for first time itnit only
											if (! empty($this->input->get('init'))) {
												$temp['isOwner'] = ! empty($item->organizer->self);
												$temp['ownerEmail'] = $item->organizer->email;
												$temp['attendees'] = $item->attendees;
											}

											if (! empty($item->start->date)) {
												$temp['allDay'] = true;
											}

											$event_list[] = $temp;
										}
										break;
									}
								}
							} else {
								$temp = [
									'start' => ! empty($item->start->date) ? $item->start->date : $item->start->dateTime,
									'end' => ! empty($item->end->date) ? $item->end->date : $item->end->dateTime,
									'title' => $item->summary,
									'url' => $item->htmlLink,
									'calendarId' => $calendar_id,
									'eventId' => empty($item->recurringEventId) ? $item->id : $item->recurringEventId
								];

								// for first time itnit only
								if (! empty($this->input->get('init'))) {
									$temp['isOwner'] = ! empty($item->organizer->self);
									$temp['ownerEmail'] = $item->organizer->email;
									$temp['attendees'] = [];
								}

								if (! empty($item->start->date)) {
									$temp['allDay'] = true;
								}

								$event_list[] = $temp;
							}
						}
						$pageToken = $events->getNextPageToken();
						if ($pageToken) {
							$event_options['pageToken'] = $pageToken;
							$events = $service->events->listEvents($calendar_id, $event_options);
						} else {
							break;
						}
					}

					if (! empty($event_list)) {
						$imported_events = $this->meeting_model->select('meetings.google_event_id')
																	->join('actions a', 'a.action_id = meetings.action_id')
																	->join('projects p', 'p.project_id = a.project_id')
																	->join('users u', 'u.user_id = meetings.owner_id')
																	->where('p.organization_id', $this->current_user->current_organization_id)
																	->where('google_event_id IS NOT NULL')
																	->group_by('meetings.google_event_id')
																	->as_array()
																	->find_all();
						if (empty($imported_events)) {
							$imported_events = [];
						} else {
							$imported_events = array_column($imported_events, 'google_event_id');
						}

						foreach ($event_list as $index => $event) {
							if (in_array($event['eventId'], $imported_events)) {
								unset($event_list[$index]);
							}
						}

						$event_list = array_values($event_list);
					}
				}
			}
		}

		if ($type == 'mbc') {
			$events = $this->meeting_model->select('meetings.*, u.first_name, u.last_name, u.email')
												->join('actions a', 'a.action_id = meetings.action_id')
												->join('projects p', 'p.project_id = a.project_id')
												->join('users u', 'u.user_id = meetings.owner_id')
												->join('meeting_members sm', 'sm.meeting_id = meetings.meeting_id AND sm.user_id = "' . $this->current_user->user_id . '"', 'LEFT')
												->where('p.organization_id', $this->current_user->current_organization_id)
												->where('(sm.user_id = "' . $this->current_user->user_id . '" OR meetings.owner_id = "' . $this->current_user->user_id . '")')
												->where('meetings.scheduled_start_time IS NOT NULL')
												//->where('meetings.scheduled_start_time BETWEEN "' . $this->input->get('start') . '" AND "' . $this->input->get('end') . '"')
												->group_by('meetings.meeting_id')
												->find_all();
			$events = $events && count($events) > 0 ? $events : [];

			foreach ($events as $event) {
				$event_list[] = [
					'start' => $event->scheduled_start_time,
					'end' => date('Y-m-d H:i:s', strtotime($event->scheduled_start_time . ' + ' . $event->in . ' ' . $event->in_type)),
					'title' => "{$event->meeting_key}: {$event->name}",
					'url' => site_url('/meeting/' . $event->meeting_key),
					'meeting_id' => $event->meeting_id
				];
			}
		}

		if ($type == 'mbcp') {
			$events = $this->meeting_model->where('organization_id', $this->current_user->current_organization_id)
												->where('created_by', $this->current_user->user_id)
												->where('is_private', 1)
												//->where('scheduled_start_time BETWEEN "' . $this->input->get('start') . '" AND "' . $this->input->get('end') . '"')
												->find_all();
			$events = $events && count($events) > 0 ? $events : [];

			foreach ($events as $event) {
				$event_list[] = [
					'start' => $event->scheduled_start_time,
					'end' => date('Y-m-d H:i:s', strtotime($event->scheduled_start_time . ' + ' . $event->in . ' ' . $event->in_type)),
					'title' => "Unspecified: {$event->name}",
					'url' => site_url('meeting/' . $event->meeting_id),
					'meeting_id' => $event->meeting_id
				];
			}
		}

		foreach($event_list as &$event) {
			$event['start'] = display_time($event['start']);
			$event['end'] = display_time($event['end']);
		}

		echo json_encode($event_list); exit;
	}

	public function init_project()
	{
		$this->load->model('project/project_model');
		$data = $this->input->post('data');

		if (empty($data)) {
			Template::set('message', 'Wrong data structure.');
			Template::set('message_type', 'danger');
			Template::set('close_modal', 0);
			Template::render(); exit;
		}

		$data = json_decode($data, true);

		if (empty($data) || empty($data['currentStep']) || $data['currentStep'] < 40) {
			Template::set('message', 'Wrong data structure.');
			Template::set('message_type', 'danger');
			Template::set('close_modal', 0);
			Template::render(); exit;
		}

		$projects = $this->project_model->select('project_id, name')
										->where('organization_id', $this->current_user->current_organization_id)
										->where('status !=', 'draft')
										->find_all();


		Template::set('data', $data);
		Template::set('projects', empty($projects) ? [] : $projects);
		Template::render();
	}

	public function init_team()
	{
		$this->load->model('user/user_model');
		$data = $this->input->post('data');

		if (empty($data)) {
			Template::set('message', 'Wrong data structure.');
			Template::set('message_type', 'danger');
			Template::set('close_modal', 0);
			Template::render(); exit;
		}

		$data = json_decode($data, true);

		if (empty($data) || empty($data['currentStep']) || $data['currentStep'] < 50) {
			Template::set('message', 'Wrong data structure.');
			Template::set('message_type', 'danger');
			Template::set('close_modal', 0);
			Template::render(); exit;
		}

		$users = [];

		foreach ($data['meetings'] as $meeting) {
			foreach ($meeting['members'] as $email) {
				if (! isset($users[$email])) {
					$users[$email] = [
						'projects' => [$meeting['project_id']],
						'as_guest' => true
					];
				} else {
					$users[$email]['as_guest'] = true;

					if (! in_array($meeting['project_id'], $users[$email]['projects'])) {
						$users[$email]['projects'][] = $meeting['project_id'];
					}
				}
			}

			if (! isset($users[$meeting['owner']])) {
				$users[$meeting['owner']] = [
					'projects' => [$meeting['project_id']],
					'as_owner' => true
				];
			} else {
				$users[$meeting['owner']]['as_owner'] = true;
				if (! in_array($meeting['project_id'], $users[$meeting['owner']]['projects'])) {
					$users[$meeting['owner']]['projects'][] = $meeting['project_id'];
				}
			}
		}

		$emails = array_keys($users);

		$existed_users = $this->user_model->select('CONCAT(first_name, " ", last_name) AS full_name, email')
										->where_in('email', $emails)
										->find_all();
		if (empty($existed_users)) $existed_users = [];

		foreach ($existed_users as $existed_user) {
			$users[$existed_user->email]['existed'] = true;
			$users[$existed_user->email]['name'] = $existed_user->full_name;
		}

		Template::set('users', $users);
		Template::render();
	}

	public function init_finish()
	{
		$this->load->model('user/user_model');
		$data = $this->input->post('data');

		if (empty($data)) {
			Template::set('message', 'Wrong data structure.');
			Template::set('message_type', 'danger');
			Template::set('close_modal', 0);
			Template::render(); exit;
		}

		$data = json_decode($data, true);

		if (empty($data) || empty($data['currentStep']) || $data['currentStep'] < 60) {
			Template::set('message', 'Wrong data structure.');
			Template::set('message_type', 'danger');
			Template::set('close_modal', 0);
			Template::render(); exit;
		}

		$emails = [];
		$projects = [];
		$total_time = 0;

		foreach ($data['meetings'] as $meeting) {
			foreach ($meeting['members'] as $email) {
				if (! in_array($email, $emails)) {
					$emails[] = $email;
				}
			}

			if (! in_array($meeting['owner'], $emails)) {
				$emails[] = $meeting['owner'];
			}

			if (! in_array($meeting['project_id'], $projects)) {
				$projects[] = $meeting['project_id'];
			}

			$total_time += $meeting['in'] * (count($meeting['members']) + 1);
		}

		$meetings_count = count($data['meetings']);
		$existed_users_count = $this->user_model->where_in('email', $emails)->count_all();
		$new_users_count = count($emails) - $existed_users_count;
		$existed_projects_count = count($projects) - $data['new_projects_count'];
		$new_projects_count = $data['new_projects_count'];
		$total_time /= 60;

		Template::set('summary', [
			'meetings_count' => $meetings_count,
			'existed_users_count' => $existed_users_count,
			'new_users_count' => $new_users_count,
			'existed_projects_count' => $existed_projects_count,
			'new_projects_count' => $new_projects_count,
			'total_time' => $total_time
		]);
		Template::render();
	}

	public function init_import()
	{
		$this->load->model('user/user_model');
		$data = $this->input->post('data');

		if (empty($data)) {
			Template::set('message', 'Wrong data structure.');
			Template::set('message_type', 'danger');
			Template::set('close_modal', 0);
			Template::render(); exit;
		}

		$data = json_decode($data, true);

		if (empty($data) || empty($data['currentStep']) || $data['currentStep'] < 60) {
			Template::set('message', 'Wrong data structure.');
			Template::set('message_type', 'danger');
			Template::set('close_modal', 0);
			Template::render(); exit;
		}

		$attachment = $_FILES;

		$unspecified_project = $this->project_model->where('is_unspecified_project', 1)->find_by('organization_id', $this->current_user->current_organization_id);
		foreach ($data['meetings'] as $event_id => $meeting) {
			$user_emails = $meeting['members'];
			$user_emails[] = $meeting['owner'];
			$user_emails = array_unique($user_emails);

			if ($meeting['project_id'] == '') {
				if (empty($unspecified_project)) {
					$organiztion_owner = $this->user_to_organizations_model->join('roles r', 'r.role_id = user_to_organizations.role_id')
								->where('r.is_public', 1)
								->where('r.system_default', 0)
								->where('r.join_default', 0)
								->limit(1)
								->find_by('user_to_organizations.organization_id', $this->current_user->current_organization_id);

					$unspecified_project = [
						'cost_code' => 'USP',
						'owner_id' => $organiztion_owner->user_id,
						'name' => 'Unspecified Project',
						'organization_id' => $this->current_user->current_organization_id,
						'is_unspecified_project' => 1,
						'created_by' => $this->current_user->user_id
					];

					$unspecified_project_id = $this->project_model->insert($unspecified_project);
					$unspecified_project['project_id'] = $unspecified_project_id;
					$unspecified_project = (object) $unspecified_project;

					/*
					Temporary disable Action functionality, auto create an Action after creating Project 
					and automatically uses it as default action for creating Meeting
					*/

					$this->action_model->insert([
						'project_id' => $unspecified_project_id,
						'action_key' => 'USP-1', // PJK-1
						'owner_id' => $organiztion_owner->user_id,
						'name' => '[default_action]',
						'action_type' => 'decide',
						'success_condition' => 'action_gate',
						'sort_order' => 999
					]);
				}
				$meeting['project_id'] = $unspecified_project->project_id;
			}

			$project_key = $this->project_model->get_field($meeting['project_id'], 'cost_code');
			$owner = $this->user_model->select('user_id')->find_by('email', $meeting['owner']);

			if (! empty($owner)) {
				$action_id = $this->mb_project->get_object_id('action', $project_key . '-1');

				$owner_id = $owner->user_id;
				$owner_in_organization = $this->db->select('COUNT(*) as count')
												->from('user_to_organizations uto')
												->where('user_id', $owner_id)
												->where('organization_id', $this->current_user->current_organization_id)
												->get()->row()->count > 0;

				if (! $owner_in_organization) {
					$this->load->model('roles/role_model');
					$default_role = $this->role_model->where('join_default', 1)->find_by('organization_id', $this->current_user->current_organization_id);
					$added = $this->db->insert('user_to_organizations', [
						'user_id' => $this->current_user->user_id,
						'organization_id' => $organization->organization_id,
						'role_id' => $default_role->role_id
					]);
				}

				$meeting_data = [
					'name' => $meeting['name'],
					//'description' => $meeting['description'],
					'scheduled_start_time' => $meeting['scheduled_start_time'],
					'in' => $meeting['in'],
					'in_type' => 'minutes',
					'action_id' => $action_id,
					'google_event_id' => $event_id,
					'meeting_key' => $this->mb_project->get_next_key($project_key . '-1'),
					'owner_id' => $owner_id
				];

				$meeting_id = $this->meeting_model->skip_validation(true)->insert($meeting_data);

				if ($meeting_id === false) {
					$error = true;
				} else {
					$this->load->library('invite/invitation');

					$query = $this->db->insert_string('project_members', [
						'project_id' => $meeting['project_id'],
						'user_id' => $meeting_data['owner_id']
					]);
		
					$query = str_replace('INSERT', 'INSERT IGNORE', $query);
					$this->db->query($query);

					$in_system_users = $this->user_model->select('user_id, email')->where_in('email', $user_emails)->as_array()->find_all();
					if (empty($in_system_users)) $in_system_users = [];

					$in_system_emails = array_column($in_system_users, 'email');

					$in_organization_users = $this->user_model->select('users.user_id, email')->join('user_to_organizations uto', 'uto.user_id = users.user_id')->where_in('email', $user_emails)->as_array()->find_all();
					if (empty($in_organization_users)) $in_organization_users = [];

					$in_organization_emails = array_column($in_organization_users, 'email');

					$meeting_users = [];
					foreach ($user_emails as $email) {
						if ($email != $this->current_user->email) {
							do {
								$invite_code = $this->invitation->generateRandomString(64);
							} while ($this->meeting_member_invite_model->count_by('invite_code', $invite_code) > 0);

							$member_data[] = [
								'meeting_id' => $meeting_id,
								'invite_email' => $email,
								'invite_code' => $invite_code,
							];

							if (! in_array($email, $in_system_emails)) {
								$temp_user_id = $this->user_model->insert([
									'email' => $email,
									'is_temporary' => 1,
								]);

								$meeting_users[] = [
									'user_id' => $temp_user_id,
									'email' => $email
								];
							}

							$index = array_search($email, $in_organization_emails);
							if ($index !== false) {
								$meeting_members[] = [
									'user_id' => $in_organization_users[$index]['user_id'],
									'meeting_id' => $meeting_id
								];
							}
						} else {
							$this->meeting_member_model->insert([
								'meeting_id' => $meeting_id,
								'user_id' => $this->current_user->user_id
							]);
						}
					}
					$meeting_users = array_merge($meeting_users, $in_system_users);

					if (! empty($member_data)) {
						$this->meeting_member_invite_model->insert_batch($member_data);
					}
					if (! empty($meeting_members)) {
						$this->meeting_member_model->insert_batch($meeting_members);
					}
					if (! empty($user_emails)) {
						$this->mb_project->invite_emails($meeting_id, 'meeting', $this->current_user, $user_emails);
					}

					$meeting_data['meeting_id'] = $meeting_id;
					if ($data['path'] == 'owner') {
						$this->init_create_objects([
							'goal' => empty($meeting['goal']) ? [] : $meeting['goal'],
							'homework' => empty($meeting['homework']) ? [] : $meeting['homework'],
							'agenda' => empty($meeting['agenda']) ? [] : $meeting['agenda'],
						], $meeting_data, $meeting_users, $data, $attachment);
					}

					if ($data['path'] == 'guest') {
						$this->init_rate_objects($meeting['rate'], $meeting_data, $meeting_users);
					}

					$this->db->where('user_id', $this->current_user->user_id)
							->where('organization_id', $this->current_user->current_organization_id)
							->update('user_to_organizations', ['inited' => 1]);
				}
			} else {
				$error = true;
			}

			if (! empty($error)) {
				Template::set('message', lang('st_wrong_provided_data'));
				Template::set('message_type', 'danger');
				Template::set('close_modal', 1);
			} else {
				Template::set('message', lang('st_import_success'));
				Template::set('message_type', 'success');
				Template::set('close_modal', 1);
			}
		}

		Template::render();
	}

	public function init_create_objects($objects, $meeting_data, $meeting_users, $init_data, $files)
	{
		if (! empty($objects)) {
			foreach ($objects as $type => $object_items) {
				foreach ($object_items as $object_index => $item) {
					if ($type == 'goal') {
						$data = [
							'meeting_id' => $meeting_data['meeting_id'],
							'type' => $item['type'],
							'importance' => $item['importance'],
							'name' => ''
						];
					}

					if ($type == 'agenda') {
						$data = [
							'meeting_id' => $meeting_data['meeting_id'],
							'agenda_key' => $this->mb_project->get_next_key($meeting_data['meeting_key']),
							'name' => $item['name'],
							'owner_id' => $this->current_user->user_id,
							'description' => ''
						];
					}

					if ($type == 'homework') {
						$data = [
							'meeting_id' => $meeting_data['meeting_id'],
							'name' => $item['name'],
							'time_spent' => $item['time_spent'],
							'created_by' => $this->current_user->user_id,
							'description' => ''
						];
					}

					$item_id = $this->{$type . '_model'}->insert($data);

					if ($type != 'goal') {
						if (! file_exists('../user_data/' . $this->current_user->user_id)) {
							mkdir('../user_data/' . $this->current_user->user_id, 0777, true);
						}

						$upload_config = [
							'upload_path'   => '../user_data/' . $this->current_user->user_id,
							'allowed_types' => 'gif|jpg|jpeg|png|doc|docx|xls|txt',
							'encrypt_name'  => true
						];

						$this->load->library('upload');
						$this->upload->initialize($upload_config);

						$temp = $files;
						if (! empty($temp[$type])) {
							for ($j = 0; $j < count($temp[$type]['name'][$meeting_data['google_event_id']][$object_index]); $j++) {
								$attachment['image']['name'] = $temp[$type]['name'][$meeting_data['google_event_id']][$object_index][$j];
								$attachment['image']['type'] = $temp[$type]['type'][$meeting_data['google_event_id']][$object_index][$j];
								$attachment['image']['tmp_name'] = $temp[$type]['tmp_name'][$meeting_data['google_event_id']][$object_index][$j];
								$attachment['image']['error'] = $temp[$type]['error'][$meeting_data['google_event_id']][$object_index][$j];
								$attachment['image']['size'] = $temp[$type]['size'][$meeting_data['google_event_id']][$object_index][$j];

								$_FILES = $attachment;
								$this->upload->do_upload('image');
								$upload_data = $this->upload->data();
								$attachment_data = [
									$type . '_id' => $item_id,
									'url' => $upload_data['full_path']
								];

								$this->{$type . '_attachment_model'}->insert($attachment_data);
							}
						}

						$object_members = [];
						foreach ($item['assignees'] as $assignee_email) {
							$index = array_search($assignee_email, array_column($meeting_users, 'email'));
							if ($index !== false && ! empty($meeting_users[$index]['user_id']) && $meeting_users[$index]['user_id'] != $meeting_data['owner_id']) {
								$assignee_id = $meeting_users[$index]['user_id'];
								$object_members[] = [
									'user_id' => $assignee_id,
									$type . '_id' => $item_id
								];
							}
						}

						if (! empty($object_members)) {
							$this->{$type . '_member_model'}->insert_batch($object_members);
						}
					}
				}
			}
		}
	}

	public function init_rate_objects($object_rate, $meeting_data, $meeting_users)
	{
		$default_agenda_data = [
			'meeting_id' => $meeting_data['meeting_id'],
			'agenda_key' => $this->mb_project->get_next_key($meeting_data['meeting_key']),
			'owner_id' => $meeting_data['owner_id'],
			'name' => 'default agenda',
			'description' => 'default agenda for rating in init process'
		];
		$default_agenda_id = $this->agenda_model->insert($default_agenda_data);

		$default_homework_data = [
			'meeting_id' => $meeting_data['meeting_id'],
			'name' => 'default homework',
			'description' => 'default homework for rating in init process',
			'time_spent' => 0,
			'created_by' => $meeting_data['owner_id']
		];
		$default_homework_id = $this->homework_model->insert($default_homework_data);

		$default_agenda_members_data = [];
		$default_homework_members_data = [];

		$rated = false;
		foreach ($meeting_users as $user) {
			if ($user['email'] != $meeting_data['owner_id']) {
				$default_agenda_members_data[] = [
					'user_id' => $user['user_id'],
					'agenda_id' => $default_agenda_id
				];

				$default_homework_members_data[] = [
					'user_id' => $user['user_id'],
					'homework_id' => $default_homework_id
				];

				if ($user['email'] == $this->current_user->email) {
					if (! $rated) {
						$rated = true;

						$this->agenda_rate_model->skip_validation(true)->insert([
							'agenda_id' => $default_agenda_id,
							'user_id' => $this->current_user->user_id,
							'rate' => $object_rate['agenda']
						]);

						$this->homework_rate_model->skip_validation(true)->insert([
							'homework_id' => $default_homework_id,
							'user_id' => $this->current_user->user_id,
							'rate' => $object_rate['homework']
						]);

						$this->meeting_member_model->skip_validation(true)
												->where('meeting_id', $meeting_data['meeting_id'])
												->update_where('user_id', $this->current_user->user_id, ['rate' => $object_rate['meeting']]);
					}
				}
			}
		}

		if (! empty($default_agenda_members_data)) {
			$this->agenda_member_model->insert_batch($default_agenda_members_data);
		}

		if (! empty($default_homework_members_data)) {
			$this->homework_member_model->insert_batch($default_homework_members_data);
		}
	}

	public function edit_calendar_event()
	{
		if (! $this->input->is_ajax_request()) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$meeting_id = $this->input->post('meeting_id');
		$start = get_utc_time($this->input->post('start'));
		$end = get_utc_time($this->input->post('end'));

		if ((empty($meeting_id) && empty($private_meeting_id)) || empty($start) || empty($end)) {
			echo json_encode([
				'status' => 0
			]); exit;
		}

		if (strtotime($start) < strtotime('now') || strtotime($end) < strtotime($start)) {
			echo json_encode([
				'status' => 0
			]); exit;
		}

		if (! empty($meeting_id)) {
			$meeting = $this->meeting_model->find($meeting_id);

			if (empty($meeting) || $meeting->status != 'open' || $meeting->manage_state != 'setup') {
				echo json_encode([
					'status' => 0
				]); exit;
			}
		}

		$data = [
			'scheduled_start_time' => $start,
			'in' => (strtotime($end) - strtotime($start)) / 60,
			'in_type' => 'minutes'
		];

		$updated = $this->meeting_model->skip_validation(true)->update($meeting_id, $data);

		if (! $updated) {
			echo json_encode([
				'status' => 0
			]); exit;
		}

		echo json_encode([
			'status' => 1
		]); exit;
	}

	public function select_project()
	{
		if (! $this->input->is_ajax_request()) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$my_projects = $this->project_model->select('projects.project_id, projects.cost_code, projects.name')
										->join('users u', 'u.user_id = projects.owner_id')
										->join('project_members pm', 'projects.project_id = pm.project_id')
										->where('(pm.user_id = \'' . $this->current_user->user_id . '\' OR projects.owner_id = \'' . $this->current_user->user_id . '\')')
										->where('organization_id', $this->current_user->current_organization_id)
										->where('projects.status !=', 'archive')
										->order_by('projects.name', 'asc')
										->group_by('projects.project_id')
										->as_array()
										->find_all();
		if (empty($my_projects)) {
			$my_projects = [];
			$my_project_ids = [];
		} else {
			$my_project_ids = array_column($my_projects, 'project_id');
		}

		if (has_permission('Project.View.All')) {
			$this->project_model->select('projects.project_id, projects.cost_code, projects.name')
											->join('users u', 'u.user_id = projects.owner_id')
											->where('projects.status !=', 'archive');
			
			if (! empty($my_project_ids)) {
				$this->project_model->where_not_in('projects.project_id', $my_project_ids);
			}

			$other_projects = $this->project_model->where('organization_id', $this->current_user->current_organization_id)
											->order_by('projects.name', 'asc')
											->as_array()
											->find_all();
		} else {
			$other_projects = [];
		}

		Template::set('my_projects', $my_projects);
		Template::set('other_projects', $other_projects);
		Template::render();
	}

	public function create_private()
	{
		if (! $this->input->is_ajax_request()) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}
		// Get list resource/team member
		$project_members = $this->user_model->get_organization_members($this->current_user->current_organization_id);

		if (isset($_POST['save'])) {
			$data = $this->meeting_model->prep_data($this->input->post());
			$data['organization_id'] = $this->current_user->current_organization_id;

			if ($this->input->post('owner_id') == '') {
				$data['owner_id'] = $this->current_user->user_id;
			}

			if (! empty($this->input->get('scheduled_start_time'))) {
				$data['scheduled_start_time'] = get_utc_time($this->input->get('scheduled_start_time'));
			} else {
				unset($data['scheduled_start_time']);
			}

			if ($team = $this->input->post('team')) {
				if ($team = explode(',', $team)) {
					$owner_email = $this->user_model->get_field($data['owner_id'], 'email');
					if ($owner_email != $this->current_user->email && ! in_array($this->current_user->email, $team)) {
						$team[] = $this->current_user->email;
					}

					if (! in_array($owner_email, $team)) {
						$data['members'] = json_encode($team);
					} else {
						Template::set('close_modal', 0);
						Template::set('message_type', 'danger');
						Template::set('message', lang('st_owner_can_not_be_member'));
						Template::render();
						return;
					}
				}
			}

			if (! empty($this->input->post('rrule_recurring'))) {
				$data['rrule'] = $this->input->post('rrule_recurring');
			}

			$data['action_id'] = 0;
			$data['meeting_key'] = '';
			$data['is_private'] = 1;

			$id = $this->meeting_model->insert($data);
			if ($id) {
				Template::set('close_modal', 1);
				Template::set('message_type', 'success');
				Template::set('message', lang('st_meeting_successfully_created'));
			} else {
				Template::set('close_modal', 0);
				Template::set('message_type', 'danger');
				Template::set('message', lang('st_there_was_a_problem_while_creating_meeting'));
			}
		}

		Template::set('project_members', $project_members);
		Template::render();
	}

	public function get_private_meetings()
	{
		$meetings = $this->meeting_model->where('organization_id', $this->current_user->current_organization_id)
										->where('created_by', $this->current_user->user_id)
										->where('is_private', 1)
										->find_all();
		if (empty($meetings)) $meetings = [];
		
		$team = [];
		foreach ($meetings as $meeting) {
			$members = [];
			if (! empty($meeting->members)) {
				$members = json_decode($meeting->members, true);
			}

			foreach ($members as $member) {
				if (! in_array($member, $team)) {
					$team[] = $member;
				}
			}
		}

		echo json_encode([
			'status' => 1,
			'meetings' => $meetings,
			'no_of_meeting' => count($meetings),
			'team' => count($team)
		]); exit;
	}
	/**
	 * deprecated function
	 */
	public function detail_private($meeting_id = null)
	{
		if (! $this->input->is_ajax_request()) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (empty($meeting_id)) {
			Template::set_message(lang('st_meeting_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$meeting = $this->meeting_model->join('users u', 'u.user_id = meetings.owner_id', 'left')
									->where('organization_id', $this->current_user->current_organization_id)
									->where('created_by', $this->current_user->user_id)
									->where('is_private', 1)
									->find($meeting_id);

		if (empty($meeting)) {
			Template::set_message(lang('st_meeting_does_not_exist'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$invited_emails = json_decode($meeting->members);
		if (! empty($invited_emails)) {
			$invited_members = $this->user_model->select('user_id, email, first_name, last_name, avatar')->where_in('email', $invited_emails)->as_array()->find_all();
		} else {
			$invited_emails = [];
			$invited_members = [];
		}

		$invited = [];
		foreach($invited_emails as $email) {
			$index = array_search($email, array_column($invited_members, 'email'));
			if ($index !== false) {
				$invited[] = $invited_members[$index];
			} else {
				$invited[count($invited)]['email'] = $email;
			}
		}
		$invited_members = $invited;

		$agendas = $this->agenda_model->select('agendas.*, u.email, u.first_name, u.last_name, u.avatar')
								->join('users u', 'u.user_id = agendas.owner_id', 'left')
								->where('meeting_id', $meeting_id)->find_all();

		if ($agendas) {
			foreach ($agendas as &$agenda) {
				$agenda->members = $this->agenda_member_model->select('avatar, email, first_name, last_name')
				->join('users u', 'u.user_id = agenda_members.user_id')
				->where('agenda_id', $agenda->agenda_id)
				->find_all();

				if (! empty($this->input->get('agenda_key')) && $this->input->get('agenda_key') == $agenda->agenda_key) {
					$chosen_agenda = $agenda;
				}
			}
		}

		$homeworks = $this->homework_model->where('meeting_id', $meeting_id)->find_all();

		if ($homeworks) {
			foreach ($homeworks as &$homework) {
				$homework->members = $this->homework_member_model->select('u.user_id, avatar, email, last_name, first_name, CONCAT(first_name, " ", last_name) AS full_name')
				->join('users u', 'u.user_id = homework_members.user_id')
				->where('homework_id', $homework->homework_id)
				->find_all();

				$homework->members = $homework->members ? $homework->members : [];

				$homework->attachments = $this->homework_attachment_model->where('homework_id', $homework->homework_id)->find_all();
				$homework->attachments = $homework->attachments ? $homework->attachments : [];
			}
		}

		if (IS_AJAX) {
			echo json_encode([$invited_members, $meeting, $agendas, $homeworks]); exit;
		}

		Assets::add_js($this->load->view('detail_js', [
			'meeting_key' => null,
			'current_user' => $this->current_user,
			'chosen_agenda' => ! empty($chosen_agenda) ? $chosen_agenda : null,
			'is_private' => 1,
			'meeting_id' => $meeting->meeting_id
		], true), 'inline');
		Template::set('agendas', $agendas);
		Template::set('homeworks', $homeworks);
		Template::set('current_user', $this->current_user);
		Template::set('page_title', $meeting->name);
		Template::set('invited_members', $invited_members);
		Template::set('meeting', $meeting);
		Template::set_view('detail_private');
		Template::render();
	}

	public function edit_private($meeting_id)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		Template::set_view('edit_private');

		if (empty($meeting_id)) {
			Template::set_message(lang('st_meeting_does_not_exist'), 'danger');
			Template::set('close_modal', 0);
			Template::render();
			return;
		}

		$meeting = $this->meeting_model->join('users u', 'u.user_id = meetings.owner_id', 'left')
									->where('organization_id', $this->current_user->current_organization_id)
									->where('created_by', $this->current_user->user_id)
									->where('is_private', 1)
									->find($meeting_id);

		if (empty($meeting)) {
			Template::set_message(lang('st_meeting_does_not_exist'), 'danger');
			Template::set('close_modal', 0);
			Template::render();
			return;
		}

		Template::set('meeting_members', json_decode($meeting->members));
		Template::set('meeting', $meeting);

		$project_members = $this->user_model->get_organization_members($this->current_user->current_organization_id);

		Template::set('project_members', $project_members);
		Assets::add_js($this->load->view('create_js', [
			'project_members' => $project_members,
			'default_cost_of_time' => 'N/A',
			'default_cost_of_time_name' => 'N/A',
			'meeting_members' => json_decode($meeting->members)
		], true), 'inline');

		if ($data = $this->input->post()) {
			$data = $this->meeting_model->prep_data($data);
			$data['modified_by'] = $this->current_user->user_id;

			if ($this->input->post('owner_id') == '') {
				$data['owner_id'] = $this->current_user->user_id;
			}


			if ($team = $this->input->post('team')) {
				if ($team = explode(',', $team)) {
					$member_data = [];
					$owner_email = $this->user_model->get_field($data['owner_id'], 'email');

					if (! empty($team)) {
						if (! in_array($owner_email, $team)) {
							if ($owner_email != $this->current_user->email && ! in_array($this->current_user->email, $team)) {
								$team[] = $this->current_user->email;
							}
							$data['members'] = json_encode($team);
							if ($this->meeting_model->update($meeting->meeting_id, $data)) {
								
								Template::set('close_modal', 1);
								Template::set('message_type', 'success');
								Template::set('message', lang('st_meeting_successfully_updated'));

								// Just to reduce AJAX request size
								if (IS_AJAX) {
									Template::set('content', '');
								}
							} else {
								Template::set('close_modal', 0);
								Template::set('message_type', 'danger');
								Template::set('message', lang('st_please_add_team_member'));
							}
						} else {
							Template::set('close_modal', 0);
							Template::set('message_type', 'danger');
							Template::set('message', lang('st_owner_can_not_be_member'));
						}
					}
				}
			} else {
				Template::set('close_modal', 0);
				Template::set('message_type', 'danger');
				Template::set('message', lang('st_please_add_team_member'));
			}

			Template::render();
			return;
		}

		Template::render();
	}

	public function date_picker()
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}
		Template::render();
	}

	private function send_meeting_result($meeting_id)
	{
		$meeting = $this->meeting_model->select('meetings.*, o.name as org_name,
										u.email AS owner_email, u.first_name AS owner_first_name, u.last_name AS owner_last_name, u.avatar AS owner_avatar,
										(SELECT (SUM(mm.rate)/(COUNT(*))) FROM ' . $this->db->dbprefix('meeting_members') . ' mm WHERE mm.meeting_id="' . $meeting_id . '" AND mm.rate IS NOT NULL) AS average_rate')
									->join('users u', 'u.user_id = meetings.owner_id')
									->join('actions a', 'a.action_id = meetings.action_id')
									->join('projects p', 'p.project_id = a.project_id')
									->join('organizations o', 'o.organization_id = p.organization_id')
									->as_array()
									->find($meeting_id);

		$meeting_members = $this->meeting_member_model->select('(SELECT (SUM(mmr.rate)/(COUNT(*))) FROM ' . $this->db->dbprefix('meeting_member_rates') . ' mmr WHERE mmr.meeting_id="' . $meeting_id . '" AND mmr.rate IS NOT NULL AND mmr.attendee_id=' . $this->db->dbprefix('meeting_members') . '.user_id) AS average_rate,
														a.email AS attendee_email, a.first_name AS attendee_first_name, a.last_name AS attendee_last_name, a.avatar AS attendee_avatar')
													->join('users a', 'a.user_id = meeting_members.user_id')
													->where('meeting_members.meeting_id', $meeting_id)
													->as_array()
													->find_all();

		$meeting_agendas = $this->agenda_model->select('agendas.*,
												o.email AS owner_email, o.first_name AS owner_first_name, o.last_name AS owner_last_name, o.avatar AS owner_avatar,
												(SELECT (SUM(ar.rate)/(COUNT(*))) FROM ' . $this->db->dbprefix('agenda_rates') . ' ar WHERE ar.agenda_id=' . $this->db->dbprefix('agendas') . '.agenda_id AND ar.rate IS NOT NULL) AS average_rate')
											->join('users o', 'o.user_id = agendas.owner_id')
											->where('meeting_id', $meeting_id)
											->order_by('agendas.agenda_id')
											->as_array()
											->find_all();

		$meeting_homeworks = $this->homework_model->select('homework.*,
													o.email AS owner_email, o.first_name AS owner_first_name, o.last_name AS owner_last_name, o.avatar AS owner_avatar,
													(SELECT (SUM(hr.rate)/(COUNT(*))) FROM ' . $this->db->dbprefix('homework_rates') . ' hr WHERE hr.homework_id=' . $this->db->dbprefix('homework') . '.homework_id AND hr.rate IS NOT NULL AND hr.user_id IN (SELECT user_id FROM ' . $this->db->dbprefix('homework_members') . ' hm WHERE hm.homework_id = hr.homework_id)) AS average_rate')
												->join('users o', 'o.user_id = homework.created_by')
												->where('meeting_id', $meeting_id)
												->order_by('homework.homework_id')
												->as_array()
												->find_all();

		$template = $this->db->where('email_template_key', 'MEETING_SUMMARY')
							->where('language_code', 'en_US')
							->get('email_templates')->row();

		if ($template) {
			$email_data = [
				'NAME' => $meeting['name'],
				'ORG' => $meeting['org_name'],
				'KEY' => $meeting['meeting_key'],
				'NOTES' => nl2br($meeting['notes']),
				'OWNER' => display_user($meeting['owner_email'], $meeting['owner_first_name'], $meeting['owner_last_name'], $meeting['owner_avatar']),
				'MEETING_RATE' => round($meeting['average_rate'], 2)
			];

			if (empty($meeting_agendas)) {
				$meeting_agendas = [];
				$email_data['AGENDAS'][] = [
					'AGENDA_NAME' => 'N/A',
					'AGENDA_RATE' => 'N/A'
				];
			}

			if (empty($meeting_homeworks)) {
				$meeting_homeworks = [];
				$email_data['HOMEWORKS'][] = [
					'HOMEWORK_NAME' => 'N/A',
					'HOMEWORK_RATE' => 'N/A'
				];
			}

			foreach ($meeting_members as $member) {
				$email_data['MEMBERS'][] = [
					'AVATAR' => display_user($member['attendee_email'], $member['attendee_first_name'], $member['attendee_last_name'], $member['attendee_avatar']),
					'MEMBER_RATE' => round($member['average_rate'], 2)
				];
			}

			foreach ($meeting_agendas as $agenda) {
				$email_data['AGENDAS'][] = [
					'AGENDA_NAME' => $agenda['name'],
					'AGENDA_RATE' => round($agenda['average_rate'], 2)
				];
			}

			foreach ($meeting_homeworks as $hw) {
				$email_data['HOMEWORKS'][] = [
					'HOMEWORK_NAME' => $hw['name'],
					'HOMEWORK_RATE' => round($hw['average_rate'], 2)
				];
			}

			$header = $this->load->view('emailer/email/_header', null, true);
			$footer = $this->load->view('emailer/email/_footer', null, true);
			$this->load->library('parser');

			$email_data['USER_NAME'] = $meeting['owner_first_name'] . ' ' . $meeting['owner_last_name'];
			$content = $header;
			$content .= $this->parser->parse_string($template->email_template_content, $email_data, true);
			$content .= $footer;

			$queue_data[] = [
				'to_email' => $meeting['owner_email'],
				'subject' => $template->email_title,
				'message' => $content,
			];

			foreach ($meeting_members as $index => $member) {
				$email_data['USER_NAME'] = $member['attendee_first_name'] . ' ' . $member['attendee_last_name'];
				$content = $header;
				$content .= $this->parser->parse_string($template->email_template_content, $email_data, true);
				$content .= $footer;

				$queue_data[] = [
					'to_email' => $member['attendee_email'],
					'subject' => $template->email_title,
					'message' => $content,
				];
			}
		}

		$this->db->insert_batch('email_queue', $queue_data);
	}

	public function check_meeting_progress($meeting_id)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$meeting = $this->meeting_model->select('meetings.status, meetings.manage_state')
									->join('users u', 'u.user_id = meetings.owner_id')
									->join('actions a', 'a.action_id = meetings.action_id')
									->join('projects p', 'p.project_id = a.project_id')
									->join('organizations o', 'o.organization_id = p.organization_id')
									->as_array()
									->find_by('meetings.meeting_id', $meeting_id);
		if (empty($meeting)) {
			echo json_encode([
				'error' => 1,
				'progress' => []
			]); exit;
		}
		echo json_encode([
			'error' => 0,
			'progress' => [
				'status' => $meeting['status'],
				'manage_state' => $meeting['manage_state']
			]
		]); exit;
	}

	public function completed_meeting_summary($meeting_id)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$meeting = $this->meeting_model->select('meetings.*, o.name as org_name,
										u.email AS owner_email, u.first_name AS owner_first_name, u.last_name AS owner_last_name, u.avatar AS owner_avatar,
										(SELECT (SUM(mm.rate)/(COUNT(*))) FROM ' . $this->db->dbprefix('meeting_members') . ' mm WHERE mm.meeting_id="' . $meeting_id . '" AND mm.rate IS NOT NULL) AS average_rate,
										(SELECT COUNT(*) FROM ' . $this->db->dbprefix('meeting_members') . ' mm WHERE mm.meeting_id="' . $meeting_id . '" AND mm.rate IS NOT NULL) AS member_rated')
									->join('users u', 'u.user_id = meetings.owner_id')
									->join('actions a', 'a.action_id = meetings.action_id')
									->join('projects p', 'p.project_id = a.project_id')
									->join('organizations o', 'o.organization_id = p.organization_id')
									->where('meetings.status', 'finished')
									->as_array()
									->find($meeting_id);
		if (empty($meeting)) {
			Template::set('content', '');
			Template::set('data', ['status' => 0]);
			Template::set('message', lang('st_meeting_not_exist_or_not_finished'));
			Template::set('message_type', 'danger');
			Template::render();
			return;
		}

		$meeting_members = $this->meeting_member_model->select('(SELECT (SUM(mmr.rate)/(COUNT(*))) FROM ' . $this->db->dbprefix('meeting_member_rates') . ' mmr WHERE mmr.meeting_id="' . $meeting_id . '" AND mmr.rate IS NOT NULL AND mmr.attendee_id=' . $this->db->dbprefix('meeting_members') . '.user_id) AS average_rate,
														a.email AS attendee_email, a.first_name AS attendee_first_name, a.last_name AS attendee_last_name, a.avatar AS attendee_avatar')
													->join('users a', 'a.user_id = meeting_members.user_id')
													->where('meeting_members.meeting_id', $meeting_id)
													->as_array()
													->find_all();

		$meeting_agendas = $this->agenda_model->select('agendas.*,
												o.email AS owner_email, o.first_name AS owner_first_name, o.last_name AS owner_last_name, o.avatar AS owner_avatar,
												(SELECT (SUM(ar.rate)/(COUNT(*))) FROM ' . $this->db->dbprefix('agenda_rates') . ' ar WHERE ar.agenda_id=' . $this->db->dbprefix('agendas') . '.agenda_id AND ar.rate IS NOT NULL) AS average_rate,
												(SELECT COUNT(*) FROM ' . $this->db->dbprefix('agenda_rates') . ' ar WHERE ar.agenda_id=' . $this->db->dbprefix('agendas') . '.agenda_id AND ar.rate IS NOT NULL) AS member_rated')
											->join('users o', 'o.user_id = agendas.owner_id')
											->where('meeting_id', $meeting_id)
											->order_by('agendas.agenda_id')
											->as_array()
											->find_all();
		if (empty($meeting_agendas)) {
			$meeting_agendas = [];
		}

		$meeting_homeworks = $this->homework_model->select('homework.*,
													o.email AS owner_email, o.first_name AS owner_first_name, o.last_name AS owner_last_name, o.avatar AS owner_avatar,
													(SELECT (SUM(hr.rate)/(COUNT(*))) FROM ' . $this->db->dbprefix('homework_rates') . ' hr WHERE hr.homework_id=' . $this->db->dbprefix('homework') . '.homework_id AND hr.rate IS NOT NULL AND hr.user_id IN (SELECT user_id FROM ' . $this->db->dbprefix('homework_members') . ' hm WHERE hm.homework_id = hr.homework_id)) AS average_rate')
												->join('users o', 'o.user_id = homework.created_by')
												->where('meeting_id', $meeting_id)
												->order_by('homework.homework_id')
												->as_array()
												->find_all();
		if (empty($meeting_homeworks)) {
			$meeting_homeworks = [];

		}

		Template::set('data', ['status' => 1]);
		Template::set('meeting', $meeting);
		Template::set('meeting_members', $meeting_members);
		Template::set('meeting_agendas', $meeting_agendas);
		Template::set('meeting_homeworks', $meeting_homeworks);
		Template::render();
	}

	public function get_meeting_alert()
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		echo json_encode(['html' => $this->mb_project->meeting_alert(false)]);exit;
	}

	public function check_is_evaluated($meeting_id)
	{
		if (! IS_AJAX) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$data = [
			'error' => 0,
			'is_member' => 0,
			'is_evaluated' => 0
		];

		if (empty($meeting_id)) {
			$data['error'] = 1;
			echo json_encode($data);exit;
		}

		$evaluated = $this->is_evaluated($meeting_id);
		if ($evaluated) {
			$data['is_evaluated'] = 1;
		}

		$owner_id = $this->meeting_model->get_field($meeting_id, 'owner_id');
		$members = $this->meeting_member_model->select('u.user_id')
											->join('users u', 'u.user_id = meeting_members.user_id')
											->where('u.user_id !=', $owner_id)
											->where('meeting_id', $meeting_id)
											->as_array()
											->find_all();
		if (in_array($this->current_user->user_id, array_column($members, 'user_id'))) {
			$data['is_member'] = 1;
		}

		echo json_encode($data);exit;
	}

	public function get_meeting_resource($meeting_id)
	{
		// if (! IS_AJAX) {
		// 	redirect(DEFAULT_LOGIN_LOCATION);
		// }

		$res = $this->meeting_member_invite_model->get_meeting_invited_members($meeting_id);

		echo json_encode(empty($res) ? [] : $res);exit;
	}
}