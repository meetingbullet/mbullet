<?php defined('BASEPATH') || exit('No direct script access allowed');

class Invite extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('invite');
		$this->load->library('invitation');
		$this->load->helper('mb_general_helper');
		$this->load->model('user_invite_model');
		$this->load->model('user_to_organizations_model');
		Assets::add_module_js('admin', 'invite.js');
	}

	public function confirm($invite_code = '')
	{
		$invite_code = urldecode($invite_code);

		if (empty($invite_code)) {
			Template::set_message(lang('iv_invalid_invitation_code_or_already_used') . $invite_code, 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
			return;
		}

		$this->user_invite_model
				->select('o.name AS organization_name, CONCAT(u.first_name, " ", u.last_name) AS inviter_name, 
							u.avatar AS inviter_avatar, u1.avatar AS my_avatar, 
							u.email AS inviter_email, u1.email AS my_email, 
							invite_role, o.organization_id, status,
							IF(uto.organization_id IS NULL, 0, 1) AS is_in_current_organization', false)
				->join('organizations o', 'user_invite.organization_id = o.organization_id')
				->join('users u', 'user_invite.invited_by = u.user_id')
				->join('users u1', 'u1.user_id = ' . $this->current_user->user_id)
				->join('user_to_organizations uto', 'user_invite.organization_id = uto.organization_id AND uto.user_id = ' . $this->current_user->user_id, 'LEFT')
				->where('invited_by !=', $this->current_user->user_id)
				->where('status', 'pending');
		
		// Invite code is CASE SeNsiTiVe
		$invitation = $this->db->where("BINARY invite_code = '$invite_code'", null, false)
								->limit(1)
								->get('user_invite')
								->row();

		if (! $invitation || $invitation->is_in_current_organization == TRUE) {
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