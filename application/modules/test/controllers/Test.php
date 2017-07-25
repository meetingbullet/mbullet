<?php defined('BASEPATH') || exit('No direct script access allowed');

class Test extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('form');
		$this->load->library('users/Auth');
		
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

		$this->load->model('meeting/meeting_model');
		$this->load->model('meeting/meeting_member_model');
		$this->load->model('meeting/meeting_member_rate_model');
		$this->load->model('meeting/meeting_member_invite_model');
		$this->load->model('meeting/meeting_comment_model');
		$this->load->model('meeting/goal_model');

		$this->load->model('action/action_model');
		$this->load->model('action/action_member_model');

		$this->load->model('project/project_model');
		$this->load->model('project/project_member_model');
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
		$this->mb_project->update_parent_object('homework');
	}

	function upload() {
		dump($_FILES);
		Template::render();
	}
}