<?php defined('BASEPATH') || exit('No direct script access allowed');

class Step extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->lang->load('step');
		$this->load->helper('mb_form');
		$this->load->helper('mb_general');
		$this->load->model('step_model');
		$this->load->model('action/action_model');
		$this->load->model('action/action_member_model');
		$this->load->model('projects/project_model');
		$this->load->model('users/user_model');

		Assets::add_module_css('step', 'step.css');
		Assets::add_module_js('step', 'step.js');
	}

	public function index()
	{
		Template::render();
	}

	public function create($action_key = null)
	{

		if (empty($action_key)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$action = $this->action_model->select('action_id')
									->join('projects p', 'actions.project_id = p.project_id')
									->join('user_to_organizations uto', 'uto.organization_id = p.organization_id AND uto.user_id = ' . $this->current_user->user_id)
									->limit(1)
									->find_by('action_key', $action_key);

		if ($action === false) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$oragnization_members = $this->user_model->get_organization_members($this->current_user->current_organization_id);
		Assets::add_js($this->load->view('create_js', [
			'oragnization_members' => $oragnization_members
		], true), 'inline');

		if ($data = $this->input->post()) {
			$data = $this->step_model->prep_data($data);
			$data['action_id'] = $action->action_id;
			$data['created_by'] = $this->current_user->user_id;

			$this->load->library('key');
			$data['step_key'] = 'TEST_KEY';

			if ($id = $this->step_model->insert($data)) {
				Template::set('close_modal', 1);
				Template::set('message_type', 'success');
				Template::set('message', lang('st_step_successfully_created'));

				// Just to reduce AJAX request size
				if ($this->input->is_ajax_request()) {
					Template::set('content', '');
				}
				
			} else {
				Template::set('close_modal', 0);
				Template::set('message_type', 'danger');
				Template::set('message', lang('st_there_was_a_problem_while_creating_step'));
			}

			Template::render();
			return;
		}


		

		Template::set('oragnization_members', $oragnization_members);
		Template::set('action_key', $action_key);
		Template::render();
	}
}