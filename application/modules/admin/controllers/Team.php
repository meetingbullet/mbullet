<?php defined('BASEPATH') || exit('No direct script access allowed');

class Team extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('invitation');
		$this->load->helper('mb_form_helper');
		$this->lang->load('invite');

		$this->load->model('user_invite_model');
		$this->load->model('role_model');
		$this->load->model('user_to_organizations_model');
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

	public function confirm($invite_code = '')
	{
		$invite_code = !empty($invite_code) ? $invite_code : $this->session->userdata('invite_code');
		$invite_code = urldecode($invite_code);

		if (empty($invite_code)) {
			Template::set_message(lang('iv_invalid_invitation_code_or_already_used') . $invite_code, 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
			return;
		}

		$this->user_invite_model
				->select('o.name AS organization_name, CONCAT(u.first_name, " ", u.last_name) AS inviter_name, invite_role, email, o.organization_id, status', false)
				->join('organizations o', 'user_invite.organization_id = o.organization_id')
				->join('users u', 'user_invite.invited_by = u.user_id')
				->where('status', 'pending');
		
		// Invite code is CASE SeNsiTiVe
		$invitation = $this->db->where("BINARY invite_code = '$invite_code'", null, false)
								->limit(1)
								->get('user_invite')
								->row();
							
		if (! $invitation) {
			Template::set_message(lang('iv_invalid_invitation_code_or_already_used'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
			return;
		}

		$this->user_invite_model->update_where('invite_code', $invite_code, ['used' => '1']);

		if (isset($_POST['accept'])) {
			$this->user_invite_model->update_where('invite_code', $invite_code, ['status' => 'accepted']);
			$this->user_to_organizations_model->insert([
				'user_id' => $this->current_user->user_id,
				'organization_id' => $invitation->organization_id,
				'role_id' => $invitation->invite_role,
			]);

			$this->load->library('domain');
			redirect($this->domain->get_main_url());
		}
		if (isset($_POST['decline'])) {
			$this->user_invite_model->update_where('invite_code', $invite_code, ['status' => 'declined']);
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$this->session->set_userdata('invite_code', NULL);
		Template::set('invitation', $invitation);
		Template::render();
	}
}