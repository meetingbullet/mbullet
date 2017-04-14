<?php defined('BASEPATH') || exit('No direct script access allowed');

class Projects extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('projects');
		$this->load->helper('mb_form_helper');
		$this->load->model('project_model');
		$this->load->model('project_constraint_model');
		$this->load->model('project_expectation_model');
	}

	public function index()
	{
		Template::render();
	}

	public function create()
	{
		if (isset($_POST['save'])) {
			if ($this->save_project()) {
				Template::set_message(lang('pj_project_successfully_created'), 'success');
			} else {
				Template::set_message(lang('pj_failed_to_create_project'), 'danger');
			}
		}

		Template::render();
	}

	private function save_project($type = 'insert')
	{
		$data = $this->input->post();
		$project_data = $this->project_model->prep_data($data);

		if ($type == 'insert') {
			$project_data['owner'] = $project_data['created_by'] = $this->current_user->user_id;

			$project_id = $this->project_model->insert($project_data);
			$data['contraints']['project_id'] = $project_id;
			$data['expectations']['project_id'] = $project_id;

			$this->project_constraint_model->insert($data['contraints']);
			$this->project_expectation_model->insert($data['expectations']);
			return true;
		} else {

		}

		return false;
	}

	public function detail($project_key = null)
	{
		if (! class_exists('Project_model')) {
			$this->load->model('Project_model');
		}

		if (! class_exists('Role_model')) {
			$this->load->model('roles/Role_model');
		}

		/***************** PROJECT AND USER CHECK *****************/
		// // check user is organization owner or not
		// $is_owner = $this->role_model->where('role_id', $this->current_user->role_ids[$this->current_user->current_organization_id])
		// 							->count_by('is_public', 1) == 1 ? true : false;
		// // get project id
		// if ($is_owner) {
		// 	$project = $this->project_model->select('pm.project_id, projects.name')
		// 								->where('projects.organization_id', $this->current_user->current_organization_id)
		// 								->find_by('projects.project_key', $project_key);
		// } else {
		// 	$project = $this->project_model->select('pm.project_id, projects.name')
		// 								->join('project_members pm', 'pm.project_id = projects.projet_id', 'inners')
		// 								->where('projects.organization_id', $this->current_user->current_organization_id)
		// 								->where('pm.user_id', $this->current_user->user_id)
		// 								->find_by('projects.project_key', $project_key);
		// }

		// if (! empty($project)) {
		// 	$project_id = $project->project_id;
		// }

		$project_id = 1; // test
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
		$all_actions = $this->db->select('a.action_key, a.name, a.status')
								->from('actions a')
								->where('a.project_id', $project_id)
								->order_by('a.created_on', 'desc')
								->get()->result();
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

		Template::set('action_tab_data', [
			'actions' => [
				'open' => $open,
				'inprogress' => $inprogress,
				'ready' => $ready,
				'resolved' => $resolved
			]
		]);

		/*---------------------------------- Report TAB ----------------------------------*/
		Template::set('report_tab_data', []);

		Assets::add_module_css('projects', 'projects.css');
		Assets::add_module_js('projects', 'projects.js');
		Template::set('project_name', 'Project test'/*$project->name*/);
		Template::set('project_key', $project_key);
		Template::render();
	}
}