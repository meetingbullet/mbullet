<?php defined('BASEPATH') || exit('No direct script access allowed');
// before each method need to verify user
class Projects extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('projects');
		$this->load->library('form_validation');
		$this->load->library('invite/invitation');
		$this->load->helper('mb_form_helper');
		$this->load->model('users/user_model');
		$this->load->model('project_model');
		$this->load->model('project_constraint_model');
		$this->load->model('project_expectation_model');
		$this->load->model('project_member_model');

		Assets::add_module_js('projects', 'projects.js');
	}

	public function _remap($method, $params = array())
	{
		if (method_exists($this, $method))
		{
			return call_user_func_array(array($this, $method), $params);
		} else {
			$this->detail($method);
		}
	}

	public function index()
	{
		Template::render();
	}

	public function create()
	{
		// Get invite emails
		Template::set('invite_emails', $this->user_model->get_organization_members($this->current_user->current_organization_id));

		if (isset($_POST['save'])) {
			if ($this->save_project()) {
				Template::set('close_modal', 1);
				Template::set('message_type', 'success');
				Template::set('message', lang('pj_project_successfully_created'));

				// Just to reduce AJAX request size
				if ($this->input->is_ajax_request()) {
					Template::set('content', '');
				}
				
				Template::render();
				return;
			} else {
				Template::set('close_modal', 0);
				Template::set('message_type', 'danger');
				Template::set('message', lang('pj_there_was_a_problem_while_creating_project'));
				Template::render();
				return;
			}
		}

		Template::set('close_modal', 0);
		Template::set('message_type', null);
		Template::set('message', '');
		Template::render();
	}

	private function save_project($type = 'insert')
	{
		$data = $this->input->post();
		$project_data = $this->project_model->prep_data($data);

		$constraint_rules = $this->project_constraint_model->project_validation_rules;
		foreach ($constraint_rules as &$rule) {
			$rule['field'] = "constraints[{$rule['field']}]";
		}

		$expectation_rules = $this->project_expectation_model->project_validation_rules;
		foreach ($expectation_rules as &$rule) {
			$rule['field'] = "expectations[{$rule['field']}]";
		}

		$this->form_validation->set_rules(array_merge(
			$this->project_model->project_validation_rules,
			$constraint_rules,
			$expectation_rules
		));

		if ($this->form_validation->run() === false) {
			return false;
		}


		if ($type == 'insert') {
			$project_data['organization_id'] = $this->current_user->current_organization_id;
			$project_data['owner_id'] = $project_data['created_by'] = $this->current_user->user_id;

			$project_id = $this->project_model->insert($project_data);

			if ($project_id === false) {
				return false;
			}

			$data['constraints']['project_id'] = $project_id;
			$data['expectations']['project_id'] = $project_id;

			$this->project_constraint_model->insert($data['constraints']);
			$this->project_expectation_model->insert($data['expectations']);

			/*
				For now, we're going to add invited members immediately into project members
				because all of their account is already created and is in inviter's organization

				We need to point the unregistered emails to the "User invite" after functionality is finished.
			*/

			$project_members = [];
			$project_members[$this->current_user->user_id] = [
					'project_id' => $project_id,
					'user_id' => $this->current_user->user_id
			];

			$invited_team = $this->input->post('invite_team');
			$invited_team = explode(',', $invited_team);

			$registered_users = $this->user_model->select('users.user_id, email')
									->join('user_to_organizations uto', 'users.user_id = uto.user_id AND enabled = 1 AND organization_id = ' . $this->current_user->current_organization_id, 'RIGHT')
									->where_in('email', $invited_team)
									->find_all();

			foreach ($invited_team as $email) {
				foreach ($registered_users as $user) {
					$is_found = false;

					if ($user->email == $email) {
						$project_members[$user->user_id] = [
							'project_id' => $project_id,
							'user_id' => $user->user_id
						];

						$is_found = true;
						break;
					}

					// Invite to the party
					if ( ! $is_found) {
						// $this->invitation->generate($email, $this->current_user);
					}
				}
			}

			$this->project_member_model->insert_batch($project_members);
		} else {

		}

		return true;
	}

	public function detail($project_key = null)
	{
		/***************** PROJECT AND USER CHECK *****************/
		if ($project_key == null) {
			redirect('/dashboard');
		}

		$project_id = $this->project_model->get_project_id($project_key, $this->current_user);
		if ($project_id === false) {
			redirect('/dashboard');
		}

		// $project_id = 1; // test
		/*---------------------------------- INFO TAB ----------------------------------*/
		/***************** PAGINATION *****************/
		$this->load->library('pagination');
		// general pagination config
		$pagination_config = [
			'base_url' => current_url(),
			'per_page' => 5,
			'use_page_numbers' => true,
			'page_query_string' => true,
			'reuse_query_string' => true,
			'enable_query_strings' => true,
			'full_tag_open' => '<ul class="pagination">',
			'full_tag_close' => '</ul>',
			'num_tag_open' => '<li>',
			'num_tag_close' => '</li>',
			'cur_tag_open' => '<li><a class="active">',
			'cur_tag_close' => '</a></li>',
			'prev_link' => '<span aria-hidden="true"><i class="ion-chevron-left"></i></span>',
			'prev_tag_open' => '<li>',
			'prev_tag_close' => '</li>',
			'next_link' => '<span aria-hidden="true"><i class="ion-chevron-right"></i></span>',
			'next_tag_open' => '<li>',
			'next_tag_close' => '</li>',
			'last_link' => lang('pj_info_pager_last'),
			'last_tag_open' => '<li>',
			'last_tag_close' => '</li>',
			'first_link' => lang('pj_info_pager_first'),
			'first_tag_open' => '<li>',
			'first_tag_close' => '</li>'
		];

		// pagination for actions
		$config_actions = $pagination_config;
		$config_actions['query_string_segment'] = 'actions_page';
		$config_actions['total_rows'] = $this->db->select('count(*) as count')
												->from('actions a')
												->where('a.project_id', $project_id)
												->get()->row()->count;
		$this->pagination->initialize($config_actions);
		// generate links
		$actions_links = $this->pagination->create_links();

		// pagination for steps
		$config_steps = $pagination_config;
		$config_steps['query_string_segment'] = 'steps_page';
		$config_steps['total_rows'] = $this->db->select('count(*) as count')
											->from('actions a')
											->join('steps s', 'a.action_id = s.action_id', 'left')
											->where('a.project_id', $project_id)
											->get()->row()->count;
		$this->pagination->initialize($config_steps);
		// generate links
		$steps_links = $this->pagination->create_links();

		// pagination for tasks
		$config_tasks = $pagination_config;
		$config_tasks['query_string_segment'] = 'tasks_page';
		$config_tasks['total_rows'] = $this->db->select('count(*) as count')
											->from('actions a')
											->join('steps s', 'a.action_id = s.action_id', 'left')
											->join('tasks t', 's.step_id = t.step_id', 'left')
											->where('a.project_id', $project_id)
											->get()->row()->count;
		$this->pagination->initialize($config_tasks);
		// generate links
		$tasks_links = $this->pagination->create_links();

		/***************** GET DATA *****************/
		// get actions current page
		$actions_current_page = 1;
		if (! empty($this->input->get('actions_page'))) {
			$actions_current_page = $this->input->get('actions_page');
		}
		// get actions list
		$actions = $this->db->select('a.action_key, a.name, a.status')
							->from('actions a')
							->where('a.project_id', $project_id)
							->order_by('a.created_on', 'desc')
							->limit($pagination_config['per_page'], ($actions_current_page - 1) * $pagination_config['per_page'])
							->get()->result();

		// get steps current page
		$steps_current_page = 1;
		if (! empty($this->input->get('steps_page'))) {
			$steps_current_page = $this->input->get('steps_page');
		}
		// get steps list
		$steps = $this->db->select('a.action_key, s.step_key, s.name, s.status')
							->from('actions a')
							->join('steps s', 'a.action_id = s.action_id', 'left')
							->where('a.project_id', $project_id)
							->order_by('s.created_on', 'desc')
							->limit($pagination_config['per_page'], ($steps_current_page - 1) * $pagination_config['per_page'])
							->get()->result();

		// get tasks current page
		$tasks_current_page = 1;
		if (! empty($this->input->get('tasks_page'))) {
			$tasks_current_page = $this->input->get('tasks_page');
		}
		// get tasks list
		$tasks = $this->db->select('a.action_key, s.step_key, t.task_key, t.name, t.status')
							->from('actions a')
							->join('steps s', 'a.action_id = s.action_id', 'left')
							->join('tasks t', 's.step_id = t.step_id', 'left')
							->where('a.project_id', $project_id)
							->order_by('t.created_on', 'desc')
							->limit($pagination_config['per_page'], ($tasks_current_page - 1) * $pagination_config['per_page'])
							->get()->result();

		Template::set('info_tab_data', [
			'paginations' => [
				'actions' => $actions_links,
				'steps' => $steps_links,
				'tasks' => $tasks_links,
			],
			'lists' => [
				'actions' => $actions,
				'steps' => $steps,
				'tasks' => $tasks,
			]
		]);

		/*---------------------------------- Action TAB ----------------------------------*/
		Template::set('action_tab_data', [
			'actions' => $this->get_actions($project_id)
		]);

		/*---------------------------------- Report TAB ----------------------------------*/
		Template::set('report_tab_data', []);

		Assets::add_module_css('action', 'action.css');
		Assets::add_module_js('action', 'action.js');
		Assets::add_module_css('projects', 'projects.css');
		Assets::add_module_js('projects', 'action_board.js');
		Template::set('project_name', $this->project_model->get_field($project_id, 'name'));
		Template::set('project_key', $project_key);
		Template::set_view('detail');
		Template::render();
	}

	public function sort_action($project_key = null)
	{
		if (! $this->input->is_ajax_request()) {
			redirect('/dashboard');
		}

		$project_id = $this->project_model->get_project_id($project_key, $this->current_user);

		// $project_id = 1; // test
		$action_id = trim($this->input->get('action_id'));
		$status = trim($this->input->get('status'));
		$status_order = trim($this->input->get('status_order'));

		if (empty($project_id) || $action_id == '' || $status == '' || $status_order == '') {
			echo json_encode([
				'status' => '0',
				'message' => 'failed at position 1'
			]);
			exit;
		}

		$action = $this->db->select('status, sort_order')
						->where('project_id', $project_id)
						->where('action_id', $action_id)
						->get('actions')->row();
		if (! $action) {
			echo json_encode([
				'status' => '0',
				'message' => 'failed at position 2'
			]);
			exit;
		}

		try {
			$this->db->trans_begin();

			$old_status_order_updated = $this->db->where('action_id !=', $action_id)
												->where('status', $action->status)
												->where('sort_order >=', $action->sort_order)
												->set('sort_order', '`sort_order`-1', false)
												->set('modified_on', date('Y-m-d H:i:s'))
												->update('actions');
			if (! $old_status_order_updated) {
				throw new Exception('failed at position 3');
			}

			$new_status_order_updated = $this->db->where('action_id !=', $action_id)
												->where('status', $status)
												->where('sort_order >=', $status_order)
												->set('sort_order', '`sort_order`+1', false)
												->set('modified_on', date('Y-m-d H:i:s'))
												->update('actions');
			if (! $new_status_order_updated) {
				throw new Exception('failed at position 4');
			}

			$action_updated = $this->db->where('action_id', $action_id)
									->update('actions', [
										'status' => $status,
										'sort_order' => $status_order,
										'modified_on' => date('Y-m-d H:i:s'),
									]);
			if (! $action_updated) {
				throw new Exception('failed at position 5');
			}

			if ($this->db->trans_status() === FALSE) {
				throw new Exception('failed at position 6');
			} else {
				$this->db->trans_commit();
			}
		} catch (Exception $e) {
			$this->db->trans_rollback();

			echo json_encode([
				'status' => '0',
				'message' => $e->getMessage()
			]);
			exit;
		}

		echo json_encode([
			'status' => '1',
			'message' => 'success'
		]);
		exit;
	}

	public function get_action_board_data($project_key = null)
	{
		if (! $this->input->is_ajax_request()) {
			redirect('/dashboard');
		}

		$project_id = $this->project_model->get_project_id($project_key, $this->current_user);
		// $project_id = 1; // test
		if ($project_id !== false) {
			$actions = $this->get_actions($project_id);
		} else {
			$actions = [
				'open' => [],
				'inprogress' => [],
				'ready' => [],
				'resolved' => []
			];
		}

		echo json_encode($actions);
		exit;
	}

	public function get_members($project_key = null)
	{
		if (! $this->input->is_ajax_request()) {
			redirect('/dashboard');
		}

		$project_id = $this->project_model->get_project_id($project_key, $this->current_user);
		if ($project_id !== false) {
			$members = $this->db->select('u.user_id, CONCAT(u.first_name, u.last_name) as full_name')
								->from('users u')
								->join('project_members pm', 'u.user_id = pm.user_id', 'inner')
								->like('CONCAT(u.first_name, u.last_name)', $this->input->get('member_name'))
								->where('pm.project_id', $project_id)
								->get()->result();
			$result = $members;
		} else {
			$result = [];
		}

		echo json_encode($result);
		exit;
	}

	private function get_actions($project_id)
	{
		// get all project actions, sort by sort order
		$all_actions = $this->db->select('a.action_id, a.action_key, a.name, a.status, IF (a.modified_on IS NULL, a.created_on, a.modified_on) AS sort_time')
								->from('actions a')
								->where('a.project_id', $project_id)
								->order_by('a.sort_order', 'asc')
								->order_by('sort_time', 'desc')
								->get()->result();
		// filter actions by status
		$open = [];
		$inprogress = [];
		$ready = [];
		$resolved = [];
		foreach ($all_actions as $action) {
			switch ($action->status) {
				case 'inprogress':
					$inprogress[] = $action;
					break;
				case 'ready':
					$ready[] = $action;
					break;
				case 'resolved':
					$resolved[] = $action;
					break;
				default:
					$open[] = $action;
			}
		}

		$actions = [
			'open' => $open,
			'inprogress' => $inprogress,
			'ready' => $ready,
			'resolved' => $resolved
		];

		return $actions;
	}

	// private function get_project_id($project_key)
	// {
	// 	if (! class_exists('Project_model')) {
	// 		$this->load->model('Project_model');
	// 	}

	// 	if (! class_exists('Role_model')) {
	// 		$this->load->model('roles/role_model');
	// 	}
	// 	// check user is organization owner or not
	// 	$is_owner = $this->role_model->where('role_id', $this->current_user->role_ids[$this->current_user->current_organization_id])
	// 								->count_by('is_public', 1) == 1 ? true : false;
	// 	// get project id
	// 	if ($is_owner) {
	// 		$project = $this->project_model->select('project_id, projects.name')
	// 									->where('projects.organization_id', $this->current_user->current_organization_id)
	// 									->find_by('projects.cost_code', $project_key);
	// 	} else {
	// 		$project = $this->project_model->select('pm.project_id, projects.name')
	// 									->join('project_members pm', 'pm.project_id = projects.projet_id', 'inner')
	// 									->where('projects.organization_id', $this->current_user->current_organization_id)
	// 									->where('pm.user_id', $this->current_user->user_id)
	// 									->find_by('projects.cost_code', $project_key);
	// 	}

	// 	if (! empty($project)) {
	// 		return $project->project_id;
	// 	}

	// 	return false;
	// }
}