<?php defined('BASEPATH') || exit('No direct script access allowed');

class Test extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('form');
		$this->load->library('users/Auth');
	}

	public function login()
	{
		if ($this->input->post()) {
			$this->auth->login($this->input->post('email'), $this->input->post('password'));
			dump($_SESSION);
		} elseif ($this->input->get()) {
			$this->auth->logout();
		}
		Template::render('login');
	}

	public function restrict() {
		$_SESSION['org_id'] = 2;
		$this->auth->restrict('restrict.access');
		if ($this->auth->has_permission('restrict.access')) {
			echo 'aaaa';die;
		} else {
			echo 'false';die;
		}
	}

	public function index() {
		$this->load->model('project/project_model');
		for($i=1; $i<30; $i++) {
			dump($this->project_model->get_agendas($i), $this->db->last_query());
		}
	}

	public function calendar()
	{
		$this->lang->load('meeting/meeting');
		$event_sources = [
			[
				'id' => 'mbc',
				'url' => site_url('meeting/get_events/mbc'),
				'color' => '#70c1b3',
				'textColor' => 'white',
				'className' => 'mbc-event'
			],
			[
				'id' => 'ggc',
				'url' => site_url('meeting/get_events/ggc'),
				'color' => '#999',
				'textColor' => 'white',
				'className' => 'ggc-event'
			]
		];

		Assets::add_js($this->load->view('calendar_js', [
			'event_sources' => $event_sources
		], true), 'inline');
		Template::render();
	}

	public function update_parent()
	{
		$this->load->library('mb_project');
		$this->mb_project->update_parent_object('homework');
	}

	public function init_project()
	{
		$this->load->model('project/project_model');
		$data = $this->input->post('data');

		// $data = json_encode([
		// 	'currentStep' => 40,
		// 	'meetings' => [
		// 		'ggc123456789' => [
		// 			'owner' => [
		// 				'email' => 'baodg@gearinc.com',
		// 				'self' => true
		// 			],
		// 			'members' => [
		// 				'tungnt@gearinc.com',
		// 				'viethd@gearinc.com',
		// 				'datls@gearinc.com'
		// 			],
		// 			'name' => 'Scopely meeting 1 - WWE Champions seminar',
		// 			'description' => 'WWE Champions seminar',
		// 			'scheduled_start_time' => '2017-07-15 10:30:00',
		// 			'in' => '90'
		// 		],
		// 		'ggc987654321' => [
		// 			'owner' => [
		// 				'email' => 'datls@gearinc.com',
		// 				'self' => false
		// 			],
		// 			'members' => [
		// 				'tungnt@gearinc.com',
		// 				'viethd@gearinc.com',
		// 				'baodg@gearinc.com'
		// 			],
		// 			'name' => 'Scopely meeting 2 - Coding email tool',
		// 			'description' => 'Coding email tool',
		// 			'scheduled_start_time' => '2017-07-18 11:30:00',
		// 			'in' => '180'
		// 		],
		// 		'ggc192837465' => [
		// 			'owner' => [
		// 				'email' => 'baodg@gearinc.com',
		// 				'self' => true
		// 			],
		// 			'members' => [
		// 				'tungnt@gearinc.com',
		// 				'viethd@gearinc.com',
		// 				'datls@gearinc.com'
		// 			],
		// 			'name' => 'Scopely meeting 3 - Review code',
		// 			'description' => 'Review code',
		// 			'scheduled_start_time' => '2017-08-15 10:26:00',
		// 			'in' => '60'
		// 		],
		// 		'ggc101010101' => [
		// 			'owner' => [
		// 				'email' => 'tungnt@gearinc.com',
		// 				'self' => false
		// 			],
		// 			'members' => [
		// 				'baodg@gearinc.com',
		// 				'viethd@gearinc.com',
		// 				'datls@gearinc.com'
		// 			],
		// 			'name' => 'Scopely meeting 4 - Finish project',
		// 			'description' => 'Finish project',
		// 			'scheduled_start_time' => '2017-10-15 11:30:00',
		// 			'in' => '30'
		// 		]
		// 	]
		// ]);

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
										->find_all();

		Template::set('data', $data);
		Template::set('projects', empty($projects) ? [] : $projects);
		Template::render();
	}

	public function init_team()
	{
		$this->load->model('user/user_model');
		$data = $this->input->post('data');

		// $data = json_encode([
		// 	'currentStep' => 50,
		// 	'meetings' => [
		// 		'ggc123456789' => [
		// 			'owner' => [
		// 				'email' => 'baodg@gearinc.com',
		// 				'self' => true
		// 			],
		// 			'members' => [
		// 				'tungnt@gearinc.com',
		// 				'viethd@gearinc.com',
		// 				'datls@gearinc.com'
		// 			],
		// 			'name' => 'Scopely meeting 1 - WWE Champions seminar',
		// 			'description' => 'WWE Champions seminar',
		// 			'scheduled_start_time' => '2017-07-15 10:30:00',
		// 			'in' => '90',
		// 			'project_id' => '1'
		// 		],
		// 		'ggc987654321' => [
		// 			'owner' => [
		// 				'email' => 'datls@gearinc.com',
		// 				'self' => false
		// 			],
		// 			'members' => [
		// 				'tungnt@gearinc.com',
		// 				'viethd@gearinc.com',
		// 				'baodg@gearinc.com'
		// 			],
		// 			'name' => 'Scopely meeting 2 - Coding email tool',
		// 			'description' => 'Coding email tool',
		// 			'scheduled_start_time' => '2017-07-18 11:30:00',
		// 			'in' => '180',
		// 			'project_id' => '2'
		// 		],
		// 		'ggc192837465' => [
		// 			'owner' => [
		// 				'email' => 'baodg@gearinc.com',
		// 				'self' => true
		// 			],
		// 			'members' => [
		// 				'tungnt@gearinc.com',
		// 				'viethd@gearinc.com',
		// 				'datls@gearinc.com'
		// 			],
		// 			'name' => 'Scopely meeting 3 - Review code',
		// 			'description' => 'Review code',
		// 			'scheduled_start_time' => '2017-08-15 10:26:00',
		// 			'in' => '60',
		// 			'project_id' => '3'
		// 		],
		// 		'ggc101010101' => [
		// 			'owner' => [
		// 				'email' => 'tungnt@gearinc.com',
		// 				'self' => false
		// 			],
		// 			'members' => [
		// 				'baodg@gearinc.com',
		// 				'viethd@gearinc.com',
		// 				'datls@gearinc.com'
		// 			],
		// 			'name' => 'Scopely meeting 4 - Finish project',
		// 			'description' => 'Finish project',
		// 			'scheduled_start_time' => '2017-10-15 11:30:00',
		// 			'in' => '30',
		// 			'project_id' => '1'
		// 		]
		// 	]
		// ]);

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

		// $data = json_encode([
		// 	'currentStep' => 60,
		// 	'meetings' => [
		// 		'ggc123456789' => [
		// 			'owner' => [
		// 				'email' => 'baodg@gearinc.com',
		// 				'self' => true
		// 			],
		// 			'members' => [
		// 				'tungnt@gearinc.com',
		// 				'viethd@gearinc.com',
		// 				'datls@gearinc.com'
		// 			],
		// 			'name' => 'Scopely meeting 1 - WWE Champions seminar',
		// 			'description' => 'WWE Champions seminar',
		// 			'scheduled_start_time' => '2017-07-15 10:30:00',
		// 			'in' => '90',
		// 			'project_id' => '1'
		// 		],
		// 		'ggc987654321' => [
		// 			'owner' => [
		// 				'email' => 'datls@gearinc.com',
		// 				'self' => false
		// 			],
		// 			'members' => [
		// 				'tungnt@gearinc.com',
		// 				'viethd@gearinc.com',
		// 				'baodg@gearinc.com'
		// 			],
		// 			'name' => 'Scopely meeting 2 - Coding email tool',
		// 			'description' => 'Coding email tool',
		// 			'scheduled_start_time' => '2017-07-18 11:30:00',
		// 			'in' => '180',
		// 			'project_id' => '2'
		// 		],
		// 		'ggc192837465' => [
		// 			'owner' => [
		// 				'email' => 'baodg@gearinc.com',
		// 				'self' => true
		// 			],
		// 			'members' => [
		// 				'tungnt@gearinc.com',
		// 				'viethd@gearinc.com',
		// 				'datls@gearinc.com'
		// 			],
		// 			'name' => 'Scopely meeting 3 - Review code',
		// 			'description' => 'Review code',
		// 			'scheduled_start_time' => '2017-08-15 10:26:00',
		// 			'in' => '60',
		// 			'project_id' => '3'
		// 		],
		// 		'ggc101010101' => [
		// 			'owner' => [
		// 				'email' => 'tungnt@gearinc.com',
		// 				'self' => false
		// 			],
		// 			'members' => [
		// 				'baodg@gearinc.com',
		// 				'viethd@gearinc.com',
		// 				'datls@gearinc.com'
		// 			],
		// 			'name' => 'Scopely meeting 4 - Finish project',
		// 			'description' => 'Finish project',
		// 			'scheduled_start_time' => '2017-10-15 11:30:00',
		// 			'in' => '30',
		// 			'project_id' => '1'
		// 		]
		// 	],
		// 	'new_projects_count' => 1
		// ]);

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

		// $data = json_encode([
		// 	'currentStep' => 6,
		// 	'meetings' => [
		// 		'ggc123456789' => [
		// 			'owner' => [
		// 				'email' => 'baodg@gearinc.com',
		// 				'self' => true
		// 			],
		// 			'members' => [
		// 				'tungnt@gearinc.com',
		// 				'viethd@gearinc.com',
		// 				'datls@gearinc.com'
		// 			],
		// 			'name' => 'Scopely meeting 1 - WWE Champions seminar',
		// 			'description' => 'WWE Champions seminar',
		// 			'scheduled_start_time' => '2017-07-15 10:30:00',
		// 			'in' => '90',
		// 			'project_id' => '1'
		// 		],
		// 		'ggc987654321' => [
		// 			'owner' => [
		// 				'email' => 'datls@gearinc.com',
		// 				'self' => false
		// 			],
		// 			'members' => [
		// 				'tungnt@gearinc.com',
		// 				'viethd@gearinc.com',
		// 				'baodg@gearinc.com'
		// 			],
		// 			'name' => 'Scopely meeting 2 - Coding email tool',
		// 			'description' => 'Coding email tool',
		// 			'scheduled_start_time' => '2017-07-18 11:30:00',
		// 			'in' => '180',
		// 			'project_id' => '2'
		// 		],
		// 		'ggc192837465' => [
		// 			'owner' => [
		// 				'email' => 'baodg@gearinc.com',
		// 				'self' => true
		// 			],
		// 			'members' => [
		// 				'tungnt@gearinc.com',
		// 				'viethd@gearinc.com',
		// 				'datls@gearinc.com'
		// 			],
		// 			'name' => 'Scopely meeting 3 - Review code',
		// 			'description' => 'Review code',
		// 			'scheduled_start_time' => '2017-08-15 10:26:00',
		// 			'in' => '60',
		// 			'project_id' => '3'
		// 		],
		// 		'ggc101010101' => [
		// 			'owner' => [
		// 				'email' => 'tungnt@gearinc.com',
		// 				'self' => false
		// 			],
		// 			'members' => [
		// 				'baodg@gearinc.com',
		// 				'viethd@gearinc.com',
		// 				'datls@gearinc.com'
		// 			],
		// 			'name' => 'Scopely meeting 4 - Finish project',
		// 			'description' => 'Finish project',
		// 			'scheduled_start_time' => '2017-10-15 11:30:00',
		// 			'in' => '30',
		// 			'project_id' => '1'
		// 		]
		// 	],
		// 	'new_projects_count' => 1
		// ]);

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

		foreach ($data['meetings'] as $event_id => $meeting) {
			$user_emails = $meeting['members'];
			$user_emails[] = $meeting['owner'];
			$user_emails = array_unique($user_emails);
			$project_key = $this->project_model->get_field($meeting['project_id'], 'cost_code');
			$owner = $this->user_model->select('user_id')->find_by('email', $meeting['owner']);

			if (empty($owner)) {
				$action_id = $this->mb_project->get_object_id('action', $project_key . '-1');

				$owner_id = $owner->owner_id;
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
					'description' => $meeting['description'],
					'scheduled_start_time' => $meeting['scheduled_start_time'],
					'in' => $meeting['in'],
					'in_type' => 'minutes',
					'project_id' => $meeting['project_id'],
					'google_event_id' => $event_id,
					'meeting_key' => $this->mb_project->get_next_key($project_key . '-1')
				];

				$meeting_id = $this->meeting_model->skip_validation(true)->insert($meeting_data);

				if ($meeting_id === false) {
					$error = true;
				} else {
					$this->load->library('invite/invitation');

					$in_system_users = $this->user_model->select('email')->where_in('user_id, email', $user_emails)->as_array()->find_all();
					if (empty($in_system_users)) $in_system_users = [];

					$in_system_emails = array_column($in_system_users, 'email');

					$meeting_users = [];
					foreach ($user_emails as $email) {
						if ($email != $this->current_user->email) {
							$member_data[] = [
								'meeting_id' => $meeting_id,
								'invite_email' => $email,
								'invite_code' => $this->invitation->generateRandomString(64),
							];

							if (! in_array($email, $in_system_emails)) {
								$temp_user_id = $this->user_model->insert([
									'email' => $email,
									'is_temporary' => 1,
								]);
							}

							$meeting_users[] = [
								'user_id' => $temp_user_id,
								'email' => $email
							];

							$meeting_users = array_merge($meeting_users, $in_system_users);
						} else {
							$this->meeting_member_model->insert([
								'meeting_id' => $meeting_id,
								'user_id' => $this->current_user->user_id
							]);
						}
					}

					$this->meeting_member_invite_model->insert_batch($member_data);
					$this->mb_project->invite_emails($meeting_id, 'meeting', $this->current_user, $user_emails);

					$meeting_data['meeting_id'] = $meeting_id;
					if ($data['path'] == 'owner') {
						$this->init_create_objects([
							'goal' => empty($meeting['goal']) ? [] : $meeting['goal'],
							'homework' => empty($meeting['homework']) ? [] : $meeting['homework'],
							'agenda' => empty($meeting['agenda']) ? [] : $meeting['agenda'],
						], $meeting_data, $meeting_users);
					}

					if ($data['path'] == 'guest') {
						$this->init_rate_objects($meeting['rate'], $meeting_data, $meeting_users);
					}
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

	public function init_create_objects($objects, $meeting_data, $meeting_users)
	{
		if (! empty($objects)) {
			foreach ($objects as $type => $object_items) {
				foreach ($object_items as $item) {
					if ($type == 'goal') {
						$data = [
							'meeting_id' => $meeting_data['meeting_id'],
							'type' => $item['type'],
							'importance' => $item['importance']
						];
					}

					if ($type == 'agenda') {
						$data = [
							'meeting_id' => $meeting_data['meeting_id'],
							'agenda_key' => $this->mb_project->get_next_key($meeting_data['meeting_key']),
							'name' => $item['name'],
							'owner_id' => $this->current_user->user_id
						];
					}

					if ($type == 'homework') {
						$data = [
							'meeting_id' => $meeting_data['meeting_id'],
							'name' => $item['name'],
							'time_spent' => $item['time_spent'],
							'created_by' => $this->current_user->user_id
						];
					}

					$item_id = $this->{$type . '_model'}->insert($data);

					if ($type != 'goal') {
						$object_members = [];
						foreach ($item['assignees'] as $assignee_email) {
							$index = array_search($assignee_email, array_column($meeting_users, 'email'));
							if ($meeting_users[$index]['user_id'] != $meeting_data['owner_id']) {
								$assignee_id = $meeting_users[$index]['user_id'];
								$object_members[] = [
									'user_id' => $assignee_id,
									$type . '_id' => $item_id
								];
							}
						}

						if (! empty($object_members)) {
							$this->{$type . '_members_model'}->insert_batch($object_members);
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
			'name' => 'default agenda',
			'description' => 'default agenda for rating in init process',
			'time_spent' => 0,
			'created_by' => $meeting_data['owner_id']
		];
		$default_homework_id = $this->agenda_model->insert($default_agenda_data);

		$default_agenda_members_data = [];
		$default_homework_members_data = [];

		$meeting_rated == false;
		foreach ($meeting_users as $user) {
			if ($user['email'] != $meeting_data['owner_id']) {
				$default_agenda_members_data[] = [
					'user_id' => $user['user_id'],
					'agenda_id' => $default_agenda_id
				];

				$default_homework_members_data[] = [
					'user_id' => $user['user_id'],
					'agenda_id' => $default_homework_id
				];

				if ($user['email'] == $this->current_user->email) {
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

					if (! $meeting_rated) {
						$meeting_rated = true;
						$this->meeting_member_model->skip_validation(true)
												->where('meeting_id', $meeting_data['meeting_id'])
												->update_where('user_id', $this->current_user->user_id, ['rate' => $object_rate['meeting']]);
					}
				}
			}
		}

		if (! empty($default_agenda_members_data)) {
			$this->agenda_member_models->insert($default_agenda_members_data);
		}

		if (! empty($default_homework_members_data)) {
			$this->homework_member_models->insert($default_homework_members_data);
		}
	}

	public function upload()
	{
		dump($_FILES);
		Template::render();
	}
}