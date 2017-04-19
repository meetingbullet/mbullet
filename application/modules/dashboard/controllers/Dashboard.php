<?php defined('BASEPATH') || exit('No direct script access allowed');

class Dashboard extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('projects/project_model');

		Assets::add_module_js('dashboard', 'dashboard.js');
	}

	public function index()
	{
		$projects = $this->project_model->select('projects.*, u.first_name, u.last_name')
										->join('users u', 'u.user_id = projects.owner_id')
										->where('owner_id', $this->current_user->user_id)
										->or_where('created_by', $this->current_user->user_id)
										->find_all();

		Template::set('projects', $projects);
		Template::render();
	}
}