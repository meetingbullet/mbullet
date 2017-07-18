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
		$data = $this->input->get('data');

		// $data = json_encode([
		// 	'current_step' => 4,
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

		if (empty($data) || empty($data['current_step']) || $data['current_step'] < 4) {
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
		$data = $this->input->get('data');

		// $data = json_encode([
		// 	'current_step' => 5,
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

		if (empty($data) || empty($data['current_step']) || $data['current_step'] < 5) {
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

			if (! isset($users[$meeting['owner']['email']])) {
				$users[$meeting['owner']['email']] = [
					'projects' => [$meeting['project_id']],
					'as_owner' => true
				];
			} else {
				$users[$meeting['owner']['email']]['as_owner'] = true;
				if (! in_array($meeting['project_id'], $users[$meeting['owner']['email']]['projects'])) {
					$users[$meeting['owner']['email']]['projects'][] = $meeting['project_id'];
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
		$data = $this->input->get('data');

		// $data = json_encode([
		// 	'current_step' => 6,
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

		if (empty($data) || empty($data['current_step']) || $data['current_step'] < 6) {
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

			if (! in_array($meeting['owner']['email'], $emails)) {
				$emails[] = $meeting['owner']['email'];
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
}