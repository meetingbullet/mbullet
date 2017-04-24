<?php defined('BASEPATH') || exit('No direct script access allowed');

class Team extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('invite/invite');
		$this->load->helper('mb_form_helper');
		$this->load->library('invite/invitation');
		$this->load->model('invite/user_invite_model');
		$this->load->model('invite/role_model');
		$this->load->model('invite/user_to_organizations_model');
		Assets::add_module_js('admin', 'invite.js');
	}

	public function index()
	{
		Template::render();
	}

	public function invite()
	{
		Template::set('close_modal', 0);
		Template::set('message_type', null);
		Template::set('message', '');

		$roles = $this->role_model->select('role_id, name, description, join_default')->where('organization_id', $this->current_user->current_organization_id)->find_all();
		Template::set('roles', $roles);
		

		if (isset($_POST['add'])) {
			$message = $this->invitation->generate($this->input->post('email'), $this->input->post('invite_role'), $this->current_user);

			if ($message === 1) {
				Template::set('close_modal', 1);
				Template::set('message_type', 'success');
				Template::set('message', lang('iv_invitation_sent'));
				Template::set_message(lang('iv_invitation_sent'), 'success');

				// Just to reduce AJAX request size
				if ($this->input->is_ajax_request()) {
					Template::set('content', '');
				}
			} else {
				Template::set('message_type', 'danger');
				Template::set('message', $message);
				Template::set_message($message, 'danger');
			}
		}

		Template::render();
	}
}