<?php defined('BASEPATH') || exit('No direct script access allowed');

class Invite extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('invite');
		$this->load->library('invitation');
		$this->load->library('domain');
		$this->load->helper('mb_general_helper');
		$this->load->model('user_invite_model');
		$this->load->model('user_to_organizations_model');
		$this->load->model('project/project_member_invite_model');
		$this->load->model('project/project_member_model');
		Assets::add_module_js('admin', 'invite.js');
	}

	public function confirm($invite_code = '')
	{
		$invite_code = urldecode($invite_code);

		if (empty($invite_code)) {
			Template::set_message(lang('iv_invalid_invitation_code_or_already_used'), 'danger');
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


		if (isset($_POST['accept'])) {
			$this->user_invite_model->update_where('invite_code', $invite_code, ['status' => 'accepted', 'used' => 1]);
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

	public function confirm_project($invite_code = null, $action = null)
	{
		if ($invite_code == null || $action == null || ! in_array($action, ['accept', 'decline'])) {
			Template::set_message(lang('iv_invalid_invitation_code_or_already_used'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$this->db->where("BINARY invite_code = '$invite_code'", null, false);

		$invitation = $this->project_member_invite_model
		->select('project_member_invite_id, p.project_id, cost_code, r.role_id, p.organization_id, o.organization_id')
		->join('projects p', 'p.project_id = project_member_invites.project_id')
		->join('organizations o', 'o.organization_id = p.organization_id')
		->join('roles r', 'r.organization_id = o.organization_id')
		->where('project_member_invites.status', 'pending')
		->where('r.join_default', 1)
		->find_by('invite_email', $this->current_user->email);

		if ($invitation === false) {
			Template::set_message(lang('iv_invalid_invitation_code_or_already_used'), 'danger');
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if ($action == 'accept') {
			if ($this->project_member_invite_model->update(
				$invitation->project_member_invite_id, 
				['status' => 'accepted'])) {

				// Add to Ogranization
				$this->user_to_organizations_model->insert([
					'user_id' => $this->current_user->user_id,
					'organization_id' => $invitation->organization_id,
					'role_id' => $invitation->role_id
				]);

				// Add to Project member
				$query = $this->db->insert_string('project_members', [
					'project_id' => $invitation->project_id,
					'user_id' => $this->current_user->user_id
				]);

				$query = str_replace('INSERT', 'INSERT IGNORE', $query);
				$query = $this->db->query($query);

				Template::set_message(lang('iv_invitation_accepted'), 'success');
				redirect($this->domain->get_organization_url($invitation->organization_id) . 
				'/project/' . $invitation->cost_code);
			} else {
				Template::set_message(lang('iv_unknown_error'), 'danger');
			}
		} else {
			if ($this->project_member_invite_model->update(
				$invitation->project_member_invite_id, 
				['status' => 'declined'])) {
				Template::set_message(lang('iv_invitation_declined'), 'danger');
			} else {
				Template::set_message(lang('iv_unknown_error'), 'danger');
			}
		}

		redirect(DEFAULT_LOGIN_LOCATION);
	}
}



